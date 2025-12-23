<?php

// Autoload will be handled by composer

use Phpml\Regression\SVR;
use Phpml\Regression\LeastSquares;
use Phpml\Clustering\KMeans;
use Phpml\Preprocessing\Normalizer;
use Phpml\SupportVectorMachine\Kernel;

class MLService
{
    private mysqli $conn;
    private string $modelCacheDir;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
        $this->modelCacheDir = __DIR__ . '/../storage/ml_models/';

        // Create cache directory if it doesn't exist
        if (!is_dir($this->modelCacheDir)) {
            mkdir($this->modelCacheDir, 0755, true);
        }
    }

    /**
     * Predict demand for an inventory item based on historical data
     */
    public function predictDemand(int $itemId, int $daysAhead = 30): array
    {
        // Check if we have a cached prediction
        $cached = $this->getCachedPrediction($itemId, $daysAhead);
        if ($cached !== null) {
            return $cached;
        }

        // Get historical training data
        $trainingData = $this->getTrainingData($itemId);

        if (count($trainingData) < 10) {
            // Not enough data, return default prediction
            return [
                'predicted_demand' => 0,
                'confidence' => 0.0,
                'message' => 'Insufficient historical data for accurate prediction'
            ];
        }

        // Prepare samples and targets for ML
        $samples = [];
        $targets = [];

        foreach ($trainingData as $data) {
            $samples[] = [
                (float) $data['month'],
                (float) $data['day_of_week'],
                $this->getSeasonValue($data['season'] ?? 'spring')
            ];
            $targets[] = (float) $data['quantity'];
        }

        try {
            // Train regression model
            $regression = new LeastSquares();
            $regression->train($samples, $targets);

            // Predict for next period
            $currentMonth = (int) date('n');
            $currentDayOfWeek = (int) date('w');
            $currentSeason = $this->getCurrentSeason();

            $prediction = $regression->predict([
                (float) $currentMonth,
                (float) $currentDayOfWeek,
                $this->getSeasonValue($currentSeason)
            ]);

            // Calculate confidence based on data quality
            $confidence = min(0.95, max(0.5, count($trainingData) / 100));

            // Ensure prediction is non-negative
            $predictedDemand = max(0, (int) round($prediction));

            // Cache the prediction
            $this->cachePrediction($itemId, $predictedDemand, $daysAhead, $confidence);

            return [
                'predicted_demand' => $predictedDemand,
                'confidence' => round($confidence, 2),
                'data_points' => count($trainingData),
                'message' => 'Prediction based on historical patterns'
            ];
        } catch (Exception $e) {
            return [
                'predicted_demand' => 0,
                'confidence' => 0.0,
                'message' => 'Error in prediction: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Detect anomalies in requests
     */
    public function detectAnomaly(array $requestData): array
    {
        $itemId = $requestData['item_id'] ?? 0;
        $quantity = $requestData['quantity'] ?? 0;
        $doctorId = $requestData['doctor_id'] ?? 0;
        $excludeRequestId = $requestData['request_id'] ?? 0;

        // Get historical request patterns for this item
        $historicalRequests = $this->getHistoricalRequests($itemId, $excludeRequestId);

        if (count($historicalRequests) < 2) {
            return [
                'is_anomaly' => false,
                'score' => 0.0,
                'reason' => 'Need at least 2 historical requests for comparison'
            ];
        }

        // Calculate statistics
        $quantities = array_column($historicalRequests, 'quantity');
        $mean = array_sum($quantities) / count($quantities);
        $variance = $this->calculateVariance($quantities, $mean);
        $stdDev = sqrt($variance);

        // Z-score calculation
        if ($stdDev > 0) {
            $zScore = abs(($quantity - $mean) / $stdDev);
        } else {
            // Handle zero variance case: if quantity is different, it's an anomaly
            $zScore = ($quantity != $mean) ? 3.0 : 0.0;
        }

        // Check for unusual patterns
        $isAnomaly = false;
        $anomalyScore = 0.0;
        $reasons = [];

        // High quantity anomaly (more than 2 standard deviations)
        if ($zScore > 2.0) {
            $isAnomaly = true;
            $anomalyScore = min(1.0, $zScore / 4.0);

            // Boost score if it's a very clear outlier but mathematically low score
            if ($zScore > 2.5 && $anomalyScore < 0.7) {
                $anomalyScore = 0.75;
            }

            $reasons[] = "Unusually high quantity requested (Z-score: " . round($zScore, 2) . ")";
        }

        // Check for unusual doctor-item combination
        $doctorItemCount = $this->getDoctorItemRequestCount($doctorId, $itemId);
        $totalItemRequests = count($historicalRequests);
        $doctorItemRatio = $totalItemRequests > 0 ? $doctorItemCount / $totalItemRequests : 0;

        if ($doctorItemRatio > 0.5 && $totalItemRequests > 10) {
            $isAnomaly = true;
            $anomalyScore = max($anomalyScore, 0.6);
            $reasons[] = "Unusual pattern: This doctor requests this item frequently";
        }

        return [
            'is_anomaly' => $isAnomaly,
            'score' => round($anomalyScore, 4),
            'z_score' => round($zScore, 2),
            'mean' => round($mean, 2),
            'std_dev' => round($stdDev, 2),
            'reasons' => $reasons
        ];
    }

    /**
     * Get optimal reorder quantity based on ML predictions
     */
    public function getOptimalReorderQuantity(int $itemId, int $currentStock, int $minStock): array
    {
        $prediction = $this->predictDemand($itemId, 30);
        $predictedDemand = $prediction['predicted_demand'] ?? 0;

        // Calculate optimal stock level (predicted demand + safety stock)
        $safetyStock = max($minStock, (int) ($predictedDemand * 0.3));
        $optimalStock = $predictedDemand + $safetyStock;

        // Calculate reorder quantity
        $reorderQuantity = max(0, $optimalStock - $currentStock);

        return [
            'current_stock' => $currentStock,
            'min_stock' => $minStock,
            'predicted_demand' => $predictedDemand,
            'optimal_stock' => $optimalStock,
            'recommended_reorder' => $reorderQuantity,
            'confidence' => $prediction['confidence'] ?? 0.0
        ];
    }

    /**
     * Train models using historical data
     */
    public function trainModels(): array
    {
        $results = [];

        // Get all items
        $items = $this->conn->query("SELECT id FROM inventory");

        while ($row = $items->fetch_assoc()) {
            $itemId = $row['id'];

            // Collect training data from requests
            $this->collectTrainingData($itemId);

            // Train and cache model
            $prediction = $this->predictDemand($itemId);

            $results[] = [
                'item_id' => $itemId,
                'status' => $prediction['predicted_demand'] > 0 ? 'trained' : 'insufficient_data',
                'data_points' => $prediction['data_points'] ?? 0
            ];
        }

        return $results;
    }

    /**
     * Collect training data from historical requests
     */
    private function collectTrainingData(int $itemId): void
    {
        // Get historical requests for this item
        $stmt = $this->conn->prepare(
            "SELECT quantity, requested_date, item_id 
             FROM requests 
             WHERE item_id = ? 
             ORDER BY requested_date DESC 
             LIMIT 1000"
        );

        if (!$stmt) {
            return;
        }

        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Get item category
        $itemStmt = $this->conn->prepare("SELECT category FROM inventory WHERE id = ?");
        $itemStmt->bind_param('i', $itemId);
        $itemStmt->execute();
        $itemResult = $itemStmt->get_result();
        $item = $itemResult->fetch_assoc();
        $category = $item['category'] ?? 'Unknown';

        while ($row = $result->fetch_assoc()) {
            $requestDate = new DateTime($row['requested_date']);
            $month = (int) $requestDate->format('n');
            $dayOfWeek = (int) $requestDate->format('w');
            $season = $this->getSeasonFromMonth($month);

            // Check if training data already exists
            $checkStmt = $this->conn->prepare(
                "SELECT id FROM ml_training_data 
                 WHERE item_id = ? AND month = ? AND day_of_week = ? 
                 AND DATE(created_at) = DATE(?)"
            );
            $checkStmt->bind_param('iiis', $itemId, $month, $dayOfWeek, $row['requested_date']);
            $checkStmt->execute();

            if ($checkStmt->get_result()->num_rows === 0) {
                // Insert training data
                $insertStmt = $this->conn->prepare(
                    "INSERT INTO ml_training_data (item_id, quantity, month, day_of_week, season, category) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $insertStmt->bind_param(
                    'iiiiss',
                    $itemId,
                    $row['quantity'],
                    $month,
                    $dayOfWeek,
                    $season,
                    $category
                );
                $insertStmt->execute();
                $insertStmt->close();
            }

            $checkStmt->close();
        }

        $stmt->close();
        $itemStmt->close();
    }

    /**
     * Get training data for an item
     */
    private function getTrainingData(int $itemId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT quantity, month, day_of_week, season, category 
             FROM ml_training_data 
             WHERE item_id = ? 
             ORDER BY created_at DESC 
             LIMIT 500"
        );

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    /**
     * Get historical requests for an item
     */
    private function getHistoricalRequests(int $itemId, int $excludeRequestId = 0): array
    {
        $query = "SELECT quantity, doctor_id FROM requests WHERE item_id = ?";
        if ($excludeRequestId > 0) {
            $query .= " AND id != ?";
        }
        $query .= " ORDER BY requested_date DESC LIMIT 100";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        if ($excludeRequestId > 0) {
            $stmt->bind_param('ii', $itemId, $excludeRequestId);
        } else {
            $stmt->bind_param('i', $itemId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    /**
     * Get cached prediction
     */
    private function getCachedPrediction(int $itemId, int $daysAhead): ?array
    {
        $predictedDate = date('Y-m-d', strtotime("+{$daysAhead} days"));

        $stmt = $this->conn->prepare(
            "SELECT predicted_demand, confidence 
             FROM ml_predictions 
             WHERE item_id = ? AND predicted_date = ? 
             ORDER BY created_at DESC 
             LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('is', $itemId, $predictedDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            return [
                'predicted_demand' => (int) $row['predicted_demand'],
                'confidence' => (float) $row['confidence']
            ];
        }

        return null;
    }

    /**
     * Cache prediction
     */
    private function cachePrediction(int $itemId, int $predictedDemand, int $daysAhead, float $confidence): void
    {
        $predictedDate = date('Y-m-d', strtotime("+{$daysAhead} days"));

        $stmt = $this->conn->prepare(
            "INSERT INTO ml_predictions (item_id, predicted_demand, predicted_date, confidence) 
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
             predicted_demand = VALUES(predicted_demand),
             confidence = VALUES(confidence),
             created_at = CURRENT_TIMESTAMP"
        );

        if ($stmt) {
            $stmt->bind_param('iisd', $itemId, $predictedDemand, $predictedDate, $confidence);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Get doctor-item request count
     */
    private function getDoctorItemRequestCount(int $doctorId, int $itemId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count 
             FROM requests 
             WHERE doctor_id = ? AND item_id = ?"
        );

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('ii', $doctorId, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int) ($row['count'] ?? 0);
    }

    /**
     * Calculate variance
     */
    private function calculateVariance(array $values, float $mean): float
    {
        $variance = 0.0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        return count($values) > 0 ? $variance / count($values) : 0.0;
    }

    /**
     * Get season value for ML
     */
    private function getSeasonValue(string $season): float
    {
        $seasons = [
            'spring' => 1.0,
            'summer' => 2.0,
            'fall' => 3.0,
            'winter' => 4.0
        ];
        return $seasons[strtolower($season)] ?? 1.0;
    }

    /**
     * Get current season
     */
    private function getCurrentSeason(): string
    {
        $month = (int) date('n');
        return $this->getSeasonFromMonth($month);
    }

    /**
     * Get season from month
     */
    private function getSeasonFromMonth(int $month): string
    {
        if ($month >= 3 && $month <= 5) {
            return 'spring';
        } elseif ($month >= 6 && $month <= 8) {
            return 'summer';
        } elseif ($month >= 9 && $month <= 11) {
            return 'fall';
        } else {
            return 'winter';
        }
    }
}


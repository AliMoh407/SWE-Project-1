// ML Predictions JavaScript

/**
 * Get base URL (helper function)
 */
function getBaseUrl() {
    const scripts = document.getElementsByTagName('script');
    for (let script of scripts) {
        if (script.src && script.src.includes('ml-predictions.js')) {
            return script.src.replace('assets/js/ml-predictions.js', '');
        }
    }
    // Fallback
    return window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/');
}

/**
 * Load ML prediction for an item
 */
async function loadMLPrediction(itemId, days = 30) {
    try {
        const baseUrl = getBaseUrl();
        const response = await fetch(
            `${baseUrl}routes/ml_api.php?action=predict-demand&item_id=${itemId}&days=${days}`
        );
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error loading ML prediction:', error);
        return null;
    }
}

/**
 * Load optimal reorder recommendation
 */
async function loadOptimalReorder(itemId) {
    try {
        const baseUrl = getBaseUrl();
        const response = await fetch(
            `${baseUrl}routes/ml_api.php?action=optimal-reorder&item_id=${itemId}`
        );
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error loading reorder recommendation:', error);
        return null;
    }
}

/**
 * Show ML prediction modal for an item
 */
async function showMLPredictionModal(itemId, itemName) {
    const modal = document.getElementById('mlPredictionModal');
    if (!modal) {
        createMLPredictionModal();
    }
    
    // Show loading state
    const content = document.getElementById('mlPredictionContent');
    if (content) {
        content.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading AI predictions...</p>
            </div>
        `;
    }
    
    // Update modal title
    const title = document.getElementById('mlPredictionTitle');
    if (title) {
        title.textContent = `AI Predictions: ${itemName}`;
    }
    
    showModal('mlPredictionModal');
    
    // Load predictions
    const [prediction, reorder] = await Promise.all([
        loadMLPrediction(itemId),
        loadOptimalReorder(itemId)
    ]);
    
    if (content) {
        if (prediction && prediction.prediction) {
            const pred = prediction.prediction;
            const confidenceColor = pred.confidence >= 0.8 ? 'success' : pred.confidence >= 0.6 ? 'warning' : 'error';
            const confidencePercent = Math.round(pred.confidence * 100);
            
            content.innerHTML = `
                <div class="ml-predictions-container">
                    <div class="ml-section">
                        <h3><i class="fas fa-chart-line"></i> Demand Forecast</h3>
                        <div class="prediction-card">
                            <div class="prediction-value">
                                <span class="prediction-number">${pred.predicted_demand || 0}</span>
                                <span class="prediction-label">units predicted</span>
                            </div>
                            <div class="prediction-details">
                                <div class="confidence-badge confidence-${confidenceColor}">
                                    <i class="fas fa-${confidenceColor === 'success' ? 'check-circle' : confidenceColor === 'warning' ? 'exclamation-circle' : 'times-circle'}"></i>
                                    ${confidencePercent}% confidence
                                </div>
                                <p class="prediction-message">${pred.message || 'Based on historical patterns'}</p>
                                <p class="prediction-data-points">Trained on ${pred.data_points || 0} data points</p>
                            </div>
                        </div>
                    </div>
                    
                    ${reorder && reorder.recommended_reorder !== undefined ? `
                    <div class="ml-section">
                        <h3><i class="fas fa-shopping-cart"></i> Reorder Recommendation</h3>
                        <div class="reorder-card">
                            <div class="reorder-info">
                                <div class="info-row">
                                    <span class="info-label">Current Stock:</span>
                                    <span class="info-value">${reorder.current_stock}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Minimum Stock:</span>
                                    <span class="info-value">${reorder.min_stock}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Predicted Demand (30 days):</span>
                                    <span class="info-value">${reorder.predicted_demand}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Optimal Stock Level:</span>
                                    <span class="info-value optimal-stock">${reorder.optimal_stock}</span>
                                </div>
                            </div>
                            <div class="reorder-recommendation ${reorder.recommended_reorder > 0 ? 'needs-reorder' : 'sufficient-stock'}">
                                <i class="fas fa-${reorder.recommended_reorder > 0 ? 'exclamation-triangle' : 'check-circle'}"></i>
                                <strong>Recommended Reorder:</strong> 
                                <span class="reorder-amount">${reorder.recommended_reorder}</span> units
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${pred.prediction && pred.prediction.predicted_demand === 0 ? `
                    <div class="ml-warning">
                        <i class="fas fa-info-circle"></i>
                        <p>Insufficient historical data for accurate predictions. More request data is needed.</p>
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="ml-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Unable to load predictions. Please try again later.</p>
                    ${prediction && prediction.error ? `<p class="error-detail">${prediction.error}</p>` : ''}
                </div>
            `;
        }
    }
}

/**
 * Create ML prediction modal if it doesn't exist
 */
function createMLPredictionModal() {
    const modal = document.createElement('div');
    modal.id = 'mlPredictionModal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="mlPredictionTitle">AI Predictions</h2>
                <span class="close" onclick="closeModal('mlPredictionModal')">&times;</span>
            </div>
            <div class="modal-body" id="mlPredictionContent">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('mlPredictionModal')">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

/**
 * Add ML prediction badge to inventory table row
 */
function addMLBadgeToRow(row, itemId) {
    const actionsCell = row.querySelector('.actions');
    if (actionsCell && !actionsCell.querySelector('.ml-prediction-btn')) {
        const mlBtn = document.createElement('button');
        mlBtn.className = 'btn btn-sm btn-info ml-prediction-btn';
        mlBtn.title = 'View AI Predictions';
        mlBtn.innerHTML = '<i class="fas fa-brain"></i>';
        mlBtn.onclick = () => {
            const itemName = row.querySelector('strong')?.textContent || 'Item';
            showMLPredictionModal(itemId, itemName);
        };
        actionsCell.insertBefore(mlBtn, actionsCell.firstChild);
    }
}

/**
 * Initialize ML predictions on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add ML buttons to all inventory rows
    const inventoryRows = document.querySelectorAll('.data-table tbody tr[data-item-id]');
    inventoryRows.forEach(row => {
        const itemId = row.getAttribute('data-item-id');
        if (itemId) {
            addMLBadgeToRow(row, itemId);
        }
    });
    
    // Add ML predictions section to stats
    addMLStatsSection();
});

/**
 * Add ML stats section to the stats overview
 */
function addMLStatsSection() {
    const statsContainer = document.querySelector('.stats-overview');
    if (statsContainer && !document.getElementById('mlStatsCard')) {
        const mlStatCard = document.createElement('div');
        mlStatCard.id = 'mlStatsCard';
        mlStatCard.className = 'stat-card stat-card-ml';
        mlStatCard.innerHTML = `
            <div class="stat-icon">
                <i class="fas fa-brain"></i>
            </div>
            <div class="stat-content">
                <h3 id="mlPredictionsCount">-</h3>
                <p>AI Predictions Available</p>
            </div>
        `;
        statsContainer.appendChild(mlStatCard);
        
        // Load count of items with predictions
        loadMLPredictionsCount();
    }
}

/**
 * Load count of items with ML predictions
 */
async function loadMLPredictionsCount() {
    // This would require a new API endpoint, for now just show a placeholder
    const countElement = document.getElementById('mlPredictionsCount');
    if (countElement) {
        countElement.textContent = 'AI';
    }
}

// Add CSS for ML predictions
const mlStyles = `
.ml-predictions-container {
    padding: 1rem 0;
}

.ml-section {
    margin-bottom: 2rem;
}

.ml-section h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.prediction-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    padding: 1.5rem;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.prediction-value {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.prediction-number {
    font-size: 3rem;
    font-weight: bold;
    line-height: 1;
}

.prediction-label {
    font-size: 1rem;
    opacity: 0.9;
}

.prediction-details {
    margin-top: 1rem;
}

.confidence-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.confidence-success {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
}

.confidence-warning {
    background: rgba(255, 152, 0, 0.2);
    color: #ff9800;
}

.confidence-error {
    background: rgba(244, 67, 54, 0.2);
    color: #f44336;
}

.prediction-message {
    margin: 0.5rem 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.prediction-data-points {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    opacity: 0.8;
}

.reorder-card {
    background: #f5f5f5;
    border-radius: 8px;
    padding: 1.5rem;
    border: 2px solid #e0e0e0;
}

.reorder-info {
    margin-bottom: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #666;
}

.info-value {
    font-weight: bold;
    color: #333;
}

.optimal-stock {
    color: #4caf50;
}

.reorder-recommendation {
    padding: 1rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.1rem;
}

.reorder-recommendation.needs-reorder {
    background: #fff3cd;
    border: 2px solid #ffc107;
    color: #856404;
}

.reorder-recommendation.sufficient-stock {
    background: #d4edda;
    border: 2px solid #28a745;
    color: #155724;
}

.reorder-amount {
    font-size: 1.5rem;
    font-weight: bold;
    margin-left: 0.5rem;
}

.ml-warning, .ml-error {
    padding: 1rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1rem;
}

.ml-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
}

.ml-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.ml-prediction-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.ml-prediction-btn:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.stat-card-ml {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card-ml .stat-icon {
    color: white;
}

.loading-state {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.loading-state i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.modal-large {
    max-width: 600px;
}
`;

// Add styles to document
const mlStyleSheet = document.createElement('style');
mlStyleSheet.textContent = mlStyles;
document.head.appendChild(mlStyleSheet);


<?php

/**
 * Observer Interface
 * Defines the contract for observers that listen to events
 */
interface Observer
{
    /**
     * Handle an event notification
     */
    public function update(string $event, array $data): void;
}

/**
 * Activity Log Observer
 * Automatically logs activities when events occur
 */
class ActivityLogObserver implements Observer
{
    private ActivityLogModel $activityLogModel;
    
    public function __construct(ActivityLogModel $activityLogModel)
    {
        $this->activityLogModel = $activityLogModel;
    }
    
    public function update(string $event, array $data): void
    {
        // Only log if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $description = $this->formatDescription($event, $data);
        $status = $data['status'] ?? 'Completed';
        
        $this->activityLogModel->create($userId, $description, $status);
    }
    
    /**
     * Format event description based on event type and data
     */
    private function formatDescription(string $event, array $data): string
    {
        return match($event) {
            'inventory.add' => "Added new inventory item: {$data['name']}",
            'inventory.update' => "Updated inventory item: {$data['name']}",
            'inventory.adjust_stock' => "Adjusted stock for {$data['item_name']}: {$data['adjustment_type']} {$data['amount']}" . 
                (isset($data['reason']) && $data['reason'] ? " (Reason: {$data['reason']})" : ''),
            'request.create' => "Requested {$data['item_name']} (Quantity: {$data['quantity']})",
            'request.approve' => "Approved request for {$data['item_name']}",
            'request.reject' => "Rejected request for {$data['item_name']}",
            'user.create' => "Created new user: {$data['username']}",
            'user.update' => "Updated user: {$data['username']}",
            'user.delete' => "Deleted user: {$data['username']}",
            default => $data['description'] ?? "Action: {$event}"
        };
    }
}

/**
 * Subject Interface
 * Defines the contract for subjects that notify observers
 */
interface Subject
{
    public function attach(Observer $observer): void;
    public function detach(Observer $observer): void;
    public function notify(string $event, array $data): void;
}

/**
 * Event Notifier
 * Centralized event notification system
 */
class EventNotifier implements Subject
{
    private array $observers = [];
    private static ?EventNotifier $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): EventNotifier
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Attach an observer
     */
    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;
    }
    
    /**
     * Detach an observer
     */
    public function detach(Observer $observer): void
    {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
            $this->observers = array_values($this->observers); // Re-index
        }
    }
    
    /**
     * Notify all observers of an event
     */
    public function notify(string $event, array $data): void
    {
        foreach ($this->observers as $observer) {
            try {
                $observer->update($event, $data);
            } catch (Exception $e) {
                // Log error but don't break the flow
                error_log("Observer error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Reset instance (for testing)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}


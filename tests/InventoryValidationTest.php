<?php

use PHPUnit\Framework\TestCase;

final class InventoryValidationTest extends TestCase
{
    /**
     * Test the isExpiringSoon helper function
     */
    public function testIsExpiringSoonReturnsTrueForUpcomingDate(): void
    {
        // SETUP: Create an item that expires in 5 days
        $expiryDate = date('Y-m-d', strtotime('+5 days'));
        $item = ['expiry_date' => $expiryDate];

        // CALL: Call the method to be tested
        $result = isExpiringSoon($item, 30);

        // ASSERTION: Compare the result with expected (true)
        $this->assertTrue($result, "Expected item expiring in 5 days to be flagged as 'expiring soon'");
    }

    public function testIsExpiringSoonReturnsFalseForFarDate(): void
    {
        // SETUP: Create an item that expires in 60 days
        $expiryDate = date('Y-m-d', strtotime('+60 days'));
        $item = ['expiry_date' => $expiryDate];

        // CALL: Call the method to be tested
        $result = isExpiringSoon($item, 30);

        // ASSERTION: Compare the result with expected (false)
        $this->assertFalse($result, "Expected item expiring in 60 days NOT to be flagged as 'expiring soon' when limit is 30 days");
    }

    /**
     * Test the isLowStock helper function (re-verifying)
     */
    public function testIsLowStockReturnsTrueWhenAtMin(): void
    {
        // SETUP
        $item = ['stock' => 10, 'min_stock' => 10];

        // CALL
        $result = isLowStock($item);

        // ASSERTION
        $this->assertTrue($result);
    }
}

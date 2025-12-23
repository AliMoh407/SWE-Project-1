<?php

use PHPUnit\Framework\TestCase;

final class InventoryModelTest extends TestCase
{
    private $mysqliMock;
    private $inventoryModel;

    /**
     * SETUP part: This runs before every test
     */
    protected function setUp(): void
    {
        // Mock the mysqli connection so we don't need a real database
        $this->mysqliMock = $this->createMock(mysqli::class);
        $this->inventoryModel = new InventoryModel($this->mysqliMock);
    }

    /**
     * Test stock adjustment logic
     */
    public function testAdjustStockWithAddType(): void
    {
        // 1. SETUP: Mock findById and the UPDATE query
        $itemId = 1;
        $currentStock = 50;
        $addAmount = 10;
        $expectedNewStock = 60;

        // Create a mock result for findById (simulated database row)
        $itemData = [
            'id' => $itemId,
            'name' => 'Paracetamol',
            'stock' => $currentStock
        ];

        // We need a partial mock for InventoryModel to override findById
        $modelPartialMock = $this->getMockBuilder(InventoryModel::class)
            ->setConstructorArgs([$this->mysqliMock])
            ->onlyMethods(['findById'])
            ->getMock();

        $modelPartialMock->method('findById')
            ->willReturn($itemData);

        // Mock the preparation and execution of the UPDATE statement
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $this->mysqliMock->method('prepare')
            ->with($this->stringContains('UPDATE inventory SET stock = ?'))
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with('ii', $expectedNewStock, $itemId)
            ->willReturn(true);

        $stmtMock->method('execute')
            ->willReturn(true);

        // 2. CALL: Call the method to be tested
        $result = $modelPartialMock->adjustStock($itemId, $addAmount, 'add');

        // 3. ASSERTION: Check that it returned success
        $this->assertTrue($result, "Inventory adjustment should return true on success");
    }

    public function testAdjustStockSubtractDoesNotGoBelowZero(): void
    {
        // 1. SETUP
        $itemId = 1;
        $currentStock = 5;
        $subtractAmount = 10;
        $expectedNewStock = 0; // Negative stock should be capped at 0

        $itemData = ['id' => $itemId, 'stock' => $currentStock];

        $modelPartialMock = $this->getMockBuilder(InventoryModel::class)
            ->setConstructorArgs([$this->mysqliMock])
            ->onlyMethods(['findById'])
            ->getMock();

        $modelPartialMock->method('findById')->willReturn($itemData);

        $stmtMock = $this->createMock(mysqli_stmt::class);
        $this->mysqliMock->method('prepare')->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with('ii', $expectedNewStock, $itemId);

        $stmtMock->method('execute')->willReturn(true);

        // 2. CALL
        $result = $modelPartialMock->adjustStock($itemId, $subtractAmount, 'subtract');

        // 3. ASSERTION
        $this->assertTrue($result);
    }
}

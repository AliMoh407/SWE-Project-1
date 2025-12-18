<?php

use PHPUnit\Framework\TestCase;

final class ConfigHelpersTest extends TestCase
{
    public function testIsLowStockReturnsTrueWhenAtOrBelowMin(): void
    {
        $item = ['stock' => 5, 'min_stock' => 5];
        $this->assertTrue(isLowStock($item));

        $item = ['stock' => 3, 'min_stock' => 5];
        $this->assertTrue(isLowStock($item));
    }

    public function testIsLowStockReturnsFalseWhenAboveMin(): void
    {
        $item = ['stock' => 10, 'min_stock' => 5];
        $this->assertFalse(isLowStock($item));
    }
}


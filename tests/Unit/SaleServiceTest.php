<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\SaleService;
use Tests\TestCase;

/**
 * SaleServiceTest
 *
 * Tests for critical sale service functionality including:
 * - Sale creation with stock validation
 * - Payment processing
 * - Total calculation
 */
class SaleServiceTest extends TestCase
{
    private SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = new SaleService();
    }

    /**
     * Test that sale items are calculated correctly
     */
    public function test_calculate_sale_totals(): void
    {
        $items = [
            [
                'product_id' => 1,
                'quantity' => 2,
                'unit_price' => 100.00,
            ],
            [
                'product_id' => 2,
                'quantity' => 3,
                'unit_price' => 50.00,
            ],
        ];

        $result = $this->saleService->calculateTotals($items, 50.00);

        $this->assertEquals(350.00, $result['subtotal'], 'Subtotal should be 350 (2*100 + 3*50)');
        $this->assertEquals(50.00, $result['discount'], 'Discount should be 50');
        $this->assertEquals(300.00, $result['receivable'], 'Receivable should be 300 (350 - 50)');
        $this->assertCount(2, $result['lines'], 'Should have 2 line items');
    }

    /**
     * Test that discount cannot exceed subtotal
     */
    public function test_discount_calculation_with_zero_floor(): void
    {
        $items = [
            [
                'product_id' => 1,
                'quantity' => 1,
                'unit_price' => 100.00,
            ],
        ];

        $result = $this->saleService->calculateTotals($items, 200.00); // Discount > subtotal

        $this->assertEquals(100.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['receivable'], 'Receivable should floor to 0');
    }

    /**
     * Test that sale can be retrieved with proper relationships
     */
    public function test_sale_retrieval_with_relationships(): void
    {
        $customer = Customer::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $retrieved = Sale::with(['customer', 'warehouse'])->find($sale->id);

        $this->assertNotNull($retrieved->customer);
        $this->assertNotNull($retrieved->warehouse);
        $this->assertEquals($customer->id, $retrieved->customer->id);
        $this->assertEquals($warehouse->id, $retrieved->warehouse->id);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Services\ProductService;
use Tests\TestCase;

/**
 * ProductServiceTest
 *
 * Tests for critical product service functionality including:
 * - Product creation and updates
 * - Stock calculations
 * - Price calculations
 */
class ProductServiceTest extends TestCase
{
    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService(app('ImportExportService'));
    }

    /**
     * Test that getCalculatedStock returns correct stock after purchase
     */
    public function test_calculated_stock_after_purchase(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create(['current_stock' => 0]);

        $purchase = Purchase::factory()->create(['warehouse_id' => $warehouse->id]);
        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);

        $calculated = $product->getCalculatedStock();

        $this->assertEquals(100, $calculated, 'Stock should be 100 after adding 100 via purchase');
    }

    /**
     * Test that stock calculation decreases after sale
     */
    public function test_calculated_stock_after_sale(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        // Add initial stock
        $purchase = Purchase::factory()->create(['warehouse_id' => $warehouse->id]);
        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);

        // Remove stock via sale
        $sale = Sale::factory()->create(['warehouse_id' => $warehouse->id]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 30,
        ]);

        $calculated = $product->getCalculatedStock();

        $this->assertEquals(70, $calculated, 'Stock should be 70 after sale of 30 units');
    }

    /**
     * Test that product can be created successfully
     */
    public function test_product_creation(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'category_id' => 1,
            'brand_id' => 1,
            'unit_id' => 1,
            'alert_quantity' => 10,
            'sale_price' => 100.00,
            'purchase_price' => 50.00,
        ];

        $product = $this->productService->createProduct($data);

        $this->assertNotNull($product);
        $this->assertNotNull($product->id);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('TEST-SKU-001', $product->sku);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class UpdateProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-product-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and update current_stock for all products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::all();
        $this->info("Found " . $products->count() . " products to update.");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $product->updateStock();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Product stock levels updated successfully.');
    }
}

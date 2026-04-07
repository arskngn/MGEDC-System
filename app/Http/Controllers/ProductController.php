<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportProductsRequest;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ProductService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use ResolvesCsvUploadPath;

    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $products = $this->filteredProductsQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        $categories = getCachedCategories();
        $brands = getCachedBrands();
        $units = getCachedUnits();
        
        // Ensure lookups are proper collections of model objects (fallback if cache is corrupted)
        if (!$categories instanceof \Illuminate\Database\Eloquent\Collection) {
            $categories = \App\Models\Category::orderBy('name')->get();
        }
        if (!$brands instanceof \Illuminate\Database\Eloquent\Collection) {
            $brands = \App\Models\Brand::orderBy('name')->get();
        }
        if (!$units instanceof \Illuminate\Database\Eloquent\Collection) {
            $units = \App\Models\Unit::orderBy('name')->get();
        }
        
        return view('products.create', [
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->createProduct($request->validated());

        return redirect()->route('products.all')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = getCachedCategories();
        $brands = getCachedBrands();
        $units = getCachedUnits();
        $warehouses = Warehouse::query()->enabled()->orderBy('name')->get();
        
        // Ensure lookups are proper collections of model objects (fallback if cache is corrupted)
        if (!$categories instanceof \Illuminate\Database\Eloquent\Collection) {
            $categories = \App\Models\Category::orderBy('name')->get();
        }
        if (!$brands instanceof \Illuminate\Database\Eloquent\Collection) {
            $brands = \App\Models\Brand::orderBy('name')->get();
        }
        if (!$units instanceof \Illuminate\Database\Eloquent\Collection) {
            $units = \App\Models\Unit::orderBy('name')->get();
        }
        
        return view('products.edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'warehouses' => $warehouses,
            'batches' => $product->batches()
                ->with('warehouse')
                ->orderBy('warehouse_id')
                ->orderBy('expiration_date')
                ->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()->route('products.all')->with('success', 'Product updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $products = $this->filteredProductsQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('products.pdf.all', [
            'products' => $products,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('products-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $products = $this->filteredProductsQuery($request)->get();

        return $this->productService->exportToCsv($products);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'product.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name', 'category', 'sku', 'brand', 'unit', 'alert_quantity', 'note']);
            fputcsv($out, ['Sample Product', 'Electronics', 'SKU001', 'Panasonic', 'piece', '10', '']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportProductsRequest $request): RedirectResponse
    {
        $path = $this->csvUploadPath($request->file('csv_file'));
        $result = $this->productService->importFromCsv($path);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if (!empty($result['errors'])) {
            $msg = "Imported {$result['imported']} products. Errors: " . implode(' ', $result['errors']);
            return redirect()->route('products.all')->with('error', $msg);
        }

        return redirect()->route('products.all')->with('success', "{$result['imported']} products imported successfully.");
    }

    private function csvRowIsEmpty(?array $row): bool
    {
        if ($row === null || $row === []) {
            return true;
        }
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function filteredProductsQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();

        return Product::query()
            ->with(['category', 'brand', 'unit'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('unit', function ($uq) use ($search) {
                            $uq->where('name', 'like', '%'.$search.'%')
                                ->orWhere('short_name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderBy('name');
    }

    private function pdfLogoDataUri(?GeneralSetting $generalSetting): ?string
    {
        if (! $generalSetting || ! $generalSetting->logo_light) {
            return null;
        }
        $path = public_path($generalSetting->logo_light);
        if (! is_readable($path)) {
            return null;
        }
        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }

    public function storeBatch(StoreBatchRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $validated['product_id'] = $product->id;
        $validated['quantity_sold'] = 0;
        $validated['quantity_alert_sent'] = 0;
        $validated['status'] = 'active';

        ProductBatch::create($validated);

        return redirect()->route('products.edit', $product)->with('success', 'Batch added successfully.');
    }

    public function updateBatch(StoreBatchRequest $request, Product $product, ProductBatch $batch): RedirectResponse
    {
        // Verify batch belongs to this product
        if ($batch->product_id !== $product->id) {
            return redirect()->route('products.edit', $product)->with('error', 'Batch not found for this product.');
        }

        $batch->update($request->validated());

        return redirect()->route('products.edit', $product)->with('success', 'Batch updated successfully.');
    }

    public function deleteBatch(Product $product, ProductBatch $batch): RedirectResponse
    {
        // Verify batch belongs to this product
        if ($batch->product_id !== $product->id) {
            return redirect()->route('products.edit', $product)->with('error', 'Batch not found for this product.');
        }

        $batch->delete();

        return redirect()->route('products.edit', $product)->with('success', 'Batch deleted successfully.');
    }
}

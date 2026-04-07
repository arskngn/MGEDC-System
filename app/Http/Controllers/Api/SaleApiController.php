<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Models\GeneralSetting;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleApiController extends Controller
{
    public function __construct(protected SaleService $saleService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $sales = Sale::with(['customer', 'warehouse'])
            ->orderBy('sale_date', 'desc')
            ->paginate($perPage);

        return SaleResource::collection($sales);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): SaleResource
    {
        $sale->load(['customer', 'warehouse', 'items']);
        return new SaleResource($sale);
    }

    /**
     * Store a new sale.
     */
    public function store(Request $request): JsonResponse
    {
        // For brevity, using basic validation here.
        // In a real app, I'd use a dedicated StoreSaleRequest for the API.
        $validated = $request->validate([
            'invoice_no' => 'required|string|unique:sales',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date' => 'required|date',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $sale = $this->saleService->createSale($validated);

        return (new SaleResource($sale->load('items')))
            ->response()
            ->setStatusCode(201);
    }
}

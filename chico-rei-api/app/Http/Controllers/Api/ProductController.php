<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Filters\ProductFilter;
use App\Http\Requests\ListProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductSearchService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ListProductsRequest $request, ProductSearchService $search)
    {
        $filter = new ProductFilter($request->validated(), $search);

        $products = $filter->apply(Product::query())
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product = $this->productService->update($product, $request->validated());
        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $this->productService->delete($product);

        return response()->noContent();
    }
}

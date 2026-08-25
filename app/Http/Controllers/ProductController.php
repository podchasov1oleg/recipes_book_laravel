<?php

namespace App\Http\Controllers;

use App\Enums\Unit;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Контроллер страницы продуктов
 */
class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $products = Product::all();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $units = collect(Unit::cases())->mapWithKeys(fn (Unit $unit) => [$unit->value => $unit->label()]);

        return view('products.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        // получить провалидированные данные из запроса
        $validated = $request->validated();

        $product = new Product($validated);

        $product->save();

        return redirect()->route('products.index')->with('success', 'Продукт успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $units = collect(Unit::cases())->mapWithKeys(fn (Unit $unit) => [$unit->value => $unit->label()]);

        return view('products.edit', compact('product', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Продукт успешно изменен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Продукт успешно удален');
    }
}

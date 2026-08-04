<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Product;
use App\Models\Recipe;

/**
 * Контроллер страницы рецептов
 */
class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = Recipe::withCount('products')->get();

        return view('recipes.index', compact('recipes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();

        return view('recipes.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request)
    {
        $validated = $request->validated();

        $recipe = new Recipe($validated);

        $recipe->save();

        $recipe->products()->sync($validated['product_ids']);

        return redirect()->route('recipes.index')
            ->with('success', 'Рецепт успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe)
    {
        $products = Product::all();

        return view('recipes.edit', compact('recipe', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $validated = $request->validated();

        $recipe->update($validated);
        $recipe->products()->sync($validated['product_ids']);

        return redirect()->route('recipes.index')
            ->with('success', 'Рецепт успешно изменен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Рецепт успешно удален');
    }
}

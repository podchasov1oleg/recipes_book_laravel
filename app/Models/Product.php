<?php

namespace App\Models;

use App\Enums\Unit;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Модель продукта
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
        'unit',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'unit' => Unit::class,
    ];

    /**
     * Получить рецепты, в которых используется текущий продукт
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class);
    }
}

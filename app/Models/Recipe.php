<?php

namespace App\Models;

use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Модель рецепта
 */
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'title',
    ];

    /**
     * Получить связанные с рецептом продукты
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Получить дни меню, к которым привязаны рецепты
     */
    public function menuDays(): BelongsToMany
    {
        return $this->belongsToMany(MenuDay::class);
    }
}

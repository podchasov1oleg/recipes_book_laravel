<?php

namespace App\Models;

use Database\Factories\HouseholdFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель домохозяйства
 */
class Household extends Model
{
    /** @use HasFactory<HouseholdFactory> */
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Получить пользователя, привязанного к домохозяйству
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить рецепты, связанные с домохозяйством
     *
     * @return HasMany<Recipe, $this>
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Получить дни меню, связанные с домохозяйством
     *
     * @return HasMany<MenuDay, $this>
     */
    public function menuDays(): HasMany
    {
        return $this->hasMany(MenuDay::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\MenuDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Модель дня меню
 */
class MenuDay extends Model
{
    /** @use HasFactory<MenuDayFactory> */
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'day',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'day' => 'immutable_date',
    ];

    /**
     * Получить рецепты, которые относятся к этому дню меню
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class);
    }
}

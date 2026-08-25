<?php

namespace App\Enums;

/**
 * Enum типов измерения продуктов
 */
enum Unit: string
{
    /**
     * Граммы
     */
    case Gram = 'г';

    /**
     * Миллилитры
     */
    case Milliliter = 'мл';

    /**
     * Штуки
     */
    case Piece = 'шт';

    /**
     * Получить название для типа измерения
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Gram => 'граммы',
            self::Milliliter => 'миллилитры',
            self::Piece => 'штуки',
        };
    }
}


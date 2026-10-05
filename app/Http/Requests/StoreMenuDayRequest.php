<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Форма запроса на создание дня меню
 */
class StoreMenuDayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'day' => 'required|date|date_format:Y-m-d',
            'recipe_ids' => 'required|array',
            'recipe_ids.*' => [
                Rule::exists('recipes', 'id')->where('household_id', $this->user()->household->id),
            ],
        ];
    }
}

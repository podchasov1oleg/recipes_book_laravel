<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
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
            'title' => 'required|max:255|unique:recipes,title',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'servings' => 'required|integer|min:1|max:255',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function messages(): array
    {
        return [
            'products.*.quantity.required' => 'Введите количество',
            'products.*.quantity.integer' => 'Количество должно быть целым числом',
            'products.*.quantity.min' => 'Количество должно быть не менее :min',
            'products.required' => 'Необходимо добавить хотя бы один продукт',
            'servings.required' => 'Введите порции',
            'servings.min' => 'Порций не может быть менее :min',
            'servings.max' => 'Порций не может быть более :max',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'products' => json_decode($this->request->get('products'), true),
        ]);
    }
}

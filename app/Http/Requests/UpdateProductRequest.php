<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'lote' => 'sometimes|required|date|before_or_equal:today',
            'stock' => 'sometimes|required|integer|min:0',
            'expiration_days' => 'sometimes|required|integer|min:1|max:3650',
            'min_stock' => 'sometimes|required|integer|min:0|lte:stock',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'lote.required' => 'La fecha de lote es obligatoria.',
            'lote.before_or_equal' => 'La fecha de lote no puede ser futura.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.min' => 'El stock no puede ser negativo.',
            'expiration_days.required' => 'Los días de expiración son obligatorios.',
            'expiration_days.min' => 'Los días de expiración deben ser al menos 1.',
            'expiration_days.max' => 'Los días de expiración no pueden exceder 10 años.',
            'min_stock.required' => 'El stock mínimo es obligatorio.',
            'min_stock.lte' => 'El stock mínimo no puede ser mayor al stock actual.',
            'price.required' => 'El precio es obligatorio.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}

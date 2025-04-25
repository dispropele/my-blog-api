<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage categories');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        //Получаем id текущей категории из маршрута
        $id = $this->route('category')->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('categories', 'name')->ignore($id),
            ]
        ];
    }
}

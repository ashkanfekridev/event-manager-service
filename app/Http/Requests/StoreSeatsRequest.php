<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSeatsRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seats' => ['required', 'array', 'min:1', 'max:1000'],
            'seats.*.section' => ['sometimes', 'string', 'max:100'],
            'seats.*.row_label' => ['required', 'string', 'max:20'],
            'seats.*.number' => ['required', 'string', 'max:20'],
            'seats.*.code' => ['required', 'string', 'max:100', 'distinct'],
            'seats.*.type' => ['sometimes', 'string', 'in:standard,vip,wheelchair'],
        ];
    }
}

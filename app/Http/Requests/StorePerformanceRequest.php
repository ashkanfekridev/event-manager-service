<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceRequest extends FormRequest
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
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'sales_start_at' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'sales_end_at' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'default_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}

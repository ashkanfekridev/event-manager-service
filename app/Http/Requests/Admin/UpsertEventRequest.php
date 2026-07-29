<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertEventRequest extends FormRequest
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
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('events')->ignore($event)],
            'type' => ['required', 'in:concert,theater'],
            'description' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'url', 'max:2048'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'age_limit' => ['nullable', 'integer', 'min:0', 'max:99'],
            'publication_mode' => ['required', 'in:draft,now,scheduled'],
            'scheduled_publish_at' => ['nullable', 'required_if:publication_mode,scheduled', 'date', 'after:now'],
        ];
    }
}

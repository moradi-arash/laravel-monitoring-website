<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCronSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cron_allowed_ip' => ['required', 'string', 'max:255'],
            'cron_secret_key' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cron_allowed_ip.required' => 'The cron allowed IP field is required.',
            'cron_allowed_ip.max' => 'The cron allowed IP must not exceed 255 characters.',
            'cron_secret_key.required' => 'The cron secret key field is required.',
            'cron_secret_key.min' => 'The cron secret key must be at least 8 characters.',
            'cron_secret_key.max' => 'The cron secret key must not exceed 255 characters.',
        ];
    }
}

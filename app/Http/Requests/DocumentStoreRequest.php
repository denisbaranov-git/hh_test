<?php

namespace App\Http\Requests;

use App\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;

class DocumentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Добавьте свою логику авторизации
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => ['required', 'in:' . implode(',', DocumentStatus::values())],
            'description' => 'nullable|string',
        ];
    }
}

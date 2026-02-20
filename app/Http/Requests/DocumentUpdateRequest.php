<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DocumentStatus;

class DocumentUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'status' => ['sometimes', 'in:' . implode(',', DocumentStatus::values())],
            'description' => 'nullable|string',
        ];
    }
}

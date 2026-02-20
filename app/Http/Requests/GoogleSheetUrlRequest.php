<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleSheetUrlRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'url' => 'required|url|regex:/\/spreadsheets\/d\//'
        ];
    }
}

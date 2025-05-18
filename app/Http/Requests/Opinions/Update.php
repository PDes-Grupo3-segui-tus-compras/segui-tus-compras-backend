<?php
namespace App\Http\Requests\Opinions;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest{
    public function rules(): array {
        return [
            'content' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
        ];
    }
}
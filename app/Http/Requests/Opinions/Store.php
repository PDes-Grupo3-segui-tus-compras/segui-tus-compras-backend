<?php
namespace App\Http\Requests\Opinions;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest{
    public function rules(): array {
        return [
            'catalog_product_id' => 'required|string',
            'name' => 'required|string',
            'image' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'content' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
        ];
    }
}


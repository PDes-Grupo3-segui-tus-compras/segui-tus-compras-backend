<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FavouriteRequest extends FormRequest {

    public function rules(): array {
        return [
            'catalog_product_id' => 'required|string',
            'name' => 'required|string',
            'image' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ];
    }

}

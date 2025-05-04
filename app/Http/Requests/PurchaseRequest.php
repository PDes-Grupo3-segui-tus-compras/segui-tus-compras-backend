<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest {

    public function rules(): array {
        return [
            'catalog_product_id' => 'required|string',
            'name' => 'required|string',
            'image' => 'required|string',
            'short_description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator): void {
        $validator->after(function ($validator) {
            $product = Product::where('catalog_product_id', $this->catalog_product_id)->first();

            if ($product && $product->price != $this->price) {
                $validator->errors()->add('price', 'The price does not match the product price.');
            }
        });
    }
}

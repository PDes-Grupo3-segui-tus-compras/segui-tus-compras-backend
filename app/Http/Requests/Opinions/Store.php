<?php
namespace App\Http\Requests\Opinions;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Opinion;

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

    public function withValidator(Validator $validator): void {
        $validator->after(function ($validator) {
            $userId = $this->user()->id;
            $catalogProductId = $this->input('catalog_product_id');

            $product = Product::where('catalog_product_id', $catalogProductId)->first();

            if ($product) {
                $this->validateIfUserPostedAnOpinion($userId, $product, $validator);
            }
        });
    }

    /**
     * @param $userId
     * @param $product
     * @param $validator
     * @return void
     */
    function validateIfUserPostedAnOpinion($userId, $product, $validator): void
    {
        $exists = Opinion::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            $validator->errors()->add('catalog_product_id', 'You have already given an opinion on this product.');
        }
    }
}


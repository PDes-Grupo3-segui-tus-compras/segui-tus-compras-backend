<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoSeeder extends Seeder {
    public function run(): void {

        $users = collect();
        $users->push(User::create([
            'name' => 'Admin1 User',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]));

        for ($i = 1; $i <= 4; $i++) {
            $users->push(User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password'),
                'user_type' => 'user',
            ]));
        }

        $catalogIds = [
            'MLA29815169',
            'MLA44209151',
            'MLA39156350',
            'MLA26800008',
            'MLA27106924',
            'MLA22868255',
            'MLA21807330',
            'MLA50811016',
            'MLA22429317',
            'MLA19730731',
        ];

        $names = [
            'Luke Skywalker Star Wars Kenner Star Wars',
            'Manga Pastelera Manga Pastelera Profesional',
            'Box 24 Boosters Yu-Gi-Oh Yu-Gi-Oh Soul Fusion',
            'Cama ginecológica + banco + mesa auxiliar + escalera',
            'Kit con 3 cojines inflables: cilíndricos, en forma de U y triangulares',
            'Cama tapizada Cama Carro do Brasil Mini cama Aranha cuna color rojo',
            'Funko The Armorer (353) - Star Wars Madalorian (star Wars)',
            'Magic Tcg Magic: The Gathering Neon Dynasty Booster',
            'Muñeca Dragon Ball Harle Anime Seven Colección Dragon Ball',
            'Figura Articulada Naruto Uzumaki Naruto',
        ];

        $images = [
            'https://http2.mlstatic.com/D_NQ_NP_777021-MLU74180675839_012024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_600416-MLA80793939162_112024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_761854-MLU78252258355_082024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_753181-MLU78026951554_082024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_934685-MLU71763262495_092023-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_795228-MLU54974250959_042023-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_636296-MLA43636093932_092020-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_667483-MLA85495250953_052025-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_790818-MLA79495912633_092024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_989155-MLM46571383339_062021-F.jpg',
        ];

        $products = collect();
        foreach ($catalogIds as $i => $catalogId) {
            $products->push(Product::create([
                'catalog_product_id' => $catalogId,
                'name' => $names[$i],
                'image' => $images[$i],
                'short_description' => 'Producto de muestra para testing',
                'price' => rand(1000, 10000) / 100,
            ]));
        }

        foreach ($users as $user) {
            $purchasedProducts = $products->random(rand(2, 5));
            foreach ($purchasedProducts as $product) {
                Purchase::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'purchase_date' => Carbon::now()->subDays(rand(1, 30)),
                    'quantity' => rand(1, 3),
                    'price' => $product->price,
                ]);
            }

            $favouriteProducts = $products->random(rand(2, 5));
            $user->favouriteProducts()->syncWithoutDetaching($favouriteProducts->pluck('id')->toArray());
        }
    }
}

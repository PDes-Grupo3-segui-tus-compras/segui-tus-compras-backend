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

        for ($i = 1; $i <= 8; $i++) {
            $users->push(User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password'),
                'user_type' => 'user',
            ]));
        }

        $catalogIds = [
            'MLA37215138',
            'MLA28957154',
            'MLA19750557',
            'MLA26800008',
            'MLA27106924',
            'MLA22868255',
            'MLA21807330',
            'MLA28797881',
            'MLA22450840',
            'MLA42525711',
        ];

        $names = [
            'Star Wars Force N Telling Vader Juguetes Star Wars',
            'Olla De Aluminio Olla Aluminio Olla Pequeña Olla A Presión Color Gris 7litros 24 cm',
            'Bloques para armar Lego LEGO CITY Lego City Caminhao Cegonha',
            'Cama ginecológica + banco + mesa auxiliar + escalera',
            'Kit con 3 cojines inflables: cilíndricos, en forma de U y triangulares',
            'Cama tapizada Cama Carro do Brasil Mini cama Aranha cuna color rojo',
            'Funko The Armorer (353) - Star Wars Madalorian (star Wars)',
            'Shampoo para vehículo Rev Auto sin nombre Rev Auto de 29.573mL',
            'Libro - Libro: El sello',
            'Carpa De Camping Carpa 2 Personas Mundoshop Carpas Camping Impermeables Carpa Impermeable Portatil Carpa Para 2 Personas Carpa Araña Carpa Playa Camping Trekking',
        ];

        $images = [
            'https://http2.mlstatic.com/D_NQ_NP_612839-MLA79451474907_092024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_715069-MLU73425706911_122023-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_651217-MLU77573559262_072024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_753181-MLU78026951554_082024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_934685-MLU71763262495_092023-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_795228-MLU54974250959_042023-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_636296-MLA43636093932_092020-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_686173-MLU73292710010_122023-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_954080-MLU77233423012_072024-F.jpg',
            'https://http2.mlstatic.com/D_NQ_NP_966148-MLA80358148865_102024-F.jpg',
        ];

        $products = collect();
        foreach ($catalogIds as $i => $catalogId) {
            $products->push(Product::create([
                'catalog_product_id' => $catalogId,
                'name' => $names[$i],
                'image' => $images[$i],
                'short_description' => 'Producto de muestra para testing',
                'price' => rand(1000, 10000),
            ]));
        }

        foreach ($users as $user) {
            $purchasedProducts = $products->random(rand(2, 10));
            foreach ($purchasedProducts as $product) {
                Purchase::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'purchase_date' => Carbon::now()->subDays(rand(1, 30)),
                    'quantity' => rand(1, 3),
                    'price' => $product->price,
                ]);
            }

            $favouriteProducts = $products->random(rand(2, 10));
            $user->favouriteProducts()->syncWithoutDetaching($favouriteProducts->pluck('id')->toArray());
        }
    }
}

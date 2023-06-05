<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryParent;

class CategoryParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'プレイ',
            ],
            [
                'name' => 'シチュエーション',
            ],
            [
                'name' => 'タイプ',
            ]
        ];


        foreach ($data as $item) {
            CategoryParent::updateOrCreate([
                'name' => $item['name']
            ], $item);
        }
    }
}

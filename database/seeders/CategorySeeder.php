<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_tr' => 'Biyoenerji',
                'name_en' => 'Bioenergy',
                'name_ru' => 'Биоэнергия',
                'name_az' => 'Bioenerji',
                'slug' => 'biyoenerji',
                'color' => 'success'
            ],
            [
                'name_tr' => 'Bioterapiya',
                'name_en' => 'Biotherapy',
                'name_ru' => 'Биотерапия',
                'name_az' => 'Bioterapiya',
                'slug' => 'bioterapiya',
                'color' => 'info'
            ],
            [
                'name_tr' => 'Yaşam Koçluğu',
                'name_en' => 'Life Coaching',
                'name_ru' => 'Лайф-коучинг',
                'name_az' => 'Həyat Koçluğu',
                'slug' => 'yasam-kocugu',
                'color' => 'warning'
            ],
            [
                'name_tr' => 'Genel',
                'name_en' => 'General',
                'name_ru' => 'Общее',
                'name_az' => 'Ümumi',
                'slug' => 'genel',
                'color' => 'gray'
            ],
            [
                'name_tr' => 'Kişisel Gelişim',
                'name_en' => 'Personal Development',
                'name_ru' => 'Личностное развитие',
                'name_az' => 'Şəxsi İnkişaf',
                'slug' => 'kisisel-gelisim',
                'color' => 'purple'
            ],
            [
                'name_tr' => 'Şifa',
                'name_en' => 'Healing',
                'name_ru' => 'Исцеление',
                'name_az' => 'Şəfa',
                'slug' => 'sifa',
                'color' => 'emerald'
            ],
            [
                'name_tr' => 'Sağlık',
                'name_en' => 'Health',
                'name_ru' => 'Здоровье',
                'name_az' => 'Sağlamlıq',
                'slug' => 'saglik',
                'color' => 'blue'
            ],
            [
                'name_tr' => 'Meditasyon',
                'name_en' => 'Meditation',
                'name_ru' => 'Медитация',
                'name_az' => 'Meditasiya',
                'slug' => 'meditasyon',
                'color' => 'indigo'
            ],
            [
                'name_tr' => 'Ruhsal Gelişim',
                'name_en' => 'Spiritual Development',
                'name_ru' => 'Духовное развитие',
                'name_az' => 'Ruhani İnkişaf',
                'slug' => 'ruhsal-gelisim',
                'color' => 'pink'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
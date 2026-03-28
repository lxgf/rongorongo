<?php

namespace Database\Seeders;

use App\Models\Tablet;
use Illuminate\Database\Seeder;

class TabletSeeder extends Seeder
{
    public function run(): void
    {
        $tablets = [
            ['code' => 'A', 'name' => 'Tahua',                       'name_en' => 'Tahua',                       'name_ru' => 'Тахуа',                             'type' => 'tablet',    'location' => 'Congrégation des Sacrés-Cœurs, Rome'],
            ['code' => 'B', 'name' => 'Aruku Kurenga',               'name_en' => 'Aruku Kurenga',               'name_ru' => 'Аруку Куренга',                      'type' => 'tablet',    'location' => 'Congrégation des Sacrés-Cœurs, Rome'],
            ['code' => 'C', 'name' => 'Mamari',                      'name_en' => 'Mamari',                      'name_ru' => 'Мамари',                             'type' => 'tablet',    'location' => 'Congrégation des Sacrés-Cœurs, Rome'],
            ['code' => 'D', 'name' => 'Échancrée',                   'name_en' => 'Échancrée',                   'name_ru' => 'Эшанкре',                            'type' => 'tablet',    'location' => 'Congrégation des Sacrés-Cœurs, Rome'],
            ['code' => 'E', 'name' => 'Keiti',                       'name_en' => 'Keiti',                       'name_ru' => 'Кеити',                              'type' => 'tablet',    'location' => 'Musée d\'histoire naturelle, Santiago'],
            ['code' => 'F', 'name' => 'Chauvet Fragment',            'name_en' => 'Chauvet Fragment',            'name_ru' => 'Фрагмент Шове',                      'type' => 'fragment',  'location' => 'Private collection (lost)'],
            ['code' => 'G', 'name' => 'Small Santiago',              'name_en' => 'Small Santiago',              'name_ru' => 'Малая табличка Сантьяго',            'type' => 'tablet',    'location' => 'Musée d\'histoire naturelle, Santiago'],
            ['code' => 'H', 'name' => 'Large Santiago',              'name_en' => 'Large Santiago',              'name_ru' => 'Большая табличка Сантьяго',          'type' => 'tablet',    'location' => 'Musée d\'histoire naturelle, Santiago'],
            ['code' => 'I', 'name' => 'Santiago Staff',              'name_en' => 'Santiago Staff',              'name_ru' => 'Жезл Сантьяго',                     'type' => 'staff',     'location' => 'Musée d\'histoire naturelle, Santiago'],
            ['code' => 'J', 'name' => 'Large Reimiro',               'name_en' => 'Large Reimiro',               'name_ru' => 'Большое реймиро',                    'type' => 'reimiro',   'location' => 'Private collection (lost)'],
            ['code' => 'K', 'name' => 'Small London',                'name_en' => 'Small London',                'name_ru' => 'Малая Лондонская табличка',          'type' => 'tablet',    'location' => 'British Museum, London'],
            ['code' => 'L', 'name' => 'London Reimiro',              'name_en' => 'London Reimiro',              'name_ru' => 'Лондонское реймиро',                 'type' => 'reimiro',   'location' => 'British Museum, London'],
            ['code' => 'M', 'name' => 'Large Vienna',                'name_en' => 'Large Vienna',                'name_ru' => 'Большая Венская табличка',           'type' => 'tablet',    'location' => 'Weltmuseum Wien, Vienna'],
            ['code' => 'N', 'name' => 'Small Vienna',                'name_en' => 'Small Vienna',                'name_ru' => 'Малая Венская табличка',             'type' => 'tablet',    'location' => 'Weltmuseum Wien, Vienna'],
            ['code' => 'O', 'name' => 'Berlin',                      'name_en' => 'Berlin',                      'name_ru' => 'Берлинская табличка',                'type' => 'tablet',    'location' => 'Ethnologisches Museum, Berlin'],
            ['code' => 'P', 'name' => 'Great St. Petersburg',        'name_en' => 'Great St. Petersburg',        'name_ru' => 'Большая Петербургская табличка',     'type' => 'tablet',    'location' => 'Peter the Great Museum, St. Petersburg'],
            ['code' => 'Q', 'name' => 'Small St. Petersburg',        'name_en' => 'Small St. Petersburg',        'name_ru' => 'Малая Петербургская табличка',       'type' => 'tablet',    'location' => 'Peter the Great Museum, St. Petersburg'],
            ['code' => 'R', 'name' => 'Small Washington',            'name_en' => 'Small Washington',            'name_ru' => 'Малая Вашингтонская табличка',       'type' => 'tablet',    'location' => 'Smithsonian Institution, Washington'],
            ['code' => 'S', 'name' => 'Large Washington',            'name_en' => 'Large Washington',            'name_ru' => 'Большая Вашингтонская табличка',     'type' => 'tablet',    'location' => 'Smithsonian Institution, Washington'],
            ['code' => 'T', 'name' => 'Honolulu B-3629',             'name_en' => 'Honolulu B-3629',             'name_ru' => 'Гонолулу фрагмент B-3629',          'type' => 'fragment',  'location' => 'Bishop Museum, Honolulu'],
            ['code' => 'U', 'name' => 'Honolulu B-3623',             'name_en' => 'Honolulu B-3623',             'name_ru' => 'Гонолулу фрагмент B-3623',          'type' => 'fragment',  'location' => 'Bishop Museum, Honolulu'],
            ['code' => 'V', 'name' => 'Honolulu B-3622',             'name_en' => 'Honolulu B-3622',             'name_ru' => 'Гонолулу фрагмент B-3622',          'type' => 'fragment',  'location' => 'Bishop Museum, Honolulu'],
            ['code' => 'W', 'name' => 'Honolulu B-3621',             'name_en' => 'Honolulu B-3621',             'name_ru' => 'Гонолулу фрагмент B-3621',          'type' => 'fragment',  'location' => 'Bishop Museum, Honolulu'],
            ['code' => 'X', 'name' => 'Tangata Manu',                'name_en' => 'Tangata Manu',                'name_ru' => 'Тангата Ману',                       'type' => 'statuette', 'location' => 'Peter the Great Museum, St. Petersburg'],
            ['code' => 'Y', 'name' => 'Snuffbox',                    'name_en' => 'Snuffbox',                    'name_ru' => 'Табакерка',                           'type' => 'snuffbox',  'location' => 'Congrégation des Sacrés-Cœurs, Rome'],
            ['code' => 'Z', 'name' => 'Poike',                       'name_en' => 'Poike',                       'name_ru' => 'Табличка Пойке',                     'type' => 'fragment',  'location' => 'Private collection'],
        ];

        foreach ($tablets as $tablet) {
            Tablet::updateOrCreate(
                ['code' => $tablet['code']],
                $tablet,
            );
        }
    }
}

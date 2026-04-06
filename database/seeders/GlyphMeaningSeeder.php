<?php

namespace Database\Seeders;

use App\Models\Glyph;
use Illuminate\Database\Seeder;

class GlyphMeaningSeeder extends Seeder
{
    public function run(): void
    {
        $meanings = [
            // Confirmed
            ['code' => '040', 'meaning' => 'Crescent moon (waning)', 'meaning_ru' => 'Полумесяц (убывающий)', 'status' => 'confirmed', 'source' => 'Barthel 1958; lunar calendar on tablet C (Mamari) b4–b6', 'source_ru' => 'Бартель 1958; лунный календарь на табличке C (Мамари) b4–b6'],
            ['code' => '041', 'meaning' => 'Crescent moon (waxing)', 'meaning_ru' => 'Полумесяц (растущий)', 'status' => 'confirmed', 'source' => 'Barthel 1958; lunar calendar on tablet C (Mamari)', 'source_ru' => 'Бартель 1958; лунный календарь на табличке C (Мамари)'],
            ['code' => '050', 'meaning' => 'Full moon', 'meaning_ru' => 'Полная луна', 'status' => 'confirmed', 'source' => 'Barthel 1958; "old woman lighting earth oven" — Oceanic lunar mythology', 'source_ru' => 'Бартель 1958; «старуха зажигает земляную печь» — океанийская лунная мифология'],
            ['code' => '060', 'meaning' => 'New moon / dark moon', 'meaning_ru' => 'Новолуние / тёмная луна', 'status' => 'confirmed', 'source' => 'Barthel 1958; part of Mamari lunar calendar sequence', 'source_ru' => 'Бартель 1958; часть лунного календаря на табличке Мамари'],
            ['code' => '280', 'meaning' => 'Turtle (honu)', 'meaning_ru' => 'Черепаха (honu)', 'status' => 'confirmed', 'source' => 'Jaussen/Metoro 1871; matches Rapa Nui petroglyphs. Metoro consistently identified as "honu"', 'source_ru' => 'Жоссен/Меторо 1871; совпадает с петроглифами Рапа-Нуи. Меторо последовательно идентифицировал как «honu»'],

            // Proposed
            ['code' => '001', 'meaning' => 'Standing human figure', 'meaning_ru' => 'Стоящая человеческая фигура', 'status' => 'proposed', 'source' => 'Barthel 1958; pictographic resemblance. Most frequent glyph in corpus', 'source_ru' => 'Бартель 1958; пиктографическое сходство. Самый частый глиф в корпусе'],
            ['code' => '002', 'meaning' => 'Sitting figure / deity', 'meaning_ru' => 'Сидящая фигура / божество', 'status' => 'proposed', 'source' => 'Barthel 1958; trilobed head possibly represents godhead', 'source_ru' => 'Бартель 1958; трёхлопастная голова, возможно, изображает божество'],
            ['code' => '003', 'meaning' => 'Bird (manu)', 'meaning_ru' => 'Птица (manu)', 'status' => 'proposed', 'source' => 'Barthel 1958; Fischer 1997; pictographic identification', 'source_ru' => 'Бартель 1958; Фишер 1997; пиктографическая идентификация'],
            ['code' => '004', 'meaning' => 'Fish', 'meaning_ru' => 'Рыба', 'status' => 'proposed', 'source' => 'Barthel 1958; pictographic resemblance to marine fauna', 'source_ru' => 'Бартель 1958; пиктографическое сходство с морской фауной'],
            ['code' => '005', 'meaning' => 'Plant / tree', 'meaning_ru' => 'Растение / дерево', 'status' => 'proposed', 'source' => 'Barthel 1958; vertical form with branches', 'source_ru' => 'Бартель 1958; вертикальная форма с ветвями'],
            ['code' => '006', 'meaning' => 'Frigate bird (makohe)', 'meaning_ru' => 'Фрегат (makohe)', 'status' => 'proposed', 'source' => 'Barthel 1958; distinctive forked tail matches Fregata minor', 'source_ru' => 'Бартель 1958; характерный раздвоенный хвост совпадает с Fregata minor'],
            ['code' => '008', 'meaning' => 'Centipede / caterpillar (veri)', 'meaning_ru' => 'Многоножка / гусеница (veri)', 'status' => 'proposed', 'source' => 'Barthel 1958; segmented body form', 'source_ru' => 'Бартель 1958; сегментированная форма тела'],
            ['code' => '009', 'meaning' => 'Hand / arm', 'meaning_ru' => 'Рука / кисть', 'status' => 'proposed', 'source' => 'Barthel 1958; Pozdniakov 1996; pictographic', 'source_ru' => 'Бартель 1958; Поздняков 1996; пиктографическое сходство'],
            ['code' => '022', 'meaning' => 'Vulva / female symbol', 'meaning_ru' => 'Вульва / женский символ', 'status' => 'proposed', 'source' => 'Barthel 1958; Fischer 1997; fertility context in parallel texts', 'source_ru' => 'Бартель 1958; Фишер 1997; контекст плодородия в параллельных текстах'],
            ['code' => '063', 'meaning' => 'Sun (ra\'a)', 'meaning_ru' => 'Солнце (ra\'a)', 'status' => 'proposed', 'source' => 'Barthel 1958; radial form. Metoro reading inconsistent', 'source_ru' => 'Бартель 1958; радиальная форма. Чтение Меторо противоречиво'],
            ['code' => '076', 'meaning' => 'Phallus / male symbol', 'meaning_ru' => 'Фаллос / мужской символ', 'status' => 'proposed', 'source' => 'Fischer 1997; procreation chant hypothesis for tablet Tahua', 'source_ru' => 'Фишер 1997; гипотеза о песне деторождения для таблички Тахуа'],
            ['code' => '095', 'meaning' => 'Double-headed figure', 'meaning_ru' => 'Двуглавая фигура', 'status' => 'proposed', 'source' => 'Barthel 1958; possible dualistic deity or twin concept', 'source_ru' => 'Бартель 1958; возможно дуалистическое божество или концепция близнецов'],
            ['code' => '200', 'meaning' => 'Chief / king (ariki)', 'meaning_ru' => 'Вождь / король (ariki)', 'status' => 'proposed', 'source' => 'Barthel 1958; Pozdniakov 2007; seated figure with headgear, precedes name sequences', 'source_ru' => 'Бартель 1958; Поздняков 2007; сидящая фигура с головным убором, предшествует именным последовательностям'],
            ['code' => '380', 'meaning' => 'Astronomer / figure with raised hands', 'meaning_ru' => 'Астроном / фигура с поднятыми руками', 'status' => 'proposed', 'source' => 'Ferrara et al. 2024; associated with calendar sections', 'source_ru' => 'Феррара и др. 2024; связан с календарными разделами'],
            ['code' => '430', 'meaning' => 'Crayfish / lobster', 'meaning_ru' => 'Рак / лобстер', 'status' => 'proposed', 'source' => 'Barthel 1958; Metoro reading; pictographic match', 'source_ru' => 'Бартель 1958; чтение Меторо; пиктографическое совпадение'],
            ['code' => '530', 'meaning' => 'Squid / octopus (heke)', 'meaning_ru' => 'Кальмар / осьминог (heke)', 'status' => 'proposed', 'source' => 'Barthel 1958; tentacled form', 'source_ru' => 'Бартель 1958; форма с щупальцами'],
            ['code' => '600', 'meaning' => 'Bird-man (tangata manu)', 'meaning_ru' => 'Человек-птица (tangata manu)', 'status' => 'proposed', 'source' => 'Barthel 1958; Ferrara et al. 2024; linked to Orongo bird-man cult', 'source_ru' => 'Бартель 1958; Феррара и др. 2024; связан с культом человека-птицы в Оронго'],
            ['code' => '700', 'meaning' => 'Figure with staff / deity', 'meaning_ru' => 'Фигура с жезлом / божество', 'status' => 'proposed', 'source' => 'Barthel 1958; possibly Makemake or ancestor figure', 'source_ru' => 'Бартель 1958; возможно Макемаке или фигура предка'],
            ['code' => '730', 'meaning' => 'Double figure / copulation', 'meaning_ru' => 'Двойная фигура / совокупление', 'status' => 'proposed', 'source' => 'Fischer 1997; procreation chant hypothesis', 'source_ru' => 'Фишер 1997; гипотеза о песне деторождения'],
        ];

        foreach ($meanings as $item) {
            Glyph::where('barthel_code', $item['code'])->update([
                'meaning' => $item['meaning'],
                'meaning_ru' => $item['meaning_ru'],
                'meaning_status' => $item['status'],
                'meaning_source' => $item['source'],
                'meaning_source_ru' => $item['source_ru'],
            ]);
        }
    }
}

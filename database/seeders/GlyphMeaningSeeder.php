<?php

namespace Database\Seeders;

use App\Models\Glyph;
use Illuminate\Database\Seeder;

class GlyphMeaningSeeder extends Seeder
{
    public function run(): void
    {
        $meanings = [
            // Confirmed — reliable identification with scholarly consensus
            ['code' => '040', 'meaning' => 'Crescent moon (waning)', 'status' => 'confirmed', 'source' => 'Barthel 1958; lunar calendar on tablet C (Mamari) b4–b6'],
            ['code' => '041', 'meaning' => 'Crescent moon (waxing)', 'status' => 'confirmed', 'source' => 'Barthel 1958; lunar calendar on tablet C (Mamari)'],
            ['code' => '050', 'meaning' => 'Full moon', 'status' => 'confirmed', 'source' => 'Barthel 1958; "old woman lighting earth oven" — Oceanic lunar mythology'],
            ['code' => '060', 'meaning' => 'New moon / dark moon', 'status' => 'confirmed', 'source' => 'Barthel 1958; part of Mamari lunar calendar sequence'],
            ['code' => '280', 'meaning' => 'Turtle (honu)', 'status' => 'confirmed', 'source' => 'Jaussen/Metoro 1871; matches Rapa Nui petroglyphs. Metoro consistently identified as "honu"'],

            // Proposed — scholarly hypotheses with partial evidence
            ['code' => '001', 'meaning' => 'Standing human figure', 'status' => 'proposed', 'source' => 'Barthel 1958; pictographic resemblance. Most frequent glyph in corpus'],
            ['code' => '002', 'meaning' => 'Sitting figure / deity', 'status' => 'proposed', 'source' => 'Barthel 1958; trilobed head possibly represents godhead'],
            ['code' => '003', 'meaning' => 'Bird (manu)', 'status' => 'proposed', 'source' => 'Barthel 1958; Fischer 1997; pictographic identification'],
            ['code' => '004', 'meaning' => 'Fish', 'status' => 'proposed', 'source' => 'Barthel 1958; pictographic resemblance to marine fauna'],
            ['code' => '005', 'meaning' => 'Plant / tree', 'status' => 'proposed', 'source' => 'Barthel 1958; vertical form with branches'],
            ['code' => '006', 'meaning' => 'Frigate bird (makohe)', 'status' => 'proposed', 'source' => 'Barthel 1958; distinctive forked tail matches Fregata minor'],
            ['code' => '008', 'meaning' => 'Centipede / caterpillar (veri)', 'status' => 'proposed', 'source' => 'Barthel 1958; segmented body form'],
            ['code' => '009', 'meaning' => 'Hand / arm', 'status' => 'proposed', 'source' => 'Barthel 1958; Pozdniakov 1996; pictographic'],
            ['code' => '022', 'meaning' => 'Vulva / female symbol', 'status' => 'proposed', 'source' => 'Barthel 1958; Fischer 1997; fertility context in parallel texts'],
            ['code' => '063', 'meaning' => 'Sun (ra\'a)', 'status' => 'proposed', 'source' => 'Barthel 1958; radial form. Metoro reading inconsistent'],
            ['code' => '076', 'meaning' => 'Phallus / male symbol', 'status' => 'proposed', 'source' => 'Fischer 1997; procreation chant hypothesis for tablet Tahua'],
            ['code' => '095', 'meaning' => 'Double-headed figure', 'status' => 'proposed', 'source' => 'Barthel 1958; possible dualistic deity or twin concept'],
            ['code' => '200', 'meaning' => 'Chief / king (ariki)', 'status' => 'proposed', 'source' => 'Barthel 1958; Pozdniakov 2007; seated figure with headgear, precedes name sequences'],
            ['code' => '380', 'meaning' => 'Astronomer / figure with raised hands', 'status' => 'proposed', 'source' => 'Ferrara et al. 2024; associated with calendar sections'],
            ['code' => '430', 'meaning' => 'Crayfish / lobster', 'status' => 'proposed', 'source' => 'Barthel 1958; Metoro reading; pictographic match'],
            ['code' => '530', 'meaning' => 'Squid / octopus (heke)', 'status' => 'proposed', 'source' => 'Barthel 1958; tentacled form'],
            ['code' => '600', 'meaning' => 'Bird-man (tangata manu)', 'status' => 'proposed', 'source' => 'Barthel 1958; Ferrara et al. 2024; linked to Orongo bird-man cult'],
            ['code' => '700', 'meaning' => 'Figure with staff / deity', 'status' => 'proposed', 'source' => 'Barthel 1958; possibly Makemake or ancestor figure'],
            ['code' => '730', 'meaning' => 'Double figure / copulation', 'status' => 'proposed', 'source' => 'Fischer 1997; procreation chant hypothesis'],
        ];

        foreach ($meanings as $item) {
            Glyph::where('barthel_code', $item['code'])->update([
                'meaning' => $item['meaning'],
                'meaning_status' => $item['status'],
                'meaning_source' => $item['source'],
            ]);
        }
    }
}

<?php

namespace Tests\Feature\Api;

use App\Models\CompoundGlyph;
use App\Models\CompoundGlyphPart;
use App\Models\Glyph;
use App\Models\Tablet;
use App\Models\TabletLine;
use App\Models\TabletRendering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorpusApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $tablet = Tablet::create(['code' => 'A', 'name' => 'Tahua', 'name_en' => 'Tahua', 'name_ru' => 'Тахуа', 'type' => 'tablet']);
        $tablet->images()->create(['path' => 'tablet-photos/A/a.jpg', 'type' => 'photo', 'sort_order' => 0]);

        $moon = Glyph::create(['barthel_code' => '050', 'meaning' => 'Full moon', 'meaning_ru' => 'Полная луна', 'meaning_status' => 'confirmed']);
        $bird = Glyph::create(['barthel_code' => '600']);
        $moonA = $moon->renderings()->create(['code' => '050a']);
        $bird->renderings()->create(['code' => '600']);

        $ligature = CompoundGlyph::create(['code' => '050.600']);
        CompoundGlyphPart::create(['compound_glyph_id' => $ligature->id, 'glyph_id' => $moon->id, 'order' => 1]);
        CompoundGlyphPart::create(['compound_glyph_id' => $ligature->id, 'glyph_id' => $bird->id, 'order' => 2]);

        $recto = TabletLine::create(['tablet_id' => $tablet->id, 'side' => 0, 'line' => 1, 'direction' => 'rtl']);
        $verso = TabletLine::create(['tablet_id' => $tablet->id, 'side' => 1, 'line' => 1, 'direction' => 'ltr']);

        $first = TabletRendering::create(['tablet_line_id' => $recto->id, 'rendering_id' => $moonA->id, 'position' => 1, 'is_inverted' => true]);
        $first->images()->create(['path' => 'svg/A/a01-001.svg', 'type' => 'rendering-svg']);
        TabletRendering::create(['tablet_line_id' => $recto->id, 'compound_glyph_id' => $ligature->id, 'position' => 2]);
        TabletRendering::create(['tablet_line_id' => $verso->id, 'rendering_id' => $moonA->id, 'position' => 1]);
    }

    public function test_index_describes_api_and_stats(): void
    {
        $this->getJson('/api/v1')
            ->assertOk()
            ->assertJsonPath('version', 'v1')
            ->assertJsonPath('stats.tablets', 1)
            ->assertJsonPath('stats.occurrences', 3)
            ->assertJsonStructure(['endpoints' => ['corpus', 'tablets', 'line', 'glyphs', 'renderings', 'ligatures', 'occurrences']])
            ->assertJsonPath('endpoints.ligature', url('/api/v1/ligatures/050.600'));
    }

    public function test_tablets_list_and_detail(): void
    {
        $this->getJson('/api/v1/tablets')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'A')
            ->assertJsonPath('data.0.name_ru', 'Тахуа')
            ->assertJsonPath('data.0.lines_count', 2)
            ->assertJsonPath('data.0.occurrences_count', 3);

        $this->getJson('/api/v1/tablets/A')
            ->assertOk()
            ->assertJsonPath('data.photos.0', url('tablet-photos/A/a.jpg'))
            ->assertJsonPath('data.lines.0.side', 'a')
            ->assertJsonPath('data.lines.0.side_label', 'recto')
            ->assertJsonPath('data.lines.0.occurrences_count', 2)
            ->assertJsonPath('data.lines.1.side', 'b');
    }

    public function test_line_lists_occurrences_in_order(): void
    {
        $response = $this->getJson('/api/v1/tablets/A/lines/a/1')
            ->assertOk()
            ->assertJsonPath('data.direction', 'rtl')
            ->assertJsonPath('data.occurrences.0.glyph', '050')
            ->assertJsonPath('data.occurrences.0.rendering', '050a')
            ->assertJsonPath('data.occurrences.0.modifiers', ['inverted'])
            ->assertJsonPath('data.occurrences.0.svg.0', url('svg/A/a01-001.svg'))
            ->assertJsonPath('data.occurrences.1.type', 'ligature')
            ->assertJsonPath('data.occurrences.1.ligature_glyphs', ['050', '600']);

        // Side may also be given as index or recto/verso letter
        $this->getJson('/api/v1/tablets/A/lines/0/1')->assertOk()->assertJsonPath('data.side', 'a');
        $this->getJson('/api/v1/tablets/A/lines/v/1')->assertOk()->assertJsonPath('data.side', 'b');
        $this->assertArrayNotHasKey('tablet', $response->json('data.occurrences.0'));
    }

    public function test_glyph_detail_includes_meaning_renderings_ligatures_and_occurrences(): void
    {
        $this->getJson('/api/v1/glyphs/050')
            ->assertOk()
            ->assertJsonPath('data.meaning.en', 'Full moon')
            ->assertJsonPath('data.meaning.ru', 'Полная луна')
            ->assertJsonPath('data.meaning.status', 'confirmed')
            ->assertJsonPath('data.occurrences_count', 2)
            ->assertJsonPath('data.renderings.0.code', '050a')
            ->assertJsonPath('data.ligatures.0.code', '050.600')
            ->assertJsonPath('data.occurrences.0.tablet', 'A')
            ->assertJsonPath('data.occurrences.0.side', 'a');

        $this->getJson('/api/v1/glyphs')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.1.meaning', null);
    }

    public function test_renderings_and_ligatures(): void
    {
        $this->getJson('/api/v1/renderings')->assertOk()->assertJsonPath('data.0.code', '050a')->assertJsonPath('data.0.occurrences_count', 2);
        $this->getJson('/api/v1/renderings/050a')->assertOk()->assertJsonCount(2, 'data.occurrences');
        $this->getJson('/api/v1/ligatures')->assertOk()->assertJsonPath('data.0.glyphs', ['050', '600']);
        $this->getJson('/api/v1/ligatures/050.600')->assertOk()->assertJsonPath('data.occurrences.0.line', 1);
    }

    public function test_occurrences_are_paginated_and_filterable(): void
    {
        $this->getJson('/api/v1/occurrences?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson('/api/v1/occurrences?glyph=050')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/occurrences?ligature=050.600')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/occurrences?per_page=5000')->assertStatus(422);
    }

    public function test_corpus_dump_contains_every_level(): void
    {
        $this->getJson('/api/v1/corpus')
            ->assertOk()
            ->assertJsonPath('stats.occurrences', 3)
            ->assertJsonPath('tablets.0.lines.0.occurrences.1.ligature', '050.600')
            ->assertJsonCount(2, 'glyphs')
            ->assertJsonCount(2, 'renderings')
            ->assertJsonCount(1, 'ligatures');

        // Second request is served from the cached JSON string
        $this->getJson('/api/v1/corpus')->assertOk()->assertJsonPath('tablets.0.code', 'A');
    }

    public function test_api_is_stateless_cors_open_and_returns_json_errors(): void
    {
        $this->get('/api/v1/tablets', ['Origin' => 'https://example.org'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertCookieMissing(config('session.cookie'));

        $this->get('/api/v1/glyphs/999')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['message' => 'Not found.']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Glyph;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The home page (glyph catalog) renders when the corpus has glyphs.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Glyph::create(['barthel_code' => '001']);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

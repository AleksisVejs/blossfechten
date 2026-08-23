<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The API answers on its own host (api.blossfechtenriga.com). Google had
 * indexed that host and was ranking a contentless page against the club's own
 * brand term, so the root must not serve a page of its own.
 */
class ApiHostIsNotIndexableTest extends TestCase
{
    public function test_the_api_root_redirects_to_the_public_site(): void
    {
        config(['app.frontend_url' => 'https://blossfechtenriga.com']);

        $this->get('/')
            ->assertStatus(301)
            ->assertRedirect('https://blossfechtenriga.com');
    }

    public function test_a_trailing_slash_on_the_configured_url_is_not_doubled(): void
    {
        config(['app.frontend_url' => 'https://blossfechtenriga.com/']);

        $this->get('/')->assertRedirect('https://blossfechtenriga.com');
    }

    public function test_it_falls_back_to_the_public_site_when_no_url_is_configured(): void
    {
        // A misconfigured .env must never resurrect a page on this host.
        config(['app.frontend_url' => '', 'app.url' => '']);

        $this->get('/')->assertRedirect('https://blossfechtenriga.com');
    }

    public function test_the_api_root_no_longer_serves_a_page(): void
    {
        $this->get('/')->assertDontSee('Blossfechten', false);
    }
}

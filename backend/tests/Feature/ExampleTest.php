<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic smoke test that the application boots and routes.
     *
     * The root used to answer 200 with Laravel's welcome view. It now redirects
     * to the public site, because this host serves the API and Google was
     * indexing that page against the club's brand. ApiHostIsNotIndexableTest
     * covers the behaviour properly.
     */
    public function test_the_application_responds_on_the_root(): void
    {
        $response = $this->get('/');

        $response->assertStatus(301);
    }
}

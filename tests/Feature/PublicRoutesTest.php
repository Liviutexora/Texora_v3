<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_welcome_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('pages.for-businesses');
    }

    public function test_upload_file_route_does_not_exist(): void
    {
        // The old unauthenticated POST /upload-file route was removed.
        // Livewire registers its own livewire-*/upload-file endpoint, but our
        // custom /upload-file is gone — a GET to that exact path returns 404.
        $this->get('/upload-file')->assertStatus(404);
    }

    public function test_upload_page_route_does_not_exist(): void
    {
        $this->get('/upload')->assertStatus(404);
    }

    public function test_files_list_route_does_not_exist(): void
    {
        $this->get('/files')->assertStatus(404);
    }
}

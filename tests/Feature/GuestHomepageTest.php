<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestHomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Fotocopy Adhijaya');
        $response->assertSee('Lihat layanan');
    }

    public function test_guest_can_view_login_and_register(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}

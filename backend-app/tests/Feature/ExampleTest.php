<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_presents_mata_ko(): void
    {
        $this->withoutVite()->get('/')->assertOk()
            ->assertSee('Your vision,')
            ->assertSee('16') // live count of student questions
            ->assertSee('images/showcase/home.webp', false)
            ->assertSee('releases/latest/download/MataKo.apk', false)
            ->assertSee('Download for Android')
            ->assertDontSee('Source on GitHub')
            ->assertDontSee('laravel.com');
    }

    public function test_the_download_button_appears_once_an_apk_link_is_set(): void
    {
        config(['services.showcase.apk_url' => 'https://github.com/KertCainAbajo/MataKO/releases/latest']);

        $this->withoutVite()->get('/')->assertOk()
            ->assertSee('https://github.com/KertCainAbajo/MataKO/releases/latest', false)
            ->assertSee('Download the APK')
            ->assertDontSee('Android download coming soon');
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_locale_defaults_to_english(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'en')
                ->has('availableLocales', 2));
    }

    public function test_administrator_can_persist_indonesian_locale_in_the_session(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.index'))
            ->post(route('admin.language.update'), ['locale' => 'id'])
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('admin_locale', 'id');

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'id'));
    }

    public function test_unsupported_admin_locale_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.language.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');
    }

    public function test_public_and_login_pages_do_not_receive_an_active_admin_locale(): void
    {
        $this->get('/')->assertOk()->assertInertia(
            fn (Assert $page) => $page->where('locale', null),
        );

        $this->get('/login')->assertOk()->assertInertia(
            fn (Assert $page) => $page->where('locale', null),
        );
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}

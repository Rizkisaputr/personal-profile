<?php

namespace Tests\Feature\Auth;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_login_screen_receives_the_shared_profile_image(): void
    {
        Profile::create([
            'full_name' => 'Rizki Saputra',
            'professional_title' => 'Full Stack Web Developer',
            'profile_image' => 'profile/rizki.jpg',
        ]);

        $this->get(route('login'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->where('profileIdentity.name', 'Rizki Saputra')
            ->where('profileIdentity.professional_title', 'Full Stack Web Developer')
            ->where('profileIdentity.image_url', 'http://localhost:8000/storage/profile/rizki.jpg'));
    }

    public function test_login_screen_prefers_login_image_while_avatar_remains_profile_image(): void
    {
        Profile::create([
            'full_name' => 'Rizki Saputra',
            'profile_image' => 'profile/sidebar/avatar.jpg',
            'hero_image' => 'profile/hero/hero.png',
            'login_image' => 'profile/login/login.webp',
        ]);

        $this->get(route('login'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('profileIdentity.image_url', 'http://localhost:8000/storage/profile/sidebar/avatar.jpg')
            ->where('profileIdentity.login_image_url', 'http://localhost:8000/storage/profile/login/login.webp'));
    }

    public function test_login_image_falls_back_to_hero_then_profile_image(): void
    {
        $profile = Profile::create([
            'full_name' => 'Rizki Saputra',
            'profile_image' => 'profile/sidebar/avatar.jpg',
            'hero_image' => 'profile/hero/hero.png',
        ]);

        $this->get(route('login'))->assertInertia(fn (Assert $page) => $page
            ->where('profileIdentity.login_image_url', 'http://localhost:8000/storage/profile/hero/hero.png'));

        $profile->update(['hero_image' => null]);

        $this->get(route('login'))->assertInertia(fn (Assert $page) => $page
            ->where('profileIdentity.login_image_url', 'http://localhost:8000/storage/profile/sidebar/avatar.jpg'));
    }

    public function test_login_screen_has_no_broken_image_source_when_all_images_are_null(): void
    {
        Profile::create(['full_name' => 'Rizki Saputra']);

        $this->get(route('login'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('profileIdentity.image_url', null)
            ->where('profileIdentity.login_image_url', null));
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_users_are_rate_limited()
    {
        $user = User::factory()->create();

        RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }
}

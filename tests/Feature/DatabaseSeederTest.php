<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_admin_seeder_creates_an_admin_user(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin@personalprofile.local')->first();

        $this->assertNotNull($admin);
        $this->assertSame('Administrator', $admin->name);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('password', $admin->password));
    }
}

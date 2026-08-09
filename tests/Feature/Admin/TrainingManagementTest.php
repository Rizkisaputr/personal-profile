<?php

namespace Tests\Feature\Admin;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrainingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_training_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/trainings')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/trainings/Index'));
    }

    public function test_admin_can_create_update_and_delete_training(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/trainings', $this->data())
            ->assertRedirect('/admin/trainings');
        $training = Training::firstOrFail();

        $this->actingAs($admin)->put("/admin/trainings/{$training->id}", $this->data([
            'program_name' => 'Advanced English Program',
        ]))->assertRedirect('/admin/trainings');
        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'program_name' => 'Advanced English Program',
        ]);

        $this->actingAs($admin)->delete("/admin/trainings/{$training->id}")
            ->assertRedirect('/admin/trainings');
        $this->assertDatabaseMissing('trainings', ['id' => $training->id]);
    }

    public function test_non_admin_cannot_access_training_management(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/trainings')->assertForbidden();
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'institution' => 'Kampung Inggris Pare',
            'program_name' => 'English Language Program',
            'field_of_study' => 'English Language',
            'location' => 'Pare, Kediri, East Java, Indonesia',
            'start_date' => null,
            'end_date' => null,
            'credential_url' => null,
            'description' => 'English language training.',
            'sort_order' => 1,
            'is_active' => true,
        ], $overrides);
    }
}

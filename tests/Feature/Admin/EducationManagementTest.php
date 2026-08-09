<?php

namespace Tests\Feature\Admin;

use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EducationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_update_and_delete_education(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/educations')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/educations/Index'));

        $this->actingAs($admin)->post('/admin/educations', $this->data())->assertRedirect('/admin/educations');
        $education = Education::firstOrFail();

        $this->actingAs($admin)->put("/admin/educations/{$education->id}", $this->data(['degree' => 'Master Degree']))
            ->assertRedirect('/admin/educations');
        $this->assertDatabaseHas('educations', ['id' => $education->id, 'degree' => 'Master Degree']);

        $this->actingAs($admin)->delete("/admin/educations/{$education->id}")->assertRedirect('/admin/educations');
        $this->assertDatabaseMissing('educations', ['id' => $education->id]);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'institution' => 'Example University',
            'degree' => 'Bachelor Degree',
            'field_of_study' => 'Information Technology',
            'location' => 'Indonesia',
            'start_year' => 2018,
            'end_year' => 2022,
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides);
    }
}

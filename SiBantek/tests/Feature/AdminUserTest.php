<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@kemendikdasmen.go.id',
            'password' => bcrypt('Admin#1234'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $verifikator = User::create([
            'name' => 'Hendro Sucipto',
            'nip' => '197803152003121002',
            'email' => 'verifikator@kemendikdasmen.go.id',
            'password' => bcrypt('Verifikator#1234'),
            'role' => 'verifikator',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$verifikator->id}/status", [
            'status' => 'nonaktif',
            'catatan_nonaktif' => 'Sedang dalam evaluasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $verifikator->id,
            'status' => 'nonaktif',
            'catatan_nonaktif' => 'Sedang dalam evaluasi',
        ]);

        $response2 = $this->actingAs($admin)->post("/admin/users/{$verifikator->id}/status", [
            'status' => 'aktif',
        ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $verifikator->id,
            'status' => 'aktif',
            'catatan_nonaktif' => null,
        ]);
    }
}

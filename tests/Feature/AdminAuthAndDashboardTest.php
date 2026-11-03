<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->regularAdmin = User::create([
            'name' => 'Regular Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_guest_cannot_access_dashboard_api(): void
    {
        $response = $this->getJson('/api/admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_active_admin_can_login_and_records_last_login(): void
    {
        $this->assertNull($this->superAdmin->last_login_at);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'superadmin@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'email' => 'superadmin@example.com',
            ],
        ]);

        $this->assertAuthenticatedAs($this->superAdmin);

        $this->superAdmin->refresh();
        $this->assertNotNull($this->superAdmin->last_login_at);
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $inactiveUser = User::create([
            'name' => 'Deactivated User',
            'email' => 'deactivated@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_INACTIVE,
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'deactivated@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertGuest();
        $response->assertJsonValidationErrors('email');
    }

    public function test_admin_can_logout(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->postJson('/api/admin/logout');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertGuest();
    }

    public function test_admin_profile_can_be_viewed_and_updated(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->getJson('/api/admin/me');
        $response->assertStatus(200);
        $response->assertJson([
            'authenticated' => true,
            'user' => [
                'email' => 'superadmin@example.com',
            ],
        ]);

        $updateResponse = $this->putJson('/api/admin/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '1234567890',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $this->superAdmin->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_password_can_be_changed(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->putJson('/api/admin/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $this->superAdmin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->superAdmin->password));
    }

    public function test_super_admin_can_create_new_admin_user(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->postJson('/api/admin/users', [
            'name' => 'New Recruiter Staff',
            'email' => 'newrecruiter@example.com',
            'role' => User::ROLE_RECRUITER,
            'status' => User::STATUS_ACTIVE,
            'password' => 'securepass123',
            'password_confirmation' => 'securepass123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newrecruiter@example.com',
            'role' => User::ROLE_RECRUITER,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_status_can_be_toggled(): void
    {
        $this->actingAs($this->superAdmin);

        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->patchJson("/api/admin/users/{$targetUser->id}/toggle-status");
        $response->assertStatus(200);

        $targetUser->refresh();
        $this->assertEquals(User::STATUS_INACTIVE, $targetUser->status);
    }

    public function test_admin_cannot_deactivate_or_delete_self(): void
    {
        $this->actingAs($this->superAdmin);

        $toggleResponse = $this->patchJson("/api/admin/users/{$this->superAdmin->id}/toggle-status");
        $toggleResponse->assertStatus(422);

        $deleteResponse = $this->deleteJson("/api/admin/users/{$this->superAdmin->id}");
        $deleteResponse->assertStatus(422);
    }

    public function test_admin_user_can_be_soft_deleted_and_restored(): void
    {
        $this->actingAs($this->superAdmin);

        $targetUser = User::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $deleteResponse = $this->deleteJson("/api/admin/users/{$targetUser->id}");
        $deleteResponse->assertStatus(200);

        $this->assertSoftDeleted('users', ['id' => $targetUser->id]);

        $restoreResponse = $this->postJson("/api/admin/users/{$targetUser->id}/restore");
        $restoreResponse->assertStatus(200);

        $this->assertNotSoftDeleted('users', ['id' => $targetUser->id]);
    }

    public function test_dashboard_computes_and_renders_all_metrics(): void
    {
        $this->actingAs($this->superAdmin);

        // Create Active Job Opening
        $activeJob = JobOpening::create([
            'job_code' => 'JOB-TEST-001',
            'job_title' => 'Software Engineer',
            'department' => 'Tech',
            'vacancies' => 2,
            'status' => JobOpening::STATUS_PUBLISHED,
            'application_deadline' => Carbon::now()->addDays(10),
            'created_by' => $this->superAdmin->id,
        ]);

        // Create Expired Job Opening
        JobOpening::create([
            'job_code' => 'JOB-TEST-002',
            'job_title' => 'Old Job',
            'department' => 'Marketing',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_EXPIRED,
            'application_deadline' => Carbon::now()->subDays(5),
            'created_by' => $this->superAdmin->id,
        ]);

        // Create Job Applications
        // 1. Pending received today
        JobApplication::create([
            'application_code' => 'APP-TEST-001',
            'job_opening_id' => $activeJob->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'phone' => '1234567890',
            'status' => JobApplication::STATUS_PENDING,
            'crm_transfer_status' => JobApplication::CRM_PENDING,
            'submitted_at' => now(),
            'created_at' => now(),
        ]);

        // 2. Shortlisted transferred to CRM
        JobApplication::create([
            'application_code' => 'APP-TEST-002',
            'job_opening_id' => $activeJob->id,
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@example.com',
            'phone' => '0987654321',
            'status' => JobApplication::STATUS_SHORTLISTED,
            'crm_transfer_status' => JobApplication::CRM_TRANSFERRED,
            'crm_application_id' => 9999,
            'submitted_at' => now()->subDay(),
            'created_at' => now()->subDay(),
        ]);

        // 3. Rejected application
        JobApplication::create([
            'application_code' => 'APP-TEST-003',
            'job_opening_id' => $activeJob->id,
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie@example.com',
            'phone' => '1122334455',
            'status' => JobApplication::STATUS_REJECTED,
            'crm_transfer_status' => JobApplication::CRM_PENDING,
            'submitted_at' => now()->subDays(2),
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200);

        // Verify API JSON metrics contain all 10 required recruitment statistics
        $response->assertJson([
            'metrics' => [
                'total_job_openings' => 2,
                'active_vacancies' => 1,
                'expired_vacancies' => 1,
                'total_applications' => 3,
                'applications_today' => 1,
                'pending_applications' => 1,
                'shortlisted_applications' => 1,
                'rejected_applications' => 1,
                'crm_transferred_applications' => 1,
            ],
        ]);

        $this->assertCount(3, $response->json('recent_applications'));
        $this->assertNotEmpty($response->json('vacancy_wise_stats.data'));
    }
}

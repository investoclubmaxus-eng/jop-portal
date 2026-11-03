<?php

namespace Tests\Feature;

use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected JobOpening $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Recruiter Admin',
            'email' => 'admin@recruitment.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->job = JobOpening::create([
            'job_code' => 'JOB-APP-TEST-001',
            'job_title' => 'Senior Full Stack Developer',
            'department' => 'Engineering',
            'designation' => 'Senior Engineer',
            'vacancies' => 2,
            'status' => JobOpening::STATUS_PUBLISHED,
            'job_description' => 'Build high-performance recruitment portal.',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_candidate_can_apply_without_creating_account(): void
    {
        Storage::fake('public');

        $resume = UploadedFile::fake()->create('candidate_cv.pdf', 300, 'application/pdf');

        $response = $this->postJson("/api/jobs/{$this->job->job_code}/apply", [
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'email' => 'sarah.connor@example.com',
            'phone' => '+1 555-0199',
            'current_location' => 'Los Angeles, CA',
            'highest_qualification' => 'Master of Computer Science',
            'total_experience_years' => 5.5,
            'current_ctc' => 90000,
            'expected_ctc' => 120000,
            'resume' => $resume,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'candidate_name' => 'Sarah Connor',
            'job_title' => $this->job->job_title,
        ]);

        $this->assertDatabaseHas('job_applications', [
            'job_opening_id' => $this->job->id,
            'email' => 'sarah.connor@example.com',
            'phone' => '+1 555-0199',
            'status' => JobApplication::STATUS_SUBMITTED,
        ]);

        $this->assertDatabaseHas('application_status_history', [
            'new_status' => JobApplication::STATUS_SUBMITTED,
        ]);
    }

    public function test_duplicate_application_validation_prevents_multiple_submissions(): void
    {
        // First submission
        JobApplication::create([
            'application_code' => 'APP-FIRST-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'David',
            'last_name' => 'Miller',
            'email' => 'david.miller@example.com',
            'phone' => '+1 555-0188',
            'status' => JobApplication::STATUS_SUBMITTED,
        ]);

        // Attempt second submission with same email for same job opening
        $response = $this->postJson("/api/jobs/{$this->job->job_code}/apply", [
            'first_name' => 'David',
            'last_name' => 'Miller',
            'email' => 'david.miller@example.com',
            'phone' => '+1 555-9999',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_admin_can_view_applications_list_with_filters(): void
    {
        $this->actingAs($this->admin);

        JobApplication::create([
            'application_code' => 'APP-LIST-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'email' => 'emma@example.com',
            'phone' => '+1 555-0123',
            'status' => JobApplication::STATUS_PENDING,
        ]);

        $response = $this->getJson('/api/admin/applications');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'full_name' => 'Emma Watson',
            'application_code' => 'APP-LIST-001',
        ]);
        $response->assertJsonStructure([
            'applications',
            'stats' => ['total', 'pending', 'shortlisted', 'rejected', 'crm_transferred'],
            'job_openings',
        ]);
    }

    public function test_admin_can_screen_candidate_and_update_status_with_history(): void
    {
        $this->actingAs($this->admin);

        $app = JobApplication::create([
            'application_code' => 'APP-SCREEN-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Lucas',
            'last_name' => 'Scott',
            'email' => 'lucas@example.com',
            'phone' => '+1 555-0177',
            'status' => JobApplication::STATUS_SUBMITTED,
        ]);

        $response = $this->patchJson("/api/admin/applications/{$app->id}/status", [
            'status' => JobApplication::STATUS_SHORTLISTED,
            'screening_remarks' => 'Strong experience with React and Laravel architectures.',
        ]);

        $response->assertStatus(200);
        $app->refresh();
        $this->assertEquals(JobApplication::STATUS_SHORTLISTED, $app->status);
        $this->assertEquals('Strong experience with React and Laravel architectures.', $app->screening_remarks);

        // Verify Status History Log
        $this->assertDatabaseHas('application_status_history', [
            'application_id' => $app->id,
            'old_status' => JobApplication::STATUS_SUBMITTED,
            'new_status' => JobApplication::STATUS_SHORTLISTED,
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_bulk_status_update_updates_multiple_applications(): void
    {
        $this->actingAs($this->admin);

        $app1 = JobApplication::create([
            'application_code' => 'APP-BULK-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Candidate',
            'last_name' => 'One',
            'email' => 'one@example.com',
            'phone' => '111',
            'status' => JobApplication::STATUS_PENDING,
        ]);

        $app2 = JobApplication::create([
            'application_code' => 'APP-BULK-002',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Candidate',
            'last_name' => 'Two',
            'email' => 'two@example.com',
            'phone' => '222',
            'status' => JobApplication::STATUS_PENDING,
        ]);

        $response = $this->postJson('/api/admin/applications/bulk-status', [
            'application_ids' => [$app1->id, $app2->id],
            'status' => JobApplication::STATUS_SHORTLISTED,
            'remarks' => 'Bulk shortlisted after initial profile review.',
        ]);

        $response->assertStatus(200);

        $app1->refresh();
        $app2->refresh();
        $this->assertEquals(JobApplication::STATUS_SHORTLISTED, $app1->status);
        $this->assertEquals(JobApplication::STATUS_SHORTLISTED, $app2->status);
    }

    public function test_crm_transfer_fails_if_candidate_is_not_shortlisted(): void
    {
        $this->actingAs($this->admin);

        // Candidate is currently PENDING (not shortlisted)
        $pendingApp = JobApplication::create([
            'application_code' => 'APP-PEND-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Unscreened',
            'last_name' => 'Applicant',
            'email' => 'unscreened@example.com',
            'phone' => '333',
            'status' => JobApplication::STATUS_PENDING,
        ]);

        $response = $this->postJson('/api/admin/applications/crm-transfer', [
            'application_ids' => [$pendingApp->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Only shortlisted candidates can be transferred to CRM. Candidate "Unscreened Applicant" has status "pending". Please shortlist candidate first.',
        ]);

        $pendingApp->refresh();
        $this->assertEquals(JobApplication::CRM_PENDING, $pendingApp->crm_transfer_status);
    }

    public function test_crm_transfer_succeeds_for_shortlisted_candidates(): void
    {
        $this->actingAs($this->admin);

        $shortlistedApp = JobApplication::create([
            'application_code' => 'APP-SHORT-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Top',
            'last_name' => 'Candidate',
            'email' => 'top@example.com',
            'phone' => '444',
            'status' => JobApplication::STATUS_SHORTLISTED,
            'crm_transfer_status' => JobApplication::CRM_PENDING,
        ]);

        $response = $this->postJson('/api/admin/applications/crm-transfer', [
            'application_ids' => [$shortlistedApp->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'transferred_count' => 1,
        ]);

        $shortlistedApp->refresh();
        $this->assertEquals(JobApplication::CRM_TRANSFERRED, $shortlistedApp->crm_transfer_status);
        $this->assertNotNull($shortlistedApp->crm_application_id);
        $this->assertNotNull($shortlistedApp->transferred_at);
    }

    public function test_export_applications_streams_csv(): void
    {
        $this->actingAs($this->admin);

        JobApplication::create([
            'application_code' => 'APP-EXPORT-001',
            'job_opening_id' => $this->job->id,
            'first_name' => 'Export',
            'last_name' => 'Person',
            'email' => 'export@example.com',
            'phone' => '555-5555',
            'status' => JobApplication::STATUS_SUBMITTED,
        ]);

        $response = $this->get('/api/admin/applications/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}

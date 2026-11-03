<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobOpeningAndPublicCareersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Module 3: Job Opening Management Module Tests (API)
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_job_openings_index(): void
    {
        $this->actingAs($this->admin);

        JobOpening::create([
            'job_code' => 'JOB-2026-001',
            'job_title' => 'Backend Architect',
            'department' => 'Engineering',
            'vacancies' => 2,
            'status' => JobOpening::STATUS_PUBLISHED,
            'job_description' => 'Great backend role',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/admin/jobs');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'job_title' => 'Backend Architect',
            'job_code' => 'JOB-2026-001',
        ]);
    }

    public function test_admin_can_create_job_opening_as_draft_and_published(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/admin/jobs', [
            'job_title' => 'Frontend React Engineer',
            'job_code' => 'JOB-2026-099',
            'department' => 'Frontend',
            'designation' => 'Lead Developer',
            'vacancies' => 3,
            'employment_type' => 'Full-time',
            'experience_min' => 3,
            'experience_max' => 6,
            'salary_min' => 70000,
            'salary_max' => 95000,
            'job_description' => 'Build high-performance React applications.',
            'required_qualifications' => 'B.S. in CS or equivalent experience',
            'required_skills' => 'React, TypeScript, Tailwind, Next.js',
            'application_deadline' => Carbon::now()->addDays(20)->format('Y-m-d'),
            'status' => JobOpening::STATUS_PUBLISHED,
            'action_type' => 'publish',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('job_openings', [
            'job_code' => 'JOB-2026-099',
            'job_title' => 'Frontend React Engineer',
            'status' => JobOpening::STATUS_PUBLISHED,
            'vacancies' => 3,
        ]);
    }

    public function test_admin_can_edit_and_update_job_opening(): void
    {
        $this->actingAs($this->admin);

        $job = JobOpening::create([
            'job_code' => 'JOB-2026-002',
            'job_title' => 'Old Title',
            'department' => 'QA',
            'designation' => 'QA Tester',
            'vacancies' => 1,
            'employment_type' => 'Full-time',
            'status' => JobOpening::STATUS_DRAFT,
            'job_description' => 'Test software',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->putJson("/api/admin/jobs/{$job->id}", [
            'job_title' => 'Senior QA Automation Engineer',
            'job_code' => 'JOB-2026-002',
            'department' => 'Quality Assurance',
            'designation' => 'QA Lead',
            'employment_type' => 'Full-time',
            'vacancies' => 2,
            'job_description' => 'Updated automated testing description.',
            'status' => JobOpening::STATUS_PUBLISHED,
        ]);

        $response->assertStatus(200);
        $job->refresh();
        $this->assertEquals('Senior QA Automation Engineer', $job->job_title);
        $this->assertEquals(2, $job->vacancies);
        $this->assertEquals(JobOpening::STATUS_PUBLISHED, $job->status);
    }

    public function test_admin_can_view_job_details_with_received_applications(): void
    {
        $this->actingAs($this->admin);

        $job = JobOpening::create([
            'job_code' => 'JOB-2026-003',
            'job_title' => 'DevOps Specialist',
            'department' => 'Infra',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_PUBLISHED,
            'job_description' => 'Kubernetes and Cloud infra',
            'created_by' => $this->admin->id,
        ]);

        JobApplication::create([
            'application_code' => 'APP-TEST-999',
            'job_opening_id' => $job->id,
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            'email' => 'alex@example.com',
            'phone' => '555-1234',
            'status' => JobApplication::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $response = $this->getJson("/api/admin/jobs/{$job->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'job_title' => 'DevOps Specialist',
            'full_name' => 'Alexander Hamilton',
            'application_code' => 'APP-TEST-999',
        ]);
    }

    public function test_admin_can_change_status_activate_expire_unpublish(): void
    {
        $this->actingAs($this->admin);

        $job = JobOpening::create([
            'job_code' => 'JOB-2026-004',
            'job_title' => 'Mobile Engineer',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_PUBLISHED,
            'job_description' => 'Flutter & iOS',
            'created_by' => $this->admin->id,
        ]);

        // Unpublish (move to draft)
        $response = $this->patchJson("/api/admin/jobs/{$job->id}/status", [
            'status' => JobOpening::STATUS_DRAFT,
        ]);
        $response->assertStatus(200);
        $job->refresh();
        $this->assertEquals(JobOpening::STATUS_DRAFT, $job->status);

        // Expire vacancy
        $response2 = $this->patchJson("/api/admin/jobs/{$job->id}/status", [
            'status' => JobOpening::STATUS_EXPIRED,
        ]);
        $response2->assertStatus(200);
        $job->refresh();
        $this->assertEquals(JobOpening::STATUS_EXPIRED, $job->status);
    }

    public function test_admin_can_soft_delete_job_opening(): void
    {
        $this->actingAs($this->admin);

        $job = JobOpening::create([
            'job_code' => 'JOB-2026-005',
            'job_title' => 'Content Writer',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_DRAFT,
            'job_description' => 'Write blogs',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->deleteJson("/api/admin/jobs/{$job->id}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('job_openings', ['id' => $job->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Module 4: Public Job Listing Module Tests (API)
    |--------------------------------------------------------------------------
    */

    public function test_public_careers_displays_active_vacancies_and_automatically_hides_expired(): void
    {
        // 1. Active unexpired job - SHOULD BE VISIBLE
        JobOpening::create([
            'job_code' => 'JOB-PUB-001',
            'job_title' => 'Active Python Developer',
            'department' => 'Data Science',
            'vacancies' => 2,
            'status' => JobOpening::STATUS_PUBLISHED,
            'application_deadline' => Carbon::now()->addDays(10),
            'job_description' => 'Machine Learning pipelines with Python.',
        ]);

        // 2. Expired by status - SHOULD BE HIDDEN
        JobOpening::create([
            'job_code' => 'JOB-PUB-002',
            'job_title' => 'Expired Sales Lead',
            'department' => 'Sales',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_EXPIRED,
            'job_description' => 'Sales description',
        ]);

        // 3. Expired by past deadline date - SHOULD BE HIDDEN
        JobOpening::create([
            'job_code' => 'JOB-PUB-003',
            'job_title' => 'Past Deadline Marketing Manager',
            'department' => 'Marketing',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_PUBLISHED,
            'application_deadline' => Carbon::now()->subDays(3),
            'job_description' => 'Marketing description',
        ]);

        // 4. Draft - SHOULD BE HIDDEN
        JobOpening::create([
            'job_code' => 'JOB-PUB-004',
            'job_title' => 'Secret Unannounced Project',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_DRAFT,
            'job_description' => 'Draft role',
        ]);

        $response = $this->getJson('/api/jobs');
        $response->assertStatus(200);

        $titles = collect($response->json('jobs'))->pluck('job_title')->all();

        // Visible
        $this->assertContains('Active Python Developer', $titles);

        // Automatically hidden
        $this->assertNotContains('Expired Sales Lead', $titles);
        $this->assertNotContains('Past Deadline Marketing Manager', $titles);
        $this->assertNotContains('Secret Unannounced Project', $titles);
    }

    public function test_candidate_can_view_job_details(): void
    {
        $job = JobOpening::create([
            'job_code' => 'JOB-PUB-100',
            'job_title' => 'Cloud Security Architect',
            'department' => 'Cybersecurity',
            'vacancies' => 1,
            'employment_type' => 'Remote',
            'salary_min' => 110000,
            'salary_max' => 150000,
            'status' => JobOpening::STATUS_PUBLISHED,
            'application_deadline' => Carbon::now()->addDays(14),
            'job_description' => 'Design robust IAM and zero-trust perimeter architectures.',
            'required_qualifications' => 'CISSP certification and 5+ years security experience.',
            'required_skills' => ['AWS', 'Zero Trust', 'SOC2', 'Penetration Testing'],
        ]);

        $response = $this->getJson("/api/jobs/{$job->job_code}");
        $response->assertStatus(200);
        $response->assertJson([
            'job' => [
                'job_code' => 'JOB-PUB-100',
                'job_title' => 'Cloud Security Architect',
                'department' => 'Cybersecurity',
            ],
        ]);
    }

    public function test_candidate_can_apply_for_job_with_resume_upload(): void
    {
        Storage::fake('public');

        $job = JobOpening::create([
            'job_code' => 'JOB-PUB-200',
            'job_title' => 'Product Analyst',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_PUBLISHED,
            'application_deadline' => Carbon::now()->addDays(7),
            'job_description' => 'Data driven decision making.',
        ]);

        $resumeFile = UploadedFile::fake()->create('resume_john_doe.pdf', 500, 'application/pdf');

        $response = $this->postJson("/api/jobs/{$job->job_code}/apply", [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'phone' => '+1 555-9876',
            'current_location' => 'San Francisco, CA',
            'highest_qualification' => 'Master of Science',
            'total_experience_years' => 3.5,
            'current_ctc' => 85000,
            'expected_ctc' => 105000,
            'resume' => $resumeFile,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'candidate_name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'job_opening_id' => $job->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'status' => JobApplication::STATUS_SUBMITTED,
        ]);

        $this->assertDatabaseHas('application_documents', [
            'document_type' => 'resume',
            'file_name' => 'resume_john_doe.pdf',
        ]);
    }

    public function test_cannot_apply_to_expired_vacancy(): void
    {
        $expiredJob = JobOpening::create([
            'job_code' => 'JOB-PUB-EXPIRED',
            'job_title' => 'Closed Position',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_EXPIRED,
            'application_deadline' => Carbon::now()->subDays(10),
            'job_description' => 'No longer open.',
        ]);

        $response = $this->postJson("/api/jobs/{$expiredJob->job_code}/apply", [
            'first_name' => 'Late',
            'last_name' => 'Applicant',
            'email' => 'late@example.com',
            'phone' => '123456789',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('job_applications', [
            'job_opening_id' => $expiredJob->id,
            'email' => 'late@example.com',
        ]);
    }

    public function test_candidate_applying_via_promotion_link_records_source_in_database(): void
    {
        $job = JobOpening::create([
            'job_code' => 'JOB-NAUKRI-001',
            'job_title' => 'Lead Database Administrator',
            'department' => 'Infrastructure',
            'vacancies' => 2,
            'status' => JobOpening::STATUS_PUBLISHED,
            'application_deadline' => Carbon::now()->addDays(20),
            'job_description' => 'Database high availability.',
        ]);

        $response = $this->postJson("/api/jobs/{$job->job_code}/apply", [
            'first_name' => 'Amit',
            'last_name' => 'Sharma',
            'email' => 'amit.sharma@example.com',
            'phone' => '+91 9876543210',
            'source' => 'naukri',
            'current_location' => 'Bangalore, India',
            'highest_qualification' => 'B.E. Computer Science',
            'total_experience_years' => 6.0,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'source' => 'Naukri',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'job_opening_id' => $job->id,
            'email' => 'amit.sharma@example.com',
            'source' => 'naukri',
            'status' => JobApplication::STATUS_SUBMITTED,
        ]);
    }

    public function test_admin_job_details_displays_promotion_and_posting_urls(): void
    {
        $this->actingAs($this->admin);

        $job = JobOpening::create([
            'job_code' => 'JOB-PROMO-001',
            'job_title' => 'Product Manager',
            'department' => 'Product',
            'vacancies' => 1,
            'status' => JobOpening::STATUS_PUBLISHED,
            'job_description' => 'Product roadmap.',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/admin/jobs/{$job->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'job' => ['job_code', 'job_title'],
            'promotion_urls' => ['direct_job', 'direct_apply', 'linkedin', 'naukri', 'indeed', 'whatsapp'],
        ]);

        $urls = $response->json('promotion_urls');
        $this->assertStringContainsString('source=linkedin', $urls['linkedin']);
        $this->assertStringContainsString('source=naukri', $urls['naukri']);
        $this->assertStringContainsString('source=indeed', $urls['indeed']);
        $this->assertStringContainsString('source=whatsapp', $urls['whatsapp']);
    }
}

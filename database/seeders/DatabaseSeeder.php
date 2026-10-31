<?php

namespace Database\Seeders;

use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@jobportal.com'],
            [
                'name' => 'System Super Admin',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]
        );

        // 2. Create Recruiter Admin
        User::updateOrCreate(
            ['email' => 'recruiter@jobportal.com'],
            [
                'name' => 'Sarah Connor (Recruiter)',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_RECRUITER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => now()->subDay(),
            ]
        );

        // 3. Create Inactive Admin (for testing inactive login prevention)
        User::updateOrCreate(
            ['email' => 'inactive@jobportal.com'],
            [
                'name' => 'Inactive Staff Member',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_INACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => now()->subMonths(2),
            ]
        );

        // 4. Create Sample Job Openings
        $job1 = JobOpening::updateOrCreate(
            ['job_code' => 'JOB-2026-001'],
            [
                'job_title' => 'Senior Laravel Full-Stack Developer',
                'department' => 'Engineering',
                'designation' => 'Senior Software Engineer',
                'vacancies' => 3,
                'employment_type' => 'Full-time',
                'experience_min' => 4,
                'experience_max' => 8,
                'salary_min' => 800000.00,
                'salary_max' => 1400000.00,
                'job_description' => 'Looking for experienced Laravel and Vue/React engineers to build enterprise CRM and job portals.',
                'required_qualifications' => 'B.Tech / B.E in Computer Science or equivalent',
                'required_skills' => ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Docker', 'Tailwind CSS'],
                'published_at' => now()->subDays(10),
                'application_deadline' => Carbon::now()->addDays(20),
                'status' => JobOpening::STATUS_PUBLISHED,
                'created_by' => $superAdmin->id,
            ]
        );

        $job2 = JobOpening::updateOrCreate(
            ['job_code' => 'JOB-2026-002'],
            [
                'job_title' => 'Lead UI/UX Product Designer',
                'department' => 'Product Design',
                'designation' => 'Product Designer',
                'vacancies' => 2,
                'employment_type' => 'Full-time',
                'experience_min' => 3,
                'experience_max' => 6,
                'salary_min' => 700000.00,
                'salary_max' => 1200000.00,
                'job_description' => 'Lead UX research and Figma designs for high-traffic enterprise recruitment systems.',
                'required_qualifications' => 'Degree in Design / Fine Arts or equivalent portfolio',
                'required_skills' => ['Figma', 'Prototyping', 'User Research', 'Design Systems'],
                'published_at' => now()->subDays(5),
                'application_deadline' => Carbon::now()->addDays(15),
                'status' => JobOpening::STATUS_PUBLISHED,
                'created_by' => $superAdmin->id,
            ]
        );

        $job3 = JobOpening::updateOrCreate(
            ['job_code' => 'JOB-2026-003'],
            [
                'job_title' => 'DevOps & AWS Cloud Engineer',
                'department' => 'Infrastructure',
                'designation' => 'Cloud Specialist',
                'vacancies' => 1,
                'employment_type' => 'Remote',
                'experience_min' => 5,
                'experience_max' => 10,
                'salary_min' => 1200000.00,
                'salary_max' => 1800000.00,
                'job_description' => 'Manage CI/CD pipelines, AWS ECS/EKS clusters, and secure infrastructure.',
                'required_qualifications' => 'AWS Certified Solutions Architect',
                'required_skills' => ['AWS', 'Kubernetes', 'Docker', 'Terraform', 'CI/CD'],
                'published_at' => now()->subDays(30),
                'application_deadline' => Carbon::now()->subDays(5), // Expired vacancy
                'status' => JobOpening::STATUS_EXPIRED,
                'created_by' => $superAdmin->id,
            ]
        );

        $job4 = JobOpening::updateOrCreate(
            ['job_code' => 'JOB-2026-004'],
            [
                'job_title' => 'Talent Acquisition & HR Executive',
                'department' => 'Human Resources',
                'designation' => 'HR Specialist',
                'vacancies' => 2,
                'employment_type' => 'Full-time',
                'experience_min' => 2,
                'experience_max' => 5,
                'salary_min' => 450000.00,
                'salary_max' => 700000.00,
                'job_description' => 'Sourcing candidates, managing interview schedules, and applicant tracking.',
                'required_qualifications' => 'MBA in HR or related discipline',
                'required_skills' => ['Talent Sourcing', 'Communication', 'HRMS', 'Interviewing'],
                'published_at' => now()->subDays(2),
                'application_deadline' => Carbon::now()->addDays(30),
                'status' => JobOpening::STATUS_ACTIVE,
                'created_by' => $superAdmin->id,
            ]
        );

        // 5. Create Sample Job Applications
        $applicationsData = [
            [
                'application_code' => 'APP-2026-001',
                'job_opening_id' => $job1->id,
                'first_name' => 'Michael',
                'last_name' => 'Scott',
                'email' => 'michael.scott@example.com',
                'phone' => '+1 555-0101',
                'current_location' => 'New York, NY',
                'highest_qualification' => 'B.S. in Computer Science',
                'total_experience_years' => 5.5,
                'current_ctc' => 900000.00,
                'expected_ctc' => 1200000.00,
                'source' => 'linkedin',
                'status' => JobApplication::STATUS_SHORTLISTED,
                'crm_transfer_status' => JobApplication::CRM_TRANSFERRED,
                'crm_application_id' => 10001,
                'transferred_at' => now()->subDays(1),
                'submitted_at' => now()->subDays(3),
                'created_at' => now()->subDays(3),
            ],
            [
                'application_code' => 'APP-2026-002',
                'job_opening_id' => $job1->id,
                'first_name' => 'Jim',
                'last_name' => 'Halpert',
                'email' => 'jim.halpert@example.com',
                'phone' => '+1 555-0102',
                'current_location' => 'Austin, TX',
                'highest_qualification' => 'B.Tech IT',
                'total_experience_years' => 4.0,
                'current_ctc' => 850000.00,
                'expected_ctc' => 1100000.00,
                'source' => 'naukri',
                'status' => JobApplication::STATUS_PENDING,
                'crm_transfer_status' => JobApplication::CRM_PENDING,
                'submitted_at' => now(), // Received today!
                'created_at' => now(),
            ],
            [
                'application_code' => 'APP-2026-003',
                'job_opening_id' => $job1->id,
                'first_name' => 'Pam',
                'last_name' => 'Beesly',
                'email' => 'pam.beesly@example.com',
                'phone' => '+1 555-0103',
                'current_location' => 'Chicago, IL',
                'highest_qualification' => 'B.Sc Software Engineering',
                'total_experience_years' => 3.5,
                'current_ctc' => 750000.00,
                'expected_ctc' => 950000.00,
                'source' => 'linkedin',
                'status' => JobApplication::STATUS_SUBMITTED,
                'crm_transfer_status' => JobApplication::CRM_PENDING,
                'submitted_at' => now(), // Received today!
                'created_at' => now(),
            ],
            [
                'application_code' => 'APP-2026-004',
                'job_opening_id' => $job2->id,
                'first_name' => 'Dwight',
                'last_name' => 'Schrute',
                'email' => 'dwight.schrute@example.com',
                'phone' => '+1 555-0104',
                'current_location' => 'Seattle, WA',
                'highest_qualification' => 'Master of Interaction Design',
                'total_experience_years' => 6.0,
                'current_ctc' => 1000000.00,
                'expected_ctc' => 1300000.00,
                'source' => 'indeed',
                'status' => JobApplication::STATUS_SHORTLISTED,
                'crm_transfer_status' => JobApplication::CRM_TRANSFERRED,
                'crm_application_id' => 10002,
                'transferred_at' => now()->subHours(6),
                'submitted_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
            ],
            [
                'application_code' => 'APP-2026-005',
                'job_opening_id' => $job2->id,
                'first_name' => 'Ryan',
                'last_name' => 'Howard',
                'email' => 'ryan.howard@example.com',
                'phone' => '+1 555-0105',
                'current_location' => 'San Francisco, CA',
                'highest_qualification' => 'B.A. Arts',
                'total_experience_years' => 1.0,
                'current_ctc' => 500000.00,
                'expected_ctc' => 900000.00,
                'status' => JobApplication::STATUS_REJECTED,
                'crm_transfer_status' => JobApplication::CRM_PENDING,
                'screening_remarks' => 'Does not meet minimum 3 years UI/UX experience requirement.',
                'submitted_at' => now()->subDays(4),
                'created_at' => now()->subDays(4),
            ],
            [
                'application_code' => 'APP-2026-006',
                'job_opening_id' => $job3->id,
                'first_name' => 'Stanley',
                'last_name' => 'Hudson',
                'email' => 'stanley.hudson@example.com',
                'phone' => '+1 555-0106',
                'current_location' => 'Atlanta, GA',
                'highest_qualification' => 'B.S. Information Systems',
                'total_experience_years' => 8.0,
                'current_ctc' => 1400000.00,
                'expected_ctc' => 1700000.00,
                'status' => JobApplication::STATUS_SHORTLISTED,
                'crm_transfer_status' => JobApplication::CRM_TRANSFERRED,
                'crm_application_id' => 10003,
                'transferred_at' => now()->subDays(8),
                'submitted_at' => now()->subDays(12),
                'created_at' => now()->subDays(12),
            ],
            [
                'application_code' => 'APP-2026-007',
                'job_opening_id' => $job4->id,
                'first_name' => 'Toby',
                'last_name' => 'Flenderson',
                'email' => 'toby.flenderson@example.com',
                'phone' => '+1 555-0107',
                'current_location' => 'Boston, MA',
                'highest_qualification' => 'MBA HR',
                'total_experience_years' => 4.2,
                'current_ctc' => 600000.00,
                'expected_ctc' => 750000.00,
                'status' => JobApplication::STATUS_PENDING,
                'crm_transfer_status' => JobApplication::CRM_PENDING,
                'submitted_at' => now(), // Received today!
                'created_at' => now(),
            ],
        ];

        foreach ($applicationsData as $appData) {
            JobApplication::updateOrCreate(
                ['application_code' => $appData['application_code']],
                $appData
            );
        }
    }
}

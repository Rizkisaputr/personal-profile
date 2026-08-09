<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Technology;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Enterprise Operations Platform',
                'slug' => 'enterprise-operations-platform',
                'short_description' => 'Integrated web platform for managing business operations, workflows, reporting, production, and internal processes.',
                'category' => 'Business System',
                'is_featured' => true,
                'description' => <<<'TEXT'
Project Overview

An integrated business application designed to centralize operational workflows, internal management, reporting, and day-to-day business processes.

Problem

Business operations often involve disconnected data, manual coordination, and multiple workflows that make monitoring and reporting difficult.

My Role

Full Stack Development, system architecture, database design, backend development, frontend implementation, infrastructure, and deployment.

Solution

Designed and developed a centralized web application that connects operational processes into a structured and maintainable system.

Key Features

- Operational dashboard
- Business workflow management
- Reporting
- Production and operational monitoring
- User access management
- Data management
- File management
- Responsive web interface
TEXT,
                'technologies' => ['Laravel', 'PHP', 'MySQL', 'Vue.js', 'Docker', 'Nginx', 'Cloudflare'],
            ],
            [
                'title' => 'Property Rental Management System',
                'slug' => 'property-rental-management-system',
                'short_description' => 'Integrated property and rental management application for rooms, tenants, contracts, payments, and operational records.',
                'category' => 'Property Management',
                'is_featured' => true,
                'description' => <<<'TEXT'
Project Overview

A web application designed to manage rental property operations from tenant registration and room management through contracts and recurring payments.

Problem

Property operations require consistent monitoring of tenants, rooms, contracts, payments, and availability.

My Role

Full Stack Development

Solution

Created an integrated system that centralizes property and tenant information and simplifies recurring administrative processes.

Key Features

- Property management
- Room management
- Tenant records
- Reservations
- Rental contracts
- Contract extensions
- Payment tracking
- Utility management
- Occupancy reporting
TEXT,
                'technologies' => ['Laravel', 'Vue.js', 'TypeScript', 'MySQL', 'Inertia.js', 'Tailwind CSS'],
            ],
            [
                'title' => 'Cloud File Storage Service',
                'slug' => 'cloud-file-storage-service',
                'short_description' => 'Centralized file storage API for uploading, retrieving, and managing application files using object storage.',
                'category' => 'Cloud Infrastructure',
                'is_featured' => true,
                'description' => <<<'TEXT'
Project Overview

A dedicated file service designed to separate application file storage from the main application server.

Problem

Storing uploaded files directly on application servers makes scaling, migration, and storage management more difficult.

My Role

Backend & Infrastructure Development

Solution

Built a centralized API layer for file upload, metadata management, retrieval, and object storage integration.

Key Features

- File upload API
- Object storage integration
- File metadata
- File retrieval
- Reusable file ID
- Centralized storage
- Integration with multiple applications
TEXT,
                'technologies' => ['TypeScript', 'REST API', 'Cloudflare'],
            ],
            [
                'title' => 'Decision-Oriented Management Dashboard',
                'slug' => 'decision-oriented-management-dashboard',
                'legacy_slug' => 'executive-dashboard-system',
                'short_description' => 'Management dashboard designed to transform operational data into focused information for decision making.',
                'category' => 'Dashboard',
                'is_featured' => false,
                'description' => <<<'TEXT'
Project Overview

A dashboard designed around business questions, measurable objectives, and actionable information.

Problem

Traditional dashboards can contain large amounts of data without clearly supporting management decisions.

My Role

System Analysis & Full Stack Development

Solution

Designed a structured dashboard approach that connects business objectives, key questions, indicators, and operational information.

Key Features

- KPI monitoring
- Period comparison
- Operational summary
- Decision-oriented information
- Responsive dashboard interface
TEXT,
                'technologies' => ['Laravel', 'Vue.js', 'MySQL', 'Tailwind CSS'],
            ],
            [
                'title' => 'Cloud Application Infrastructure',
                'slug' => 'cloud-application-infrastructure',
                'legacy_slug' => 'infrastructure-migration-deployment',
                'short_description' => 'Cloud deployment architecture for web applications using containers, reverse proxy, managed storage, and separated database infrastructure.',
                'category' => 'Infrastructure',
                'is_featured' => false,
                'description' => <<<'TEXT'
Project Overview

A deployment architecture designed to improve maintainability, separation of services, storage management, and application reliability.

My Role

Infrastructure & Deployment

Solution

Structured infrastructure consisting of application server, reverse proxy, Docker containers, external database, DNS/proxy, and object storage.

Key Features

- Docker deployment
- Nginx reverse proxy
- External database
- Cloudflare proxy and DNS
- Object storage
- SSL
- Backup strategy
TEXT,
                'technologies' => ['Docker', 'Nginx', 'Linux', 'AWS', 'Cloudflare', 'MySQL'],
            ],
        ];

        foreach ($projects as $sortOrder => $data) {
            $technologyNames = $data['technologies'];
            $legacySlug = $data['legacy_slug'] ?? null;
            unset($data['technologies'], $data['legacy_slug']);

            $project = Project::query()
                ->where('slug', $data['slug'])
                ->when($legacySlug, fn ($query) => $query->orWhere('slug', $legacySlug))
                ->first();

            $attributes = $data + [
                'project_date' => null,
                'client_name' => null,
                'is_published' => true,
                'sort_order' => $sortOrder,
            ];

            if ($project) {
                $project->update($attributes);
            } else {
                $project = Project::create($attributes);
            }

            $project->technologies()->sync(
                Technology::whereIn('name', $technologyNames)->pluck('id'),
            );
        }
    }
}

# AWS-System

AWS-System is a role-based document automation platform for creating, managing, previewing, and sharing business documents. It is built as a Laravel + Inertia.js app with a Vue 3 frontend and is focused on structured document entry, branded PDF output, and secure public sharing.

GitHub repository: https://github.com/EE2506/AWS-System

## Overview

The application currently supports:

* authenticated document creation and editing
* role-based access control for Admin and User accounts
* server-side PDF generation with multi-page templates
* shareable public links with expiry controls
* responsive document detail and public view pages
* client auto-suggest in the document form
* branded layouts with updated logo assets

## Supported Documents

* Statement of Account (SOA)
* Purchase Order (PO)
* Quotation (QT)
* Delivery Receipt (DR)

## Tech Stack

* Backend: Laravel 11
* Frontend: Vue 3 + Inertia.js
* Styling: Tailwind CSS v4.1
* UI: shadcn/ui-style components and Magic UI
* RBAC: Spatie Laravel Permission
* Database: SQLite for local development, MySQL for production
* PDF Engine: DomPDF / Snappy

## Key Features

* Role-based document access for Admin and User roles
* Document forms for SOA, PO, Quotation, and Delivery Receipt
* Auto-calculated totals and discount handling
* Recent-client auto-suggestions to speed up data entry
* Public document sharing with expiring links
* Copy-link and revoke-link actions from the document detail page
* PDF preview and download from both private and public views
* Responsive layouts for desktop and mobile

## Local Development

1. Clone the repository from GitHub:

```bash
git clone https://github.com/EE2506/AWS-System.git
cd AWS-System
```

2. Install PHP dependencies with `composer install`.
3. Install Node dependencies with `npm install`.
4. Copy `.env.example` to `.env` and configure the database connection.
5. Generate an application key with `php artisan key:generate`.
6. Run migrations with `php artisan migrate`.
7. Seed roles and starter data if needed with `php artisan db:seed`.
8. Start the Laravel app with `php artisan serve`.
9. Start the frontend build watcher with `npm run dev`.

## Production Build

Build the frontend assets before deploying:

```bash
npm run build
```

## Notes

* The app uses updated branded logo assets in authenticated, guest, and public views.
* Public links are time-limited and show expiry state in the UI.
* PDF templates were updated for cleaner pagination and more consistent totals.

# AWS-System - Document Automation Platform

AWS-System is a role-based web application designed to automate the creation, management, and distribution of business documents. It acts as a specialized, document-focused Google Drive for structured business records like Statements of Account, Purchase Orders, Quotations, and Delivery Receipts.

## 🚀 Features

*   **Role-Based Access Control (RBAC):** Distinct Admin and User roles ensuring users only see their own documents while Admins have full system control.
*   **Automated Document Creation:** Streamlined data entry forms that map directly to professional PDFs.
*   **Auto-Incrementing Control Numbers:** Smart, prefix-based control numbers (e.g., `S-1004`, `PO-1001`) that automatically increment per document type.
*   **Client Auto-Suggest:** Intelligent dropdowns that remember past clients and auto-fill contact details to speed up data entry.
*   **PDF Generation:** Server-side generation of high-quality PDFs with precise formatting.
*   **Secure Sharing:** Generate temporary, secure public links to share documents directly with clients.

## 🛠️ Tech Stack

*   **Backend:** Laravel 11, Breeze / Sanctum, Spatie Permission
*   **Frontend:** Vue 3 (Composition API), Inertia.js
*   **Styling:** Tailwind CSS v4.1, shadcn/vue, Magic UI
*   **Database:** SQLite (Local) / MySQL (Production)
*   **PDF Engine:** DomPDF / Snappy

## 💻 Local Development

1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install Node dependencies: `npm install`
4. Copy `.env.example` to `.env` and configure your database
5. Generate application key: `php artisan key:generate`
6. Run migrations (and seeders if applicable): `php artisan migrate`
7. Start the development server: `php artisan serve`
8. Start the Vite bundler: `npm run dev`

## 📦 Production Deployment

To build the frontend assets for production (e.g., on Hostinger shared hosting):
```bash
npm run build
```

## 📄 Supported Documents
*   Statement of Account (SOA)
*   Purchase Order (PO)
*   Quotation (QT)
*   Delivery Receipt (DR)

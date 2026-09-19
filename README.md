ZATCA.app - Saudi E-Invoicing & Compliance Platform 🇸🇦

ZATCA.app is a full-stack, enterprise-grade e-invoicing web platform built with Laravel and Livewire. It provides a seamless, secure, and fully compliant solution for businesses operating in Saudi Arabia to issue, manage, and report electronic invoices in accordance with the Zakat, Tax and Customs Authority (ZATCA / Fatoora) regulations for Phase 1 (Generation) and Phase 2 (Integration).

 Key Features

 Invoice & Quotation Management

B2B & B2C Compliance: Supports generation of Standard Tax Invoices (B2B/B2G) and Simplified Tax Invoices (B2C).

Automated Calculations: Real-time tax (VAT 15%) computation, line-item discounts, and subtotal handling.

Quotations & Debit/Credit Notes: Issue compliant debit and credit notes directly linked to original tax invoices.

Custom PDF & Printing: Generates print-ready PDF invoices formatted according to Saudi regulatory layout standards.

 ZATCA Phase 1 & 2 Readiness

Cryptographic QR Code Generation: On-the-fly TLV Base64 encoding for invoice QR codes containing seller VAT, timestamp, totals, and cryptographic stamps.

XML / UBL 2.1 Standard: Structured invoice data formatted for seamless submission to ZATCA's Fatoora API portal.

Cryptographic Hashing & Signing: ECDSA signing and SHA-256 hash validation for audit-proof record keeping.

UUID Tracking: Universal Unique Identifier generation per transaction.

 Business & Multi-Tenant Features

Organization Profiles: Manage commercial registration (CR), VAT identification number, and business branding (logos, stamps).

Payment Tracking: Real-time tracking for unpaid, partially paid, and cleared client balances.

Client & Supplier Directory: Centralized management of buyer identification details and tax metadata.

Livewire Interactive UI: Ultra-fast, single-page application experience without heavy browser reloads.

🛠️ Tech Stack & Architecture

Backend Framework: Laravel 11.x

Reactivity / Frontend: Livewire 3.x, Alpine.js, Tailwind CSS

Database: MySQL

PDF & Security Engine: Dompdf / TCPDF, OpenSSL Cryptographic Utilities

Architecture Pattern: Service Repository Pattern & Event-Driven Architecture

🚀 Getting Started Locally

Prerequisites

PHP >= 8.2

Composer

Node.js & NPM

MySQL Database

Installation Steps

Clone the Repository:

git clone https://github.com/saeedawd/zatca-invoicing-platform.git
cd zatca-invoicing-platform


Install Dependencies:

composer install
npm install && npm run build


Configure Environment:
Copy the environment template and set your database and ZATCA credentials:

cp .env.example .env
php artisan key:generate


Run Migrations & Seeders:

php artisan migrate --seed


Start Local Development Server:

php artisan serve


 Security & Privacy Notice

This repository contains NO production keys, sensitive API credentials, or digital certificates.

Configuration properties rely strictly on environment variables (.env).

Certificate paths, private keys (.pem, .pfx), and local database files are explicitly ignored via .gitignore.

 Author & Contact

Elsaid Awad

Full-Stack Laravel / SaaS Developer

GitHub: @saeedawd

Website: zatca.app

Disclaimer: ZATCA.app is built independently to fulfill Saudi e-invoicing technical specifications.<img width="1520" height="855" alt="{805B931C-FB80-465E-99A3-403A0E6B5D3C}" src="https://github.com/user-attachments/assets/d2201b9c-3478-48bd-b0e0-6e33a16d0caa" />

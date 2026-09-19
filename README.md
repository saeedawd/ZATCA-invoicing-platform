# ZATCA.app 🇸🇦
### Saudi E-Invoicing & Compliance Platform

A full-stack e-invoicing platform built for Saudi businesses, designed around ZATCA's e-invoicing requirements and workflows.

> Built independently with Laravel and Livewire, focusing on real-world invoicing, VAT, payment tracking, and ZATCA integration workflows.

<img width="1520" height="855" alt="ZATCA.app Dashboard" src="https://github.com/user-attachments/assets/d2201b9c-3478-48bd-b0e0-6e33a16d0caa" />

---

## 🚀 Overview

ZATCA.app is an enterprise-grade Saudi e-invoicing platform built with **Laravel and Livewire**.

The platform brings invoicing, quotations, VAT calculations, payment tracking, customer management, PDF generation, QR codes, XML generation, and ZATCA integration workflows into a single system.

It is designed around the technical requirements and workflows of **Saudi e-invoicing — Phase 1 and Phase 2**.

---

## ✨ Key Features

### 🧾 Invoice & Quotation Management

- B2B, B2C, and B2G invoice workflows
- Standard Tax Invoices
- Simplified Tax Invoices
- Quotations
- Debit Notes
- Credit Notes
- Invoice-to-note relationship tracking
- Automatic VAT calculation (15%)
- Line-item discounts
- Subtotal and total calculations
- Invoice status tracking
- Custom PDF invoice generation
- Print-ready Saudi invoice layouts

---

### 🇸🇦 ZATCA Phase 1 & Phase 2

Built around Saudi e-invoicing technical workflows, including:

- QR Code generation
- TLV encoding
- Base64 encoding
- Seller VAT number
- Invoice timestamp
- Invoice totals
- Cryptographic stamp handling
- XML invoice generation
- UBL 2.1 structure
- Invoice UUID generation
- Invoice hashing
- ECDSA signing
- SHA-256 validation
- ZATCA API integration workflow

---

## 🏢 Business Management

### Organization Management

Each business can manage:

- Commercial Registration (CR)
- VAT Registration Number
- Company information
- Business address
- Logo
- Stamp
- Invoice branding

### 💳 Payment Tracking

Track customer payments in real time:

- Unpaid invoices
- Partially paid invoices
- Fully paid invoices
- Outstanding balances
- Payment history
- Invoice payment status

### 👥 Customers & Suppliers

Centralized management for:

- Customers
- Suppliers
- VAT numbers
- Commercial registration numbers
- Business identification
- Billing information
- Tax-related metadata

---

## ⚡ Interactive Experience

The application uses **Livewire 3** to provide a fast, interactive experience without requiring full-page browser reloads for most operations.

The interface combines:

- Livewire
- Alpine.js
- Tailwind CSS
- Responsive dashboards
- Interactive tables
- Real-time form validation
- Dynamic invoice management

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 |
| PHP | PHP 8.3+ |
| Frontend | Livewire 3 |
| JavaScript | Alpine.js |
| CSS | Tailwind CSS |
| Database | MySQL |
| PDF | Dompdf / TCPDF |
| Cryptography | OpenSSL |
| API | REST / ZATCA APIs |
| Architecture | Service & Repository Pattern |
| Events | Event-Driven Architecture |

---

## 🏗️ Architecture

The application follows a modular architecture designed to keep business logic separated from controllers and UI components.

```text
Application
│
├── Controllers
├── Livewire Components
├── Services
├── Repositories
├── Events & Listeners
├── Models
├── Policies
└── Support / Helpers

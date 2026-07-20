# AI Agent Handover & System Architecture Guide

This document acts as an onboarding guide for other AI agents taking over development in this repository.

## 🚀 Overview of Webmon

**Webmon** is a Laravel-based web monitoring application powered by **Filament PHP (v3/v5)** for its administration dashboard. It performs recurring checks on monitored websites (checking status codes, response times, SSL certificate validity, and domain expirations via WHOIS query).

---

## 🛠️ System Architecture & Workflow

### 1. Monitoring Command
The core monitoring checks are defined in a Laravel console command:
- **Command:** `php artisan monitor:websites`
- **Location:** [MonitorWebsites.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Console/Commands/MonitorWebsites.php)
- **Workflow:**
  1. Iterates over all records in the `websites` table.
  2. Uses `cURL` to check HTTP response code, response time, and content.
  3. Checks SSL peer certificate expiration date.
  4. Resolves TLD and queries WHOIS servers (using socket connections) to fetch domain expiry date.
  5. Logs each check in the `uptime_logs` database table.
  6. Compares current check results with the previous check. If a status change (UP/DOWN/SSL status) or domain expiry warning (<= 30 days) occurs, triggers email alerts.
  7. Sends summary email via Laravel Mail to addresses defined in the `notification_emails` table (or falls back to default `User` emails if empty).

---

## 📂 Core Directory & Code Structure

### 1. Database & Models
- [Website.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Models/Website.php): Represents a monitored website. Contains `product_id` (foreign key to the Product model), `pic_phone`, `pic_email`, `url`, etc.
- [Product.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Models/Product.php): Represents a package/product from the inventory system (e.g., Support Level, Plan tier).
- [UptimeLogs.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Models/UptimeLogs.php): Represents history logs for uptime/SSL status.
- [NotificationEmail.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Models/NotificationEmail.php): Email recipients for notification alerts.

### 2. Filament Dashboard Architecture
Resources use a modular architecture style where schemas and tables are extracted into separate files for clean code organization:

#### Websites Resource
- [WebsitesResource.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Filament/Resources/Websites/WebsitesResource.php)
- **Form Schema:** [WebsitesForm.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Filament/Resources/Websites/Schemas/WebsitesForm.php)
  - Features smart phone number formatting: Automatically sanitizes local inputs (e.g., `0123456789`) to international standards (e.g., `+60123456789`) using a `dehydrateStateUsing` hook.
- **Table Schema:** [WebsitesTable.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Filament/Resources/Websites/Tables/WebsitesTable.php)
  - Displays a green WhatsApp chat button linking to `https://wa.me/{phone_number}` when a contact phone number is present.
  - Displays the Package column referencing the Product relationship `product.name`.
  - Displays the Remark column.

#### Products Resource (Inventory Module)
- [ProductResource.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Filament/Resources/Products/ProductResource.php)
- **Form Schema:** [ProductForm.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Filament/Resources/Products/Schemas/ProductForm.php)
- **Table Schema:** [ProductsTable.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Filament/Resources/Products/Tables/ProductsTable.php)

### 3. Notifications & Mail
- **Mailable:** [WebsiteDownAlert.php](file:///Users/clemchooi/Codex/webmon-app/Webmon/app/Mail/WebsiteDownAlert.php)
- **Subject Title:** `Website Monitoring Alert`
- **Mail View:** `emails.website-down`

---

## ⚙️ Environment Configuration

- Environment config resides in [.env](file:///Users/clemchooi/Codex/webmon-app/Webmon/.env).
- Email notifications are configured via SMTP credentials. 
- Ensure `MAIL_MAILER=smtp` is set for live deployments, or `MAIL_MAILER=log` to dump emails to `storage/logs/laravel.log` during testing.

---

## 🤖 AI Agent Coding Guidelines

When writing code in this repository:
1. **GitHub Pushes**: Do NOT push code automatically. Always wait for the user to explicitly request a push (defined in [.agents/AGENTS.md](file:///Users/clemchooi/Codex/webmon-app/.agents/AGENTS.md)).
2. **Form / Table Structure**: Follow the established pattern of extracting forms into `<ResourceName>Form.php` and tables into `<ResourceName>Table.php` under their respective folders.
3. **Database Changes**: Write migrations for structural changes. Run `php artisan migrate` and verify tests using `php artisan test` before presenting work to the user.

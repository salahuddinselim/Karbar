# Hishab — single-store business manager

Created by **Salah Uddin Selim**.

A plain HTML/CSS(Tailwind)/PHP/MySQL business manager for a single shop: sales,
purchases, expenses, a customer/supplier credit ledger ("khata"), inventory,
invoicing, and profit & loss reporting — no framework, no build step, runs on
any shared PHP host.

## Stack

- PHP 8+ with PDO (prepared statements only, no query building from strings)
- MySQL / MariaDB
- Tailwind via CDN (no Node build step) + a small `assets/css/app.css` override file
- Vanilla JS for the one interactive form (transaction line items)

## Security measures (the "safety first" part)

- **SQL injection**: every query goes through PDO prepared statements with
  bound parameters — nowhere does user input get concatenated into SQL.
- **Passwords**: hashed with `password_hash()` (bcrypt, cost 12), verified
  with `password_verify()`. Never stored or logged in plaintext.
- **CSRF**: every state-changing form carries a per-session token
  (`includes/csrf.php`); POST handlers reject requests with a missing/invalid
  token via `hash_equals()` (403).
- **XSS**: all dynamic output goes through `e()` (`htmlspecialchars`) before
  being echoed into HTML.
- **Session hardening**: `httponly` + `samesite=Lax` cookies, session ID
  regenerated on login (fixation protection), idle timeout, secure flag
  auto-enabled when `APP_HTTPS=1`.
- **File uploads**: receipt images are validated by real content-sniffed MIME
  type (`finfo`, not the client-supplied `Content-Type`), size-capped at 5MB,
  renamed to a random filename, and the upload folder has a `.htaccess` that
  refuses to execute any script even if one gets uploaded.
- **Access control**: every page and action re-checks the session + the
  module permission server-side (`includes/permissions.php`) — the UI never
  is the only thing hiding an action.
- **Login throttling**: basic per-session rate limit (8 attempts / 5 minutes)
  to slow down password guessing.
- **Security headers**: `X-Content-Type-Options`, `X-Frame-Options`,
  `Referrer-Policy`, a restrictive `Content-Security-Policy`, and HSTS when
  served over HTTPS.
- **No secrets in the repo**: `.env` (real credentials) is gitignored;
  `.env.example` documents the required keys. `config/` and `includes/` are
  also blocked from direct web access via `.htaccess`.

## Setup

1. Create the database and load the schema:
   ```bash
   mysql -u root -p -e "CREATE DATABASE hishab CHARACTER SET utf8mb4;"
   mysql -u root -p hishab < schema.sql
   ```
2. Copy `.env.example` to `.env` and fill in your DB credentials + a random
   `APP_SECRET`. Set `APP_HTTPS=1` once you're serving over HTTPS.
3. Point your web server's document root at this folder (`php-app/`), or run
   the PHP built-in server for local development:
   ```bash
   php -S localhost:8000
   ```
4. Visit `/setup.php` to create the store + owner account (one-time; it
   auto-redirects to `/login.php` once a user already exists).

## Structure

```
config/        DB connection + env loading (blocked from web access)
includes/      auth, permissions, CSRF, ledger math, layout partials (blocked from web access)
actions/       POST-only handlers that mutate data (create/update/delete)
uploads/       receipt image storage (script execution disabled)
*.php          the actual pages (index, products, parties, transactions, reports, staff, ...)
schema.sql     MySQL schema — run once to provision the database
```

Business logic mirrors `../app`'s `src/lib` and `src/actions` 1:1 (see the
ledger sign convention documented at the top of `includes/ledger.php`), so
the two versions should always behave identically given the same input.

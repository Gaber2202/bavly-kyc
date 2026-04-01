# Refactor plan — security — performance — production

## 1. Refactor plan (architecture)

### What changed

- **Query layer**: `App\Services\Kyc\KycRecordQueryService` centralizes listing/export filters and enforces that non-admins cannot apply `created_by` scoping (defense in depth with `KycRecordFilterRequest`).
- **Commands / domain**: `App\Services\Kyc\KycRecordService` owns create/update/soft-delete transactions and audit emission; controllers stay thin.
- **Admin users**: `App\Services\Admin\AdminUserService` owns user lifecycle + password reset + structured audit metadata (including role transitions).
- **HTTP validation**: `KycRecordFilterRequest`, `ReportFilterRequest`, `AdminResetPasswordRequest` validate query/body inputs instead of ad-hoc `$request->filled()` chains.
- **Authorization**: `UserPolicy` + `authorizeResource()` on `UserController`; existing `KycRecordPolicy` retained. **Route model binding** for `{kyc}` returns **404** when the viewer cannot `view` the record (anti-enumeration / IDOR mitigation).
- **Analytics**: `AnalyticsService` now uses fewer round-trips (MySQL KPI rollup), `reorder()` before aggregates to avoid invalid `GROUP BY`, **join-based** top creators (no N+1), employee performance rows suitable for export, optional **cache** via `config/analytics.php`.

### Folder structure

Services are grouped under `app/Services/Kyc` and `app/Services/Admin` to keep boundaries obvious as the app grows.

---

## 2. Security checklist

- [x] **Password hashing**: `users.password` uses the `hashed` cast; `bcrypt` via Laravel defaults. No plaintext persistence.
- [x] **Mass assignment**: `User::$fillable` excludes `password`, `must_change_password`, `last_login_at`. `KycRecord::$fillable` excludes `created_by` / `updated_by`; services set these explicitly or use controlled persistence paths.
- [x] **Sessions**: database driver by default; `http_only=true`; `same_site=lax`; `secure` driven by `SESSION_SECURE_COOKIE` (set `true` behind HTTPS).
- [x] **Login throttling**: `throttle:login` + `RateLimiter::for('login')`.
- [x] **Sensitive operations throttling**: `admin-sensitive`, `export`, `reports` limiters.
- [x] **Active account gate**: `EnsureUserIsActive` + `Auth::attempt` includes `is_active`.
- [x] **IDOR**: Policies on every mutation; **custom `Route::bind('kyc')`** hides unauthorized records as **404**.
- [x] **Admin routes**: `role:admin` middleware + `UserPolicy` + FormRequest authorization.
- [x] **CSRF**: web middleware stack (default).
- [x] **Audit logging**: `admin.user_created`, `admin.user_updated` (includes role_before/after when changed), `admin.password_reset`, `admin.user_deleted`, `kyc.*`. `ActivityLogger` strips `*password*` properties.
- [x] **Output escaping**: Blade `{{ }}` for untrusted values; avoid `{!! !!}` for user-controlled fields.
- [x] **Debug**: `.env.example` ships with `APP_DEBUG=false` for production guidance.
- [x] **Proxy safety**: `TRUSTED_PROXIES` controls `TrustProxies` (avoid wide open `*` on hardened VPS if you know upstream IPs).
- [x] **Security headers**: `SecurityHeaders` middleware (baseline framing / MIME sniffing).

---

## 3. Performance checklist

- [x] **Indexes** (migration `0001_01_01_000008_...`): composite indexes aligned to typical reporting filters (`status+created_at`, `service_type+created_at`, `created_at+created_by`).
- [x] **Reports**: KPI rollup query on MySQL; aggregates call `reorder()` before grouping; top employees via **join + group** (no per-row `User::find`).
- [x] **N+1**: listing uses `with(['creator'])`; user detail uses `loadCount` for KYC counts.
- [x] **Caching**: `ANALYTICS_CACHE_TTL` / `config/analytics.php` — tune upward with Redis for busy dashboards.
- [x] **Date range + employee filters**: validated via `ReportFilterRequest`; non-admin reports forced to `employee_id = self`.

---

## 4. Production readiness (Hostinger / generic PHP hosting)

1. `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL`.
2. Run `php artisan config:cache route:cache view:cache` on deploy.
3. `storage/` + `bootstrap/cache/` writable by PHP user.
4. HTTPS enabled → `SESSION_SECURE_COOKIE=true`.
5. Set `TRUSTED_PROXIES` to known proxy IPs when not on generic shared hosting.
6. **Rotate** seeded credentials; never ship default `SEED_ADMIN_PASSWORD` to production without rotation.
7. Queue: default `database` works on shared hosting; move to Redis only if needed.
8. Logs: `LOG_LEVEL=error` in production; monitor `storage/logs`.

---

## 5. Deployment checklist (quick)

- [ ] Composer production install (`--no-dev --optimize-autoloader`)
- [ ] `npm ci && npm run build`
- [ ] Migrate DB
- [ ] Cache config/routes/views
- [ ] Permissions on `storage`, `bootstrap/cache`
- [ ] Verify HTTPS + cookies
- [ ] Smoke test login, KYC CRUD, admin user reset, reports

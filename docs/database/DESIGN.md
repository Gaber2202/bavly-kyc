# MySQL schema design — KYC management (Laravel)

Target: **MySQL 8.x**, **InnoDB**, **utf8mb4_unicode_ci** (full Arabic coverage, Hostinger-compatible).  
Philosophy: **one principal KYC row per intake** (wide table is appropriate here), **normalized audit and security sidecars**, optional **materialized snapshots** for scale.

Executable DDL: [mysql_schema.sql](mysql_schema.sql).

---

## 1. ERD explanation (conceptual)

```
┌─────────────┐         ┌──────────────────┐
│   users     │         │   kyc_records    │
│─────────────│         │──────────────────│
│ PK id       │───┐     │ PK id            │
│ username    │   │     │ FK created_by ───┼──► users.id
│ role        │   └────►│ FK updated_by ───┼──► users.id (nullable)
│ flags…      │         │ business fields…  │
│ deleted_at  │         │ deleted_at        │
└─────────────┘         └──────────────────┘
       │
       │ 1:N
       ▼
┌─────────────────┐     ┌─────────────────────┐
│ activity_logs   │     │ password_reset_logs │
│─────────────────│     │─────────────────────│
│ FK user_id?     │     │ FK target_user_id   │
│ action          │     │ FK reset_by_user_id │
│ subject (morph) │     └─────────────────────┘
│ properties JSON │
└─────────────────┘

┌─────────────────────┐     ┌──────────────────┐
│ failed_login_attempts│    │ report_snapshots │  (optional)
│─────────────────────│     │──────────────────│
│ username, ip, time  │     │ period, filter   │
└─────────────────────┘     │ hash, payload    │
                            └──────────────────┘
```

- **users** is the spine for **auth**, **RBAC flags** (admin vs employee + visibility), and **soft delete** for account lifecycle.
- **kyc_records** stores the Arabic-heavy intake; **created_by** is set on insert; **updated_by** is nullable and set only when the row is edited (not on initial creation).
- **activity_logs** captures **who did what** (admin actions, KYC updates, password resets) with optional **polymorphic** `subject` and **JSON** details (never store secrets).
- **password_reset_logs** records **admin reset events** separately from Laravel’s `password_reset_tokens` (if used later for email-based reset).
- **failed_login_attempts** supports **brute-force monitoring** without coupling to `users` (username may be wrong/unknown).
- **report_snapshots** (optional) stores **pre-aggregated** dashboard payloads keyed by **date range + filter hash** — useful when analytics queries grow heavy or you need **historical “as of”** exports.

---

## 2. Nullable rules (conditional KYC fields)

Business logic in Laravel validates conditionally; the database stays **permissive** so partial saves and legacy rows remain valid.

| Column area | Nullable | Rationale |
|-------------|----------|-----------|
| `age` | YES | Rare edge cases / imports |
| `available_balance` / `expected_balance` | YES | Mutually exclusive by rules; one null when other applies |
| `children_count` | YES | Required only when `marital_status = متزوج` |
| `nationality`, `residency_status` | YES | Required only when `nationality_type = غير مصري` |
| Rejection block | YES | Required only when `previous_rejected = نعم` |
| `previous_visa_countries` | YES | Required only when `has_previous_visas = نعم` |
| `assigned_to` | YES | Empty for `service_type = أخرى`; populated for بافلي / ترانس روفر in app |
| `kyc_records.email` | YES | Optional contact |
| `kyc_records.updated_by` | YES | First version may only have creator |

**Not nullable (integrity):** `employee_name`, `client_full_name`, enum-like choice fields (`has_bank_statement`, …), `status`, `phone_number`, `created_by`, `service_type`.

---

## 3. Service type → assignment (business snapshot)

Assignment is **derived in the application** from `service_type`:

- بافلي → `assigned_to` = default name (e.g. أحمد الشيخ)
- ترانس روفر → محمود الشيخ
- أخرى → `NULL` (or empty string normalized to NULL)

Persisting `assigned_to` on `kyc_records` is still valuable: **reporting** and **audit** reflect what was shown at save time even if mapping rules change later.

---

## 4. Indexing strategy

### Users

- **Unique:** `username`, `email` (MySQL allows multiple `NULL` for unique `email`).
- **Filter:** `role`, `is_active` for admin dashboards and “active employees” KPIs.

### kyc_records

- **Search / list:** `client_full_name` + `created_at` (prefix search on name still benefits from index for ordering/pagination combined with filters).
- **Ownership & analytics:** `created_by` + `created_at`.
- **Time slicing:** `created_at` alone; **composites** `(status, created_at)`, `(service_type, created_at)`, `(created_at, created_by)` for filtered aggregates and trends.
- **Phone lookup:** `phone_number`.

Avoid over-indexing: add more composites only when **slow queries** appear in production (Hostinger shared hosting — keep the list tight).

### activity_logs

- `user_id`, `action`, `created_at`, `(subject_type, subject_id)` for investigations and user activity panes.

### password_reset_logs

- FK indexes implicit; add composite `(target_user_id, created_at)` if you often list resets per user.

### report_snapshots

- **Unique:** `(snapshot_key, period_start, period_end, filter_hash)` prevents duplicate materializations.

---

## 5. Analytics query considerations

1. **Filter anchor:** Almost all report queries should constrain **`created_at`** (range) first — aligns with `created_at` and composite indexes.
2. **Group by dimension:** `status`, `service_type`, `nationality_type`, `created_by` — use **single pass** aggregates (`COUNT(*)`, conditional sums for “today/week/month”) on MySQL 8.
3. **Join users for labels:** Prefer **`JOIN users` + `GROUP BY users.id`** for “top employees” instead of N+1 lookups.
4. **Soft deletes:** All aggregates must respect **`deleted_at IS NULL`** (Eloquent default scope).
5. **Caching:** Short TTL in-app cache keyed by **canonical filter JSON**; optionally persist in **report_snapshots** for “frozen” weekly/monthly boards or email digests.
6. **Hostinger:** Prefer **database sessions/cache** (as in Laravel defaults) over Redis unless on VPS — schema stays the same.

---

## 6. Laravel migration plan

Recommended order (matches dependencies):

| Order | Migration purpose |
|-------|-------------------|
| 1 | `users` (+ `password_reset_tokens`, `sessions` per Laravel skeleton) |
| 2 | `failed_login_attempts` (no FK) |
| 3 | `kyc_records` (FK → `users`) |
| 4 | `activity_logs` (FK → `users`, nullable) |
| 5 | `password_reset_logs` (FK → `users` ×2) |
| 6 | `report_snapshots` (optional FK → `users`) |
| 7 | **Additive** migrations: soft deletes on `users`, extra **composite indexes** on `kyc_records` |

**Charset:** in `config/database.php` use `utf8mb4` and `utf8mb4_unicode_ci` for MySQL connection. Collation on columns can follow connection default.

**Models:** `SoftDeletes` on `User`, `KycRecord`; strict mode for app-level validation; `$fillable` tightened for mass-assignment safety (mirror production app).

---

## 7. Scalability notes (future, still “not over-engineered”)

- **`service_types` lookup table** if you need admin-editable labels or per-tenant assignment rules.
- **Partitioning `kyc_records` by `created_at` (year)** only if millions of rows and retention policies exist.
- **`activity_logs` archival** to cold storage table or object store after N months.
- **Read replica** on VPS-grade hosting for heavy reporting — schema unchanged.

---

## 8. Alignment with this repository

The Laravel migrations under `database/migrations/` implement the same conceptual model; `mysql_schema.sql` here is a **single-file reference DDL** and adds **`report_snapshots`** as an optional table you can adopt via a new migration if you want materialized analytics.

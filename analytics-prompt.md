# Analytics Page — Implementation Prompt (AWS-System)

> Hand this file to your coding agent together with `PRD.md`. It is written so the agent reads the real schema first, then builds a read-only analytics feature on top of the existing SQL data.

---

## 1. Role & Goal

You are a senior Laravel + Vue engineer working inside the existing **AWS-System** repository (Laravel 11, Vue 3 via Inertia.js, Tailwind CSS v4.1, shadcn-style components, Magic UI, SQLite in dev / MySQL in production, Spatie Laravel Permission, DomPDF).

**Goal:** Add a new, **read-only Analytics page** that pulls all of its data from the existing SQL tables (no new tracking infrastructure required) and presents business insights about documents, revenue, clients, sharing, and users.

Do **not** change existing document creation, PDF, or sharing behavior. This feature only reads data.

---

## 2. Step 0 — Discover Before You Build (mandatory)

Do not assume table or column names. Before writing code:

1. Read every file in `database/migrations/` and list the actual tables, columns, types, indexes, and foreign keys.
2. Read the Eloquent models (`Document`, `DocumentItem`, `User`, any share/link model) and their relationships, casts, and scopes.
3. Read `routes/web.php`, the RBAC middleware/policies, and how Admin vs User scoping is already enforced on the documents list.
4. Read `resources/js/Layouts/AuthenticatedLayout.vue` to see how sidebar navigation items are registered.
5. Check `package.json` for an existing chart library. Prefer what is already installed. If none exists, use the shadcn-vue chart components (Unovis) or Chart.js, and tell me which you picked and why.
6. Note the DB driver in `.env` and make sure every query works on **both SQLite and MySQL** (see Section 7).

Then output a short **"Schema Map"** (table → key columns → how it maps to the metrics below) and flag any metric that cannot be computed from the current schema. Proceed with everything that can be computed; list the rest under "Needs schema change" with the smallest migration that would enable it.

---

## 3. Access Control (RBAC)

| Role | What they see |
| --- | --- |
| **Admin** | System-wide analytics across all users and documents, plus a per-user breakdown |
| **User** | The same widgets, scoped strictly to **their own documents** only. No other users' data, names, or totals anywhere in the payload |

Rules:

- Scoping must be enforced **server-side** in the query layer, not by hiding things in Vue.
- Use a single reusable scope or service method (e.g. `forViewer(User $user)`) so no analytics query can forget the filter.
- Admin-only widgets (user leaderboard, user counts) must not be present in the Inertia props for non-admins.
- Add a Policy or Gate (`viewAnalytics`) and protect the route with existing auth + role middleware.
- Add tests proving a User cannot see another user's numbers.

---

## 4. Metrics to Deliver

Implement these as sections on one page. Every number must come from SQL aggregates, not from loading whole tables into PHP.

### 4.1 KPI cards (top row)
- Total documents (in selected period) with % change vs previous equal-length period
- Documents by type: SOA, Purchase Order, Quotation, Delivery Receipt (count for each)
- Total billed value: sum of SOA totals (item totals minus fixed discount)
- Total quoted value: sum of Quotation totals (item totals minus fixed discount)
- Total discounts given (fixed amounts, SOA + Quotation)
- Average document value per type (SOA, Quotation)

### 4.2 Trends
- Documents created over time, grouped by day / week / month (auto-pick granularity from the date range), split by document type (stacked)
- Billed value (SOA) and quoted value (Quotation) over time
- Quotation → SOA activity comparison per month (volume only; do not invent a conversion link unless the schema actually stores one)

### 4.3 Breakdowns
- Document type share (donut or bar)
- Status breakdown, if a `status` column exists (the PRD mentions admin-controlled status)
- Top 10 clients by total billed value and by document count (group by the client/`to`/name field that exists in the schema; normalize with `TRIM` + case-insensitive grouping)
- Top 10 items by quantity and by revenue (from `document_items`, grouped by normalized description)
- Busiest days of week / hours of day for document creation

### 4.4 Sharing & links
- Public links created in period
- Active vs expired vs revoked links right now
- Links expiring in the next 7 days (with document reference)
- Percentage of documents that have ever been shared
- If a view/download counter already exists, show top viewed documents. If it does not, list it under "Needs schema change" (see Section 9)

### 4.5 People (Admin only)
- Documents created per user (leaderboard)
- Value billed per user
- New registrations over time
- Users with no documents yet

### 4.6 Data quality (small "health" panel)
- Documents missing a control number or SOA number
- Documents with zero items
- Duplicate control numbers
- Documents with a discount larger than the item subtotal

---

## 5. Filters & Interactions

- **Date range**: presets (Today, 7d, 30d, This month, Last month, This year, Custom). Default: last 30 days.
- **Document type** multi-select
- **Compare to previous period** toggle
- **Admin only**: filter by user
- Filters live in the URL query string (Inertia `router.get` with `preserveState` and `preserveScroll`) so views are shareable and survive refresh.
- Validate filters with a `FormRequest` (allowed presets, max range e.g. 2 years, valid document types).
- Provide **CSV export** of the current filtered summary and the underlying document list (server-side streamed response, same scoping rules).
- Clicking a chart segment or a top-client row should link to the existing documents list with the matching filter applied, where the list already supports that filter.

---

## 6. Architecture

Follow the patterns already in the PRD (MVC, Repository Pattern, API Resource transformers). Suggested structure, adapt names to the repo's conventions:

```
app/
  Http/
    Controllers/AnalyticsController.php        // thin: validate, authorize, call service, render Inertia
    Requests/AnalyticsFilterRequest.php
  Services/Analytics/
    AnalyticsService.php                       // orchestrates, caching
    Queries/                                   // one small class per metric group
      KpiQuery.php
      TrendQuery.php
      BreakdownQuery.php
      SharingQuery.php
      UserActivityQuery.php
      DataQualityQuery.php
    DateBucket.php                             // driver-aware date grouping helper
  Policies/AnalyticsPolicy.php
resources/js/
  Pages/Analytics/Index.vue
  Components/Analytics/
    KpiCard.vue
    TrendChart.vue
    BreakdownChart.vue
    TopList.vue
    FilterBar.vue
    EmptyState.vue
tests/Feature/Analytics/
```

Controller rules:
- One Inertia render for the page shell with filters and KPI props.
- Heavier widgets (trends, top lists, data quality) use **Inertia deferred props** (`Inertia::defer`) or partial reloads so the page paints fast and shows skeleton loaders while the rest loads.
- Return data through API Resources or plain arrays with stable, documented shapes.

Route: `GET /analytics` named `analytics.index`; export at `GET /analytics/export`. Add an **Analytics** item to the sidebar in `AuthenticatedLayout.vue` (icon consistent with the existing set), visible to all authenticated roles.

---

## 7. SQL & Performance Requirements

- Use the Query Builder / Eloquent aggregates (`selectRaw`, `groupBy`, `sum`, `count`). No N+1 queries; no `->get()->groupBy()` on large sets.
- **SQLite vs MySQL compatibility** is critical. Create a `DateBucket` helper that returns the correct expression per driver:
  - SQLite: `strftime('%Y-%m-%d', created_at)`, `strftime('%w', created_at)`, `strftime('%H', created_at)`
  - MySQL: `DATE_FORMAT(created_at, '%Y-%m-%d')`, `DAYOFWEEK(created_at)`, `HOUR(created_at)`
  - Normalize outputs (e.g. weekday numbering) so the frontend gets identical shapes from both drivers.
- Compute document totals in SQL from `document_items` (quantity × unit cost) minus the document discount. Use one shared query/subquery for "document total" so every metric agrees to the centavo.
- Use integer or `DECIMAL` math consistently; never sum floats in PHP. Format currency as **PHP (₱)** on the frontend only.
- Fill missing days/weeks/months in trend series with zeros so charts have continuous axes.
- Respect the app timezone (`Asia/Manila`) when bucketing by day; convert range boundaries correctly.
- Add **composite indexes** via a new migration only if the Schema Map shows they are missing and useful (for example `(user_id, type, created_at)` on documents, `(document_id)` on items, `(expires_at)` on share links). Keep the migration reversible.
- Cache expensive aggregates with `Cache::remember` for 5 minutes, keyed by viewer id (or `admin`), filters, and range. Use cache tags only if the configured store supports them; otherwise use plain keys.
- Target: page interactive in under 1 second on ~50k documents / ~500k items on MySQL. Provide the `EXPLAIN` output or a short note for the two heaviest queries.

---

## 8. UI / UX Requirements

Match the PRD's brand direction:

- **Dark mode by default**, cool palette: deep blue, slate, cool gray; accents in cyan, teal, ice blue. Use existing Tailwind v4.1 theme tokens and shadcn-style components already in the repo (Card, Select, Popover, Calendar, Tabs, Skeleton, Badge, Table).
- Layout: header with title + filter bar → KPI card row → trend section → breakdown grid → sharing panel → (Admin) people panel → data quality panel.
- Charts: consistent color per document type across the whole page (e.g. SOA = cyan, PO = teal, Quotation = blue, Delivery Receipt = slate). Accessible contrast, tooltips with formatted values, legends, and keyboard-reachable elements.
- Subtle Magic UI touches only (e.g. number ticker on KPI cards, soft fade-in). Nothing distracting; respect `prefers-reduced-motion`.
- Fully responsive: KPI cards wrap to 2 columns on tablet and 1 on phone; wide tables scroll inside their own container; charts resize cleanly.
- States for every widget: loading skeleton, empty ("No documents in this period"), error with retry.
- Numbers: locale `en-PH`, compact notation for large values in cards (₱1.2M) with full value in tooltip.

---

## 9. Optional Schema Additions (propose, don't force)

Only if the Schema Map shows these are missing, propose them as a separate, clearly labeled migration and implement the matching widgets behind a feature check so the page still works without them:

1. `view_count` / `download_count` and `last_viewed_at` on the share-link table (increment in the public document controller).
2. A `status` column on documents (the PRD says admins control status).
3. A `revoked_at` timestamp on share links, if revocation currently deletes the row (analytics on revoked links needs the record to persist).

Do not add event-log or audit tables; those are out of MVP scope in the PRD.

---

## 10. Forward Compatibility

The document system will grow (new document types and roles). Build analytics so a new document type only needs a config entry, not a code rewrite:

- Centralize document types, labels, and colors in one config or enum used by queries and Vue.
- Queries group by the `type` column dynamically rather than hard-coding four branches, except where a type-specific total formula is genuinely required (type → total-expression map in one place).
- Keep the viewer-scoping method the single place where role rules live, so adding a role later (for example one limited to certain document types or to documents assigned to them) means editing one scope.

---

## 11. Testing & Quality Gates

- **Feature tests** (Pest or PHPUnit, whichever the repo uses): Admin sees all data; User sees only own; guests are redirected; invalid filters return validation errors; CSV export respects scoping.
- **Unit tests** for `DateBucket` on both drivers (SQLite test DB required; MySQL assertions on the generated SQL string if a MySQL test DB isn't available).
- **Correctness test**: seed a small known dataset (e.g. 3 SOAs, 2 quotations with discounts, one expired link) and assert exact KPI and top-client values.
- Run the existing test suite, `pint` (or the repo's formatter), and the frontend lint/type-check/build. All must pass.
- No `dd()`, no debug output, no unused imports, no commented-out code.

---

## 12. Deliverables & Working Style

1. The **Schema Map** and list of assumptions (first message, before coding).
2. Implementation of everything above, in small logical commits.
3. A short `docs/analytics.md` describing metrics definitions (how "billed value", "active link", etc. are calculated), filters, caching, and how to add a new document type.
4. A final summary listing: files added/changed, migrations (if any), what was not possible with the current schema, and how to run the tests.

If a requirement conflicts with the actual codebase, pick the option that best fits existing conventions, state the decision in one line, and continue. Ask me only when a choice would change data or behavior outside the analytics feature.

---

## 13. Definition of Done

- [ ] `/analytics` reachable from the sidebar for Admin and User
- [ ] User cannot see anyone else's data (server-verified, tested)
- [ ] All KPI, trend, breakdown, sharing, people, and data-quality widgets render from SQL with correct totals
- [ ] Works on SQLite (dev) and MySQL (prod)
- [ ] Date range, type, compare, and (admin) user filters work and persist in the URL
- [ ] CSV export works and respects scoping
- [ ] Dark, cool-toned, responsive UI with loading, empty, and error states
- [ ] Tests, formatter, lint, and build all pass
- [ ] `docs/analytics.md` written

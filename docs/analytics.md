# Analytics

The analytics page is read-only and uses the existing `documents`, `document_items`, `public_links`, and `users` tables. Every query starts from `AnalyticsService::documentQuery()`, which applies the viewer scope, date range, soft-delete filter, type filter, and optional admin user filter.

## Metric definitions

- **Document total:** `SUM(document_items.total_cost)` minus the document discount, floored at zero.
- **Billed value:** the document total for `soa` documents.
- **Quoted value:** the document total for `quotation` documents.
- **Discounts:** the stored fixed discount for SOA and quotation documents.
- **Active link:** a public link with no expiry or an expiry later than the current application time.
- **Expired link:** a public link whose expiry is at or before the current application time.
- **Top viewed:** public links ordered by the existing `access_count`. The current schema does not distinguish views from downloads.
- **Client grouping:** `LOWER(TRIM(recipient_name))` so casing and surrounding whitespace do not create duplicate clients.

Date buckets use SQLite `strftime` and MySQL `DATE_FORMAT` expressions. The application timezone controls date boundaries; the current repository configuration is UTC.

## Filters and caching

The default range is the last 30 days. Presets and type/user filters are sent through the query string. Aggregate reports are cached for five minutes using the viewer identity and normalized filters in the cache key.

## Schema limitations

Revoked links cannot be counted historically because revocation deletes the row. The existing `access_count` is aggregate access only, so separate view and download counts are unavailable. Quotation-to-SOA conversion cannot be calculated because documents do not store a conversion relationship.

## Adding a document type

Add the type constant and label to `Document::getTypes()`, then add its presentation color to `resources/js/Pages/Analytics/Index.vue`. The aggregate queries group dynamically by the stored `type` value.
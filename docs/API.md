# JSON API

The application exposes a small JSON API for progressive enhancement. All endpoints are relative to `APP_URL`. Responses are UTF-8 JSON with `Content-Type: application/json`.

## Authentication and CSRF

| Endpoint group | Requirement |
|---|---|
| `/api/jobs`, `/api/opportunities`, `/api/health` | Public |
| `/api/notifications`, `/api/notifications/read`, `/api/match/{id}`, `/api/saved-searches` | Signed-in session |

Session authentication uses the `joblink_session` cookie. POST endpoints are protected by CSRF middleware: send `_csrf` as a form field (or JSON body if you adapt the client) using the token embedded in the page as `META_CSRF` / `csrf_token()`.

A browser client should send:

```
X-Requested-With: XMLHttpRequest
Accept: application/json
```

This tells `POST /api/saved-searches` to return JSON instead of redirecting.

## Endpoints

### `GET /api/health`

Returns application status and database connectivity.

```json
{
  "ok": true,
  "app": "JobLink Rwanda",
  "version": "1.0.0",
  "database": "connected",
  "time": "2026-11-26T10:15:00+02:00"
}
```

### `GET /api/jobs`

Runs the same search as the public jobs page and returns both structured rows and pre-rendered HTML cards. This lets the UI filter without duplicating template logic.

Query parameters (all optional):

`q`, `location`, `country`, `category`, `industry`, `employment_type`, `work_mode`, `experience_level`, `education_level`, `salary_min`, `salary_max`, `posted_within`, `deadline_before`, `featured`, `sort`, `page`, `limit` (1–24).

```json
{
  "ok": true,
  "total": 10,
  "rows": [
    {
      "id": 3,
      "title": "Backend Developer",
      "slug": "backend-developer-3",
      "location": "Kigali",
      "url": "http://localhost/job-finding/jobs/3/backend-developer-3"
    }
  ],
  "html": "<p class=\"small muted mb-2\">…</p>"
}
```

### `GET /api/opportunities`

Searches the external opportunity feed.

Query parameters: `q`, `page` (currently page 1), plus the same filters used by the Radar page when called from server-side code.

```json
{
  "ok": true,
  "rows": [
    { "id": 4, "title": "Product Design Internship", "organization": "Irembo", "location": "Kigali", "url": "…/opportunity-radar/4" }
  ]
}
```

### `GET /api/notifications`

Returns the 20 newest notifications for the signed-in user.

```json
{
  "ok": true,
  "notifications": [
    {
      "id": 12,
      "type": "application",
      "title": "Application update: Shortlisted",
      "body": "Your application for … is now Shortlisted.",
      "link": "http://localhost/job-finding/applications",
      "is_read": false,
      "created_at": "2026-11-25 09:31:00"
    }
  ]
}
```

### `POST /api/notifications/read`

Marks one notification as read for the current user. Ownership is enforced by `id AND user_id`.

Form fields: `_csrf`, `id`.

```json
{ "ok": true }
```

### `GET /api/match/{id}`

Returns the deterministic compatibility breakdown for a job. Available to `seeker` and `freelancer` roles only.

```json
{
  "ok": true,
  "score": 78,
  "matched": ["PHP", "MySQL"],
  "missing": ["Laravel (required)"],
  "breakdown": {
    "Skills": 32,
    "Experience": 12,
    "Education": 10,
    "Location": 14,
    "Employment type": 10,
    "Category": 0
  }
}
```

### `POST /api/saved-searches`

Saves the current job-search query for later. A normal browser form submission redirects back to `/jobs`; AJAX clients receive JSON.

Form fields: `_csrf`, `name` (required), `query_params` (URL-encoded query string).

```json
{ "ok": true, "message": "Search saved." }
```

Validation failure returns HTTP 422:

```json
{ "ok": false, "message": "Name is required." }
```

## Error handling

- Public HTML pages use `404`, `403` and `500` views from `app/Views/errors/error.php`.
- API validation failures return the most specific status available (`403`, `404`, `422`).
- Database errors are logged to `storage/logs/php-error.log`. Debug stack traces appear only when `APP_DEBUG=true`.

## Rate limiting

Login attempts are rate limited per email/IP in `login_attempts` (5 attempts, 15 minute lockout). Public JSON read endpoints are lightweight and cacheable by a reverse proxy; add application-level throttling at the web-server or gateway layer if you expose them at high volume.

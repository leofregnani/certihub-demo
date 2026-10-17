# API reference

Base URL: `http://localhost:8000/api` · JSON in/out · Auth: `Authorization: Bearer <token>` (Laravel Sanctum).

Lists are paginated (`?page=`, `?per_page=` up to 100) and return the standard Laravel paginator
(`data`, `current_page`, `last_page`, `total`, ...). Validation errors return `422` with
`{ "message": "...", "errors": { "field": ["..."] } }`.

## Public

| Method | Path | Description |
|---|---|---|
| POST | `/auth/login` | `email`, `password` → `{ token, user }` (throttled) |
| POST | `/auth/forgot-password` | `email` → sends a reset link (always answers the same) |
| POST | `/auth/reset-password` | `token`, `email`, `password`, `password_confirmation` |
| GET | `/public/certificates/{code}` | Validates a certificate. `200` with `valid: true/false`, or `404` if the code does not exist (throttled) |

## Authenticated (admin and user)

| Method | Path | Description |
|---|---|---|
| POST | `/auth/logout` | Revokes the current token |
| GET | `/auth/me` | Current user |
| GET | `/dashboard` | Totals, certificates per month, latest certificates |
| GET, POST | `/participants` | List (`search`) / create |
| GET, PUT | `/participants/{id}` | Show / update |
| GET, POST | `/courses` | List (`search`) / create |
| GET, PUT | `/courses/{id}` | Show / update |
| GET | `/certificates` | List. Filters: `search`, `status`, `course_id`, `from`, `to` |
| POST | `/certificates` | Issue: `participant_id`, `course_id`, optional `issued_at` |
| GET | `/certificates/{id}` | Show |
| GET | `/certificates/{id}/pdf` | Download the PDF (with QR code) |

## Admin only

| Method | Path | Description |
|---|---|---|
| DELETE | `/participants/{id}`, `/courses/{id}`, `/certificates/{id}` | Delete |
| POST | `/certificates/{id}/revoke` | Revoke (public validation then reports it as invalid) |
| GET, POST | `/users` | List / create (`name`, `email`, `password`, `role`: `admin` or `user`) |
| GET, PUT, DELETE | `/users/{id}` | Show / update / delete (you cannot delete or demote yourself) |
| GET | `/logs` | Audit trail. Filters: `search`, `action` (prefix), `user_id`, `from`, `to` |

## Logged actions

`auth.login`, `auth.login_failed`, `auth.logout`, `auth.password_reset_requested`, `auth.password_reset`,
`user.created|updated|deleted`, `participant.created|updated|deleted`, `course.created|updated|deleted`,
`certificate.issued|downloaded|revoked|deleted|validated`.

Each entry stores the responsible user (when known), date/time, IP address, user agent and a short description.

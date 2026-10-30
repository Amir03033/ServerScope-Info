---
order: 20
icon: code
category: api
feature: api
description: All REST API v1 endpoints, permissions, error codes and repeatable write requests with Idempotency-Key.
---
# API v1 reference

This is the overview of the REST API, version 1. How to create a token and receive webhooks is described in [API and webhooks](api-webhooks).

## Basics

- Base URL: `{app_url}/api/v1`
- Send a personal token as a bearer token and ask for JSON:

  ```http
  Authorization: Bearer ssk_…
  Accept: application/json
  ```

- Every token can read (`read`). Writing needs the `write` ability.
- At most 120 requests per minute per token; above that HTTP 429.
- Lists are paginated with `?page=2&per_page=50` (at most 100).
- Times are ISO 8601 in UTC.

## Permissions

Every token can read. Writes need the `write` ability. Configuration changes (pages, discovery, dependencies) also need a token owned by an admin; otherwise the answer is 403. Incident actions follow the web interface: any user may acknowledge, investigate, resolve, comment and assign.

## Endpoints

| Method | Path | Permission | Notes |
|---|---|---|---|
| GET | `/servers` | read | `status` = online/offline/pending/maintenance, `environment` |
| GET | `/servers/{id}` | read | includes the latest metrics |
| GET | `/servers/{id}/metrics` | read | `from`, `to` (ISO 8601), `limit` ≤ 1440 |
| GET | `/servers/{id}/services` | read | reported services; `stale` while the agent is offline |
| GET | `/domains` | read | `status` = healthy/warning/critical/unknown |
| GET | `/domains/{id}` | read | HTTP, SSL, DNS and registration state |
| GET | `/domains/{id}/checks` | read | `type` = http/ssl/dns/expiry |
| GET | `/domains/{id}/pages` | read | monitored pages |
| POST | `/domains/{id}/pages` | write + admin | `urls` (one per line) and optional page settings |
| GET | `/pages/{id}` | read | state and settings of a page |
| PUT | `/pages/{id}` | write + admin | `url` and settings |
| DELETE | `/pages/{id}` | write + admin | deletes the page and its checks |
| POST | `/pages/{id}/pause` · `/resume` · `/approve` · `/reject` | write + admin | |
| GET | `/pages/{id}/checks` | read | `result` = up/degraded/down |
| POST | `/domains/{id}/discovery` | write + admin | starts page discovery (202) |
| GET | `/domains/{id}/discovery-runs` | read | results of discovery runs |
| GET | `/dependencies` | read | confirmed dependencies |
| POST | `/dependencies` | write + admin | `dependent_type`, `dependent_id`, `dependency_type`, `dependency_id`, `label` |
| DELETE | `/dependencies/{id}` | write + admin | |
| GET | `/incidents` | read | `state` = active/resolved/closed/all, `severity` |
| GET | `/incidents/{id}` | read | with timeline, child incidents and affected resources |
| POST | `/incidents/{id}/acknowledge` | write | |
| POST | `/incidents/{id}/investigate` | write | |
| POST | `/incidents/{id}/resolve` | write | optional `notes` |
| POST | `/incidents/{id}/comments` | write | `message` |
| POST | `/incidents/{id}/assign` | write | `user_id` (or `null`) |
| GET | `/predictions` | read | `kind` = anomaly/forecast, `status` = active/resolved |

Severity values in the API: `info` (low), `warning` (medium), `high` and `critical`.

## Error codes

Errors look like `{"message": "...", "errors": {"field": ["..."]}}`.

| Code | Meaning |
|---|---|
| 401 | Token missing or invalid |
| 403 | Ability or role missing |
| 404 | Not found |
| 409 | Invalid incident transition, or a concurrent duplicate request |
| 422 | Validation error |
| 429 | Too many requests |

## Repeatable write requests

Send an `Idempotency-Key` (8 to 128 characters) with a write request. If you repeat the same request with the same key (per token, within 24 hours), you get the stored response back with `Idempotent-Replayed: true` and it is not executed again. The same key with a different body or path returns 422; a concurrent duplicate returns 409.

```sh
curl -s -X POST "{app_url}/api/v1/domains/3/pages" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" -H "Accept: application/json" \
  -H "Idempotency-Key: deploy-2026-10-28-pages" \
  -d urls=$'/\n/pricing\n/api/health'
```

> [!NOTE]
> The agent uses its own endpoint (`POST /api/agent/metrics`) with agent tokens per server. Those tokens do not work for this API.

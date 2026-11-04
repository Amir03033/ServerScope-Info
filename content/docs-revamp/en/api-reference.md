---
order: 20
icon: code
category: api
feature: api
description: All REST API v1 endpoints including deployments, permissions, error codes, repeatable write requests, and the public ping, badge and webhook URLs.
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
- A token only sees the data of its own team.
- API tokens are part of **Pro and Premium**; Free has no API tokens.

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
| GET | `/deployments` | read | filters `application`, `environment` |
| GET | `/deployments/{id}` | read | includes `analysis` (before/after) |
| POST | `/deployments` | write | report a deployment (see below) |

Severity values in the API: `info` (low), `warning` (medium), `high` and `critical`.

## Deployments

`POST /deployments` reports a deployment, so ServerScope can compare before and after (Deploy Judge). Deployments seen by the agent or GitHub need no API call; see [Deployments](deployments).

| Field | Required | Rules |
|---|---|---|
| `application` | yes | at most 100 characters |
| `environment` | no | `[A-Za-z0-9._-]`, at most 50, default `production` |
| `version` | no | at most 100 |
| `commit_sha` | no | 7 to 64 hex characters |
| `deployed_at` | no | ISO 8601, within the last 30 days and at most 5 minutes ahead |
| `source`, `actor` | no | for example `github-actions` and a user name |
| `url` | no | link to the run or release |
| `external_id` | no | sending the same value again returns the existing deployment (200) |
| `resources` | yes | 1 to 50 items `{"type": "server"\|"domain"\|"page"\|"service", "id": n}`, of your own team only |

```sh
curl -sS -X POST {app_url}/api/v1/deployments \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Idempotency-Key: deploy-shop-1f3a9c2" \
  -d '{"application":"shop","environment":"production","version":"1.4.2","commit_sha":"1f3a9c2","resources":[{"type":"server","id":3},{"type":"domain","id":7}]}'
```

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
> The agent uses its own endpoints (`/api/agent/…`) with agent tokens per server. Those tokens do not work for this API, and you never call those endpoints yourself.

## Public URLs (no token)

| Method | URL | Purpose |
|---|---|---|
| GET | `/install.sh` | Install script for the agent; see [Connect a server with one command](connect-server) |
| GET/POST/HEAD | `/api/ping/{token}` · `/start` · `/fail` · `/{0-255}` | Check-ins of a [scheduled job](scheduled-jobs) |
| POST | `/api/webhooks/github/{uuid}` | GitHub webhook of a connected repository; see [Deployments](deployments) |
| GET | `/status/{slug}` and `/status/{slug}.json` | Public status page, also as JSON |
| GET | `/status/{slug}/badge.svg` · `/status/{slug}/badge/{component}.svg` | Status badges; see [Status pages](status-pages) |
| GET | `/postmortems/{token}` | A shared postmortem (read-only, can be revoked, not indexed) |
| GET | `/up` | Health check of the application, for uptime tools |

The ping token (32 characters, 0-9 and a-f) is the only secret of a scheduled job; a ping URL is limited to 120 requests per minute.

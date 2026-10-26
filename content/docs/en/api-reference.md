---
order: 1
category: developers
icon: code
description: Authenticate with a personal API token and use the REST API (v1) to read servers, metrics, domains and incidents, and to act on incidents.
---
# API reference

ServerScope has a **REST API (version 1)** for reading monitoring data and working on incidents from your own tools: dashboards, chat bots, runbooks or automation such as n8n.

> [!NOTE]
> The response examples on this page show the **shape** of each response. The values are examples, not data from a real environment.

## Quick start

1. Create a token under [Profiel → API-tokens]({app_url}/profile/api-tokens) (see [Creating a token](#creating-a-token)).
2. Store it in an environment variable, for example `SERVERSCOPE_API_TOKEN`. Never put it in source code.
3. Request the active incidents:

```curl
curl -s "{app_url}/api/v1/incidents" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"
```

```php
<?php
// Laravel's HTTP client; any HTTP client works the same way.
use Illuminate\Support\Facades\Http;

$incidents = Http::withToken(getenv('SERVERSCOPE_API_TOKEN'))
    ->acceptJson()
    ->get('{app_url}/api/v1/incidents')
    ->throw()
    ->json('data');
```

```javascript
const response = await fetch('{app_url}/api/v1/incidents', {
  headers: {
    Authorization: `Bearer ${process.env.SERVERSCOPE_API_TOKEN}`,
    Accept: 'application/json',
  },
});

if (!response.ok) throw new Error(`ServerScope API: ${response.status}`);
const { data: incidents } = await response.json();
```

```python
import os, requests

response = requests.get(
    "{app_url}/api/v1/incidents",
    headers={"Authorization": f"Bearer {os.environ['SERVERSCOPE_API_TOKEN']}", "Accept": "application/json"},
    timeout=10,
)
response.raise_for_status()
incidents = response.json()["data"]
```

## Creating a token

1. Open [Profiel → API-tokens]({app_url}/profile/api-tokens) (Profile → API tokens).
2. Give the token a **Naam** (name) that says where it is used, for example `grafana` or `n8n`.
3. Choose the **Bevoegdheden** (abilities). `read` is always included; only add `write` if the token must change incidents.
4. Choose the **Geldigheid** (validity): 30 days, 90 days, 1 year or unlimited.
5. Click **Token aanmaken** (create token) and copy the token. It starts with `ssk_`.

> [!WARNING]
> The token is shown **once**; ServerScope only stores a SHA-256 hash of it. A token acts with the permissions of your account, limited to its abilities. Revoke a token immediately if it may have leaked, and never paste it into a browser-based tool you do not control.

## Authentication

Send the token as a bearer token on every request and ask for JSON:

```http
GET /api/v1/servers HTTP/1.1
Host: serverscope.nl
Authorization: Bearer ssk_…
Accept: application/json
```

| Ability | Allows |
|---|---|
| `read` | All `GET` endpoints |
| `write` | The `POST` endpoints for incidents (acknowledge, investigate, resolve, comment) |

The monitoring agent uses its own endpoint (`POST /api/agent/metrics`) with per-server agent tokens. Agent tokens do not work for this API, and API tokens do not work for the agent.

## Conventions

| Topic | Rule |
|---|---|
| Base URL | `{app_url}/api/v1` |
| Format | JSON only; send `Accept: application/json` |
| Timestamps | ISO 8601, for example `2026-10-26T10:00:00+00:00` |
| Pagination | `?page=2&per_page=50` (1–100, default 25). Lists return `data`, `links` and `meta`. |
| Rate limit | 120 requests per minute per token by default (the operator can change it); above that `429` |
| Language | ServerScope's own error messages (401, 403, 409, 429) are in Dutch; validation messages follow the application's language setting |

A paginated list looks like this:

```json
{
  "data": [ … ],
  "links": { "first": "…?page=1", "last": "…?page=3", "prev": null, "next": "…?page=2" },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 25, "total": 61, "…": "…" }
}
```

## Endpoints

| Method | Path | Ability | Description |
|---|---|---|---|
| `GET` | `/servers` | read | List servers |
| `GET` | `/servers/{id}` | read | One server with its latest measurement |
| `GET` | `/servers/{id}/metrics` | read | Measurements in a time window |
| `GET` | `/domains` | read | List domains |
| `GET` | `/domains/{id}` | read | One domain with HTTP, SSL, DNS and registration state |
| `GET` | `/domains/{id}/checks` | read | Check history of a domain |
| `GET` | `/incidents` | read | List incidents |
| `GET` | `/incidents/{id}` | read | One incident with its timeline |
| `POST` | `/incidents/{id}/acknowledge` | write | Acknowledge |
| `POST` | `/incidents/{id}/investigate` | write | Start investigating |
| `POST` | `/incidents/{id}/resolve` | write | Resolve |
| `POST` | `/incidents/{id}/comments` | write | Add a note |

## Servers

### List servers

`GET /servers`

| Parameter | Type | Description |
|---|---|---|
| `status` | string | `online`, `offline`, `pending` or `maintenance` |
| `environment` | string | `production`, `staging`, `development`, `testing` or `other` |
| `per_page` | integer | 1–100, default 25 |

Results are sorted by name. Each server looks like this:

```json
{
  "id": 3,
  "name": "web-01",
  "hostname": "web-01.example.com",
  "ip_address": "203.0.113.10",
  "environment": "production",
  "operating_system": "Ubuntu 24.04",
  "agent_version": "1.0.0",
  "status": "online",
  "last_seen_at": "2026-10-26T10:00:00+00:00",
  "maintenance": { "active": false, "starts_at": null, "ends_at": null },
  "latest_metrics": {
    "collected_at": "2026-10-26T10:00:00+00:00",
    "cpu_percent": 23.4,
    "memory_percent": 61.2,
    "load_1": 0.42,
    "uptime_seconds": 1762310
  },
  "created_at": "2026-09-24T08:12:00+00:00"
}
```

`latest_metrics` is `null` when no measurement has arrived yet.

### Get one server

`GET /servers/{id}` returns `{"data": { … }}` with the same fields.

### Measurements

`GET /servers/{id}/metrics`

| Parameter | Type | Description |
|---|---|---|
| `from` | date | Start of the window; default one hour ago |
| `to` | date | End of the window, after `from`; default now |
| `limit` | integer | 1–1440, default 120. The newest measurements in the window are returned, oldest first. |

This endpoint is not paginated:

```json
{
  "data": [
    {
      "collected_at": "2026-10-26T09:59:00+00:00",
      "cpu_percent": 21.7,
      "memory_used_bytes": 2621440000,
      "memory_total_bytes": 4294967296,
      "load_1": 0.38,
      "load_5": 0.41,
      "load_15": 0.35,
      "disks": [ … ],
      "network_rx_bytes": 918273645,
      "network_tx_bytes": 129384756
    }
  ]
}
```

## Domains

### List domains

`GET /domains`

| Parameter | Type | Description |
|---|---|---|
| `status` | string | `healthy`, `warning`, `critical` or `unknown` (active domains only) |
| `per_page` | integer | 1–100, default 25 |

```json
{
  "id": 7,
  "name": "example.com",
  "display_name": "example.com",
  "url": "https://example.com/",
  "status": "healthy",
  "monitoring_active": true,
  "http": { "enabled": true, "status": "up", "last_status_code": 200, "last_response_ms": 182, "consecutive_failures": 0, "last_checked_at": "2026-10-26T10:00:00+00:00" },
  "ssl": { "enabled": true, "status": "valid", "issuer": "…", "valid_to": "2026-12-06T23:59:59+00:00", "days_remaining": 41 },
  "dns": { "enabled": true, "status": "ok", "last_changed_at": null },
  "registration": { "enabled": true, "status": "verified", "expires_at": "2027-03-01T00:00:00+00:00", "registrar": "…", "source": "rdap", "checked_at": "2026-10-26T03:00:00+00:00" },
  "created_at": "2026-09-24T08:30:00+00:00"
}
```

`registration.status` is `verified` only when the registry publishes an expiry date.

### Check history

`GET /domains/{id}/checks`

| Parameter | Type | Description |
|---|---|---|
| `type` | string | `http`, `ssl`, `dns` or `expiry` |
| `per_page` | integer | 1–100, default 50 |

Newest first, with its own `meta` (`current_page`, `last_page`, `per_page`, `total`) and `links` (`next`, `prev`):

```json
{
  "data": [
    { "type": "http", "result": "…", "success": true, "response_time_ms": 182, "http_status_code": 200, "error_code": null, "message": null, "checked_at": "2026-10-26T10:00:00+00:00" }
  ],
  "meta": { "current_page": 1, "last_page": 12, "per_page": 50, "total": 583 },
  "links": { "next": "…?page=2", "prev": null }
}
```

## Incidents

### List incidents

`GET /incidents`

| Parameter | Type | Description |
|---|---|---|
| `state` | string | `active` (default: open, acknowledged or investigating), `resolved`, `closed` or `all` |
| `severity` | string | `info`, `warning` or `critical` |
| `per_page` | integer | 1–100, default 25 |

Newest first:

```json
{
  "id": 12,
  "title": "shop.example.com is onbereikbaar",
  "description": "…",
  "severity": "critical",
  "status": "acknowledged",
  "source": "http",
  "subject": { "type": "domain", "id": 7, "name": "shop.example.com" },
  "occurrence_count": 4,
  "reopened_count": 0,
  "assigned_to": 2,
  "opened_at": "2026-10-26T10:02:00+00:00",
  "last_occurrence_at": "2026-10-26T10:08:00+00:00",
  "acknowledged_at": "2026-10-26T10:04:00+00:00",
  "resolved_at": null,
  "closed_at": null,
  "resolution_notes": null
}
```

`source` is one of `alert`, `agent`, `http`, `ssl`, `ssl_expiry`, `dns`, `expiry` or `manual`.

### Get one incident

`GET /incidents/{id}` adds `events`, the timeline:

```json
"events": [
  { "type": "opened", "message": "…", "user_id": null, "created_at": "2026-10-26T10:02:00+00:00" },
  { "type": "acknowledged", "message": "…", "user_id": 2, "created_at": "2026-10-26T10:04:00+00:00" }
]
```

### Acknowledge, investigate or resolve

`POST /incidents/{id}/acknowledge`, `POST /incidents/{id}/investigate`, `POST /incidents/{id}/resolve`

| Parameter | Type | Description |
|---|---|---|
| `notes` | string | Optional, at most 5000 characters (for `resolve`: the resolution) |

Returns the updated incident including `events`. Only these transitions are allowed:

| From | To |
|---|---|
| `open` | `acknowledged`, `investigating`, `resolved` |
| `acknowledged` | `investigating`, `resolved` |
| `investigating` | `acknowledged`, `resolved` |

Any other transition returns `409 Conflict`.

```curl
curl -s -X POST "{app_url}/api/v1/incidents/12/resolve" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Disk cleaned up and log rotation fixed."}'
```

```php
$incident = Http::withToken(getenv('SERVERSCOPE_API_TOKEN'))
    ->acceptJson()
    ->post('{app_url}/api/v1/incidents/12/resolve', ['notes' => 'Disk cleaned up and log rotation fixed.'])
    ->throw()
    ->json('data');
```

```javascript
const response = await fetch('{app_url}/api/v1/incidents/12/resolve', {
  method: 'POST',
  headers: {
    Authorization: `Bearer ${process.env.SERVERSCOPE_API_TOKEN}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({ notes: 'Disk cleaned up and log rotation fixed.' }),
});
```

```python
response = requests.post(
    "{app_url}/api/v1/incidents/12/resolve",
    headers={"Authorization": f"Bearer {os.environ['SERVERSCOPE_API_TOKEN']}", "Accept": "application/json"},
    json={"notes": "Disk cleaned up and log rotation fixed."},
    timeout=10,
)
```

### Add a note

`POST /incidents/{id}/comments`

| Parameter | Type | Description |
|---|---|---|
| `message` | string | **Required**, at most 5000 characters |

Returns `201 Created`:

```json
{ "data": { "type": "comment", "message": "Restarted php-fpm.", "created_at": "2026-10-26T10:11:00+00:00" } }
```

## Errors

Errors are JSON with a `message` and, for validation errors (`422`), an `errors` object with the messages per field:

```json
{
  "message": "…",
  "errors": { "per_page": ["…"] }
}
```

Use the HTTP status code, not the message text, to decide what to do.

| Status | Meaning | What to do |
|---|---|---|
| `401` | Token missing, malformed, expired or revoked (*Ontbrekend of ongeldig API-token.*) | Check the `Authorization` header; create a new token if needed |
| `403` | The token lacks the ability, for example `write` (*Dit token mist de bevoegdheid 'write'.*) | Create a token with the right abilities |
| `404` | The object does not exist | Check the id |
| `409` | Incident transition not allowed | Read the incident first and check its `status` |
| `422` | Validation failed | See `errors` |
| `429` | Too many requests (*Te veel verzoeken.*) | Wait and retry with a back-off |

## Good practice

- Use one token per integration, with only the abilities it needs and an expiry date.
- Keep tokens in a secret store or environment variable, never in a repository or front-end code.
- Back off and retry on `429` and `5xx`; do not retry `4xx` errors other than `429` without changing the request.
- Want ServerScope to call you instead of polling? Use [webhooks](webhooks).

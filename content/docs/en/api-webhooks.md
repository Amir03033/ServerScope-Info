---
order: 7
icon: code
description: Create a personal API token, use the REST API, and receive and verify signed webhooks.
---
# API and webhooks

ServerScope has a **REST API** (version 1) to read data, work on incidents and report deployments, and **webhooks** that push events to your own system. Both are part of **Pro and Premium**; Free has no API tokens and no webhooks. Every endpoint is listed in the [API v1 reference](api-reference).

## Creating an API token

1. Open [Profile → API tokens]({app_url}/profile/api-tokens).
2. Give the token a **Name**, for example `n8n` or `grafana`.
3. Choose the **Abilities**: `read` is always included; only add `write` if the token must update incidents, report deployments or change configuration.
4. Choose the **Validity**: 30 days, 90 days, 1 year or unlimited.
5. Click **Create token** and copy the token (it starts with `ssk_`).

> [!WARNING]
> The token is shown once; ServerScope only stores a SHA-256 hash. A token has the same permissions as your account, limited to the abilities you chose. Revoke a token immediately if it has leaked.

## Authentication and conventions

- Base URL: `{app_url}/api/v1`
- Send the token as a bearer token and ask for JSON:

  ```http
  Authorization: Bearer ssk_…
  Accept: application/json
  ```

- JSON only. Lists are paginated with `?page=2&per_page=50` (max. 100) and return `data`, `links` and `meta`.
- Timestamps are ISO 8601 in UTC.
- At most 120 requests per minute per token.
- A token only sees the data of its own team.

## Endpoints

| Method | Path | Ability | Notes |
|---|---|---|---|
| GET | `/servers` | read | filter `status` = online/offline/pending/maintenance, `environment` |
| GET | `/servers/{id}` | read | includes the latest metrics |
| GET | `/servers/{id}/metrics` | read | `from`, `to` (ISO 8601), `limit` ≤ 1440 (default last hour, 120 samples) |
| GET | `/domains` | read | filter `status` = healthy/warning/critical/unknown |
| GET | `/domains/{id}` | read | HTTP, SSL, DNS and registration state |
| GET | `/domains/{id}/checks` | read | `type` = http/ssl/dns/expiry |
| GET | `/incidents` | read | `state` = active (default)/resolved/closed/all, `severity` |
| GET | `/incidents/{id}` | read | includes the timeline |
| POST | `/incidents/{id}/acknowledge` | write | |
| POST | `/incidents/{id}/investigate` | write | |
| POST | `/incidents/{id}/resolve` | write | optional `notes` |
| POST | `/incidents/{id}/comments` | write | `message` (required) |
| POST | `/deployments` | write | report a deployment; see the [API v1 reference](api-reference#deployments) |

Pages, discovery, dependencies, services, forecasts and assigning incidents are in the [API v1 reference](api-reference).

## Examples

List active incidents:

```sh
curl -s "{app_url}/api/v1/incidents?state=active" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"
```

Acknowledge an incident:

```sh
curl -s -X POST "{app_url}/api/v1/incidents/12/acknowledge" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"
```

Add a note:

```sh
curl -s -X POST "{app_url}/api/v1/incidents/12/comments" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"message": "Restarted php-fpm."}'
```

## Error responses

Errors look like `{"message": "...", "errors": {"field": ["..."]}}`. Messages are in English or Dutch.

| Status | Meaning |
|---|---|
| 401 | Token missing or invalid |
| 403 | The token lacks the ability, for example `write` |
| 404 | The object does not exist |
| 409 | Invalid incident status transition (for example resolving a closed incident) |
| 422 | Validation error; see `errors` |
| 429 | Too many requests; slow down |

> [!NOTE]
> The monitoring agent uses its own endpoint (`POST /api/agent/metrics`) with per-server agent tokens. Agent tokens do not work for this API, and API tokens do not work for the agent.

## Webhooks

Administrators configure webhooks under [Administration → Webhooks]({app_url}/webhooks) with **Add webhook**: a name, a public `https` URL and the events. After creating it you see the signing secret **once** (it starts with `whsec_`).

Events: `incident.opened`, `incident.reopened`, `incident.resolved`, `incident.escalated` (threshold crossed, severity increased or not acknowledged in time), `dns.changed`, `page.changed` (a significant content change on a page that has notifications for it switched on) and `test`.

Generic webhooks receive **every** incident, also incidents grouped under a root incident; those carry `parent_id` and `correlation`, so your own system can group them.

Each delivery is a `POST` with a JSON body:

```json
{"id": "<delivery uuid>", "event": "incident.opened", "created_at": "2026-10-26T10:00:00+00:00", "data": {"incident": {"id": 12, "title": "…", "severity": "critical", "status": "open"}}}
```

With these headers:

| Header | Content |
|---|---|
| `X-ServerScope-Event` | The event, for example `incident.opened` |
| `X-ServerScope-Delivery` | Unique id; use it to ignore duplicate deliveries |
| `X-ServerScope-Timestamp` | Unix time of sending |
| `X-ServerScope-Signature` | `sha256=<hex>` with HMAC-SHA256(secret, `timestamp + "." + raw body`) |

### Verifying the signature

1. Read the **raw** body, before parsing the JSON.
2. Compute `sha256=` + HMAC-SHA256 with your secret over `timestamp + "." + body`.
3. Compare with `X-ServerScope-Signature` using a constant-time comparison.
4. Reject deliveries whose timestamp is more than 5 minutes off.

PHP:

```php
$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
$valid = abs(time() - (int) $timestamp) < 300 && hash_equals($expected, $signatureHeader);
```

Python:

```python
import hashlib, hmac, time

expected = "sha256=" + hmac.new(secret.encode(), f"{timestamp}.".encode() + raw_body, hashlib.sha256).hexdigest()
valid = abs(time.time() - int(timestamp)) < 300 and hmac.compare_digest(expected, signature_header)
```

### Delivery rules

- Only public `https` destinations on allowed ports; checked when saving and again before every attempt. Redirects are not followed.
- Any `2xx` is success. Network errors, timeouts, `408`, `429` and `5xx` are retried up to 6 times (1 min, 5 min, 15 min, 1 h, 6 h); other responses fail immediately.
- After 20 consecutive failed deliveries the webhook is disabled.
- The webhook page lists every delivery; failed ones can be sent again with **Retry**. **Test delivery** sends a `test` event.
- **Rotate secret** creates a new secret; the old one stops working immediately.

Want messages in a chat channel instead? Use [Slack, Teams and Telegram](chat-channels) or [Discord](discord-notifications); they turn events into chat messages.

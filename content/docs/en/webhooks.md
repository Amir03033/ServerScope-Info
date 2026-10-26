---
order: 2
category: integrations
icon: webhook
description: Send incident and DNS events to your own system as signed JSON, verify the signature, and understand retries and the delivery log.
---
# Webhooks

A webhook sends a `POST` request to your own URL whenever something happens in ServerScope, such as an incident opening or a DNS record changing. Use it to connect ticketing, chat, on-call or automation tools.

Only **administrators** can manage webhooks. Want messages in a Discord channel? Use the [Discord integration](discord-notifications) instead: it uses the same events but posts readable messages.

## Creating a webhook

1. Open [Beheer → Webhooks]({app_url}/webhooks) (Admin → Webhooks) and click **Webhook toevoegen** (add webhook).
2. Enter a **name**, the public `https` **URL** of your receiver and choose the **events**.
3. Save. The **signing secret** is shown **once**; it starts with `whsec_`. Store it with your receiver.
4. Click **Testlevering** (test delivery) to send a `test` event and check that your receiver answers with `2xx`.

## Events

| Event | When |
|---|---|
| `incident.opened` | A new incident was opened |
| `incident.reopened` | A resolved incident came back |
| `incident.resolved` | An incident was resolved (manually or automatically) |
| `incident.escalated` | A threshold was crossed, the severity increased, or the incident was not acknowledged in time |
| `dns.changed` | DNS records of a monitored domain changed |
| `test` | Sent with **Testlevering** |

## Request format

Each delivery is a `POST` with a JSON body:

```json
{
  "id": "<delivery uuid>",
  "event": "incident.opened",
  "created_at": "2026-10-26T10:00:00+00:00",
  "data": { "incident": { "id": 12, "title": "…", "severity": "critical", "status": "open", "…": "…" } }
}
```

| Header | Content |
|---|---|
| `X-ServerScope-Event` | The event, for example `incident.opened` |
| `X-ServerScope-Delivery` | Unique delivery id; store it to ignore duplicate deliveries |
| `X-ServerScope-Timestamp` | Unix time of sending |
| `X-ServerScope-Signature` | `sha256=<hex>`: HMAC-SHA256 with your secret over `timestamp + "." + raw body` |

## Verifying the signature

Always verify a delivery before acting on it:

1. Read the **raw** request body, before parsing the JSON.
2. Compute `sha256=` + the hex HMAC-SHA256 of `timestamp + "." + body` with your secret.
3. Compare it with `X-ServerScope-Signature` using a **constant-time** comparison.
4. Reject deliveries whose timestamp is more than 5 minutes away from your clock.
5. Answer quickly with `2xx`, and do slow work afterwards.

```php
<?php
$secret = getenv('SERVERSCOPE_WEBHOOK_SECRET');
$body = file_get_contents('php://input');
$timestamp = $_SERVER['HTTP_X_SERVERSCOPE_TIMESTAMP'] ?? '';
$signature = $_SERVER['HTTP_X_SERVERSCOPE_SIGNATURE'] ?? '';

$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);

if (! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300 || ! hash_equals($expected, $signature)) {
    http_response_code(401);
    exit;
}

$event = json_decode($body, true);
http_response_code(204);
```

```javascript
// Node.js (Express): keep the raw body for the signature check.
import crypto from 'node:crypto';
import express from 'express';

const app = express();

app.post('/serverscope', express.raw({ type: 'application/json' }), (req, res) => {
  const timestamp = req.get('X-ServerScope-Timestamp') ?? '';
  const signature = req.get('X-ServerScope-Signature') ?? '';
  const expected = 'sha256=' + crypto
    .createHmac('sha256', process.env.SERVERSCOPE_WEBHOOK_SECRET)
    .update(`${timestamp}.`)
    .update(req.body)
    .digest('hex');

  const fresh = Math.abs(Date.now() / 1000 - Number(timestamp)) <= 300;
  const valid = signature.length === expected.length
    && crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));

  if (!fresh || !valid) return res.sendStatus(401);

  const event = JSON.parse(req.body);
  res.sendStatus(204);
});
```

```python
import hashlib, hmac, os, time

def verify(raw_body: bytes, timestamp: str, signature: str) -> bool:
    secret = os.environ["SERVERSCOPE_WEBHOOK_SECRET"].encode()
    expected = "sha256=" + hmac.new(secret, f"{timestamp}.".encode() + raw_body, hashlib.sha256).hexdigest()
    return timestamp.isdigit() and abs(time.time() - int(timestamp)) <= 300 and hmac.compare_digest(expected, signature)
```

## Delivery and retries

- Only public `https` destinations on allowed ports are accepted. This is checked when saving and again before every attempt, and redirects are not followed.
- Any `2xx` answer counts as delivered.
- Network errors, timeouts, `408`, `429` and `5xx` are retried up to 6 attempts in total, after 1 minute, 5 minutes, 15 minutes, 1 hour and 6 hours. Other responses fail immediately.
- After **20 consecutive failed deliveries** the webhook is disabled.

> [!NOTE]
> Retries need a queue worker on the ServerScope server. If your ServerScope environment runs without one, a failed delivery is not retried automatically; use **Opnieuw** (retry) in the delivery log.

## Delivery log

The webhook page lists every delivery with its status and error. Failed deliveries can be sent again with **Opnieuw** (retry).

## Rotating the secret

**Geheim vernieuwen** (rotate secret) creates a new secret and shows it once. The old secret stops working **immediately**, so update your receiver straight away.

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| The URL is refused when saving | Not `https`, not a public address, or a port that is not allowed |
| Every delivery fails with a signature error | The body was parsed or re-encoded before verifying; use the raw bytes |
| Deliveries are rejected as too old | Your server's clock is off; synchronise it with NTP |
| The same event arrives twice | A retry after a timeout; deduplicate on `X-ServerScope-Delivery` |
| The webhook switched itself off | 20 failures in a row; fix the receiver, then enable it again |

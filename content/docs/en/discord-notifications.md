---
order: 1
category: integrations
icon: message
description: Create a Discord webhook, connect it to ServerScope, test it, and replace the URL securely.
---
# Discord notifications

ServerScope can post incidents and DNS changes as tidy messages (embeds) in a Discord channel. It uses Discord's **incoming webhooks**: no bot or extra account is needed.

Only a ServerScope **administrator** can set up a Discord connection. In Discord you need the **Manage Webhooks** permission on the channel.

## 1. Create a webhook in Discord

1. In Discord, open the channel settings (the gear next to the channel name).
2. Go to **Integrations → Webhooks** and click **New Webhook**.
3. Name the webhook (for example `ServerScope`) and check the channel.
4. Click **Copy Webhook URL**.

The URL looks like `https://discord.com/api/webhooks/<id>/<token>`.

> [!WARNING]
> Anyone with this URL can post to your channel. Treat it like a password: never paste it in tickets, chats, screenshots or repositories.

## 2. Connect the webhook in ServerScope

1. Open [Beheer → Webhooks]({app_url}/webhooks) (Admin → Webhooks) and click **Discord koppelen** (connect Discord).
2. Enter a **Naam** (name), for example `#ops-alerts`.
3. Paste the **Discord-webhook-URL**.
4. Optionally choose a **Minimale ernst** (minimum severity); incidents below it are not sent to Discord.
5. Select the **Gebeurtenissen** (events) you want to receive.
6. Leave **Actief** (active) ticked and click **Discord-webhook aanmaken** (create Discord webhook).

ServerScope only accepts genuine Discord webhook URLs (`discord.com` or `discordapp.com`, including `ptb.`/`canary.`, with `/api/webhooks/…` or `/api/v10/webhooks/…`) without extra parameters.

## 3. Test the connection

1. Open the webhook in the list.
2. Click **Testmelding naar Discord** (send test message to Discord).
3. Check that a *Testmelding* message appears in the channel.

**Leveringen** (deliveries) lists every attempt with its status (*Geleverd* delivered, *Opnieuw proberen* retrying, *Mislukt* failed), Discord's HTTP code and any error. ServerScope never sends a test message automatically, not even when it is updated.

## Supported events

| Event | Example messages in Discord |
|---|---|
| `incident.opened` | Server offline · Monitoring-alert geactiveerd · Website onbereikbaar · Probleem met SSL-certificaat · SSL-certificaat verloopt binnenkort · DNS-resolutie mislukt · Domeinregistratie verloopt binnenkort · Incident aangemaakt |
| `incident.resolved` | Server weer online · Monitoring-alert opgelost · Website weer bereikbaar · SSL-certificaat weer geldig/vernieuwd · DNS-resolutie hersteld · Domeinregistratie in orde · Incident opgelost |
| `incident.reopened` | “Opnieuw: …” when a problem recurs within the reopen window |
| `incident.escalated` | “Escalatie: …” on a higher severity, the next expiry threshold or an unacknowledged incident |
| `dns.changed` | DNS-records gewijzigd, with the added and removed values |

The messages are in Dutch, like the application. Each one shows the ServerScope name, the kind of event, title and description, status, severity, source, the affected server or domain, the time and a link to the incident in ServerScope. The colour reflects the severity: **red** critical, **orange** warning, **blue** info, **green** resolved. Messages can never mention `@everyone`, `@here` or individual people.

## How ServerScope avoids message floods

- Only changes to an incident are posted, never every failed check.
- The same event for the same incident in the same state is posted at most once per **15 minutes**, so a server that keeps dropping out and coming back does not flood the channel.
- **Minimale ernst** lets you post, for example, critical incidents only.

## Troubleshooting

| What you see under *Leveringen* | Cause and fix |
|---|---|
| **Mislukt**, HTTP `404` | The webhook no longer exists in Discord (deleted, or the channel is gone). Create a new webhook and replace the URL. |
| **Mislukt**, HTTP `401` or `403` | The URL is wrong or the token is invalid. Copy the URL from Discord again. |
| **Opnieuw proberen**, HTTP `429` | Discord is rate limiting. ServerScope retries later. |
| **Opnieuw proberen** with a network error or timeout | A temporary problem; up to 6 attempts follow (1 minute to 6 hours). |
| The webhook is **disabled** | After 20 failed deliveries in a row ServerScope disables the webhook. Fix the cause and tick **Actief** again. |
| No messages and no deliveries | Check that the event is selected, that **Minimale ernst** is not too high and that the webhook is **Actief**. |

> [!NOTE]
> Deliveries are processed by ServerScope's queue. Without a queue worker they are not sent or retried; that is a task for whoever hosts ServerScope.

## Replacing (rotating) the URL securely

If the URL leaked, or you want to use another channel:

1. Create a **new** webhook in Discord and copy its URL.
2. Open the webhook in ServerScope and click **Discord-URL vervangen** (replace Discord URL) or **Bewerken** (edit).
3. Paste the new URL and save. An empty field keeps the current URL.
4. Send a **Testmelding naar Discord** to check the new URL.
5. **Delete the old webhook in Discord.** Only then is the old URL truly unusable.

## How the URL is protected

- The full URL is stored **encrypted** and never shown again. ServerScope only displays `https://discord.com/api/webhooks/<id>/••••••`.
- The token never appears in the audit log, in error messages or in the delivery history.
- Only administrators can view, create, change, test or delete Discord webhooks.

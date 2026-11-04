---
order: 5
icon: message
description: Create a Discord webhook, connect it to ServerScope, test it, and replace the URL securely.
---
# Discord notifications

ServerScope can post incidents and DNS changes as tidy messages (embeds) in a Discord channel. It uses Discord's **incoming webhooks**: no bot or extra account is needed.

Only a team **owner or administrator** can set up a Discord connection, on Pro and Premium. Prefer Slack, Microsoft Teams or Telegram? See [Slack, Teams and Telegram](chat-channels). In Discord you need the **Manage Webhooks** permission on the channel.

## 1. Create a webhook in Discord

1. In Discord, open the channel settings (the gear next to the channel name).
2. Go to **Integrations → Webhooks** and click **New Webhook**.
3. Name the webhook (for example `ServerScope`) and check the channel.
4. Click **Copy Webhook URL**.

The URL looks like `https://discord.com/api/webhooks/<id>/<token>`.

> [!WARNING]
> Anyone with this URL can post to your channel. Treat it like a password: never paste it in tickets, chats, screenshots or repositories.

## 2. Connect the webhook in ServerScope

1. Open [Administration → Webhooks]({app_url}/webhooks) and click **Connect Discord**.
2. Enter a **Name**, for example `#ops-alerts`.
3. Paste the **Discord webhook URL**.
4. Optionally choose a **Minimum severity**; incidents below it are not sent to Discord.
5. Select the **Events** you want to receive.
6. Leave **Active** ticked and click **Create Discord webhook**.

ServerScope only accepts genuine Discord webhook URLs (`discord.com` or `discordapp.com`, including `ptb.`/`canary.`, with `/api/webhooks/…` or `/api/v10/webhooks/…`) without extra parameters.

## 3. Test the connection

1. Open the webhook in the list.
2. Click **Test notification to Discord**.
3. Check that a test message appears in the channel.

**Deliveries** lists every attempt with its status (*Delivered*, *Retrying* or *Failed*), Discord's HTTP code and any error. ServerScope never sends a test message automatically, not even when it is updated.

## Supported events

| Event | Example messages in Discord |
|---|---|
| `incident.opened` | A server went offline, an alert fired, a website or page is down, an SSL certificate is invalid or about to expire, DNS resolution failed, a domain registration expires, a service stopped, a scheduled job is late or failed |
| `incident.resolved` | The same problems recovering: the server is back online, the website answers again, the certificate is valid again, and so on |
| `incident.reopened` | A problem recurs within the reopen window |
| `incident.escalated` | A higher severity, the next expiry threshold or an incident nobody acknowledged |
| `dns.changed` | Changed DNS records, with the added and removed values |
| `page.changed` | A significant content change on a page that has notifications for it switched on |

The messages are in the team's language (English or Dutch). Each one shows the ServerScope name, the kind of event, title and description, status, severity, source, the affected server or domain, the time and a link to the incident in ServerScope. The colour reflects the severity: **red** critical, **orange** high, **yellow** medium, **blue** low, **green** resolved. Messages can never mention `@everyone`, `@here` or individual people.

## How ServerScope avoids message floods

- Only changes to an incident are posted, never every failed check.
- The same event for the same incident in the same state is posted at most once per **15 minutes**, so a server that keeps dropping out and coming back does not flood the channel.
- **Minimum severity** lets you post, for example, critical incidents only.
- When several incidents are grouped into one outage (see [Dependencies](dependencies)), Discord gets one message for the root incident.

## Troubleshooting

| What you see under *Leveringen* | Cause and fix |
|---|---|
| **Failed**, HTTP `404` | The webhook no longer exists in Discord (deleted, or the channel is gone). Create a new webhook and replace the URL. |
| **Failed**, HTTP `401` or `403` | The URL is wrong or the token is invalid. Copy the URL from Discord again. |
| **Try again**, HTTP `429` | Discord is rate limiting. ServerScope retries later. |
| **Try again** with a network error or timeout | A temporary problem; up to 6 attempts follow (1 minute to 6 hours). |
| The webhook is **disabled** | After 20 failed deliveries in a row ServerScope disables the webhook. Fix the cause and tick **Active** again. |
| No messages and no deliveries | Check that the event is selected, that **Minimum severity** is not too high and that the webhook is **Active**. |

> [!NOTE]
> Deliveries are processed by ServerScope's queue. Without a queue worker they are not sent or retried; that is a task for whoever hosts ServerScope.

## Replacing (rotating) the URL securely

If the URL leaked, or you want to use another channel:

1. Create a **new** webhook in Discord and copy its URL.
2. Open the webhook in ServerScope and click **Replace Discord URL** or **Edit**.
3. Paste the new URL and save. An empty field keeps the current URL.
4. Send a **Test notification to Discord** to check the new URL.
5. **Delete the old webhook in Discord.** Only then is the old URL truly unusable.

## How the URL is protected

- The full URL is stored **encrypted** and never shown again. ServerScope only displays `https://discord.com/api/webhooks/<id>/••••••`.
- The token never appears in the audit log, in error messages or in the delivery history.
- Only administrators can view, create, change, test or delete Discord webhooks.

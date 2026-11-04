---
order: 5
icon: chat
category: alerts
feature: chat-channels
description: Send incidents to Slack, Microsoft Teams or Telegram, with buttons to view or acknowledge them.
---
# Slack, Teams and Telegram

Next to [Discord](discord-notifications), ServerScope posts incidents to **Slack**, **Microsoft Teams** and **Telegram**. Messages use each channel's own format and have two buttons: **View incident** and **Acknowledge**.

Team administrators set this up under [Administration → Webhooks]({app_url}/webhooks) with **Connect chat**. Chat channels, like webhooks, are part of Pro and Premium.

## Slack

1. Go to `api.slack.com/apps` → **Create New App**, turn on **Incoming Webhooks**, choose **Add New Webhook to Workspace** and pick a channel.
2. Copy the webhook URL (`https://hooks.slack.com/services/…`; `/triggers/` and `/workflows/` URLs work too).
3. In ServerScope choose **Connect chat → Slack**, paste the URL, choose the events and save.

## Microsoft Teams

1. In the Teams channel open **⋯ → Workflows** and choose *Post to a channel when a webhook request is received*.
2. Copy the URL of the workflow (on `*.logic.azure.com`, `*.api.powerplatform.com` or `*.webhook.office.com`).
3. In ServerScope choose **Connect chat → Microsoft Teams**, paste the URL and save.

## Telegram

1. Create a bot with **@BotFather** (`/newbot`) and copy its **Bot token** (it looks like `123456789:AA…`).
2. Add the bot to your group and send a message in the group.
3. Look up the **Chat ID**:

   ```sh
   curl -s "https://api.telegram.org/bot{token}/getUpdates" | grep -o '"chat":{"id":-\?[0-9]*,"title":"[^"]*"'
   ```

   A group ID often starts with `-100`. For a public channel `@channelname` works too.
4. In ServerScope choose **Connect chat → Telegram**, enter the bot token and the chat ID, and save.

## Testing

Open the channel in ServerScope and send a **Test message**. The delivery log shows every attempt with its status and any error, just like for webhooks.

## How it behaves

- **View incident** opens the incident in ServerScope. **Acknowledge** opens a confirmation page where you acknowledge with one tap. You always need to be logged in: the link itself changes nothing.
- The same incident in the same state is posted at most once per **15 minutes** per channel, so flapping does not flood the channel.
- A grouped incident (see [Dependencies](dependencies)) is posted as one message for the root incident.
- A minimum severity per channel skips lower incidents.

> [!IMPORTANT]
> Webhook URLs and bot tokens are secrets. ServerScope stores them encrypted, shows them masked and filters them out of error messages. To change them use **Replace webhook URL** or **Change bot or chat**.

## Troubleshooting Telegram

| Error | Fix |
|---|---|
| `chat not found` | The chat ID is wrong, or the bot is not in the group. |
| `group chat was upgraded to a supergroup` | The group got a new ID; look it up again with `getUpdates`. |
| `need administrator rights` | In a channel the bot must be an administrator. |

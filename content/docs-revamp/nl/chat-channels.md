---
order: 5
icon: chat
category: alerts
feature: chat-channels
description: Incidenten naar Slack, Microsoft Teams of Telegram sturen, met knoppen om ze te bekijken of te bevestigen.
---
# Slack, Teams en Telegram

Naast [Discord](discord-notifications) plaatst ServerScope incidenten ook in **Slack**, **Microsoft Teams** en **Telegram**. Berichten gebruiken de opmaak van het kanaal en hebben twee knoppen: **Bekijk incident** en **Bevestigen**.

Teambeheerders stellen dit in onder [Beheer → Webhooks]({app_url}/webhooks) met **Chat koppelen**. Chatkanalen horen, net als webhooks, bij Pro en Premium.

## Slack

1. Ga naar `api.slack.com/apps` → **Create New App**, zet **Incoming Webhooks** aan, kies **Add New Webhook to Workspace** en kies een kanaal.
2. Kopieer de webhook-URL (`https://hooks.slack.com/services/…`; URL's met `/triggers/` en `/workflows/` werken ook).
3. Kies in ServerScope **Chat koppelen → Slack**, plak de URL, kies de gebeurtenissen en sla op.

## Microsoft Teams

1. Open in het Teams-kanaal **⋯ → Workflows** en kies *Post to a channel when a webhook request is received*.
2. Kopieer de URL van de workflow (op `*.logic.azure.com`, `*.api.powerplatform.com` of `*.webhook.office.com`).
3. Kies in ServerScope **Chat koppelen → Microsoft Teams**, plak de URL en sla op.

## Telegram

1. Maak een bot via **@BotFather** (`/newbot`) en kopieer het **Bot-token** (het ziet eruit als `123456789:AA…`).
2. Voeg de bot toe aan je groep en stuur een bericht in de groep.
3. Zoek de **Chat-ID** op:

   ```sh
   curl -s "https://api.telegram.org/bot{token}/getUpdates" | grep -o '"chat":{"id":-\?[0-9]*,"title":"[^"]*"'
   ```

   Een groeps-ID begint vaak met `-100`. Voor een openbaar kanaal werkt ook `@kanaalnaam`.
4. Kies in ServerScope **Chat koppelen → Telegram**, vul het bot-token en de chat-ID in en sla op.

## Testen

Open het kanaal in ServerScope en stuur een **Testmelding**. Het leverlog toont elke poging met de status en een eventuele fout, net als bij webhooks.

## Hoe het werkt

- **Bekijk incident** opent het incident in ServerScope. **Bevestigen** opent een bevestigpagina waar je met één tik bevestigt. Je moet altijd ingelogd zijn: de link zelf verandert niets.
- Hetzelfde incident in dezelfde toestand wordt per kanaal maximaal één keer per **15 minuten** geplaatst, zodat een storing die komt en gaat het kanaal niet overspoelt.
- Een gegroepeerd incident (zie [Afhankelijkheden](dependencies)) wordt één bericht voor het hoofdincident.
- Met een minimale ernst per kanaal sla je lagere incidenten over.

> [!IMPORTANT]
> Webhook-URL's en bottokens zijn geheimen. ServerScope bewaart ze versleuteld, toont ze gemaskeerd en filtert ze uit foutmeldingen. Wijzigen doe je met **Webhook-URL vervangen** of **Bot of chat wijzigen**.

## Problemen met Telegram

| Fout | Oplossing |
|---|---|
| `chat not found` | De chat-ID klopt niet, of de bot zit niet in de groep. |
| `group chat was upgraded to a supergroup` | De groep kreeg een nieuwe ID; zoek hem opnieuw op met `getUpdates`. |
| `need administrator rights` | In een kanaal moet de bot beheerder zijn. |

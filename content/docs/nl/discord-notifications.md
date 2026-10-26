---
order: 1
category: integrations
icon: message
description: Een Discord-webhook aanmaken, koppelen aan ServerScope, testen, en de URL veilig vervangen.
---
# Discord-meldingen

ServerScope kan incidenten en DNS-wijzigingen als nette berichten (embeds) in een Discord-kanaal plaatsen. Dit gebruikt de **incoming webhooks** van Discord: er is geen bot of extra account nodig.

Een Discord-koppeling instellen kan alleen een **beheerder** van ServerScope. In Discord heb je het recht **Webhooks beheren** nodig op het kanaal.

## 1. Een webhook aanmaken in Discord

1. Open in Discord de instellingen van het kanaal (tandwiel naast de kanaalnaam).
2. Ga naar **Integraties → Webhooks** en klik op **Nieuwe webhook**.
3. Geef de webhook een naam (bijvoorbeeld `ServerScope`) en controleer het kanaal.
4. Klik op **Webhook-URL kopiëren**.

De URL ziet eruit als `https://discord.com/api/webhooks/<id>/<token>`.

> [!WARNING]
> Iedereen met deze URL kan berichten in je kanaal plaatsen. Behandel hem als een wachtwoord: niet in tickets, chats, screenshots of repositories plakken.

## 2. De webhook koppelen in ServerScope

1. Open [Beheer → Webhooks]({app_url}/webhooks) en klik op **Discord koppelen**.
2. Vul een **Naam** in, bijvoorbeeld `#ops-meldingen`.
3. Plak de **Discord-webhook-URL**.
4. Kies optioneel een **Minimale ernst**; incidenten met een lagere ernst worden dan niet naar Discord gestuurd.
5. Selecteer de **Gebeurtenissen** die je wilt ontvangen.
6. Laat **Actief** aangevinkt en klik op **Discord-webhook aanmaken**.

ServerScope accepteert alleen echte Discord-webhook-URL's (`discord.com` of `discordapp.com`, ook `ptb.`/`canary.`, met `/api/webhooks/…` of `/api/v10/webhooks/…`) zonder extra parameters.

## 3. De verbinding testen

1. Open de webhook in de lijst.
2. Klik op **Testmelding naar Discord**.
3. Controleer of er in het kanaal een bericht *Testmelding* verschijnt.

Onder **Leveringen** zie je elke poging met status (*Geleverd*, *Opnieuw proberen*, *Mislukt*), de HTTP-code van Discord en eventuele foutmelding. Er wordt nooit automatisch een testmelding gestuurd, ook niet bij een update van ServerScope.

## Ondersteunde gebeurtenissen

| Gebeurtenis | Voorbeelden van berichten in Discord |
|---|---|
| `incident.opened` | Server offline · Monitoring-alert geactiveerd · Website onbereikbaar · Probleem met SSL-certificaat · SSL-certificaat verloopt binnenkort · DNS-resolutie mislukt · Domeinregistratie verloopt binnenkort · Incident aangemaakt |
| `incident.resolved` | Server weer online · Monitoring-alert opgelost · Website weer bereikbaar · SSL-certificaat weer geldig/vernieuwd · DNS-resolutie hersteld · Domeinregistratie in orde · Incident opgelost |
| `incident.reopened` | “Opnieuw: …” wanneer een probleem binnen het heropen-venster terugkeert |
| `incident.escalated` | “Escalatie: …” bij een hogere ernst, een volgende vervaldrempel of een onbevestigd incident |
| `dns.changed` | DNS-records gewijzigd, met de toegevoegde en verwijderde waarden |

Elk bericht toont de ServerScope-naam, het soort gebeurtenis, de titel en omschrijving, status, ernst, bron, de betrokken server of het domein, het tijdstip en een link naar het incident in ServerScope. De kleur geeft de ernst aan: **rood** kritiek, **oranje** waarschuwing, **blauw** info, **groen** opgelost. Berichten kunnen nooit `@everyone`, `@here` of personen taggen.

## Zo voorkomt ServerScope te veel berichten

- Alleen wijzigingen in een incident worden gemeld, nooit elke mislukte controle.
- Dezelfde gebeurtenis voor hetzelfde incident in dezelfde toestand wordt maximaal één keer per **15 minuten** geplaatst. Een server die steeds wegvalt en terugkomt, vult het kanaal dus niet.
- Met **Minimale ernst** filter je bijvoorbeeld alleen kritieke incidenten.

## Problemen oplossen

| Wat je ziet bij *Leveringen* | Oorzaak en oplossing |
|---|---|
| **Mislukt**, HTTP `404` | De webhook bestaat niet meer in Discord (verwijderd of kanaal weg). Maak een nieuwe webhook en vervang de URL. |
| **Mislukt**, HTTP `401` of `403` | De URL klopt niet of het token is ongeldig. Kopieer de URL opnieuw uit Discord. |
| **Opnieuw proberen**, HTTP `429` | Discord beperkt tijdelijk het aantal berichten. ServerScope probeert het later opnieuw. |
| **Opnieuw proberen** met een netwerkfout of time-out | Tijdelijk probleem; er volgen tot 6 pogingen (1 min tot 6 uur). |
| De webhook staat op **uitgeschakeld** | Na 20 mislukte leveringen op rij schakelt ServerScope de webhook uit. Los de oorzaak op en zet **Actief** weer aan. |
| Geen berichten, geen leveringen | Controleer of de gebeurtenis is geselecteerd, of de **Minimale ernst** niet te hoog staat en of de webhook **Actief** is. |

> [!NOTE]
> Leveringen worden verwerkt door de wachtrij van ServerScope. Zonder queue-worker worden ze niet (opnieuw) verstuurd; dat is een taak voor wie ServerScope host.

## De URL veilig vervangen (roteren)

Lekt de URL uit, of wil je een ander kanaal gebruiken:

1. Maak in Discord een **nieuwe** webhook en kopieer de URL.
2. Open de webhook in ServerScope en klik op **Discord-URL vervangen** (of **Bewerken**).
3. Plak de nieuwe URL en sla op. Een leeg veld laat de huidige URL ongewijzigd.
4. Stuur een **Testmelding naar Discord** om de nieuwe URL te controleren.
5. **Verwijder de oude webhook in Discord**. Pas dan is de oude URL echt onbruikbaar.

## Hoe de URL beschermd wordt

- De volledige URL wordt **versleuteld** opgeslagen en daarna nooit meer getoond. In ServerScope zie je alleen `https://discord.com/api/webhooks/<id>/••••••`.
- Het token komt niet in het auditlog, in foutmeldingen of in de leveringsgeschiedenis.
- Alleen beheerders kunnen Discord-webhooks bekijken, aanmaken, wijzigen, testen of verwijderen.

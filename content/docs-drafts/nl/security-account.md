# Beveiliging en accountbeheer

Wat je mag doen, hangt af van je **rol** in je team (eigenaar, beheerder of lid; zie [Teams en rollen](teams-and-roles)). Wat je team kan toevoegen en hoe lang de geschiedenis wordt bewaard, hangt af van het **plan** (Free, Pro of Premium); zie de prijspagina.

| Functie | Wie |
|---|---|
| Tweestapsverificatie, wachtwoord, API-tokens, meldingsvoorkeuren | Iedereen, voor het eigen account |
| Leden uitnodigen, rollen wijzigen, leden verwijderen | Eigenaar of beheerder (de rol van een eigenaar wijzigt alleen een eigenaar) |
| Auditlog bekijken | Eigenaar of beheerder |
| Onderhoudsvensters voor servers en domeinen | Eigenaar of beheerder |
| Plan, betaling en facturen (**Beheer → Abonnement en betaling**) | Eigenaar of beheerder |
| Escalatiecontacten (maximaal 5 per team) | Eigenaar of beheerder |
| Bewaartermijnen en e-mailinstellingen | Host van ServerScope (serverconfiguratie) |

## Tweestapsverificatie (2FA)

Bescherm je account met een code uit een authenticator-app (TOTP), zoals 1Password, Authy of Google Authenticator.

1. Open [Profiel]({app_url}/profile) en klik bij **Tweestapsverificatie** op **Tweestapsverificatie instellen**.
2. Voeg in je authenticator-app een account toe via “sleutel invoeren” met de getoonde sleutel (tijdgebaseerd, 6 cijfers), of open op je telefoon de getoonde link.
3. Vul de code uit de app in en klik op **Bevestigen en inschakelen**.
4. **Bewaar de herstelcodes** die nu verschijnen op een veilige plek. Elke code werkt één keer en ze worden niet opnieuw getoond.

Bij het inloggen vraagt ServerScope voortaan na je wachtwoord om een code. Ben je je telefoon kwijt, gebruik dan een herstelcode.

- **Nieuwe herstelcodes** maakt een nieuwe set; de oude codes vervallen.
- **Uitschakelen** vraagt om je huidige wachtwoord.
- Beheerders zien in **Gebruikers** wie 2FA heeft ingeschakeld.

> [!TIP]
> Schakel 2FA in voor elk beheerdersaccount: beheerders kunnen webhooks, gebruikers en de monitoringconfiguratie wijzigen.

## Wachtwoord

- Wijzig je wachtwoord onder **Profiel → Wachtwoord wijzigen**. Het nieuwe wachtwoord heeft minimaal 12 tekens met hoofdletters, kleine letters en cijfers.
- Na een wijziging worden je **andere sessies afgemeld**.
- Wachtwoord vergeten? Gebruik **Wachtwoord vergeten** op de inlogpagina; de link is 60 minuten geldig.

## Gebruikers uitnodigen

1. Open [Beheer → Gebruikers]({app_url}/users).
2. Vul bij **Gebruiker uitnodigen** de naam en het e-mailadres in en vink eventueel **Beheerder** aan.
3. Klik op **Uitnodigen**. De persoon ontvangt een e-mail met een link die **7 dagen** geldig is en accepteert de uitnodiging zelf. Zolang die niet is geaccepteerd, kun je de uitnodiging intrekken of opnieuw sturen.

Alle leden van een team zien de servers, domeinen en incidenten van dat team; eigenaren en beheerders wijzigen de configuratie. Beveiligingen:

- Een team houdt altijd minstens één eigenaar en één beheerder; de laatste kan niet worden verwijderd of gedegradeerd.
- Je kunt je eigen beheerdersrechten niet intrekken en jezelf niet verwijderen onder **Gebruikers**.

## Auditlog

Onder [Beheer → Auditlog]({app_url}/audit-logs) zien eigenaren en beheerders wie in hun team wat heeft gewijzigd, met tijdstip, gebruiker, onderdeel en IP-adres. Je kunt filteren op actie en gebruiker en zoeken.

Vastgelegd worden onder andere: servers en domeinen (aanmaken, wijzigen, pauzeren, verwijderen, herstellen, onderhoud), agenttokens (uitgeven, roteren, intrekken), agent-updates, handmatige incidenten, statuspagina's, webhooks en chatkanalen (inclusief vervangen van een URL of bot-token en automatisch uitschakelen), GitHub-koppelingen, geplande taken (ook een nieuwe ping-URL), gedeelde nabeschouwingen, API-tokens, en gebruikersacties (uitnodiging, rolwijziging, wachtwoord, 2FA, gebruik van herstelcodes).

> [!NOTE]
> Geheimen (tokens, wachtwoorden, webhook-URL's, bottokens, ping-URL's) worden nooit in het auditlog opgeslagen. Wijzigingen aan **alertregels** worden op dit moment niet in het auditlog vastgelegd.

## Onderhoudsvensters

Plan onderhoud zodat er geen onnodige incidenten ontstaan:

- **Servers**: gebruik het paneel **onderhoud** op de serverpagina: start onderhoud nu voor een gekozen duur, of kies zelf een start- en eindtijd. Er worden dan geen nieuwe alerts of incidenten voor de server geopend.
- **Domeinen**: stel bij **Bewerken** een start, einde en reden in. De controles lopen door, maar er ontstaan geen incidenten.
- Op statuspagina's verschijnt het onderdeel als *Onderhoud*.

Tijden worden ingevoerd en getoond in de tijdzone Europe/Amsterdam en opgeslagen in UTC.

## Geheimen en persoonsgegevens

- Webhook-URL's, bottokens van chatkanalen en het GitHub-webhookgeheim worden **versleuteld** opgeslagen, gemaskeerd getoond en uit foutmeldingen gefilterd.
- Logregels worden **twee keer** ontdaan van wachtwoorden, tokens en e-mailadressen: door de agent en door ServerScope.
- De ping-URL van een geplande taak is een geheim; vervang hem met **Nieuwe URL** als hij is uitgelekt.
- Agent-updates zijn **ondertekend**; zelfs een gehackte ServerScope-server kan geen eigen code op je servers installeren. Zie [Agent-updates](agent-updates).
- Elk team ziet alleen zijn eigen gegevens.

## Bewaartermijnen

Hoe lang de geschiedenis wordt bewaard, hangt af van het plan (Free: 14 dagen, Pro: 365 dagen, Premium: onbeperkt). Logregels blijven 14 dagen bewaard. Host je ServerScope zelf, dan stel je per soort gegevens een bewaartermijn in dagen in (`SERVERSCOPE_RETAIN_*_DAYS`); een dagelijkse taak verwijdert oudere gegevens.

> [!WARNING]
> Rapporten kunnen alleen periodes beslaan waarvoor nog gegevens bewaard zijn.

## Een beveiligingsprobleem melden

Zie je iets dat niet klopt, of denk je dat een token of webhook-URL is uitgelekt? Trek het token in of vervang de URL (zie [Slack, Teams en Telegram](chat-channels), [Discord](discord-notifications) en [API en webhooks](api-webhooks)) en neem [contact](/nl/contact) op. Stuur nooit tokens, wachtwoorden of webhook-URL's mee.

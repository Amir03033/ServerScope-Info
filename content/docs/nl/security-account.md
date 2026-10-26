---
order: 1
category: account
icon: shield
description: Tweestapsverificatie, wachtwoorden, gebruikers uitnodigen, rollen, het auditlog, onderhoudsvensters en bewaartermijnen.
---
# Beveiliging en accountbeheer

ServerScope heeft geen abonnementen of pakketten: alle functies zijn beschikbaar in elke omgeving. Wat je mag doen, hangt alleen af van je **rol** (gebruiker of beheerder). Sommige instellingen, zoals bewaartermijnen, regelt degene die ServerScope host.

| Functie | Wie |
|---|---|
| Tweestapsverificatie, wachtwoord, API-tokens, meldingsvoorkeuren | Iedere gebruiker, voor het eigen account |
| Gebruikers uitnodigen, rollen wijzigen, gebruikers verwijderen | Beheerder |
| Auditlog bekijken | Beheerder |
| Onderhoudsvensters voor servers en domeinen | Beheerder |
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
3. Klik op **Uitnodigen**. De gebruiker ontvangt een e-mail om zelf een wachtwoord in te stellen (60 minuten geldig).

Alle gebruikers zien alle servers, domeinen en incidenten; beheerders wijzigen de configuratie. Beveiligingen:

- Er blijft altijd minstens één beheerder over; de laatste beheerder kan niet worden verwijderd of gedegradeerd.
- Je kunt je eigen beheerdersrechten niet intrekken en je eigen account niet via *Gebruikers* verwijderen.

## Auditlog

Onder [Beheer → Auditlog]({app_url}/audit-logs) zien beheerders wie wat heeft gewijzigd, met tijdstip, gebruiker, onderdeel en IP-adres. Je kunt filteren op actie en gebruiker en zoeken.

Vastgelegd worden onder andere: servers en domeinen (aanmaken, wijzigen, pauzeren, verwijderen, herstellen, onderhoud), agenttokens (uitgeven, roteren, intrekken), handmatige incidenten, statuspagina's, webhooks (inclusief vervangen van een Discord-URL en automatisch uitschakelen), API-tokens, en gebruikersacties (uitnodiging, rolwijziging, wachtwoord, 2FA, gebruik van herstelcodes).

> [!NOTE]
> Geheimen (tokens, wachtwoorden, webhook-URL's van Discord) worden nooit in het auditlog opgeslagen. Wijzigingen aan **alertregels** worden op dit moment niet in het auditlog vastgelegd.

## Onderhoudsvensters

Plan onderhoud zodat er geen onnodige incidenten ontstaan:

- **Servers**: start op de serverpagina een onderhoudsvenster van 5 minuten tot 7 dagen, of stel bij *Bewerken* een start- en eindtijd in. Er worden dan geen alerts of incidenten voor die server aangemaakt.
- **Domeinen**: stel bij *Bewerken* een start, einde en reden in. De controles lopen door, maar er ontstaan geen incidenten.
- Op statuspagina's verschijnt het onderdeel als *Onderhoud*.

Tijden worden ingevoerd en getoond in de tijdzone Europe/Amsterdam en opgeslagen in UTC.

## Bewaartermijnen

Standaard bewaart ServerScope alle historie. Wie ServerScope host, kan per soort gegevens een bewaartermijn in dagen instellen voor metingen, domeincontroles, verzendgeschiedenis van meldingen, gelezen meldingen en het auditlog (`SERVERSCOPE_RETAIN_*_DAYS`). Een dagelijkse taak (`serverscope:prune`) verwijdert oudere gegevens.

> [!WARNING]
> Rapporten kunnen alleen periodes beslaan waarvoor nog gegevens bewaard zijn.

## Een beveiligingsprobleem melden

Zie je iets dat niet klopt, of denk je dat een token of webhook-URL is uitgelekt? Trek het token in of vervang de URL (zie [Discord](discord-notifications) en [API-referentie](api-reference) en [webhooks](webhooks)) en lees [hoe je een probleem meldt](/nl/security). Stuur nooit tokens, wachtwoorden of webhook-URL's mee.

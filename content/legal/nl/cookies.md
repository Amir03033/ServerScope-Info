---
title: Cookieverklaring
description: Welke cookies info.serverscope.nl en ServerScope gebruiken, en waarom er geen cookiebanner is.
version: 0.1
changes: Eerste concept.
---
<!-- JURIST: toets of alle hieronder genoemde cookies onder de uitzondering voor strikt noodzakelijke cookies vallen (art. 11.7a lid 3 Telecommunicatiewet). -->

## 1. Kort gezegd

> [!SUMMARY]
> We gebruiken alleen cookies die nodig zijn om de site en de applicatie te laten werken. Geen tracking, geen advertenties. Daarom is er geen cookiebanner.

## 2. Cookies op info.serverscope.nl

> [!SUMMARY]
> Twee functionele cookies: een sessiecookie en een beveiligingscookie voor formulieren.

| Naam | Doel | Bewaartermijn |
|---|---|---|
| `{cookie.session}` | Houdt je sessie bij, bijvoorbeeld voor meldingen na het versturen van een formulier en het inloggen in het beheerpaneel | {cookie.lifetime} minuten na je laatste bezoek |
| `XSRF-TOKEN` | Beschermt formulieren (wachtlijst, contact, "Was dit nuttig?") tegen misbruik vanaf andere websites | {cookie.lifetime} minuten na je laatste bezoek |

De sessie zelf staat als bestand op onze server. Daarin bewaren we geen IP-adres en geen user agent.

### Thema-keuze (geen cookie)

Kies je met de knop in de kopregel een licht of donker thema, dan bewaren we die keuze in de lokale opslag van je browser (`ss-theme`). Die keuze wordt nooit naar onze server gestuurd. Je verwijdert haar door de sitegegevens in je browser te wissen.

### Bezoekersteller (geen cookie)

We tellen hoe vaak een pagina wordt bekeken: alleen het pad, de dag en het aantal. Daarvoor gebruiken we geen cookie en bewaren we niets over jou.

## 3. Cookies in de ServerScope-applicatie

> [!SUMMARY]
> Dezelfde twee functionele cookies, plus een "onthoud mij"-cookie als je die zelf aanvinkt.

| Naam | Doel | Wanneer |
|---|---|---|
| Sessiecookie | Je ingelogd houden | Altijd na inloggen |
| `XSRF-TOKEN` | Formulieren beveiligen | Altijd |
| `remember_web_…` | Ingelogd blijven op dit apparaat | Alleen als je bij het inloggen "onthoud mij" aanvinkt |

## 4. Waarom er geen cookiebanner is

> [!SUMMARY]
> Voor cookies die strikt nodig zijn om de dienst te leveren, is geen toestemming nodig.

De wet vraagt toestemming voor cookies, behalve als een cookie strikt noodzakelijk is om de dienst te leveren die je zelf vraagt. Alle cookies hierboven zijn van dat soort. We gebruiken geen analyse-, tracking- of advertentiecookies, en geen diensten van derden die cookies plaatsen. Daarom vragen we geen toestemming en tonen we geen banner.

## 5. Vragen

Vragen over cookies of privacy: {company.privacy_email}. Meer over hoe wij met persoonsgegevens omgaan, lees je in de [privacyverklaring](/nl/privacy).

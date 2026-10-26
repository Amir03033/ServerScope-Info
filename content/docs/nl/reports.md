---
order: 2
category: incidents
icon: chart
description: Beschikbaarheid, responstijden, MTTA en MTTR per periode voor je domeinen en servers, hoe die worden berekend en de CSV-export.
---
# Rapporten

[Rapporten]({app_url}/reports) laat zien hoe beschikbaar je domeinen en servers in een periode waren, en hoe snel incidenten werden opgepakt en opgelost.

> [!IMPORTANT]
> Rapporten tonen **berekende** beschikbaarheid op basis van de eigen monitoringgegevens van ServerScope. Het is geen contractuele SLA-verklaring.

## Een periode kiezen

| Veld | Mogelijkheden |
|---|---|
| Periode | Afgelopen 7, 30 (standaard) of 90 dagen, deze maand of een eigen periode |
| Van / Tot | Voor een eigen periode; maximaal een jaar |
| Doel (%) | Het beschikbaarheidsdoel om mee te vergelijken, 90–100. De standaard stelt de beheerder van de omgeving in (99,9 tenzij aangepast). |

## Wat het rapport toont

Bovenaan:

| Cijfer | Betekenis |
|---|---|
| Gemiddelde uptime | Gemiddelde beschikbaarheid over alle domeinen en servers, vergeleken met het doel |
| Incidenten | Incidenten geopend in de periode, met het aantal kritieke |
| MTTA | Gemiddelde tijd tot bevestigen: van openen tot de eerste bevestiging |
| MTTR | Gemiddelde tijd tot oplossen: van openen tot oplossing |

Daaronder een tabel per **domein** en per **server** met uptime, dekking, downtime, het aantal incidenten en of het doel is gehaald. Domeinen tonen ook de gemiddelde responstijd.

## Hoe het wordt berekend

**Domeinen**

- Uptime = geslaagde HTTP-controles ÷ uitgevoerde controles.
- Dekking = uitgevoerde controles ÷ verwachte controles voor het interval.
- Waargenomen downtime = mislukte controles × het controle-interval.
- Ontbrekende controles tellen **nooit** als geslaagd; een lage dekking betekent dat het uptimecijfer minder zegt.

**Servers**

- Downtime = de tijd waarin er een *agent offline*-incident liep in de periode.
- Dekking = ontvangen metingen ÷ verwachte metingen (één per minuut) sinds de server is toegevoegd.
- Tijdens een [onderhoudsvenster](security-account) worden geen incidenten geopend, dus gepland onderhoud telt niet als downtime.

Is er voor een onderdeel geen data, dan toont het rapport *geen data* in plaats van een oordeel.

## Exporteren naar CSV

Klik op **CSV exporteren** om hetzelfde rapport te downloaden als `serverscope-beschikbaarheid-<van>-<tot>.csv`. De kolommen:

| Kolom | Inhoud |
|---|---|
| `type` | `domein` of `server` |
| `naam` | Naam |
| `uptime_pct` | Uptime in procenten |
| `dekking_pct` | Dekking in procenten |
| `downtime_min` | Downtime in minuten |
| `gem_responstijd_ms` | Gemiddelde responstijd (alleen domeinen) |
| `incidenten` | Aantal incidenten |
| `controles_of_metingen` | Aantal controles (domeinen) of metingen (servers) |
| `haalt_doel` | `ja` / `nee`, leeg zonder data |

Namen die beginnen met `=`, `+`, `-`, `@` of een tab krijgen een `'` ervoor, zodat spreadsheetprogramma's ze niet als formule uitvoeren.

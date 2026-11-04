---
order: 8
icon: chart
category: monitoring
feature: reports
description: Rapporten over beschikbaarheid en incidenten met doelen, MTTD, MTTA en MTTR, CSV-exports en afdrukken, plus de systeemstatus van ServerScope zelf.
---
# Rapporten en systeemstatus

## Rapporten

Open [Rapporten]({app_url}/reports) (zijbalkgroep **Rapportage**).

1. Kies een periode: de laatste 7, 30 of 90 dagen, deze maand of een eigen periode.
2. Vul eventueel een doel in procenten in, bijvoorbeeld `99.9`.
3. Toon het rapport.

Het rapport bevat:

- **Beschikbaarheid** per server, domein en websitepagina, met uitvaltijd, responstijd (p95 bij pagina's) en het aantal incidenten.
- **Doelen**: het eigen beschikbaarheidsdoel van een onderdeel (ingesteld bij de pagina, website, server of het domein) gaat voor het doel op de rapportpagina.
- **Incidentcijfers**: MTTD (tijd tot detectie), MTTA (tijd tot bevestiging), MTTR (tijd tot oplossing) en de totale duur van incidenten.
- **Incidenten naar ernst en bron**.
- **Terugkerende problemen**: problemen die in de periode vaker dan één keer voorkwamen.
- **Monitoringdekking**: hoeveel van de periode echt is bewaakt.

### Zo worden de cijfers berekend

| Cijfer | Berekening |
|---|---|
| Uptime (domeinen, pagina's) | Geslaagde controles ÷ uitgevoerde controles. Een *verminderde* (trage) controle telt als beschikbaar. |
| Uitvaltijd (servers) | De tijd van *Agent offline*-incidenten. |
| Dekking | Uitgevoerde controles of ontvangen metingen ÷ verwachte. Perioden zonder controles (gepauzeerd, scheduler stil) verlagen de dekking en tellen **nooit** als beschikbaar of onbeschikbaar. |
| MTTD | Eerste mislukte controle → incident geopend. Handmatige incidenten tellen niet mee. |
| MTTA / MTTR | Incident geopend → eerste bevestiging / oplossing. |
| Totale incidentduur | Alleen hoofdincidenten, en alleen het deel binnen de periode. |

> [!NOTE]
> Controles lopen door tijdens onderhoud en tellen gewoon mee. Voor servers wordt tijdens onderhoud geen *Agent offline*-incident geopend, dus die tijd is geen uitvaltijd. De cijfers komen uit de eigen metingen van ServerScope; het is geen contractuele SLA.

### Exporteren

- **Beschikbaarheid als CSV**: één rij per server, domein en pagina.
- **Incidenten als CSV**: één rij per incident met de detectie-, bevestigings- en oplostijd en het hoofdincident.
- **Afdrukken / pdf**: een afdrukbare pagina; gebruik *Opslaan als pdf* in je browser.

## Systeemstatus

Monitoring werkt alleen als ServerScope zelf werkt. De pagina **Beheer → Systeemstatus** toont per onderdeel of het in orde is, aandacht nodig heeft of een probleem heeft, met uitleg:

| Onderdeel | Wat wordt gecontroleerd |
|---|---|
| Scheduler | Of de geplande taken elke minuut draaien |
| Wachtrij | Of de wachtrij het bijhoudt |
| Controles op tijd | Of domein-, pagina- en servicecontroles te laat zijn |
| Meldingen en webhooks | Of meldingen en webhooks worden afgeleverd |
| Paginaontdekking | Of ontdekkingen worden afgerond |

De pagina wordt elke minuut opnieuw beoordeeld. Host je ServerScope zelf, zet dan `SERVERSCOPE_SCHEDULER_PING_URL` op de ping-URL van een [geplande taak](scheduled-jobs): je krijgt dan een incident zodra de scheduler stopt.

## Gegevens bewaren

Hoe lang de geschiedenis wordt bewaard, hangt af van je plan (Free: 14 dagen). Logregels worden 14 dagen bewaard. Rapporten kunnen alleen perioden beslaan waarvan de gegevens nog bestaan.

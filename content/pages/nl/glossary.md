---
title: Begrippenlijst
description: De vaste begrippen van ServerScope uitgelegd, van agent en koppeltoken tot incident, alertregel, statuspagina en team.
summary: In deze lijst staan de begrippen die ServerScope en de documentatie gebruiken, met steeds dezelfde betekenis. Elke uitleg komt uit de documentatie en verwijst ernaar.
---
# Begrippenlijst

## Agent
Een klein programma op je server dat elke 60 seconden metingen verzamelt (CPU, load, geheugen, swap, schijven, netwerk, processen en uptime) en via HTTPS naar ServerScope stuurt. De agent voert geen commando's uit en luistert niet op een poort. Zie [Servermonitoring](/nl/docs/server-monitoring).

## Bash-agent
Een variant van de agent voor hosts en containers zonder Python of systemd. Hij stuurt dezelfde gegevens en gebruikt alleen `/proc`, `df`, `pgrep` en `curl`. Zie [De Bash-agent](/nl/docs/bash-agent).

## Koppeltoken
Een eenmalig token (begint met `sse_`) waarmee het installatiescript een server koppelt. Het is 60 minuten geldig, werkt één keer en kan worden ingetrokken. ServerScope bewaart alleen een hash. Zie [Een server koppelen met één commando](/nl/docs/connect-server).

## Agenttoken
Het token waarmee de agent van één server zich bij ServerScope meldt. Het wordt één keer getoond; ServerScope bewaart alleen een hash. Je kunt het roteren of intrekken op de serverpagina.

## API-token
Een persoonlijk token (begint met `ssk_`) voor de REST API, met de bevoegdheden `read` en eventueel `write`. Het wordt één keer getoond en kan verlopen en worden ingetrokken. Zie [API en webhooks](/nl/docs/api-webhooks).

## Alertregel
Een regel die een incident aanmaakt als een meting te lang boven of onder een drempel blijft, bijvoorbeeld CPU-gebruik, RAM-gebruik, schijfgebruik per mountpoint, of *Agent offline*. Zie [Alerts en incidenten](/nl/docs/alerts-incidents).

## Incident
Een probleem dat ServerScope heeft gevonden, met een ernst (info, waarschuwing of kritiek), een status (open, bevestigd, in onderzoek, opgelost of gesloten) en een tijdlijn. Herhaalde mislukkingen werken hetzelfde incident bij.

## Faaldrempel en hersteldrempel
Het aantal mislukte controles op rij voordat er een incident ontstaat (faaldrempel), en het aantal geslaagde controles op rij voordat een storing als hersteld geldt (hersteldrempel). Zie [Domeinmonitoring](/nl/docs/domain-monitoring).

## Escalatie
Een incident wordt geëscaleerd als het te lang onbevestigd blijft of als een volgende drempel wordt gepasseerd, zoals een lager waarschuwingsmoment voor een vervaldatum.

## Onderhoudsvenster
Een periode waarin voor een server of domein geen alerts of incidenten worden aangemaakt, bijvoorbeeld tijdens gepland werk.

## Domeinregistratie (RDAP)
De vervaldatum van een domeinnaam bij de registry, opgehaald via RDAP. Niet elke registry publiceert die datum; dan kan ServerScope niet waarschuwen voor het verlopen van de registratie.

## Statuspagina
Een pagina die per onderdeel de status en eventueel de beschikbaarheid per dag over 90 dagen toont, zonder dat bezoekers een account nodig hebben. Hostnames, IP-adressen en URL's worden nooit getoond. Zie [Publieke statuspagina's](/nl/docs/status-pages).

## Webhook
Een bericht dat ServerScope bij een gebeurtenis naar je eigen systeem stuurt, zoals `incident.opened` of `dns.changed`. Webhooks zijn ondertekend, zodat je kunt controleren dat ze van ServerScope komen.

## Team
De eenheid waar alles bij hoort: servers, domeinen, websites, alertregels, incidenten, webhooks, statuspagina's, API-tokens en het auditlog. Elk team ziet alleen zijn eigen gegevens. Zie [Teams en rollen](/nl/docs/teams-and-roles).

## Rol
Wat je binnen een team mag: **lid** (gegevens bekijken), **beheerder** (ook de configuratie beheren) of **eigenaar**. Je rol geldt per team.

## Auditlog
Het logboek van beheeracties binnen een team, zoals het aanmaken en intrekken van tokens. Geheimen worden er niet in opgeslagen.

## Tweestapsverificatie
Inloggen met een wachtwoord plus een code uit een authenticator-app (TOTP), met herstelcodes als reserve. Zie [Beveiliging en accountbeheer](/nl/docs/security-account).

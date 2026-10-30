---
title: Een statuspagina maken
seo_title: Statuspagina maken voor je servers en websites — ServerScope
description: Laat klanten of collega's zien of je diensten werken, zonder account. Publiek of privé, met beschikbaarheid per dag over 90 dagen.
summary: Met ServerScope maak je een statuspagina die per onderdeel de status toont en, als je wilt, de beschikbaarheid per dag over de laatste 90 dagen. Bezoekers hebben geen account nodig. Je kiest zelf de namen die verschijnen; hostnames, IP-adressen en URL's worden nooit getoond.
---
# Een statuspagina maken

## Wat bezoekers zien

- Per onderdeel de **status**, afgeleid van de metingen en controles van ServerScope.
- Als je dat aanzet: de **beschikbaarheid per dag** over de laatste 90 dagen.
- Een onderdeel in een onderhoudsvenster wordt als **Onderhoud** getoond.
- Incidenttitels alleen als je **Incidenttitels tonen** aanzet; anders een algemene omschrijving.

Hostnames, IP-adressen, URL's en interne notities worden **nooit** getoond. Alleen de publieke namen die je zelf kiest.

## Een pagina maken

1. Open in ServerScope **Beheer → Statuspagina's** en klik op **Statuspagina maken**.
2. Vul een naam, een URL-deel (slug), eventueel een omschrijving en een accentkleur in.
3. Kies of de pagina **publiek** is en of de beschikbaarheid per dag zichtbaar is.
4. Voeg tot 50 **onderdelen** toe: servers en domeinen, elk met een publieke naam.

De pagina staat daarna op `/status/<slug>` van je ServerScope-omgeving. Er is ook een JSON-versie op `/status/<slug>.json`, bijvoorbeeld voor een eigen dashboard.

## Publiek of privé

Een **privé**-pagina is alleen zichtbaar voor wie is ingelogd op ServerScope. Gebruik dat voor een interne statuspagina voor je team.

Lees alles in [Publieke statuspagina's](/nl/docs/status-pages).

## Veelgestelde vragen

### Is de beschikbaarheid een SLA?
Nee. De beschikbaarheid is berekend uit de metingen en controles van ServerScope; het is geen contractuele SLA.

### Hoeveel bezoekers kan de pagina aan?
De pagina en de JSON-versie zijn beperkt tot 60 verzoeken per minuut per bezoeker.

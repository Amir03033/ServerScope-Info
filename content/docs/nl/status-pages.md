---
order: 3
category: integrations
icon: status
description: Een publieke statuspagina maken voor je servers en domeinen, instellen wat er zichtbaar is, en de pagina delen.
---
# Publieke statuspagina's

Met een statuspagina laat je klanten of collega's zien of je diensten werken, zonder dat ze een account nodig hebben. De pagina toont per onderdeel de afgeleide status en, als je dat wilt, de beschikbaarheid per dag over de laatste 90 dagen.

Statuspagina's beheren kan alleen een **beheerder**.

## Een statuspagina maken

1. Open [Beheer → Statuspagina's]({app_url}/status-pages) en klik op **Statuspagina maken**.
2. Vul in:
   - **Naam**, bijvoorbeeld `Status Acme`
   - **URL-deel** (de *slug*): het laatste deel van de URL, alleen kleine letters, cijfers en streepjes (max. 60 tekens), bijvoorbeeld `acme`
   - **Omschrijving** (optioneel)
   - **Accentkleur** in de vorm `#38bdf8`
3. Kies de zichtbaarheid:
   - **Publiek (zonder inloggen zichtbaar)**
   - **Beschikbaarheid per dag tonen (90 dagen)**
   - **Incidenttitels tonen**: anders verschijnt een algemene omschrijving
4. Voeg onder **Onderdelen** met **Onderdeel toevoegen** de servers en domeinen toe die je wilt tonen. Kies per onderdeel de **Monitor** en een **Publieke naam** (bijvoorbeeld `Website` in plaats van `web-01`) en eventueel een korte omschrijving. De volgorde bepaalt de weergave (maximaal 50 onderdelen).
5. Klik op **Statuspagina maken**.

> [!IMPORTANT]
> Op de pagina verschijnen alleen de namen die je zelf kiest, de status en de beschikbaarheid. Hostnames, IP-adressen, URL's en interne notities worden **nooit** getoond. Incidenttitels kunnen wel interne namen bevatten; zet **Incidenttitels tonen** alleen aan als dat geen probleem is.

## De pagina delen

De statuspagina staat op:

```text
{app_url}/status/<slug>
```

Er is ook een JSON-versie, bijvoorbeeld voor een eigen dashboard of een monitor:

```text
{app_url}/status/<slug>.json
```

Beide adressen zijn beperkt tot 60 verzoeken per minuut per bezoeker.

## Privé-pagina's

Staat **Publiek** uit, dan moet een bezoeker ingelogd zijn op ServerScope; anders wordt de bezoeker naar de inlogpagina gestuurd. Gebruik dit voor een interne statuspagina voor je team.

## Status en onderhoud

- Een onderdeel in een **onderhoudsvenster** wordt als *Onderhoud* getoond.
- De beschikbaarheid is berekend uit de metingen en controles van ServerScope; het is geen contractuele SLA.

## Problemen oplossen

| Wat je ziet | Oplossing |
|---|---|
| Foutmelding bij het URL-deel | Kies een andere slug; elke slug is uniek. `create`, `admin` en `api` zijn niet toegestaan. |
| Bezoekers zien de inlogpagina | De pagina staat niet op **Publiek**. |
| Een onderdeel ontbreekt | Lege rijen worden genegeerd; controleer of je een monitor en publieke naam hebt gekozen. |

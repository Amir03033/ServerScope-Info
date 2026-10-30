---
title: Uptime monitoring voor je website
seo_title: Uptime monitoring met HTTP-controles en drempels — ServerScope
description: Controleer of je website bereikbaar is, standaard elke 5 minuten, met verwachte statuscodes, verwachte inhoud en drempels tegen valse meldingen.
summary: ServerScope controleert of een website antwoordt met een verwachte statuscode, standaard elke 5 minuten (instelbaar van 1 tot 1440 minuten). Pas na een aantal mislukte controles op rij ontstaat een kritiek incident, en na een aantal geslaagde controles op rij wordt het automatisch opgelost.
---
# Uptime monitoring voor je website

## Hoe de HTTP-controle werkt

Voor elk domein controleert ServerScope of de URL antwoordt met een **verwachte statuscode**, en eventueel of er een **verwachte tekst** op de pagina staat.

| Instelling | Standaard | Betekenis |
|---|---|---|
| Interval | 5 minuten | Hoe vaak de controle draait (1–1440 minuten) |
| Time-out | 10 seconden | Maximale wachttijd per verzoek (2–30) |
| Faaldrempel | 3 | Mislukte controles op rij voordat er een incident ontstaat |
| Hersteldrempel | 2 | Geslaagde controles op rij voordat de storing als hersteld geldt |
| Verwachte statuscodes | `200-399` | Bijvoorbeeld `200` of `200-299,301` |
| Verwachte inhoud | – | Optionele tekst die op de pagina moet staan (hoofdlettergevoelig) |
| Redirects volgen | aan | Maximaal 5 redirects |

## Wat er gebeurt bij een storing

- Na het aantal mislukte controles uit de **faaldrempel** ontstaat een **kritiek** incident: "… is onbereikbaar".
- Herhaalde mislukkingen werken hetzelfde incident bij; er komt geen nieuw incident per controle.
- Na de **hersteldrempel** wordt het incident automatisch opgelost. Je kunt ook een herstelmelding krijgen.
- Tijdens een **onderhoudsvenster** lopen de controles door, maar worden er geen incidenten aangemaakt.

Meldingen gaan per e-mail, in de app, naar Discord of naar een webhook. Met een [statuspagina](/nl/statuspagina) laat je bezoekers de beschikbaarheid zien.

Lees alles in [Domeinmonitoring](/nl/docs/domain-monitoring) en [Alerts en incidenten](/nl/docs/alerts-incidents).

## Veelgestelde vragen

### Kan ik direct een controle starten?
Ja, met **Nu controleren** op de domeinpagina, maximaal één keer per minuut.

### Hoe voorkom ik meldingen bij een korte hapering?
Met de faaldrempel: standaard ontstaat pas een incident na 3 mislukte controles op rij. Je kunt die drempel per domein instellen van 1 tot 10.

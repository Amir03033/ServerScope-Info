---
order: 20
icon: users
category: account
feature: teams
description: Wat een team is, welke rol (eigenaar, beheerder of lid) wat mag, hoe je van team wisselt en waarom elk team alleen zijn eigen gegevens ziet.
---
# Teams en rollen

In ServerScope hoort alles bij een **team**: servers, domeinen, websites, alertregels, incidenten, webhooks, statuspagina's, API-tokens en het auditlog. Elk team ziet alleen zijn eigen gegevens.

## Rollen

Je rol geldt per team. In het ene team kun je beheerder zijn en in een ander team lid.

| | Lid | Beheerder | Eigenaar |
|---|---|---|---|
| Monitoringgegevens van het team bekijken | ✓ | ✓ | ✓ |
| Configuratie beheren (servers, domeinen, alertregels, webhooks, statuspagina's) | | ✓ | ✓ |
| Leden beheren onder **Gebruikers** | | ✓ | ✓ |
| De rol van een eigenaar wijzigen of een eigenaar verwijderen | | | ✓ |

Een paar vaste regels:

- Een team heeft altijd minstens één **eigenaar** en minstens één **beheerder**. De laatste kan niet worden verwijderd of teruggezet.
- Je kunt je eigen beheerdersrechten niet intrekken.
- Hoort een account ook bij andere teams, dan verlaat het bij verwijderen alleen dit team.

## Van team wisselen

Ben je lid van meer dan één team, dan staat onder in de zijbalk een keuzelijst **Team**. Kies een ander team; je komt op het dashboard van dat team. Het wisselen wordt vastgelegd in het auditlog van dat team.

Ben je lid van één team, dan zie je op die plek alleen de naam van je team.

## Elk team ziet alleen zijn eigen gegevens

- Gegevens van een ander team bestaan voor jou niet: een link naar een server of incident van een ander team geeft "niet gevonden".
- Een **API-token** hoort bij één team. Verlaat de eigenaar van het token dat team, dan werkt het token niet meer.
- Een **agent** stuurt zijn metingen naar de server waar hij bij hoort, en die server hoort bij één team.

> [!NOTE]
> Bestaande gegevens van vóór de invoering van teams staan in één standaardteam. Er is niets verloren gegaan.

## Problemen oplossen

| Wat je ziet | Oorzaak en oplossing |
|---|---|
| Ik zie geen keuzelijst **Team** | Je bent lid van één team. |
| Een link naar een server geeft "niet gevonden" | De server hoort bij een ander team. Wissel van team of vraag een beheerder van dat team om toegang. |
| Een API-koppeling geeft opeens 401 | De eigenaar van het token heeft het team verlaten, of het token is ingetrokken of verlopen. Maak een nieuw token aan. |

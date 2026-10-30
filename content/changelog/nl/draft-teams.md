---
date: 2026-10-29
title: Teams en rollen
status: draft
feature: teams
source: Amir03033/ServerScope docs/AUDIT.md §5 (fase 1)
---
ServerScope werkt nu met **teams**. Elke klant is een team, en een team ziet
alleen zijn eigen servers, domeinen, incidenten en instellingen.

### Nieuw

- **Teams:** al je servers, domeinen, websites, alertregels, incidenten,
  webhooks, statuspagina's, API-tokens en het auditlog horen bij één team.
  Gegevens van een ander team zijn nooit zichtbaar.
- **Rollen per team:** eigenaar, beheerder of lid. Je rol geldt alleen voor
  het team waarin je hem hebt.
- **Teams wisselen:** ben je lid van meerdere teams, dan wissel je met de
  teamwisselaar van team.
- **Leden per team:** een overzicht van wie er in je team zit.

### Goed om te weten

- Alle bestaande gegevens staan in één standaardteam. Er gaat niets verloren.
- Een API-token hoort bij één team en werkt niet meer als de eigenaar het
  team verlaat.
- Bestaande agents blijven werken zoals ze waren.

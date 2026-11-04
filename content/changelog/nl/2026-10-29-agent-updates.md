---
date: 2026-10-29
title: Agent-updates met één klik, veilig ondertekend (agent 0.3.0)
status: published
feature: agent-updates
source: Amir03033/ServerScope PR #6–#7, agent/README.md, docs/RELEASE_KEYS.md
---
De agent werk je nu bij vanuit het dashboard. Elke release is ondertekend,
zodat een server alleen code installeert die echt van ServerScope komt.

### Nieuw

- Per server zie je de geïnstalleerde en de nieuwste agentversie. Met
  **Bijwerken naar …** op een server, of **Alles bijwerken naar …** in de
  serverlijst, werk je bij.
- **Automatisch bijwerken** kan per server. Het staat standaard uit.
- Een klein updateprogramma op de server installeert alleen releases die met
  de offline releasesleutel van ServerScope zijn ondertekend. Mislukt een
  update, dan zet het de vorige versie terug.

### Beveiliging

- Releases zijn ondertekend met Ed25519. De server controleert de
  handtekening, het manifest, de SHA-256 en of de versie nieuwer is dan wat
  er ooit op de server stond. Terugzetten naar een oudere versie kan dus niet
  van buitenaf.
- Zelfs een gehackte ServerScope-server kan op bestaande servers geen eigen
  code installeren.
- Er is een lokale noodknop:
  `sudo bash serverscope-install.sh --disable-updates`.

### Goed om te weten

- Bestaande installaties (agent 0.2.0) blijven werken. Om updates te krijgen
  draai je het installatiecommando één keer opnieuw.
- Vingerafdruk van de releasesleutel:
  `511215f3eccc6640c9ee8d42443d13501e2f291e03aa566e21e1df5efa0a1488`.

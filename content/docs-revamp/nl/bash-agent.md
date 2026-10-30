---
order: 30
icon: terminal
category: servers
feature: bash-agent
description: De Bash-agent voor hosts en containers zonder Python of systemd, naast de handmatige installatie van de gewone agent.
---
# De Bash-agent

Voor hosts en containers **zonder Python of systemd** is er een Bash-agent. Hij stuurt dezelfde gegevens als de gewone agent en gebruikt alleen `/proc`, `df`, `pgrep` en `curl`.

> [!NOTE]
> Heb je wel Python en systemd? Gebruik dan de gewone agent: met één commando, of volgens de [handmatige installatie](server-monitoring). Beide blijven bestaan.

## Installeren

1. Registreer de server in ServerScope met **Servers → Server toevoegen → Handmatig registreren** en kopieer het agenttoken. Het wordt één keer getoond.
2. Zet het script `agent.sh` (uit de map `agent/bash` van de ServerScope-agent) in een map **buiten de webroot**.
3. Maak in dezelfde map een bestand `.env`:

   ```sh
   SERVERSCOPE_URL={app_url}
   SERVERSCOPE_TOKEN=<token>
   SERVERSCOPE_INTERVAL=60
   ```

   Optioneel: `SERVERSCOPE_SERVICES` met **procesnamen** die de agent moet controleren.

4. Controleer eerst wat de agent zou versturen, zonder iets te versturen:

   ```sh
   SERVERSCOPE_DRY_RUN=1 bash agent.sh
   ```

5. Start de agent:

   ```sh
   nohup bash agent.sh >> agent.log 2>&1 &
   ```

## Goed om te weten

- In een container meet de Bash-agent **die container**, niet de host.
- De updater voor agent-updates werkt **niet** met de Bash-agent; die wordt nooit automatisch bijgewerkt.
- Houd de klok gelijk: metingen die meer dan 30 seconden in de toekomst of meer dan 10 minuten in het verleden liggen, worden geweigerd.

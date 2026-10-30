---
order: 30
icon: terminal
category: servers
feature: bash-agent
description: The Bash agent for hosts and containers without Python or systemd, next to the manual installation of the regular agent.
---
# The Bash agent

For hosts and containers **without Python or systemd** there is a Bash agent. It sends the same data as the regular agent and only uses `/proc`, `df`, `pgrep` and `curl`.

> [!NOTE]
> Do you have Python and systemd? Then use the regular agent: with one command, or following the [manual installation](server-monitoring). Both remain available.

## Installing

1. Register the server in ServerScope with **Servers → Server toevoegen → Handmatig registreren** and copy the agent token. It is shown once.
2. Put the script `agent.sh` (from the `agent/bash` folder of the ServerScope agent) in a directory **outside the web root**.
3. Create a `.env` file in the same directory:

   ```sh
   SERVERSCOPE_URL={app_url}
   SERVERSCOPE_TOKEN=<token>
   SERVERSCOPE_INTERVAL=60
   ```

   Optional: `SERVERSCOPE_SERVICES` with **process names** the agent should check.

4. First check what the agent would send, without sending anything:

   ```sh
   SERVERSCOPE_DRY_RUN=1 bash agent.sh
   ```

5. Start the agent:

   ```sh
   nohup bash agent.sh >> agent.log 2>&1 &
   ```

## Good to know

- Inside a container the Bash agent measures **that container**, not the host.
- The updater for agent updates does **not** work with the Bash agent; it is never updated automatically.
- Keep the clock in sync: samples more than 30 seconds in the future or more than 10 minutes in the past are rejected.

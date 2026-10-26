---
order: 2
icon: server
description: Add a server, install and configure the agent, verify that data arrives and fix common installation problems.
---
# Server monitoring

A server sends its own measurements to ServerScope with the **ServerScope agent**: a small Python program that collects CPU, load, memory, swap, disks, network counters and uptime every 60 seconds and sends them over HTTPS. The agent runs no commands and needs no open port; only outbound traffic to ServerScope on port 443.

For this guide you need an **administrator account** and root access (sudo) on the server.

## Requirements

- Ubuntu or Debian with `systemd`
- Python 3 with `venv` (`sudo apt install python3-venv`)
- Outbound HTTPS to `{app_url}`
- A synchronised clock (NTP)
- The agent files: the `agent/` directory of the ServerScope source code (`serverscope_agent/`, `requirements.txt` and `serverscope-agent.service`)

> [!NOTE]
> There is no installer script or package (yet). You copy the agent files to the server yourself, as described below.

## 1. Add the server

1. Open [Servers → Server toevoegen]({app_url}/servers/create) (Add server).
2. Fill in:
   - **Servernaam** (server name, required, max. 100 characters), for example `Production VPS`
   - **Hostname** (required), for example `server.example.com`
   - **IP-adres** and **Besturingssysteem** (IP address and operating system, optional, for reference)
   - **Omgeving** (environment): Productie, Staging, Ontwikkeling, Test or Overig
   - **Naam agentcredential** (credential name, default `Primary agent`)
   - **Notities** (notes, optional)
3. Click **Server registreren** (Register server).
4. Copy the **agent token** that is now shown.

> [!WARNING]
> The agent token is shown **once**. ServerScope only stores a hash. If you lose it, generate a new token on the server page (see *Rotating the token*).

The server now shows **In afwachting** (pending) until the first measurement arrives.

## 2. Install the agent

Run these steps on the server, in the directory where you placed the agent files.

1. Create a system user and the installation directory:

   ```sh
   sudo useradd --system --home /opt/serverscope-agent --shell /usr/sbin/nologin serverscope
   sudo mkdir -p /opt/serverscope-agent
   sudo cp -r serverscope_agent requirements.txt /opt/serverscope-agent/
   ```

2. Install the Python dependencies in a dedicated virtual environment:

   ```sh
   cd /opt/serverscope-agent
   sudo python3 -m venv .venv
   sudo .venv/bin/pip install -r requirements.txt
   ```

3. Create the configuration file. Replace `<token>` with the token from step 1:

   ```sh
   sudo tee /opt/serverscope-agent/.env >/dev/null <<'ENV'
   SERVERSCOPE_URL={app_url}
   SERVERSCOPE_TOKEN=<token>
   SERVERSCOPE_INTERVAL=60
   ENV
   ```

4. Protect the file so only root and the agent can read the token:

   ```sh
   sudo chown -R root:serverscope /opt/serverscope-agent
   sudo chmod 640 /opt/serverscope-agent/.env
   ```

5. Install and start the systemd service:

   ```sh
   sudo cp serverscope-agent.service /etc/systemd/system/
   sudo systemctl daemon-reload
   sudo systemctl enable --now serverscope-agent
   ```

6. Keep the clock in sync:

   ```sh
   sudo timedatectl set-ntp true
   ```

The bundled service runs as the `serverscope` user with hardening such as `NoNewPrivileges`, `ProtectSystem=strict` and a read-only installation directory.

## 3. Configuration

The agent reads `/opt/serverscope-agent/.env`:

| Variable | Required | Meaning |
|---|---|---|
| `SERVERSCOPE_URL` | yes | Base URL of ServerScope. Must start with `https://`. |
| `SERVERSCOPE_TOKEN` | yes | The agent token of this server. |
| `SERVERSCOPE_INTERVAL` | no | Seconds between measurements. Default `60`, minimum `10`. |

Restart after a change: `sudo systemctl restart serverscope-agent`.

## 4. Verify that data arrives

1. Follow the agent log:

   ```sh
   journalctl -u serverscope-agent -f
   ```

   You see `ServerScope agent started.` and then `Metrics submitted successfully.` every minute.

2. Test the token independently of the agent (optional):

   ```sh
   curl -s -H "Authorization: Bearer <token>" {app_url}/agent/ping
   ```

   A valid token returns `{"authenticated":true,"server_id":…,"server_name":"…"}`.

3. Open the server under [Servers]({app_url}/servers). Its status becomes **Online**, *Laatste meting* (last measurement) keeps updating and the CPU, memory, disk and network charts fill up.

## Troubleshooting

| What you see | Cause and fix |
|---|---|
| `Metrics submission failed.` in the log | The agent cannot reach ServerScope or the token is rejected. Test with the `curl` command above and check firewalls/proxies for outbound traffic on port 443. |
| `ping` answers *Ongeldige of niet meer geldige agentcredential.* (HTTP 401) | The token is wrong, revoked or rotated. Generate a new token and put it in `.env`. |
| `SERVERSCOPE_URL must be a valid HTTPS base URL.` | Use `{app_url}` with `https://` and without a path. |
| `SERVERSCOPE_INTERVAL must be at least 10 seconds.` | Choose an interval of 10 seconds or more. |
| The service does not start, or `Permission denied` on `.env` | Check owner and mode (`root:serverscope`, `640`) and `systemctl status serverscope-agent`. |
| The server stays **In afwachting** or turns **Offline** while the agent runs (and the log shows `Metrics submission failed.`) | Check the clock: measurements more than 30 seconds in the future or more than 10 minutes in the past are rejected. |

> [!IMPORTANT]
> A server that sends nothing for 2 minutes is shown as **Offline**. The default alert rule *Agent offline* opens a **critical** incident after 5 minutes of silence. See [Alerts and incidents](alerts-incidents).

## Rotating the token

On the server page, under **Agentcredentials**:

1. Click **Token roteren** (Rotate token). All active tokens of this server are revoked and one new token is shown.
2. Put the new token in `/opt/serverscope-agent/.env`.
3. Restart: `sudo systemctl restart serverscope-agent`.

The old token stops working immediately. You can also create an **Extra token** (for example during a migration) and revoke individual tokens.

## Maintenance

Planning work on a server? Start a **maintenance window** on the server page (5 minutes to 7 days) or set a start and end time under **Bewerken** (Edit). During maintenance no alerts or incidents are created for that server.

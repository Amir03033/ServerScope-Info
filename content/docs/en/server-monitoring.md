---
order: 2
icon: server
description: Install the agent by hand, configure it (services, deployments, logs), verify that data arrives and fix common problems.
---
# Server monitoring

A server sends its own measurements to ServerScope with the **ServerScope agent**: a small Python program that collects CPU, load, memory, swap, disks, disk activity, network traffic and errors, the number of processes and uptime every 60 seconds and sends them over HTTPS. It can also report [services](service-checks), notice [deployments](deployments) and follow [log files](logs). The agent only reads data, runs no remote commands and needs no open port; only outbound traffic to ServerScope on port 443.

> [!TIP]
> The quickest way is the install script: see [Connect a server with one command](connect-server). It also sets up [signed agent updates](agent-updates). This guide describes the **manual installation** and the configuration that applies to both.

For this guide you need to be a team owner or administrator, and root access (sudo) on the server.

## Requirements

- Ubuntu or Debian with `systemd`
- Python 3 with `venv` (`sudo apt install python3-venv`)
- Outbound HTTPS to `{app_url}`
- A synchronised clock (NTP)
- The agent files: the `agent/` directory of the ServerScope source code (`serverscope_agent/`, `requirements.txt` and `serverscope-agent.service`)

> [!NOTE]
> For a manual installation you copy the agent files to the server yourself, as described below. No Python or systemd, for example in a container? Use the [Bash agent](bash-agent).

## 1. Add the server

1. Open [Servers → Add server]({app_url}/servers/add) and choose **Register manually**.
2. Fill in:
   - **Server name** (required, max. 100 characters), for example `Production VPS`
   - **Hostname** (required), for example `server.example.com`
   - **IP address** and **Operating system** (optional, for reference)
   - **Environment**: Production, Staging, Development, Test or Other
   - the name of the agent credential (default `Primary agent`)
   - **Notes** (optional)
3. Click **Register server**.
4. Copy the **agent token** that is now shown.

> [!WARNING]
> The agent token is shown **once**. ServerScope only stores a hash. If you lose it, generate a new token on the server page (see *Rotating the token*).

The server now shows **Pending** until the first measurement arrives.

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
| `SERVERSCOPE_SERVICES` | no | Services to watch, for example `nginx,php8.4-fpm,mysql` (agent 0.2.0+). See [Service checks](service-checks). |
| `SERVERSCOPE_DEPLOY_PATHS` | no | `name=/path,name2=/path2`: git checkouts (or bare repositories) to detect deployments (agent 0.4.0+). See [Deployments](deployments). |
| `SERVERSCOPE_LOG_FILES` | no | `name=/path/file.log,…`: log files to follow (agent 0.4.0+). See [Logs](logs). |

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

3. Open the server under [Servers]({app_url}/servers). Its status becomes **Online**, *Latest measurement* keeps updating and the charts fill up: CPU, memory, swap, disks, disk activity, network traffic and network errors. On the dashboard the server's heartbeat starts beating.

## Server diagnosis

At the top of the server page the **diagnosis** explains in plain words what is going on. ServerScope separates situations that look alike from the outside: a stopped service (the agent still reports), an agent that does not respond (websites on the server still work) and a server that is really unreachable (its websites fail too). It also shows missing data, an outdated agent and the agent's integrity (a known build, an unknown build or not verified).

## Troubleshooting

| What you see | Cause and fix |
|---|---|
| `Metrics submission failed.` in the log | The agent cannot reach ServerScope. Test with the `curl` command above and check firewalls/proxies for outbound traffic on port 443. |
| `Metrics submission failed: HTTP 401, token rejected; …` (or `ping` answers HTTP 401) | The token is wrong, revoked or rotated, or belongs to another ServerScope URL. Generate a new token and put it in `.env`. |
| `Metrics submission failed: HTTP 404, endpoint not found; …` | `SERVERSCOPE_URL` is wrong; use the base URL without a path. |
| `Metrics submission failed: HTTP 422, rejected fields: …` | ServerScope refused part of the sample, usually because the clock is off (see below). The log names the fields, never values or tokens. |
| `Metrics submission failed: HTTP 429, rate limited; …` | Increase `SERVERSCOPE_INTERVAL`. |
| `SERVERSCOPE_URL must be a valid HTTPS base URL.` | Use `{app_url}` with `https://` and without a path. |
| `SERVERSCOPE_INTERVAL must be at least 10 seconds.` | Choose an interval of 10 seconds or more. |
| The service does not start, or `Permission denied` on `.env` | Check owner and mode (`root:serverscope`, `640`) and `systemctl status serverscope-agent`. |
| The server stays **Pending** or turns **Offline** while the agent runs (and the log shows `Metrics submission failed.`) | Check the clock: measurements more than 30 seconds in the future or more than 10 minutes in the past are rejected. |

> [!IMPORTANT]
> A server that sends nothing for 2 minutes is shown as **Offline**. The default alert rule *Agent offline* opens a **critical** incident after 5 minutes of silence. See [Alerts and incidents](alerts-incidents).

## Rotating the token

On the server page, under **Agent credentials**:

1. Click **Rotate token**. All active tokens of this server are revoked and one new token is shown.
2. Put the new token in `/opt/serverscope-agent/.env`.
3. Restart: `sudo systemctl restart serverscope-agent`.

The old token stops working immediately. You can also create an **Extra token** (for example during a migration) and revoke individual tokens.

## Maintenance

Planning work on a server? Use the **maintenance** panel on the server page: start maintenance now for a set time, or choose your own start and end time. During maintenance no new alerts or incidents are opened for the server's resource metrics, agent offline and service checks.

---
order: 20
icon: cpu
category: servers
feature: service-checks
description: Let the agent watch services such as Nginx, MySQL, PHP-FPM and Redis (from version 0.2.0).
---
# Service checks

From agent version **0.2.0** the agent can report the state of services next to the server metrics, such as Nginx, Apache, MySQL, PostgreSQL, Redis, PHP-FPM and other systemd services. You choose which ones; the agent assumes nothing exists.

## Setting it up

In `/opt/serverscope-agent/.env`, list the systemd units you want to watch, separated by commas:

```sh
SERVERSCOPE_SERVICES=nginx,mysql,php8.3-fpm,redis-server
```

Then restart the agent:

```sh
sudo systemctl restart serverscope-agent
```

## How the agent checks

For each service the agent runs:

```sh
systemctl show <unit> --property=LoadState,ActiveState,SubState
```

That is read-only, without a shell, with a 3-second time-out. Without systemd the agent looks for a process with the same name.

The agent reports one of these states per service:

| State | Meaning |
|---|---|
| `running` | The service is running |
| `starting` | The service is starting |
| `stopped` | The service is stopped |
| `failed` | The service failed |
| `not_found` | No service with this name exists |
| `unknown` | The state could not be determined |

## Incidents

ServerScope opens an incident after a service was reported as not healthy the configured number of times in a row. A stopped service is reported separately from an unreachable server or an agent that stopped sending.

Through the API, `GET /servers/{id}/services` returns the latest reported states. While the agent is offline, the status there is `stale`.

> [!NOTE]
> Agent 0.1.0 keeps working. Services and the other new server data need version 0.2.0.

---
title: Monitoring a VPS
seo_title: VPS monitoring with a lightweight agent — ServerScope
description: Monitor CPU, load, memory, disks and network of your VPS with a read-only agent. Connect it with one command on Ubuntu or Debian.
summary: With ServerScope you monitor a VPS through a small agent that measures CPU, load, memory, swap, disks, network, processes and uptime every 60 seconds. You connect the server with one command and a one-time enrollment token. The agent does not run commands and needs no open port.
---
# Monitoring a VPS

## What you see

Every 60 seconds the agent sends over HTTPS:

- CPU, load, memory and swap
- disks and disk activity
- network traffic and network errors
- the number of processes and the uptime
- optionally the state of systemd services you choose, such as Nginx, MySQL, PHP-FPM or Redis

In the dashboard you see each server's status (online, offline or pending), the last measurement and charts for CPU, memory, disks and network.

## Connect with one command

1. Create an **enrollment token** in ServerScope: **Servers → Server toevoegen → Linux → Koppeltoken aanmaken**. It is valid for 60 minutes and works once.
2. Run the install command on the VPS and paste the token when the script asks for it.
3. The script installs the agent, exchanges the enrollment token for an agent token of its own and waits until the first measurement has arrived.

Requirements: Ubuntu 22.04 or later, or Debian 12 or later, on amd64 or arm64, with systemd and sudo. Read the full steps in [Connect a server with one command](/en/docs/connect-server). If your VPS does not meet them, there is a [manual installation](/en/docs/server-monitoring) and a [Bash agent](/en/docs/bash-agent).

## What the agent does not do

- It **never runs commands on behalf of ServerScope**.
- It does not listen on a port: only outgoing HTTPS traffic to ServerScope on port 443.
- It runs as the user `serverscope` without a shell and without write access to its own installation.

See [Security of the agent](/en/docs/agent-security).

## Warnings

- A server that sends nothing for 2 minutes is shown as **offline**. The default alert rule *Agent offline* opens a critical incident after 5 minutes of silence.
- With **alert rules** you get an incident when CPU, RAM or disk usage stays above a threshold for too long.
- During a **maintenance window** no alerts or incidents are created for that server.

See [Alerts and incidents](/en/docs/alerts-incidents).

## Frequently asked questions

### How often does the agent measure?
Every 60 seconds by default. You can change the interval with `SERVERSCOPE_INTERVAL`, with a minimum of 10 seconds.

### Do I need to open a port in the firewall?
No. The agent only makes outgoing HTTPS connections to ServerScope.

### Can I remove the agent again?
Yes, with `sudo bash serverscope-install.sh --uninstall`. That revokes the agent token and removes the service and the installation folder. The server and its history stay in ServerScope.

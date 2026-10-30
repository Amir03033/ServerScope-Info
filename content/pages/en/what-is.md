---
title: What is ServerScope?
seo_title: What is ServerScope? Monitoring for servers, websites and domains
description: ServerScope monitors Linux servers through a read-only agent, and websites and domains through HTTP, SSL, DNS and RDAP checks.
summary: ServerScope is a web application that monitors Linux servers through a small, read-only agent, and websites and domains through HTTP, SSL, DNS and registration checks. Problems become incidents with a timeline, and notifications go out by e-mail, in the app, to Discord or to your own webhook.
---
# What is ServerScope?

## What ServerScope monitors

- **Servers.** A small agent collects CPU, load, memory, swap, disks, network traffic, the number of processes and the uptime every 60 seconds, and sends them to ServerScope over HTTPS. The agent can also report the state of systemd services you choose, such as Nginx, MySQL or PHP-FPM. See [Server monitoring](/en/docs/server-monitoring) and [Service checks](/en/docs/service-checks).
- **Websites and domains.** For each domain you choose which checks are on: HTTP availability, the SSL/TLS certificate, DNS records and the domain registration through RDAP. See [Domain monitoring](/en/docs/domain-monitoring).

## What happens when something goes wrong

- A problem becomes an **incident**, with a severity (info, warning or critical), a status and a timeline. Repeated failures update the same incident.
- Notifications go out by **e-mail**, **in the app**, to **Discord** or to your own **webhook**. Each user sets the minimum severity they are notified about.
- A **status page** shows customers or colleagues whether your services work, without them needing an account.

See [Alerts and incidents](/en/docs/alerts-incidents) and [Public status pages](/en/docs/status-pages).

## How the agent works

- You connect a server with **one command** and a one-time **enrollment token** (Ubuntu 22.04+ or Debian 12+, amd64 or arm64, with systemd). See [Connect a server with one command](/en/docs/connect-server).
- The agent **never runs commands on behalf of ServerScope** and does not listen on a port. It only makes outgoing HTTPS connections.
- Agent updates are **signed**. A server only installs a release with a valid signature from a key that is not on the ServerScope server. See [Agent updates from the dashboard](/en/docs/agent-updates) and [Security of the agent](/en/docs/agent-security).

## Who it is for

For people who run their own Linux servers, websites or domains and want one overview of metrics, outages and expiring certificates or registrations. Everything belongs to a **team**; each team only sees its own data.

## Language

The ServerScope application is in Dutch. The documentation is available in Dutch and English.

## Frequently asked questions

### Does the agent have access to my server?
The agent reads system data and sends it to ServerScope. It does not run commands on behalf of ServerScope and does not listen on a port. It runs as the user `serverscope` without a shell.

### Which operating systems are supported?
The install script supports Ubuntu 22.04 or later and Debian 12 or later, on amd64 or arm64, with systemd. For hosts and containers without Python or systemd there is a Bash agent.

### Does ServerScope have an API?
Yes. There is a REST API (version 1) with personal tokens, and webhooks that send signed events to your own system. See [API and webhooks](/en/docs/api-webhooks).

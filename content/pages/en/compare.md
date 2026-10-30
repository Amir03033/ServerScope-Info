---
title: ServerScope compared
seo_title: Comparing monitoring tools: criteria and the answers for ServerScope
description: Criteria to assess a server and domain monitoring tool, filled in for ServerScope with a source in the documentation for every answer.
summary: This page lists criteria to assess a monitoring tool, and the answers for ServerScope. Every answer links to the documentation. We make no claims about other tools; fill in that column yourself.
---
# ServerScope compared

Comparing tools for server and domain monitoring? Take these questions with you. We only fill in ServerScope, with the source next to it. We do not name other tools: their capabilities change, and we do not want to make claims we cannot verify.

## Servers

| Criterion | ServerScope | Source |
|---|---|---|
| How are servers measured? | With an agent on the server that measures every 60 seconds | [Server monitoring](/en/docs/server-monitoring) |
| Can the service run commands on your server? | No. The agent never runs commands on behalf of ServerScope | [Security of the agent](/en/docs/agent-security) |
| Does the server need an open port? | No, only outgoing HTTPS traffic | [Security of the agent](/en/docs/agent-security) |
| How do you install the agent? | With one command and a one-time enrollment token, or manually | [Connect a server with one command](/en/docs/connect-server) |
| Which systems? | Ubuntu 22.04+ and Debian 12+, amd64 and arm64, with systemd; a Bash agent for systems without Python or systemd | [Connect a server](/en/docs/connect-server), [The Bash agent](/en/docs/bash-agent) |
| Are agent updates signed? | Yes, with Ed25519; the key is not on the ServerScope server | [Agent updates](/en/docs/agent-updates) |
| Can you monitor services such as Nginx or MySQL? | Yes, systemd services you choose (from agent 0.2.0) | [Service checks](/en/docs/service-checks) |

## Websites and domains

| Criterion | ServerScope | Source |
|---|---|---|
| HTTP availability | Yes, with expected status codes, optional expected content, failure and recovery thresholds | [Domain monitoring](/en/docs/domain-monitoring) |
| SSL certificates | Yes: validity and expiry date, with warnings at configurable points | [Domain monitoring](/en/docs/domain-monitoring) |
| DNS changes | Yes: A, AAAA, CNAME, MX, NS and CAA, with history | [Domain monitoring](/en/docs/domain-monitoring) |
| Domain registration expiry | Yes, through RDAP, if the registry publishes an expiry date | [Domain monitoring](/en/docs/domain-monitoring) |

## Incidents and notifications

| Criterion | ServerScope | Source |
|---|---|---|
| Incidents with timeline and status | Yes | [Alerts and incidents](/en/docs/alerts-incidents) |
| Notification channels | E-mail, in the app, Discord and webhooks | [Alerts and incidents](/en/docs/alerts-incidents), [Discord notifications](/en/docs/discord-notifications) |
| Status pages | Yes, public or private, with availability per day over 90 days | [Public status pages](/en/docs/status-pages) |
| API | REST API v1 with tokens (`read`, `write`) and signed webhooks | [API and webhooks](/en/docs/api-webhooks) |

## Account and team

| Criterion | ServerScope | Source |
|---|---|---|
| Teams and roles | Yes: member, admin and owner; each team only sees its own data | [Teams and roles](/en/docs/teams-and-roles) |
| Two-factor authentication | Yes, TOTP with recovery codes | [Security and account management](/en/docs/security-account) |
| Audit log | Yes, without secrets | [Security of the agent](/en/docs/agent-security) |
| Language | The application is in Dutch; the documentation is in Dutch and English | [Getting started](/en/docs/getting-started) |

## What is not on this page

- **Prices.** See the [pricing page](/en/pricing).
- **Uptime percentages or customer numbers.** We do not state them.

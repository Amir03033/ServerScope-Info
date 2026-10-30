---
title: Glossary
description: The fixed terms of ServerScope explained, from agent and enrollment token to incident, alert rule, status page and team.
summary: This list contains the terms that ServerScope and its documentation use, always with the same meaning. Every explanation comes from the documentation and links to it.
---
# Glossary

## Agent
A small program on your server that collects metrics every 60 seconds (CPU, load, memory, swap, disks, network, processes and uptime) and sends them to ServerScope over HTTPS. The agent does not run commands and does not listen on a port. See [Server monitoring](/en/docs/server-monitoring).

## Bash agent
A variant of the agent for hosts and containers without Python or systemd. It sends the same data and only uses `/proc`, `df`, `pgrep` and `curl`. See [The Bash agent](/en/docs/bash-agent).

## Enrollment token
A one-time token (starting with `sse_`) with which the install script connects a server. It is valid for 60 minutes, works once and can be revoked. ServerScope only stores a hash. See [Connect a server with one command](/en/docs/connect-server).

## Agent token
The token with which the agent of one server identifies itself to ServerScope. It is shown once; ServerScope only stores a hash. You can rotate or revoke it on the server page.

## API token
A personal token (starting with `ssk_`) for the REST API, with the permissions `read` and optionally `write`. It is shown once and can expire and be revoked. See [API and webhooks](/en/docs/api-webhooks).

## Alert rule
A rule that opens an incident when a metric stays above or below a threshold for too long, for example CPU usage, RAM usage, disk usage per mount point, or *Agent offline*. See [Alerts and incidents](/en/docs/alerts-incidents).

## Incident
A problem ServerScope has found, with a severity (info, warning or critical), a status (open, acknowledged, investigating, resolved or closed) and a timeline. Repeated failures update the same incident.

## Failure and recovery threshold
The number of failed checks in a row before an incident is opened (failure threshold), and the number of successful checks in a row before an outage counts as recovered (recovery threshold). See [Domain monitoring](/en/docs/domain-monitoring).

## Escalation
An incident is escalated when it stays unacknowledged for too long or when a next threshold is passed, such as a lower warning point for an expiry date.

## Maintenance window
A period in which no alerts or incidents are created for a server or domain, for example during planned work.

## Domain registration (RDAP)
The expiry date of a domain name at the registry, retrieved through RDAP. Not every registry publishes that date; then ServerScope cannot warn about the registration expiring.

## Status page
A page that shows the status of each component and optionally the availability per day over 90 days, without visitors needing an account. Hostnames, IP addresses and URLs are never shown. See [Public status pages](/en/docs/status-pages).

## Webhook
A message ServerScope sends to your own system when something happens, such as `incident.opened` or `dns.changed`. Webhooks are signed, so you can verify they come from ServerScope.

## Team
The unit everything belongs to: servers, domains, websites, alert rules, incidents, webhooks, status pages, API tokens and the audit log. Each team only sees its own data. See [Teams and roles](/en/docs/teams-and-roles).

## Role
What you may do within a team: **member** (view data), **admin** (also manage the configuration) or **owner**. Your role applies per team.

## Audit log
The log of administrative actions within a team, such as creating and revoking tokens. Secrets are not stored in it.

## Two-factor authentication
Signing in with a password plus a code from an authenticator app (TOTP), with recovery codes as a backup. See [Security and account management](/en/docs/security-account).

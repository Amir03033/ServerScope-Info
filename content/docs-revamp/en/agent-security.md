---
order: 30
icon: lock
category: account
description: What the agent does and does not do, why there are no remote commands, how tokens are stored, two-factor authentication and the audit log.
---
# Security of the agent and your account

## What the agent does

Every 60 seconds the agent reads system data and sends it to ServerScope over HTTPS:

- CPU, load, memory and swap
- disks and disk activity
- network traffic and network errors
- the number of processes and the uptime
- optionally the state of [services you choose](service-checks)
- a SHA-256 checksum of its own source files, so a modified agent stands out

## What the agent does not do

- The agent **never executes commands at the request of ServerScope**. There is no way to make something run on your server through ServerScope.
- The agent runs as the user `serverscope` **without a shell** and without write access to its own installation.
- The agent does not listen on a port: it only makes outgoing HTTPS connections.

### Why no remote commands?

Because then ServerScope is never a route into your servers. Even someone who took over the ServerScope server itself cannot run commands on your servers through the agent. [Agent updates](agent-updates) work the same way: the updater only receives a version number and only installs releases with a valid signature from a key that is not on the ServerScope server.

## How tokens are stored

| Token | What ServerScope stores |
|---|---|
| Enrollment token (`sse_…`) for one-command installs | Only a SHA-256 hash. Usable once, valid for 60 minutes, revocable. |
| Agent token | Only a hash. Shown once; rotate or revoke it on the server page. |
| API token (`ssk_…`) | Only a SHA-256 hash. Shown once; can expire and be revoked. |

Tokens and credentials never end up in log files, the audit log, error messages or the output of the install script. On the server, the agent token is in `/opt/serverscope-agent/.env`, owned by `root:serverscope` with mode `640`.

## Two-factor authentication

Protect your account with a code from an authenticator app (TOTP) and keep your recovery codes somewhere safe. How to set it up is described in [Security and account management](security-account).

> [!TIP]
> Enable two-factor authentication for every owner and admin: they can change webhooks, members and the monitoring configuration.

## The audit log

Administrative actions are recorded in your team's **audit log**, such as creating, using and revoking enrollment tokens, revoking an agent token and switching teams. Secrets are not stored in the audit log.

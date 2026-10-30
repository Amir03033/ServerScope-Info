---
date: 2026-10-29
title: Connect a server with one command
status: draft
feature: one-command-install
source: Amir03033/ServerScope agent/README.md and docs/AUDIT.md §6 (phase 2)
---
You now connect a Linux server with **one command**, without copying files
or filling in tokens by hand.

### New

- **Servers → Server toevoegen → Linux:** create an enrollment token and
  copy the install command. The token is valid for 60 minutes, works once
  and can be revoked.
- The script installs the agent on **Ubuntu 22.04+ or Debian 12+** (amd64 or
  arm64), asks for the enrollment token (hidden input) and waits for the
  first sample. The page shows live when the server is connected.
- **Running it again is safe:** a working installation is only updated, not
  enrolled a second time.
- **Uninstall** with `--uninstall`: the agent revokes its own access and
  removes its files. The server and its history stay in ServerScope.

### Security

- Only a hash of an enrollment token is stored. Tokens and credentials never
  end up in logs, the audit log or the script's output.
- HTTPS only (TLS 1.2+). The agent package is checked with a SHA-256
  checksum.
- The agent never executes commands on request of the server.

### Good to know

- The manual installation and all existing agents keep working.

---
date: 2026-10-29
title: One-click agent updates, securely signed (agent 0.3.0)
status: published
feature: agent-updates
source: Amir03033/ServerScope PR #6–#7, agent/README.md, docs/RELEASE_KEYS.md
---
You now update the agent from the dashboard. Every release is signed, so a
server only installs code that really comes from ServerScope.

### New

- Each server shows the installed and the latest agent version. Use
  **Update to …** on a server, or **Update all to …** on the server list.
- **Update automatically** can be turned on per server. It is off by
  default.
- A small updater on the server only installs releases signed with
  ServerScope's offline release key. If an update fails, it restores the
  previous version.

### Security

- Releases are signed with Ed25519. The server checks the signature, the
  manifest, the SHA-256 and whether the version is newer than anything ever
  installed on it, so nobody can force a downgrade.
- Even a compromised ServerScope server cannot install its own code on
  existing servers.
- There is a local emergency switch:
  `sudo bash serverscope-install.sh --disable-updates`.

### Good to know

- Existing installations (agent 0.2.0) keep working. To receive updates, run
  the install command once more.
- Release key fingerprint:
  `511215f3eccc6640c9ee8d42443d13501e2f291e03aa566e21e1df5efa0a1488`.

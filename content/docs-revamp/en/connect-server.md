---
order: 10
icon: terminal
category: servers
feature: one-command-install
description: Connect a Linux server with one command and a one-time enrollment token. Requirements, what the script does, running it again, uninstalling and troubleshooting.
---
# Connect a server with one command

You connect a Linux server to ServerScope with one command. The script installs the agent, exchanges a one-time enrollment token for the agent's own token and waits until the first sample arrives.

## Requirements

- **Ubuntu 22.04 or newer**, or **Debian 12 or newer**
- An **amd64** or **arm64** processor
- **systemd** (the agent runs as a systemd service)
- A user with **sudo** rights
- Outgoing HTTPS from the server to [{app_url}]({app_url})
- In ServerScope: the **owner** or **admin** role in your team

Does your server not meet these requirements? See the [manual installation](server-monitoring).

## 1. Create an enrollment token

In ServerScope open **Servers → Server toevoegen → Linux → Koppeltoken aanmaken**. The page shows the install command and an enrollment token.

- The token is valid for **60 minutes** and works **once**.
- You can **revoke** a token on the same page.
- ServerScope only stores a hash of the token.

## 2. Run the command

On the server, run:

```sh
curl --proto '=https' --tlsv1.2 -fsSL {app_url}/install.sh -o serverscope-install.sh && sudo bash serverscope-install.sh
```

Want to read the script first? Open [{app_url}/install.sh]({app_url}/install.sh) in your browser. It installs software as root.

## 3. Paste the enrollment token

The script asks for the enrollment token. The input is hidden. The token is deliberately not part of the command, so it never ends up in your shell history or the process list.

> [!TIP]
> Automating the installation? Put the token in the environment variable `SERVERSCOPE_ENROLL_TOKEN`, for example from a secret store. Do not type it on the command line.

The script waits for the first sample. The server then appears as **online** in the dashboard of the team that created the token. The page in ServerScope shows live when the server is connected.

## What the script does

- It stops on anything but Ubuntu 22.04+ or Debian 12+ on amd64 or arm64 with systemd.
- It installs `python3`, `python3-venv`, `python3-psutil`, `python3-requests` and `python3-dotenv` from your distribution's package sources (no pip, no PyPI) and creates a Python virtual environment.
- It creates the system user `serverscope` without a shell (if it does not exist yet).
- It downloads the agent package and its SHA-256 checksum from ServerScope, over HTTPS only (TLS 1.2 or newer), and checks the checksum.
- It installs the agent in `/opt/serverscope-agent`, with the configuration in `/opt/serverscope-agent/.env` (owner `root:serverscope`, mode `640`).
- It exchanges the enrollment token for a normal agent token, starts the `serverscope-agent` service and waits up to 90 seconds for the first sample.

> [!NOTE]
> The checksum comes from the same server as the package. It protects against a corrupt or truncated download, not against a compromised ServerScope server.

## Running it again

Running the script again is safe:

- An installation made by the script with a working agent token is only updated. No second server is created.
- If the agent token was revoked, the script asks for a new enrollment token and **creates a new server**. Delete the old server in the dashboard afterwards; its history stays until you do.
- An agent you installed **by hand** is left alone.

## Uninstalling

```sh
sudo bash serverscope-install.sh --uninstall
```

This revokes the agent's own token in ServerScope, stops and removes the service, removes `/opt/serverscope-agent` and removes the `serverscope` user if the script created it. The server and its history stay in ServerScope. The Python packages from apt are kept.

## Troubleshooting

The script stops with a message (in Dutch) and a fix. The most common ones:

| Message (meaning) | Fix |
|---|---|
| The Ubuntu/Debian version or operating system is not supported | Use Ubuntu 22.04+ or Debian 12+, or the [manual installation](server-monitoring). |
| The processor architecture is not supported | Only amd64 and arm64. |
| systemd is not running on this system | The agent runs as a systemd service; without systemd the script cannot install it. |
| This script must run as root | Start it with `sudo bash serverscope-install.sh`. |
| The enrollment token is invalid, expired, already used or revoked | Create a new enrollment token and run the script again. |
| Too many enrollment attempts | Wait a minute and try again. |
| ServerScope cannot be reached | Check DNS, firewall and proxy: the server must reach ServerScope over HTTPS. |
| The checksum of the agent package does not match | Try again. If it keeps happening, get in touch. |
| The agent runs, but ServerScope received no sample within 90 seconds | Check the clock (`timedatectl`; enable NTP with `sudo timedatectl set-ntp true`) and the log: `journalctl -u serverscope-agent -n 50`. Running the script again is safe. |
| A manually installed agent is already present | Update it following the manual instructions, or remove it first. |

View the agent's log with:

```sh
journalctl -u serverscope-agent -n 50
```

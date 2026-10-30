---
order: 40
icon: shield
category: servers
feature: agent-updates
description: Signed agent releases, the updater, automatic updates, rolling back, the local emergency switch and checking the fingerprint.
---
# Agent updates from the dashboard

From agent version **0.3.0** you can update the agent from the dashboard. Every release is **signed**. A server only installs a release when its signature matches the public key the server stored at installation.

## Signed releases

Every agent release is signed with one private key that only exists **offline**, with the ServerScope maintainer. That key is not on the ServerScope server, not in the repository and not in CI. So even a fully compromised ServerScope server cannot make a release your servers accept.

The signature is Ed25519. Your server keeps the public key in `/etc/serverscope/release.pub`.

## The updater

The agent runs as the user `serverscope` without write access to its own installation, so it cannot update itself. A small separate program does that: `/usr/local/sbin/serverscope-updater`. It runs as root from the systemd timer `serverscope-updater.timer`, every 5 minutes.

When a team admin clicks **Bijwerken** (or **automatisch bijwerken** is on for the server), the updater:

1. receives only a request id and a version number: no URLs, scripts or commands. It downloads that release from fixed paths;
2. checks the signature with the **local** key, then the manifest, the SHA-256 and the contents of the package, and the version. It refuses any version equal to or lower than the installed one or the highest one ever installed (no downgrades), and a version that already failed here;
3. installs the new version next to the current one, switches over in one step and restarts the agent;
4. waits at least 3 minutes for a sample from the new version;
5. **rolls back** to the previous version when that sample does not arrive;
6. reports the result: succeeded, failed and rolled back, or refused (with a fixed reason such as `bad_signature`).

**Automatic updates** are off by default and are turned on per server.

Log and status on the server:

```sh
journalctl -u serverscope-updater
```

```sh
sudo serverscope-updater --status
```

## The emergency switch on your own server

Want nothing to be updated any more? You control that locally; ServerScope cannot override it:

```sh
sudo bash serverscope-install.sh --disable-updates
```

This creates `/etc/serverscope/updates-disabled` and stops the timer. As long as that file exists, the updater does nothing at all, not even a network request. Turn updates back on with:

```sh
sudo bash serverscope-install.sh --enable-updates
```

## Checking the fingerprint

The fingerprint of the release key (SHA-256 of the public key, DER) is:

`{release_fingerprint}`

Compare it with the line `Releasesleutel: vingerafdruk …` the install script prints, and on an installed server with:

```sh
openssl pkey -pubin -in /etc/serverscope/release.pub -outform DER | sha256sum
```

> [!WARNING]
> On a **first** installation the public key comes from the install script, which comes from the ServerScope server. So always check the fingerprint above on a first installation. ServerScope cannot take over **existing** installations through the updater: they keep the key they stored at installation.

## Existing installations (agent 0.2.0)

They keep working without you doing anything. For signed updates, run the install script once more on the server, with the same command as for a new server. You do not need a new enrollment token and no new server is created:

```sh
curl --proto '=https' --tlsv1.2 -fsSL {app_url}/install.sh -o serverscope-install.sh && sudo bash serverscope-install.sh
```

Manual installations and the Bash agent are never touched by the updater.

## Uninstalling

`sudo bash serverscope-install.sh --uninstall` also removes the updater, `/etc/serverscope` and `/var/lib/serverscope-updater`.

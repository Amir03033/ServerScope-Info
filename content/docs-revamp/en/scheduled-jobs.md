---
order: 5
icon: clock
category: monitoring
feature: scheduled-jobs
description: Monitor cron jobs, backups and imports with a secret ping URL. Examples for crontab, the Laravel scheduler and Plesk.
---
# Scheduled jobs

Backups, imports and other cron jobs fail silently: nothing happens, and nobody notices. With **scheduled jobs** the job calls a secret URL when it finishes. When that check-in does not arrive on time, or the job reports a failure, ServerScope opens an incident with the logs and deployments around it. The next good check-in resolves it.

Open [Scheduled jobs]({app_url}/heartbeats) in the sidebar (group **Monitoring**).

## Creating a job

1. Click **New job**.
2. Give it a **Name**, for example `Nightly backup`.
3. Optionally choose the **Server** it runs on: that server's logs and deployments then belong to an incident.
4. Under **How often does the job run?** choose:
   - **Fixed interval**, for example every hour or every day, or
   - **Cron schedule**: the expression exactly as in your crontab (five fields, for example `0 3 * * *` for every night at 03:00) and the **Time zone**.
5. Set the **Grace period**: how late the job may be (or how much longer it may run) before it becomes an incident.
6. Choose the **Incident severity** and click **Create job**.

The job page shows its **Ping URL** and ready-made examples. Monitoring starts with the first check-in.

> [!WARNING]
> Anyone with the ping URL can send check-ins: treat it like a password. Use **New URL** in the ⋯ menu to replace it; the old one stops working at once.

## Checking in

The ping URL is `{app_url}/api/ping/{token}`. It accepts `GET`, `POST` and `HEAD`:

| URL | Meaning |
|---|---|
| `/api/ping/{token}` | The job finished successfully |
| `/api/ping/{token}/start` | The job started (used to measure the duration) |
| `/api/ping/{token}/fail` | The job failed: opens an incident straight away |
| `/api/ping/{token}/{0-255}` | The job ended with this exit code (0 = success, anything else = failure) |

The answer is `OK` (200), or `Not found` (404) for an unknown token. With `POST` the body is kept as the job's output: at most 2 KB, without passwords, tokens or e-mail addresses.

### After your cron line

Check in only when the command succeeds (`&&`):

```sh
0 3 * * * /usr/local/bin/backup.sh && curl -fsS -m 10 --retry 3 -o /dev/null {app_url}/api/ping/{token}
```

### Start, result and output

Measure the duration, report the exit code and send the last lines of output:

```sh
curl -fsS -m 10 --retry 3 -o /dev/null {app_url}/api/ping/{token}/start
output=$(/usr/local/bin/backup.sh 2>&1); code=$?
curl -fsS -m 10 --retry 3 -o /dev/null --data-raw "$(printf '%s' "$output" | tail -c 2000)" {app_url}/api/ping/{token}/$code
```

### Laravel scheduler

For tasks in `routes/console.php`:

```php
Schedule::command('backup:run')->daily()
    ->pingBefore('{app_url}/api/ping/{token}/start')
    ->pingOnSuccess('{app_url}/api/ping/{token}')
    ->pingOnFailure('{app_url}/api/ping/{token}/fail');
```

### Plesk

Plesk's *Run a PHP script* cannot run `curl`, and *Run a command* runs in a restricted shell without PHP. To watch the scheduler of a Laravel app on Plesk, use an environment variable in that app instead (see below).

## Watching the ServerScope scheduler

If you host ServerScope yourself, create a job with a 1-minute interval and put its ping URL in ServerScope's `.env`:

```sh
SERVERSCOPE_SCHEDULER_PING_URL={app_url}/api/ping/{token}
```

The scheduler then checks in every minute, and you get an incident as soon as it stops.

## The job page

- **Recent runs**: green succeeded, red failed, oldest on the left.
- **Check-ins**: everything the job reported, with the duration, exit code and output.
- **Pause** keeps check-ins but opens no incident; after **Resume** the job is monitored from the next check-in.
- Scheduled jobs also appear in [NEXUS](dashboard-nexus) as rings around their server, and in the server's side panel.

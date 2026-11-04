---
date: 2026-11-04
title: Automatic deployments, logs, postmortems, scheduled jobs and chat channels (agent 0.4.0)
status: published
source: Amir03033/ServerScope PR #40–#45 (4 November 2026)
---
ServerScope now notices your deployments by itself, reads along in your log
files, watches your cron jobs and posts incidents to Slack, Microsoft Teams
and Telegram as well.

### New

- **Automatic deployments:**
  - The **agent** notices when the code in a folder changes
    (`SERVERSCOPE_DEPLOY_PATHS`). It only reads `.git/HEAD` and runs no git
    commands.
  - **Connect GitHub** (a signed webhook) to add the commit or pull request
    title, the author and the link. A push or a GitHub deployment can also
    count as a deployment by itself.
  - Find it under **Deployments → Automatic deployments**.
- **Logs** (new page):
  - the agent follows the log files you choose (`SERVERSCOPE_LOG_FILES`) and
    only sends errors and warnings, with stack traces;
  - it recognises Laravel, nginx, Apache, PHP-FPM and syslog;
  - identical errors are grouped into **issues**, with a trend;
  - the **Stream** view shows every single line;
  - every incident shows **Logs around this incident**;
  - a new alert rule warns about a spike in errors per 5 minutes.
- **Postmortem** with one click from Incident Memory:
  - pre-filled with the duration, time to detect and acknowledge, impact, a
    timeline with deployments, the cause and the fix;
  - you add the summary, lessons and action items;
  - share it through a **public, read-only link** you can revoke, as Markdown
    or as a PDF.
- **Scheduled jobs** (cron monitoring):
  - every cron job, backup or import gets a **secret ping URL**;
  - when the check-in does not arrive on time (its schedule plus grace), or
    the job reports a failure, an incident opens;
  - the next good check-in resolves the incident;
  - a schedule is a fixed interval or a cron expression with a time zone;
  - you see the recent runs, the duration, the exit code and the output
    (without passwords or tokens);
  - ready-made examples for crontab, the Laravel scheduler and Plesk.
- **Slack, Microsoft Teams and Telegram** next to Discord, under
  **Webhooks → Connect chat**. Messages in each channel's own format, with
  **View incident** and **Acknowledge** buttons.
- **Watch the scheduler:** with `SERVERSCOPE_SCHEDULER_PING_URL` the
  ServerScope scheduler checks in on a scheduled job every minute, so you
  notice when it stops.
- **Scheduled jobs in NEXUS**, as rings around their server, with status and
  incidents. They also appear in the NEXUS list and in the server's side
  panel.

### Improved

- **A redesigned incident page:** one overview with the key figures, a calm
  step bar, the root cause analysis with the rules' and the AI's conclusion
  side by side (with confidence meters and "AI and rules agree"), and a fixed
  side column.
- **A redesigned Logs page:** figures and chart in one card, a table of
  issues, a console-style stream and filters on one line (behind a button on
  phones).
- **Richer hover cards:**
  - **server:** all figures, stopped services, late scheduled jobs, errors in
    the logs of the last hour and the latest deployment;
  - **website:** the latest failure, availability over the last 24 hours, the
    latest check and the latest deployment;
  - **incident:** the description, who it is assigned to, the likely or
    recorded cause, who acknowledged it and the fix;
  - **scheduled job** (new): status, when it is due, the recent runs, the
    latest failure and the open incident.

### Fixed

- A time-out during a domain or page check made the check hang, so no outage
  was reported. It is now reported as a normal "Time-out".
- The heartbeat (ECG) on the dashboard was invisible on phones.
- The living logo now also pulses on iPhone (Safari).

### Security

- Webhook URLs and bot tokens of chat channels are stored encrypted, shown
  masked and filtered out of error messages.
- Log lines are stripped of passwords, tokens and e-mail addresses twice: by
  the agent and by ServerScope.
- A ping URL is a secret. Make a new one with one click.
- **Acknowledge** in a chat message opens a page that needs you to log in;
  the link itself changes nothing.

### Good to know

- Deploy detection and log following need **agent 0.4.0**. The Bash agent
  does not support them.
- Log lines are kept for 14 days by default.

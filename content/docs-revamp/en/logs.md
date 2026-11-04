---
order: 60
icon: logs
category: servers
feature: logs
description: Let the agent follow your log files, see errors grouped by issue, find the logs around an incident and get an alert on error spikes.
---
# Logs

The agent can follow the log files you choose and send **only errors and warnings**, with their stack traces. ServerScope groups identical errors into **issues**, so a hundred identical lines become one row with a trend. Open [Logs]({app_url}/logs) in the sidebar.

## Setting it up (agent 0.4.0)

1. Tell the agent which files to follow, in `/opt/serverscope-agent/.env`, as `name=file` (several separated by commas):

   ```sh
   SERVERSCOPE_LOG_FILES=app=/var/www/shop/storage/logs/laravel.log,nginx=/var/log/nginx/error.log
   ```

2. Let the agent read them. nginx logs belong to the `adm` group; give read access to app logs:

   ```sh
   sudo usermod -aG adm serverscope
   sudo setfacl -m u:serverscope:r /var/www/shop/storage/logs/laravel.log
   sudo setfacl -d -m u:serverscope:r /var/www/shop/storage/logs
   ```

   The last line makes new log files in that folder (for example daily Laravel logs) readable too.

3. Restart the agent:

   ```sh
   sudo systemctl restart serverscope-agent
   ```

New errors appear within a minute. The agent recognises the formats of Laravel, nginx, Apache, PHP-FPM and syslog.

> [!IMPORTANT]
> Only new lines are read and nothing is written. Passwords, tokens and e-mail addresses are removed **twice**: by the agent before sending, and by ServerScope when storing. Log lines are kept for 14 days.

> [!NOTE]
> Log following needs agent **0.4.0** or newer. The Bash agent does not support it.

## Reading the Logs page

- At the top: the number of errors, warnings and issues, and how many servers send log lines, compared with the previous period (1 hour, 24 hours or 7 days), with an activity chart.
- **Issues**: one row per problem, with its trend, how often it happened and when it was last seen. **Show lines** opens the individual lines.
- **Stream**: every single line, newest first, in a console style. **All lines like this** jumps to the lines of the same issue.
- Filter by server, source and level (errors and warnings, errors only or warnings only), or search the message.

## Logs around an incident

Every incident page has a section **Logs around this incident**: the errors and warnings on the servers involved, from a few minutes before the start until the end. Often this is the fastest way to the cause.

## An alert on error spikes

Use **Spike alert** on the Logs page to create an alert rule that fires when the number of errors per 5 minutes suddenly rises. You can tune it under **Administration → Alert rules** like any other rule.

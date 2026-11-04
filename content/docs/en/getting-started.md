---
order: 1
icon: book
description: What ServerScope does, how to sign up or join a team, how to find your way around and what to set up first.
---
# Getting started

ServerScope monitors your **servers** (through a small, read-only agent), the **services** on them, your **domains** (HTTP, SSL certificates, DNS and domain registration), complete **websites** page by page and your **scheduled jobs**. It notices **deployments** and follows your **log files**. Problems become **incidents**: related outages are grouped into one, every incident is investigated automatically, and notifications go out by e-mail, in the app, as push notifications, to Slack, Microsoft Teams, Telegram, Discord or your own webhook.

The application runs at [{app_url}]({app_url}). This website (documentation and information) is separate from it and has no accounts of its own.

> [!NOTE]
> ServerScope is available in **English** (default) and **Dutch**. Switch under **Profile → Language** or in the user menu. These guides use the English names of buttons and menus.

## Getting an account

You can get access in two ways:

- **Sign up yourself.** Choose a plan (Free, Pro or Premium) on the pricing page and create an account. You confirm your e-mail address and accept the terms and privacy statement. You then own a new team. While sign-up is closed, the pricing page offers the waiting list instead.
- **Accept an invitation.** A team owner or administrator invites you under **Administration → Users**. You receive an e-mail with a link that is valid for **7 days**; you accept the invitation yourself. The inviter can revoke or resend it.

Choose a password of at least 12 characters with upper-case letters, lower-case letters and digits. Forgot it? Use **Forgot password** on the login page.

> [!TIP]
> Hosting ServerScope yourself and there are no users yet? Create the first administrator on the server with `php artisan serverscope:create-admin`.

## Logging in

1. Go to [{app_url}/login]({app_url}/login) and enter your e-mail address and password.
2. If you turned on two-factor authentication, enter the code from your authenticator app (or a recovery code).
3. You land on the **Dashboard**.

Too many failed attempts are blocked temporarily; wait a minute and try again.

## Finding your way around

The sidebar has these groups:

| Group | Items |
|---|---|
| (top) | Dashboard |
| Monitoring | Servers, Domains, NEXUS (with Skyline, Globe, Time travel and Wall screen), Dependencies, Deployments, Logs, Scheduled jobs |
| Incidents | Incidents, Alerts |
| Reporting | Reports |
| Administration (owners and administrators) | Alert rules, Status pages, Webhooks, Users, Plan and billing, Audit log, System status |
| Account | Notifications, Profile |

- The **Dashboard** starts with mission control: a health score, a tile per server and website, the last 24 hours and **Needs attention**. See [Dashboard, NEXUS and finding your way](dashboard-nexus).
- **⌘K / Ctrl+K** searches servers, websites and incidents and runs actions.
- The number next to **Incidents** and **Alerts** is the count of open incidents and active alerts.
- If you are a member of several teams, switch with the team switcher.
- On a phone you can [install ServerScope as an app](mobile-app).

## Teams and roles

Everything in ServerScope belongs to a **team**, and your role applies per team:

| | Member | Administrator | Owner |
|---|---|---|---|
| View the team's servers, domains, metrics, incidents and reports | ✓ | ✓ | ✓ |
| Acknowledge, investigate, resolve, assign and comment on incidents | ✓ | ✓ | ✓ |
| Manage own notifications, password, 2FA and API tokens | ✓ | ✓ | ✓ |
| Add or change servers, domains, pages, scheduled jobs and alert rules | | ✓ | ✓ |
| Create incidents manually, change severity, close or reopen | | ✓ | ✓ |
| Webhooks and chat channels, status pages, members, audit log | | ✓ | ✓ |
| Change an owner's role or remove an owner | | | ✓ |

More in [Teams and roles](teams-and-roles). What your team can add depends on its plan; on Free, for example, 1 server and 3 domains, without API tokens and webhooks.

## Next steps

1. [Connect a server with one command](connect-server)
2. [Monitor a domain](domain-monitoring) and [its pages](website-monitoring)
3. [Monitor your cron jobs](scheduled-jobs)
4. [Understand alerts and incidents](alerts-incidents)
5. [Record dependencies](dependencies), so one outage becomes one incident
6. [Send notifications to Slack, Teams or Telegram](chat-channels) or [Discord](discord-notifications)

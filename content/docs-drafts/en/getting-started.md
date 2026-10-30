# Getting started

ServerScope monitors your **servers** (through a small agent) and your **domains** (HTTP availability, SSL certificates, DNS and domain registration). Problems become **incidents** with a timeline, and notifications are delivered by e-mail, in the app, to Discord or to your own webhook.

The application runs at [{app_url}]({app_url}). This website (documentation and information) is separate from it and has no accounts of its own.

> [!NOTE]
> The ServerScope application interface is in Dutch. These English guides quote the Dutch button and menu names (with a translation) so you can find them in the app.

## Getting an account

There is **no public sign-up**. Accounts are created by an administrator of your ServerScope environment:

1. An administrator opens **Beheer → Gebruikers** (Admin → Users) and enters your name and e-mail address (optionally with administrator rights).
2. You receive an e-mail with a link to set your own password. The link is valid for **60 minutes**.
3. Choose a password of at least 12 characters with upper-case letters, lower-case letters and digits.
4. Log in at [{app_url}/login]({app_url}/login).

Did the link expire? Use **Wachtwoord vergeten** (Forgot password) on the login page to receive a new one.

> [!TIP]
> Hosting ServerScope yourself and there are no users yet? Create the first administrator on the server with `php artisan serverscope:create-admin`.

## Logging in

1. Go to [{app_url}/login]({app_url}/login) and enter your e-mail address and password.
2. If you enabled two-factor authentication, enter the code from your authenticator app (or a recovery code).
3. You land on the **Dashboard**.

Too many failed attempts are blocked temporarily; wait a minute and try again.

## Finding your way around the dashboard

The sidebar has three groups:

| Group | Items |
|---|---|
| Main menu | Dashboard, Servers, Domeinen (Domains), Incidenten (Incidents), Alerts, Rapporten (Reports) |
| Beheer (Admin, owners and administrators only) | Alertregels (Alert rules), Statuspagina's (Status pages), Webhooks, Gebruikers (Users), Auditlog (Audit log) |
| Account | Meldingen (Notifications), Profiel (Profile) |

- The **Dashboard** shows the state of your servers and domains and the active incidents.
- A **search box** at the top finds servers, domains and incidents.
- The number next to **Incidenten** and **Alerts** is the count of open incidents and active alerts.
- A link to this documentation sits at the bottom of the sidebar.

## Roles

Everything in ServerScope belongs to a **team**, and your role applies per team. There are three roles:

| | Member | Administrator | Owner |
|---|---|---|---|
| View the team's servers, domains, metrics, incidents and reports | ✓ | ✓ | ✓ |
| Acknowledge, investigate, resolve, assign and comment on incidents | ✓ | ✓ | ✓ |
| Manage own notifications, password, 2FA and API tokens | ✓ | ✓ | ✓ |
| Add or change servers, domains and alert rules | | ✓ | ✓ |
| Create incidents manually, change severity, close or reopen | | ✓ | ✓ |
| Webhooks (incl. Discord), status pages, members and audit log | | ✓ | ✓ |
| Change an owner's role or remove an owner | | | ✓ |

More about teams, roles and switching teams: [Teams and roles](teams-and-roles).

## Next steps

1. [Add a server and install the agent](server-monitoring)
2. [Monitor a domain](domain-monitoring)
3. [Understand alerts and incidents](alerts-incidents)
4. [Send notifications to Discord](discord-notifications)

# Security and account management

No subscriptions are active yet: every feature is available in every environment. What you may do depends on your **role** in your team (owner, administrator or member; see [Teams and roles](teams-and-roles)). Some settings, such as data retention, are handled by whoever hosts ServerScope.

| Feature | Who |
|---|---|
| Two-factor authentication, password, API tokens, notification preferences | Everyone, for their own account |
| Invite members, change roles, remove members | Owner or administrator (only an owner changes an owner's role) |
| View the audit log | Owner or administrator |
| Maintenance windows for servers and domains | Owner or administrator |
| Data retention and e-mail settings | Host of ServerScope (server configuration) |

## Two-factor authentication (2FA)

Protect your account with a code from an authenticator app (TOTP), such as 1Password, Authy or Google Authenticator.

1. Open [Profiel]({app_url}/profile) (Profile) and, under **Tweestapsverificatie** (two-factor authentication), click **Tweestapsverificatie instellen** (set up).
2. In your authenticator app, add an account via “enter a setup key” with the key shown (time-based, 6 digits), or open the link shown on your phone.
3. Enter the code from the app and click **Bevestigen en inschakelen** (confirm and enable).
4. **Store the recovery codes** that now appear somewhere safe. Each code works once and they are not shown again.

From now on ServerScope asks for a code after your password. Lost your phone? Use a recovery code.

- **Nieuwe herstelcodes** (new recovery codes) creates a new set; the old codes stop working.
- **Uitschakelen** (disable) asks for your current password.
- Administrators can see under **Gebruikers** (Users) who has 2FA enabled.

> [!TIP]
> Enable 2FA for every administrator account: administrators can change webhooks, users and the monitoring configuration.

## Password

- Change your password under **Profiel → Wachtwoord wijzigen** (change password). The new password needs at least 12 characters with upper-case letters, lower-case letters and digits.
- After a change your **other sessions are logged out**.
- Forgot your password? Use **Wachtwoord vergeten** on the login page; the link is valid for 60 minutes.

## Inviting users

1. Open [Beheer → Gebruikers]({app_url}/users) (Admin → Users).
2. Under **Gebruiker uitnodigen** (invite user) enter the name and e-mail address and optionally tick **Beheerder** (administrator).
3. Click **Uitnodigen** (invite). The user receives an e-mail to set their own password (valid for 60 minutes).

All members of a team see that team's servers, domains and incidents; owners and administrators change the configuration. Safeguards:

- A team always keeps at least one owner and one administrator; the last one cannot be deleted or demoted.
- You cannot revoke your own administrator rights or delete your own account under *Gebruikers*.

## Audit log

Under [Beheer → Auditlog]({app_url}/audit-logs) (Admin → Audit log) owners and administrators see who changed what in their team, with time, user, component and IP address. You can filter by action and user, and search.

Recorded actions include: servers and domains (create, update, pause, delete, restore, maintenance), agent tokens (issue, rotate, revoke), manual incidents, status pages, webhooks (including replacing a Discord URL and automatic disabling), API tokens, and user actions (invitation, role change, password, 2FA, use of recovery codes).

> [!NOTE]
> Secrets (tokens, passwords, Discord webhook URLs) are never stored in the audit log. Changes to **alert rules** are currently not recorded in the audit log.

## Maintenance windows

Plan maintenance to avoid unnecessary incidents:

- **Servers**: start a maintenance window on the server page (5 minutes to 7 days), or set a start and end time under *Bewerken* (Edit). No alerts or incidents are created for that server meanwhile.
- **Domains**: set a start, end and reason under *Bewerken*. The checks keep running, but no incidents are opened.
- On status pages the component is shown as *Onderhoud* (maintenance).

Times are entered and shown in the Europe/Amsterdam timezone and stored in UTC.

## Data retention

By default ServerScope keeps all history. Whoever hosts ServerScope can set a retention period in days for measurements, domain checks, notification delivery history, read notifications and the audit log (`SERVERSCOPE_RETAIN_*_DAYS`). A daily task (`serverscope:prune`) deletes older data.

> [!WARNING]
> Reports can only cover periods for which data is still kept.

## Reporting a security problem

Noticed something wrong, or think a token or webhook URL has leaked? Revoke the token or replace the URL (see [Discord](discord-notifications) and [API and webhooks](api-webhooks)) and [get in touch](/en/contact). Never include tokens, passwords or webhook URLs.

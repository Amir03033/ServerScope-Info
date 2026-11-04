# Security and account management

What you may do depends on your **role** in your team (owner, administrator or member; see [Teams and roles](teams-and-roles)). What your team can add, and how long history is kept, depends on its **plan** (Free, Pro or Premium); see the pricing page.

| Feature | Who |
|---|---|
| Two-factor authentication, password, API tokens, notification preferences | Everyone, for their own account |
| Invite members, change roles, remove members | Owner or administrator (only an owner changes an owner's role) |
| View the audit log | Owner or administrator |
| Maintenance windows for servers and domains | Owner or administrator |
| Plan, payment and invoices (**Administration → Plan and billing**) | Owner or administrator |
| Escalation contacts (up to 5 per team) | Owner or administrator |
| Data retention and e-mail settings | Host of ServerScope (server configuration) |

## Two-factor authentication (2FA)

Protect your account with a code from an authenticator app (TOTP), such as 1Password, Authy or Google Authenticator.

1. Open [Profile]({app_url}/profile) and, under **Two-factor authentication**, click **Set up two-factor authentication**.
2. In your authenticator app, add an account via “enter a setup key” with the key shown (time-based, 6 digits), or open the link shown on your phone.
3. Enter the code from the app and click **Confirm and turn on**.
4. **Store the recovery codes** that now appear somewhere safe. Each code works once and they are not shown again.

From now on ServerScope asks for a code after your password. Lost your phone? Use a recovery code.

- **New recovery codes** creates a new set; the old codes stop working.
- **Disable** asks for your current password.
- Administrators can see under **Users** who has 2FA enabled.

> [!TIP]
> Enable 2FA for every administrator account: administrators can change webhooks, users and the monitoring configuration.

## Password

- Change your password under **Profile → Change password**. The new password needs at least 12 characters with upper-case letters, lower-case letters and digits.
- After a change your **other sessions are logged out**.
- Forgot your password? Use **Forgot password** on the login page; the link is valid for 60 minutes.

## Inviting users

1. Open [Administration → Users]({app_url}/users).
2. Under **Invite user** enter the name and e-mail address and optionally tick **Administrator**.
3. Click **Invite**. The person receives an e-mail with a link that is valid for **7 days** and accepts the invitation themselves. You can revoke or resend an invitation as long as it has not been accepted.

All members of a team see that team's servers, domains and incidents; owners and administrators change the configuration. Safeguards:

- A team always keeps at least one owner and one administrator; the last one cannot be deleted or demoted.
- You cannot revoke your own administrator rights or remove yourself under **Users**.

## Audit log

Under [Administration → Audit log]({app_url}/audit-logs) owners and administrators see who changed what in their team, with time, user, component and IP address. You can filter by action and user, and search.

Recorded actions include: servers and domains (create, update, pause, delete, restore, maintenance), agent tokens (issue, rotate, revoke), agent updates, manual incidents, status pages, webhooks and chat channels (including replacing a URL or bot token and automatic disabling), GitHub connections, scheduled jobs (including a new ping URL), shared postmortems, API tokens, and user actions (invitation, role change, password, 2FA, use of recovery codes).

> [!NOTE]
> Secrets (tokens, passwords, webhook URLs, bot tokens, ping URLs) are never stored in the audit log. Changes to **alert rules** are currently not recorded in the audit log.

## Maintenance windows

Plan maintenance to avoid unnecessary incidents:

- **Servers**: use the **maintenance** panel on the server page: start maintenance now for a set time, or choose your own start and end time. No new alerts or incidents are opened for the server meanwhile.
- **Domains**: set a start, end and reason under **Edit**. The checks keep running, but no incidents are opened.
- On status pages the component is shown as *Maintenance*.

Times are entered and shown in the Europe/Amsterdam timezone and stored in UTC.

## Secrets and personal data

- Webhook URLs, chat bot tokens and the GitHub webhook secret are stored **encrypted**, shown masked and filtered out of error messages.
- Log lines are stripped of passwords, tokens and e-mail addresses **twice**: by the agent and by ServerScope.
- A ping URL of a scheduled job is a secret; replace it with **New URL** if it leaked.
- Agent updates are **signed**; even a compromised ServerScope server cannot install its own code on your servers. See [Agent updates](agent-updates).
- Every team only sees its own data.

## Data retention

How long history is kept depends on the plan (Free: 14 days, Pro: 365 days, Premium: unlimited). Log lines are kept for 14 days. If you host ServerScope yourself, you set the retention per kind of data in days (`SERVERSCOPE_RETAIN_*_DAYS`); a daily task deletes older data.

> [!WARNING]
> Reports can only cover periods for which data is still kept.

## Reporting a security problem

Noticed something wrong, or think a token or webhook URL has leaked? Revoke the token or replace the URL (see [Slack, Teams and Telegram](chat-channels), [Discord](discord-notifications) and [API and webhooks](api-webhooks)) and [get in touch](/en/contact). Never include tokens, passwords or webhook URLs.

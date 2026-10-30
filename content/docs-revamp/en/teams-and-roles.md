---
order: 20
icon: users
category: account
feature: teams
description: What a team is, what each role (owner, admin or member) may do, how to switch teams and why every team only sees its own data.
---
# Teams and roles

In ServerScope everything belongs to a **team**: servers, domains, websites, alert rules, incidents, webhooks, status pages, API tokens and the audit log. Every team only sees its own data.

> [!NOTE]
> The ServerScope application is in Dutch. This guide names the Dutch buttons and menus so you can find them in the app.

## Roles

Your role applies per team. You can be an admin in one team and a member in another.

| | Member | Admin | Owner |
|---|---|---|---|
| View the team's monitoring data | ✓ | ✓ | ✓ |
| Manage configuration (servers, domains, alert rules, webhooks, status pages) | | ✓ | ✓ |
| Manage members under **Gebruikers** | | ✓ | ✓ |
| Change an owner's role or remove an owner | | | ✓ |

A few fixed rules:

- A team always has at least one **owner** and at least one **admin**. The last one cannot be removed or demoted.
- You cannot revoke your own admin rights.
- If an account also belongs to other teams, removing it only takes it out of this team.

## Switching teams

If you are a member of more than one team, a **Team** drop-down appears at the bottom of the sidebar. Choose another team and you land on that team's dashboard. The switch is recorded in that team's audit log.

If you are a member of one team, that place only shows your team's name.

## Every team only sees its own data

- Data of another team does not exist for you: a link to a server or incident of another team returns "not found".
- An **API token** belongs to one team. If the token's owner leaves that team, the token stops working.
- An **agent** sends its samples to the server it belongs to, and that server belongs to one team.

> [!NOTE]
> Data from before teams were introduced is in one default team. Nothing was lost.

## Troubleshooting

| What you see | Cause and fix |
|---|---|
| There is no **Team** drop-down | You are a member of one team. |
| A link to a server returns "not found" | The server belongs to another team. Switch teams or ask an admin of that team for access. |
| An API integration suddenly returns 401 | The token's owner left the team, or the token was revoked or expired. Create a new token. |

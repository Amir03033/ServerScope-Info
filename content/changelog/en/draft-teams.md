---
date: 2026-10-29
title: Teams and roles
status: draft
feature: teams
source: Amir03033/ServerScope docs/AUDIT.md §5 (phase 1)
---
ServerScope now works with **teams**. Every customer is a team, and a team
only sees its own servers, domains, incidents and settings.

### New

- **Teams:** all your servers, domains, websites, alert rules, incidents,
  webhooks, status pages, API tokens and the audit log belong to one team.
  Data of another team is never visible.
- **Roles per team:** owner, admin or member. Your role only applies to the
  team in which you have it.
- **Switching teams:** if you are a member of several teams, switch with the
  team switcher.
- **Members per team:** an overview of who is in your team.

### Good to know

- All existing data is in one default team. Nothing is lost.
- An API token belongs to one team and stops working when its owner leaves
  that team.
- Existing agents keep working as they did.

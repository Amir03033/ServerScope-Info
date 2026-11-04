---
order: 6
icon: link
category: monitoring
feature: correlation
description: Record which website runs on which server, so one outage becomes one incident with the affected resources underneath, and see it all in NEXUS.
---
# Dependencies and grouped incidents

When a server goes down, every website on it goes down too. Without context you would get a stream of separate alerts. With **dependencies** ServerScope knows how your resources relate and opens **one incident** for the cause, with the affected websites, pages and services underneath.

## Recording a dependency

Team owners and administrators record dependencies.

1. Open [Dependencies]({app_url}/dependencies) in the sidebar (group **Monitoring**).
2. Under **Add dependency** choose the resource and what it depends on. Resources are servers, websites, website pages and services.
3. Optionally add a description, for example `runs on` or `database`, and notes.
4. Save.

Read a dependency as: *the resource only works when the resource it depends on works*. A typical chain is server → Nginx → website → MySQL. Pages belong to their website automatically and services to their server; you do not record those.

> [!NOTE]
> ServerScope refuses a dependency that would create a cycle (A depends on B, B depends on A).

## Suggested relations

ServerScope suggests relations itself, for example when a domain points in DNS to the IP address of one of your servers. Suggestions are shown on the server and domain pages and are **not used** until you confirm them.

The same panel shows what may be affected by an outage, which is handy before maintenance. For the full picture, open [NEXUS](dashboard-nexus): the map shows every confirmed dependency and what falls over if something fails.

## How incidents are grouped

An incident is grouped under another one (the **root incident**) only when:

- there is a **confirmed** dependency between the two resources, or it is the same resource, **and**
- the root can explain the failure, **and**
- both started close together (within 30 minutes).

Outages that merely happen at the same time are **never** combined. Every grouped incident states why it was grouped and shows the dependency path. Grouping is not proof of the cause: each grouped incident still recovers based on its own checks. An administrator can detach an incident from its group.

## What changes for notifications

- While the root incident is active, a grouped incident sends **no notification of its own**: you get one e-mail, push or chat message for the outage.
- Generic webhooks still receive every incident, with `parent_id` and `correlation`, so your own system can make the same choice.
- Only the root incident escalates.

## Tips

- Start with the obvious: which websites run on which server.
- Add services (Nginx, PHP-FPM, MySQL) once the agent reports them; see [Service checks](service-checks).
- After an outage, check the grouping reason on the incident page and adjust dependencies where needed.

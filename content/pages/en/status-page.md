---
title: Creating a status page
seo_title: Create a status page for your servers and websites — ServerScope
description: Show customers or colleagues whether your services work, without an account. Public or private, with availability per day over 90 days.
summary: With ServerScope you create a status page that shows the status of each component and, if you want, the availability per day over the last 90 days. Visitors need no account. You choose the names that appear; hostnames, IP addresses and URLs are never shown.
---
# Creating a status page

## What visitors see

- The **status** of each component, derived from ServerScope's measurements and checks.
- If you turn it on: the **availability per day** over the last 90 days.
- A component in a maintenance window is shown as **Maintenance**.
- Incident titles only if you turn on **Incidenttitels tonen**; otherwise a general description.

Hostnames, IP addresses, URLs and internal notes are **never** shown. Only the public names you choose.

## Creating a page

1. In ServerScope, open **Beheer → Statuspagina's** and click **Statuspagina maken**.
2. Enter a name, a URL part (slug), optionally a description and an accent colour.
3. Choose whether the page is **public** and whether the availability per day is visible.
4. Add up to 50 **components**: servers and domains, each with a public name.

The page is then at `/status/<slug>` of your ServerScope environment. There is also a JSON version at `/status/<slug>.json`, for example for your own dashboard.

## Public or private

A **private** page is only visible to people signed in to ServerScope. Use it for an internal status page for your team.

Read everything in [Public status pages](/en/docs/status-pages).

## Frequently asked questions

### Is the availability an SLA?
No. The availability is calculated from ServerScope's measurements and checks; it is not a contractual SLA.

### How many visitors can the page handle?
The page and the JSON version are limited to 60 requests per minute per visitor.

---
title: Monitoring domain name expiry
seo_title: Domain expiry monitoring through RDAP — ServerScope
description: Get a warning before your domain name registration expires. ServerScope retrieves the expiry date daily through RDAP.
summary: ServerScope retrieves the expiry date of your domain registration daily through RDAP and warns at fixed points in advance (by default 30, 14, 7, 3 and 1 day). This only works if the registry publishes an expiry date; ServerScope does not make one up.
---
# Monitoring domain name expiry

## How it works

- ServerScope asks the registry for the registration's expiry date **daily**, through **RDAP**.
- You get a warning when a **warning point** is passed. By default those are **30, 14, 7, 3 and 1 day** in advance; you set them per domain.
- The incident is a **warning**, and **critical** at 7 days or less.
- If the date passes a next point while the incident is still open, the incident is **escalated**. After renewal it is resolved automatically.

## When the registry gives no date

Not every registry publishes an expiry date through RDAP. The registration status is only called **verified** when the registry returns a date. Otherwise ServerScope cannot warn about that registration expiring. We do not estimate or make up a date.

## Setting it up

1. Open **Domeinen → Domein toevoegen** and enter the domain name.
2. Under **Controles**, tick **Domeinregistratie (RDAP)**.
3. Adjust the **registration expiry warnings (days)** if needed.

Read everything in [Domain monitoring](/en/docs/domain-monitoring).

## Frequently asked questions

### Which data does ServerScope send to the registry?
Only the domain name, for the RDAP lookup.

### Why do I not see an expiry date?
The registry of that domain does not publish an expiry date through RDAP. Then ServerScope cannot warn about it.

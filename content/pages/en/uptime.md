---
title: Uptime monitoring for your website
seo_title: Uptime monitoring with HTTP checks and thresholds — ServerScope
description: Check whether your website is reachable, every 5 minutes by default, with expected status codes, expected content and thresholds against false alarms.
summary: ServerScope checks whether a website responds with an expected status code, every 5 minutes by default (configurable from 1 to 1440 minutes). Only after a number of failed checks in a row is a critical incident opened, and after a number of successful checks in a row it is resolved automatically.
---
# Uptime monitoring for your website

## How the HTTP check works

For each domain, ServerScope checks whether the URL responds with an **expected status code**, and optionally whether an **expected text** is on the page.

| Setting | Default | Meaning |
|---|---|---|
| Interval | 5 minutes | How often the check runs (1–1440 minutes) |
| Timeout | 10 seconds | Maximum wait per request (2–30) |
| Failure threshold | 3 | Failed checks in a row before an incident is opened |
| Recovery threshold | 2 | Successful checks in a row before the outage counts as recovered |
| Expected status codes | `200-399` | For example `200` or `200-299,301` |
| Expected content | – | Optional text that must be on the page (case-sensitive) |
| Follow redirects | on | At most 5 redirects |

## What happens during an outage

- After the number of failed checks in the **failure threshold**, a **critical** incident is opened: "… is unreachable".
- Repeated failures update the same incident; there is no new incident per check.
- After the **recovery threshold** the incident is resolved automatically. You can also get a recovery notification.
- During a **maintenance window** the checks keep running, but no incidents are created.

Notifications go out by e-mail, in the app, to Discord or to a webhook. With a [status page](/en/status-page) you show visitors the availability.

Read everything in [Domain monitoring](/en/docs/domain-monitoring) and [Alerts and incidents](/en/docs/alerts-incidents).

## Frequently asked questions

### Can I start a check right away?
Yes, with **Nu controleren** on the domain page, at most once per minute.

### How do I avoid alerts for a short hiccup?
With the failure threshold: by default an incident is only opened after 3 failed checks in a row. You can set that threshold per domain from 1 to 10.

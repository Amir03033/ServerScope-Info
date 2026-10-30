---
title: Monitoring an SSL certificate
seo_title: SSL certificate monitoring and expiry warnings — ServerScope
description: Get a warning before your SSL certificate expires, and a critical incident for an invalid certificate. By default 30, 14, 7, 3 and 1 day in advance.
summary: ServerScope checks a domain's SSL/TLS certificate every 6 hours by default. You get a warning at fixed points before it expires (by default 30, 14, 7, 3 and 1 day in advance) and a critical incident when the certificate is invalid.
---
# Monitoring an SSL certificate

## What is checked

For a domain with an `https://` address, ServerScope checks:

- whether the certificate is **valid**: not expired, already valid, for the right hostname, trusted and not self-signed;
- when the certificate **expires**.

The check runs every 6 hours by default, or at the HTTP interval if that is longer.

## When you are notified

| Situation | Incident | Severity |
|---|---|---|
| Invalid certificate | Immediately | Critical |
| A warning point before the expiry date is passed | "Certificate of … expires in N days" | Warning; critical at 3 days or less |

- You set the warning points per domain. By default they are **30, 14, 7, 3 and 1 day**.
- If the expiry date passes a next point while the incident is still open, the incident is **escalated**.
- Once the certificate is renewed, the incident is **resolved automatically**.

## Setting it up

1. In ServerScope, open **Domeinen → Domein toevoegen**.
2. Enter the domain name and choose `https://`.
3. Under **Controles**, tick the SSL/TLS certificate, and optionally HTTP, DNS and the domain registration.
4. Adjust the **certificate warnings (days)** if needed.

Notifications go out by e-mail, in the app, to Discord or to a webhook. Read everything in [Domain monitoring](/en/docs/domain-monitoring) and [Alerts and incidents](/en/docs/alerts-incidents).

## Frequently asked questions

### What happens when I renew the certificate?
With a renewed certificate, an open incident is resolved automatically.

### Can I check internal addresses?
No. ServerScope only checks public addresses on the allowed ports (80 and 443 by default). Internal IP addresses are refused.

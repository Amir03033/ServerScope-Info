---
title: Monitoring DNS changes
seo_title: DNS change monitoring for A, AAAA, CNAME, MX, NS and CAA — ServerScope
description: See when your domain's DNS records change, with a history and a notification by e-mail, in the app, to Discord or as a webhook.
summary: ServerScope checks every 15 minutes by default whether a domain name resolves, and tracks changes in the A, AAAA, CNAME, MX, NS and CAA records. A change goes into the DNS history and can send a notification; resolution that keeps failing becomes a critical incident.
---
# Monitoring DNS changes

## What is checked

- Whether the name **resolves**.
- **Changes** in the A, AAAA, CNAME, MX, NS and CAA records.

The check runs every 15 minutes by default, or at the HTTP interval if that is longer.

## What happens on a change

- A DNS change does **not open an incident**. It is stored in the domain's **DNS history**.
- You can get a **notification** for changed records. Each user sets that under **Meldingen**.
- Discord notifications and webhooks can also report DNS changes; for webhooks that is the event `dns.changed`.

## What happens when DNS fails

If resolution fails the number of times in the **failure threshold** in a row (3 by default), a **critical** incident is opened. After the recovery threshold it is resolved automatically.

Read everything in [Domain monitoring](/en/docs/domain-monitoring), [Discord notifications](/en/docs/discord-notifications) and [API and webhooks](/en/docs/api-webhooks).

## Frequently asked questions

### Does a DNS change become an incident?
No. A change goes into the DNS history and can send a notification if you turn that on. Only when the name repeatedly fails to resolve is an incident opened.

### Which record types are tracked?
A, AAAA, CNAME, MX, NS and CAA.

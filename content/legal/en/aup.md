---
title: Acceptable use policy
description: What you may and may not do with ServerScope.
version: 0.1
changes: First draft.
---
<!-- LAWYER: review the consequences in section 4 and the relation to the terms of service. -->

## 1. Why these rules

> [!SUMMARY]
> ServerScope runs checks on servers, websites and domains. Use them only for systems you are allowed to monitor.

ServerScope performs HTTP, TLS, DNS and RDAP checks and receives samples from agents. These rules apply to everyone who uses ServerScope and supplement the terms of service.

## 2. What is not allowed

> [!SUMMARY]
> Monitor nothing without permission, do not use ServerScope to attack or scan, and do not get around limits.

### 2.1 Only monitor with permission

Only monitor servers, websites and domains that are yours, or that their owner has allowed you to monitor.

### 2.2 No attacks or scans

Do not use ServerScope to attack, overload or scan systems for vulnerabilities. This includes page discovery, and choosing intervals or numbers of pages for that purpose.

### 2.3 Do not get around limits

Do not get around the limits of your plan or of the service, for example by using several accounts or teams to monitor more than allowed, or by using the API in ways it was not meant for.

### 2.4 No abuse of notifications

Do not use notifications, webhooks or status pages to send spam or to harass others.

## 3. How ServerScope itself limits abuse

> [!SUMMARY]
> ServerScope blocks checks to internal networks and limits page discovery.

- Checks to internal, local and cloud metadata addresses are blocked, also after a redirect.
- Page discovery is by default limited to your own domain and at most 50 pages, and found pages wait for your approval.
- The API has a limit per token.

## 4. Consequences

> [!SUMMARY]
> In case of abuse we may stop checks or block an account.

If we reasonably suspect that these rules are broken, we may stop checks, (temporarily) block an account or team and, where the law requires it, inform the competent authorities.

## 5. Reporting abuse

Do you see ServerScope being abused? E-mail {company.email}.

---
title: Responsible disclosure
description: How to report a security problem in ServerScope.
version: 0.1
changes: First draft.
---
<!-- LAWYER: review the commitment in section 5 (no legal action). The text follows the main points of the Dutch NCSC guideline on Coordinated Vulnerability Disclosure. -->

## 1. Found a problem?

> [!SUMMARY]
> E-mail it to {company.security_email}. Do not make it public before it is fixed.

We take the security of ServerScope seriously. If you found a vulnerability, please report it to us so we can fix it before others abuse it.

Report to: **{company.security_email}**. Our security.txt file is at [/.well-known/security.txt](/.well-known/security.txt).

## 2. What this covers

> [!SUMMARY]
> The website, the application, the agent and the install script.

- info.serverscope.nl
- the ServerScope application at {app_url}
- the ServerScope agent and the Bash agent
- the install script (`install.sh`) and agent updates

## 3. What to include

> [!SUMMARY]
> Enough to reproduce the problem. No real data of others.

- a description of the problem and where it is;
- the steps to reproduce it;
- your contact details, if you want a reply.

Never include tokens, passwords or other people's data.

## 4. What not to do

> [!SUMMARY]
> No damage, no data of others, no overload.

- do not access, change or delete other people's data; if you gain access by accident, stop and report it;
- no denial of service and no attacks that disrupt the service;
- no social engineering, phishing or physical attacks;
- no automated scans that put load on the service;
- do not make the problem public before it is fixed.

## 5. What we do

> [!SUMMARY]
> We confirm your report, keep you informed and take no legal action if you follow these rules.

- We confirm receipt of your report.
- We keep you informed about the fix.
- If you follow these rules, we will not take legal action.
- If you want, we credit you as the reporter once the problem is fixed.

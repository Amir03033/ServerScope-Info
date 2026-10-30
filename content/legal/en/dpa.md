---
title: Data processing agreement
description: Terms for the personal data ServerScope processes on behalf of customers.
version: 0.1
changes: First draft.
---
<!-- LAWYER: draft based on art. 28 GDPR and the ServerScope code (October 2026). Review in particular: authorisation of sub-processors (article 6), the breach notification period (article 8), audits (article 9) and the relation to the terms of service. -->

## 1. Parties and subject

> [!SUMMARY]
> The customer decides what is monitored; we only process that data for the customer.

1. This data processing agreement belongs to the agreement between the customer (**controller**) and {company.name}, {company.address} (**processor**) about the use of ServerScope.
2. The processor only processes personal data to deliver ServerScope, as described in annex A.

## 2. Duration

> [!SUMMARY]
> As long as the customer uses ServerScope.

This agreement applies as long as the processor processes personal data for the customer.

## 3. Instructions

> [!SUMMARY]
> We only do what the customer asks through the ServerScope settings.

1. The processor only processes personal data on documented instructions from the customer. The settings the customer chooses in ServerScope (such as what is monitored, who receives notifications and which retention periods apply) count as instructions.
2. If the processor believes an instruction breaks the law, it informs the customer immediately.

## 4. Confidentiality

> [!SUMMARY]
> Everyone on our side who works with the data must keep it confidential.

Everyone with access to the personal data on behalf of the processor is bound by confidentiality.

## 5. Security

> [!SUMMARY]
> The security measures are listed in annex C.

The processor takes appropriate technical and organisational measures. Annex C describes the measures built into ServerScope.

## 6. Sub-processors

> [!SUMMARY]
> We only use the sub-processors in annex B and announce changes in advance.

1. The customer authorises the sub-processors in annex B.
2. The processor informs the customer in advance of a new sub-processor. The customer may object.
3. The processor imposes the same obligations on sub-processors as in this agreement.

## 7. Data subject rights

> [!SUMMARY]
> We help the customer when someone exercises their rights.

The processor assists the customer with requests from data subjects, such as access or erasure. Much of this the customer can do in ServerScope itself, for example deleting servers, domains, pages and users and setting retention periods.

## 8. Personal data breaches

> [!SUMMARY]
> We notify the customer of a breach without undue delay.

1. The processor notifies the customer of a personal data breach without undue delay, with the information the customer needs for a possible notification to the supervisory authority.
2. The processor takes measures to limit the consequences.

## 9. Audits

> [!SUMMARY]
> The customer may check that we keep to this agreement.

The processor makes available the information needed to demonstrate compliance with this agreement and cooperates with reasonable audits.

## 10. End

> [!SUMMARY]
> Afterwards we delete the data, unless the law requires keeping it.

After the agreement ends, the processor deletes the customer's personal data, or returns it on request, unless the law requires it to be kept.

## Annex A. Data processed

> [!SUMMARY]
> Which data ServerScope processes for the customer, and about whom.

**Categories of data subjects:** users in the customer's team, recipients of notifications, and people whose data appears on a web page the customer monitors.

| Part | Data |
|---|---|
| Servers (through the agent) | hostname, IP address, operating system, agent version, CPU, memory, swap, disk usage, disk activity, network traffic and network errors, load, number of processes, uptime, and the state of services the customer chooses. No file contents, no log files, no remote commands. |
| Connecting servers | the IP address a server was connected from |
| Website and domain monitoring | URLs, HTTP responses, response times, TLS certificates, DNS records, domain expiry (RDAP) |
| Content monitoring (if the customer turns it on) | the text of the monitored page and the differences between checks; that text may contain personal data |
| Notifications | e-mail addresses of recipients and a delivery log; webhook addresses encrypted |
| Team users | name, e-mail address, role, audit log with IP address and time |

**Retention:** configurable per type of data. Currently: samples {retention.metrics}; domain checks {retention.domain_checks}; page checks {retention.page_checks}; page changes {retention.page_changes}; delivery log {retention.notification_logs}; audit log {retention.audit_logs}.

## Annex B. Sub-processors

> [!SUMMARY]
> Hosting and e-mail.

| Sub-processor | Purpose | Country |
|---|---|---|
| {company.host_app} | Hosting of the application and the database | {company.host_app_country} |
| {company.mail_service} | Sending e-mail (notifications, invitations) | {company.mail_service_country} |

### External information sources

To check domains, ServerScope consults public information sources. These are **not sub-processors**: they **only receive domain names**, no personal data of users.

| Source | Used for |
|---|---|
| DNS resolvers (by default Cloudflare 1.1.1.1, Google 8.8.8.8 and Quad9 9.9.9.9; configurable) | looking up DNS records |
| RDAP: IANA (data.iana.org) and the registries of the domain extensions | looking up a domain's expiry date |

Notifications also go to destinations the customer chooses, such as Discord or their own webhook.

## Annex C. Security measures

> [!SUMMARY]
> What is built into ServerScope to protect data.

- **Team separation:** every team only sees its own data; an ID of another team returns "not found".
- **Tokens and passwords:** passwords, API tokens, agent tokens and enrollment tokens are stored only as hashes and shown once. Enrollment tokens are valid for 60 minutes and work once.
- **Two-factor authentication:** TOTP with recovery codes.
- **Audit log:** administrative actions with user, time and IP address; without secrets.
- **Agent without remote commands:** the agent only reads data, runs as a user without a shell and does not listen on a port.
- **Signed agent updates:** releases are signed with a key kept offline. Servers check the signature with their local key, and a local emergency switch turns updates off.
- **Encryption in transit:** HTTPS; the agent and the install script use TLS 1.2 or newer.
- **Encryption of secrets:** webhook addresses (such as Discord) are stored encrypted.
- **Protection against abuse of checks:** checks to internal, local and cloud metadata addresses are blocked, also after redirects and DNS changes.
- **Rate limits** on the API, enrollment and login.
- **Retention periods:** configurable per type of data.

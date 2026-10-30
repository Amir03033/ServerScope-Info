---
title: Privacy statement
description: Which personal data ServerScope processes, why, for how long, and what your rights are.
version: 0.1
changes: First draft.
---
<!-- LAWYER: draft based on the code of ServerScope and info.serverscope.nl (October 2026). Review in particular the legal bases (section 4), the roles (section 2) and transfers outside the EEA (section 7). -->

## 1. Who we are

> [!SUMMARY]
> {company.name} makes ServerScope. Send privacy questions to {company.privacy_email}.

This privacy statement covers the website info.serverscope.nl and the ServerScope application. The controller is:

{company.name}, {company.address}.

For privacy questions or to exercise your rights: {company.privacy_email}.

## 2. When we are responsible and when the customer is

> [!SUMMARY]
> We are responsible for your account, the website, the waiting list and the contact form. For what a customer monitors with ServerScope, the customer is responsible and we process on their behalf.

**We are the controller** for:

- accounts and teams in ServerScope;
- security of the service (sessions and the audit log);
- the website info.serverscope.nl, the waiting list and the contact form.

**We are a processor** for the data a customer collects with ServerScope: server samples, website and domain checks, and notifications to recipients the customer chooses. For that we sign a data processing agreement with the customer. If you want to know what a customer does with your data, contact that customer.

## 3. What data we process

> [!SUMMARY]
> Only what is needed to run and secure ServerScope. The agent reads no files and runs no commands.

### 3.1 Accounts

Name, e-mail address, password (stored only as a hash), two-factor authentication data (the secret encrypted, the recovery codes as hashes), your notification preferences, the teams you belong to and your role in them.

### 3.2 Sessions in the application

When you are logged in to the ServerScope application, we store the IP address and user agent of your browser with your session.

### 3.3 Audit log

For administrative actions in the application we store who performed the action, what happened, the time and the IP address. Secrets such as tokens and passwords are never stored in the audit log.

### 3.4 Customer servers (through the agent)

The agent sends: hostname, IP address, operating system, agent version, CPU, memory, swap, disk usage, disk activity, network traffic and network errors, load, number of processes, uptime, and the state of services the customer chooses. The agent reads **no file contents and no log files** and executes **no remote commands**.

When a server is connected with an enrollment token, we store the IP address the server connected from.

### 3.5 Website and domain monitoring

URLs, HTTP responses, response times, TLS certificates, DNS records and domain expiry dates (through RDAP). With **content monitoring** we store the text of the monitored page and the differences between checks. If that page contains personal data, that is stored too.

### 3.6 Notifications

E-mail addresses of recipients and a log of sent notifications. The customer's webhook addresses (for example Discord) are stored encrypted.

### 3.7 Website info.serverscope.nl

- **Waiting list:** your e-mail address, language, chosen plan, the consent text you saw and the time of your consent.
- **Contact form:** your name, e-mail address, subject and message. We forward them by e-mail; the website does not store the message.
- **Visitor counter:** only the page path, the day and the number of views. No IP address, no cookie and nothing about you.
- **"Was this helpful?":** only your answer and optional comment.

The website stores no IP address or user agent of visitors.

## 4. Why, and on what legal basis

> [!SUMMARY]
> Your account because we must deliver the service, security because that is a legitimate interest, and the waiting list because you consent.

| Processing | Purpose | Legal basis |
|---|---|---|
| Accounts and teams | Delivering the service | Performance of the contract |
| Sessions and audit log | Security and preventing abuse | Legitimate interest |
| Enrollment IP of servers | Securing server enrollment | Legitimate interest |
| Waiting list | Telling you when registration opens | Consent |
| Contact form | Answering your question | Legitimate interest, or steps prior to a contract |
| Customers' monitoring data | Monitoring for the customer | On behalf of the customer (data processing agreement) |

You can withdraw your consent for the waiting list at any time with the personal link you received after signing up, or by e-mailing {company.privacy_email}.

## 5. How long we keep data

> [!SUMMARY]
> Retention periods are set per type of data. Where no period is listed, the data is not deleted automatically.

| Data | Retention |
|---|---|
| Waiting list | {retention.waitlist} |
| Contact messages (in our mailbox) | {retention.contact} |
| Accounts after cancellation | {retention.accounts} |
| Server samples | {retention.metrics} |
| Domain checks | {retention.domain_checks} |
| Page checks | {retention.page_checks} |
| Page changes (content) | {retention.page_changes} |
| Notification log | {retention.notification_logs} |
| Read notifications | {retention.notifications} |
| Audit log | {retention.audit_logs} |
| Service events | {retention.service_events} |
| Forecasts | {retention.predictions} |

## 6. Who we share data with

> [!SUMMARY]
> Only our hosting provider and e-mail service. We sell nothing and use no third-party tracking.

- **Hosting of the application:** {company.host_app} ({company.host_app_country}).
- **Hosting of this website:** {company.host_site} ({company.host_site_country}).
- **E-mail:** {company.mail_service} ({company.mail_service_country}), for notifications, invitations and the contact form.

Notifications also go to the destinations a customer sets up, such as Discord or their own webhook.

We use no third-party advertising or analytics services.

## 7. Transfers outside the European Economic Area

> [!SUMMARY]
> That depends on where our service providers are located; see section 6.

<!-- LAWYER: if a hosting or e-mail provider is outside the EEA, state the transfer mechanism here (adequacy decision or standard contractual clauses). -->

We only transfer personal data outside the EEA when there is a valid basis under the GDPR.

## 8. Security

> [!SUMMARY]
> Passwords and tokens are stored only as hashes, connections use HTTPS, and every customer sees only their own data.

- Passwords, API tokens, agent tokens and enrollment tokens are stored only as hashes.
- Two-factor authentication (TOTP with recovery codes) is available.
- All connections use HTTPS; the agent uses TLS 1.2 or newer.
- Every team only sees its own data.
- Administrative actions are recorded in the audit log.
- Webhook addresses are stored encrypted.

## 9. Your rights

> [!SUMMARY]
> You may access, correct or delete your data, and complain to the Dutch Data Protection Authority.

You have the right of access, rectification, erasure, restriction of processing, data portability and objection, and the right to withdraw your consent. Send your request to {company.privacy_email}.

If you disagree with how we handle your data, you can file a complaint with the Dutch Data Protection Authority (Autoriteit Persoonsgegevens, autoriteitpersoonsgegevens.nl).

## 10. Changes

> [!SUMMARY]
> When this statement changes, the new version is published here with its effective date and a summary of the changes.

Earlier versions remain available through "Earlier versions" at the top of this page.

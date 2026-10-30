---
title: Cookie statement
description: Which cookies info.serverscope.nl and ServerScope use, and why there is no cookie banner.
version: 0.1
changes: First draft.
---
<!-- LAWYER: check that all cookies below fall under the exemption for strictly necessary cookies (art. 11.7a(3) Dutch Telecommunications Act / art. 5(3) ePrivacy Directive). -->

## 1. In short

> [!SUMMARY]
> We only use cookies that the site and the application need to work. No tracking, no advertising. That is why there is no cookie banner.

## 2. Cookies on info.serverscope.nl

> [!SUMMARY]
> Two functional cookies: a session cookie and a security cookie for forms.

| Name | Purpose | Lifetime |
|---|---|---|
| `{cookie.session}` | Keeps your session, for example for messages after submitting a form and for logging in to the admin panel | {cookie.lifetime} minutes after your last visit |
| `XSRF-TOKEN` | Protects forms (waiting list, contact, "Was this helpful?") against abuse from other websites | {cookie.lifetime} minutes after your last visit |

The session itself is stored as a file on our server. It contains no IP address and no user agent.

### Theme choice (not a cookie)

If you choose a light or dark theme with the button in the header, we keep that choice in your browser's local storage (`ss-theme`). It is never sent to our server. You remove it by clearing the site data in your browser.

### Visitor counter (not a cookie)

We count how often a page is viewed: only the path, the day and the number. We use no cookie for this and store nothing about you.

## 3. Cookies in the ServerScope application

> [!SUMMARY]
> The same two functional cookies, plus a "remember me" cookie if you tick that box yourself.

| Name | Purpose | When |
|---|---|---|
| Session cookie | Keeping you logged in | Always after logging in |
| `XSRF-TOKEN` | Protecting forms | Always |
| `remember_web_…` | Staying logged in on this device | Only if you tick "remember me" when logging in |

## 4. Why there is no cookie banner

> [!SUMMARY]
> Cookies that are strictly necessary to deliver the service need no consent.

The law requires consent for cookies, except for cookies that are strictly necessary to deliver the service you ask for. All cookies above are of that kind. We use no analytics, tracking or advertising cookies, and no third-party services that set cookies. So we do not ask for consent and show no banner.

## 5. Questions

Questions about cookies or privacy: {company.privacy_email}. How we handle personal data is described in the [privacy statement](/en/privacy).

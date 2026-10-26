---
order: 2
category: monitoring
icon: globe
description: Monitor HTTP availability, SSL certificates, DNS records and domain registration, with the settings and when an incident is opened.
---
# Domain monitoring

ServerScope can run four checks for each domain. You choose per domain which ones are enabled:

| Check | What is checked | How often (default) |
|---|---|---|
| **HTTP availability** | Whether the URL answers with an expected status code (and optionally expected content) | Every 5 minutes (configurable 1–1440) |
| **SSL/TLS certificate** | Validity and expiry date of the certificate (`https` only) | Every 6 hours (or the HTTP interval, if longer) |
| **DNS records** | Whether the name resolves, and changes in A, AAAA, CNAME, MX, NS and CAA | Every 15 minutes (or the HTTP interval, if longer) |
| **Domain registration (RDAP)** | The registration expiry date at the registry | Daily |

Only an **administrator** can add and change domains.

## Adding a domain

1. Open [Domeinen → Domein toevoegen]({app_url}/domains/create) (Domains → Add domain).
2. Enter the **Domeinnaam** (domain name), for example `example.com` or `www.example.nl`. A full URL is fine too; only the hostname is stored. Internationalised domain names are supported.
3. Under **Te controleren URL** (URL to check) choose `https://` or `http://` and optionally a path (default `/`).
4. Under **Controles** (checks) tick what you want to monitor.
5. Review the settings below and click **Domein toevoegen**.

The first results appear on the domain page within a few minutes. **Nu controleren** (Check now) starts a check immediately (at most once per minute).

## Settings

| Field | Default | Meaning |
|---|---|---|
| Interval (minuten) | `5` | How often the HTTP check runs (1–1440 minutes). |
| Time-out (seconden) | `10` | Maximum wait per request (2–30 seconds). |
| Faaldrempel (failure threshold) | `3` | Consecutive failed checks before an incident is opened (1–10). Applies to HTTP and DNS. |
| Hersteldrempel (recovery threshold) | `2` | Consecutive successful checks before the outage counts as recovered (1–10). |
| Verwachte statuscodes (expected status codes) | `200-399` | For example `200` or `200-299,301`. |
| Verwachte inhoud (expected content) | – | Optional text that must appear on the page. Case-sensitive; only the first 1 MB is searched. |
| Redirects volgen (follow redirects) | on | Up to 5 redirects; every hop is validated again. |
| Certificaatwaarschuwingen (certificate warnings, days) | `30, 14, 7, 3, 1` | When you are warned before the certificate expires. |
| Vervalwaarschuwingen registratie (registration warnings, days) | `30, 14, 7, 3, 1` | The same for the domain registration. |

> [!NOTE]
> ServerScope only checks **public** addresses on allowed ports (80 and 443 by default). Internal IP addresses are refused, also when a redirect or DNS record points to them.

## When is an incident opened?

| Check | Incident | Severity |
|---|---|---|
| HTTP | After *failure threshold* consecutive failures: “… is onbereikbaar” (unreachable) | Critical |
| SSL | Invalid certificate (expired, not yet valid, hostname mismatch, untrusted or self-signed) | Critical |
| SSL expiry | When a warning threshold is crossed: “Certificaat van … verloopt over N dagen” | Warning; critical at 3 days or less |
| DNS | After *failure threshold* consecutive failed resolutions | Critical |
| Registration | When a warning threshold is crossed | Warning; critical at 7 days or less |

- Repeated failures update the same incident; there is no new incident per check.
- If an expiry date crosses the next (lower) threshold while the incident is still open, the incident is **escalated**.
- On recovery (after the *recovery threshold*, or a renewed certificate) the incident is resolved automatically.
- **DNS changes** do not open an incident, but are stored in the DNS history and can send a notification (a per-user setting).

> [!WARNING]
> Not every registry publishes an expiry date over RDAP. The registration status is only **verified** when the registry returns a date; otherwise ServerScope cannot warn about that registration expiring.

## Pausing and maintenance

- **Pauzeren** (Pause) stops all checks for a domain; **Hervatten** (Resume) enables them again.
- A **maintenance window** (start, end and reason under *Bewerken*) keeps the checks running but opens no incidents. Times use the Europe/Amsterdam timezone.

## Troubleshooting

| What you see | What to do |
|---|---|
| HTTP fails with an unexpected status code | Adjust **Verwachte statuscodes**, or check whether the site redirects while **Redirects volgen** is off. |
| *Deze URL is niet toegestaan* (URL not allowed) or a blocked address | The hostname points to an internal or reserved IP address, or to a port that is not allowed. |
| Timeouts | Increase the **Time-out** or check that the server is reachable from the internet. |
| Expected content not found | The text is case-sensitive and must appear in the first 1 MB of the response. |
| No registration date | The registry does not publish an RDAP expiry date (see the warning above). |

Notifications for these incidents follow the rules in [Alerts and incidents](alerts-incidents).

---
order: 3
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

1. Open [Domains → Add domain]({app_url}/domains/create).
2. Enter the **Domain name**, for example `example.com` or `www.example.nl`. A full URL is fine too; only the hostname is stored. Internationalised domain names are supported.
3. Under **Te controleren URL** (URL to check) choose `https://` or `http://` and optionally a path (default `/`).
4. Under **Checks** tick what you want to monitor.
5. Review the settings below and click **Add a domain**.

The first results appear on the domain page within a few minutes. **Check now** starts a check immediately (at most once per minute).

## Settings

| Field | Default | Meaning |
|---|---|---|
| Interval (minutes) | `5` | How often the HTTP check runs (1–1440 minutes). |
| Timeout (seconds) | `10` | Maximum wait per request (2–30 seconds). |
| Failure threshold | `3` | Consecutive failed checks before an incident is opened (1–10). Applies to HTTP and DNS. |
| Recovery threshold | `2` | Consecutive successful checks before the outage counts as recovered (1–10). |
| Expected status codes | `200-399` | For example `200` or `200-299,301`. |
| Expected content | – | Optional text that must appear on the page. Case-sensitive; only the first 1 MB is searched. |
| Follow redirects | on | Up to 5 redirects; every hop is validated again. |
| Certificate warnings (days) | `30, 14, 7, 3, 1` | When you are warned before the certificate expires. |
| Registration expiry warnings (days) | `30, 14, 7, 3, 1` | The same for the domain registration. |

> [!NOTE]
> ServerScope only checks **public** addresses on allowed ports (80 and 443 by default). Internal IP addresses are refused, also when a redirect or DNS record points to them.

## When is an incident opened?

| Check | Incident | Severity |
|---|---|---|
| HTTP | After *failure threshold* consecutive failures: “… is unreachable” | Critical |
| SSL | Invalid certificate (expired, not yet valid, hostname mismatch, untrusted or self-signed) | Critical |
| SSL expiry | When a warning threshold is crossed | Medium; critical at 3 days or less |
| DNS | After *failure threshold* consecutive failed resolutions | Critical |
| Registration | When a warning threshold is crossed | Medium; critical at 7 days or less |

- Repeated failures update the same incident; there is no new incident per check.
- If an expiry date crosses the next (lower) threshold while the incident is still open, the incident is **escalated**.
- On recovery (after the *recovery threshold*, or a renewed certificate) the incident is resolved automatically.
- **DNS changes** do not open an incident, but are stored in the DNS history and can send a notification (a per-user setting).
- If an external **probe** is set up, an HTTP outage is first confirmed from a second location before the incident opens. A probe only checks when there is an outage to confirm.

## Certificate history

The domain page keeps every certificate ServerScope has seen for the domain, with its issuer, validity and when it was in use. Handy to see when a certificate was renewed, or whether another issuer suddenly appeared.

## More pages of the same website

The HTTP check covers one URL per domain. Add more pages, such as `/pricing` or `/api/health`, under the tab **Website & pages**; see [Website monitoring](website-monitoring).

> [!WARNING]
> Not every registry publishes an expiry date over RDAP. The registration status is only **verified** when the registry returns a date; otherwise ServerScope cannot warn about that registration expiring.

## Pausing and maintenance

- **Pause** stops all checks for a domain; **Resume** enables them again.
- A **maintenance window** (start, end and reason under *Bewerken*) keeps the checks running but opens no incidents. Times use the Europe/Amsterdam timezone.

## Troubleshooting

| What you see | What to do |
|---|---|
| HTTP fails with an unexpected status code | Adjust **Expected status codes**, or check whether the site redirects while **Follow redirects** is off. |
| *This URL is not allowed* or a blocked address | The hostname points to an internal or reserved IP address, or to a port that is not allowed. |
| Timeouts | Increase the **Timeout** or check that the server is reachable from the internet. |
| Expected content not found | The text is case-sensitive and must appear in the first 1 MB of the response. |
| No registration date | The registry does not publish an RDAP expiry date (see the warning above). |

Notifications for these incidents follow the rules in [Alerts and incidents](alerts-incidents).

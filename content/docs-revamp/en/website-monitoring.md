---
order: 4
icon: globe
category: monitoring
feature: website-monitoring
description: Monitor a website page by page, discover pages automatically, track content changes and read the website overview.
---
# Website monitoring

A domain check watches one URL. **Website monitoring** goes further: you add the pages that matter, such as `/pricing`, `/login` or `/api/health`, and each page gets its own checks, thresholds, notifications and history.

Open a domain under [Domains]({app_url}/domains) and use the tabs **Domain**, **Website & pages** and **Discover**. Team owners and administrators add pages and run discovery.

> [!NOTE]
> The number of pages per website depends on your plan (Free: 15). See the pricing page.

## Adding pages

1. Open the domain and choose the tab **Website & pages**.
2. Click **Add pages**.
3. Under **URLs (one per line)** enter one page per line: a path such as `/pricing` or a full URL on the same domain.
4. Choose the checks and settings (see below) and save.

A new page has no status until its first check has run.

## What is checked per page

| Setting | Default | Meaning |
|---|---|---|
| Interval | 5 minutes | How often the page is checked. |
| Timeout | 10 seconds | Maximum wait per request. |
| Failure threshold / Recovery threshold | 2 / 2 | Consecutive failures before an incident; consecutive successes before recovery. |
| Expected status codes | `200-399` | For example `200` or `200-299,301`. |
| Maximum response time (ms) | – | Slower is **degraded**: shown, but not counted as an outage. |
| Expected final URL after redirects | – | Where the page must end up, for example after a login redirect. |
| Must contain / Must not contain | – | Text that must appear, or must **not** appear (for example `Fatal error`). |
| Header checks | – | A header must be present, absent, equal to or contain a value, for example `Strict-Transport-Security`. |
| Severity on outage | High | Severity of the incident when the page fails. |
| Availability target (%) | website or report target | Used in [reports](reports-system-status). |

When a check fails, ServerScope records **why**: a network error, a timeout, a DNS error, a TLS error, a blocked destination, a redirect error, an unexpected status code, or a failed content or header check.

> [!TIP]
> **Pause** stops a page's checks without losing its history. Use **Check now** after a deployment.

## Discovering pages automatically

On the **Discover** tab ServerScope finds pages for you:

1. Check the settings: **Max. pages** (default 50), **Max. link depth**, **Only these paths** and **Exclude** (patterns with `*`), **Extra sitemap URLs** and **Include subdomains**.
2. Click **Discover now**. Discovery runs in the background.
3. ServerScope reads `robots.txt`, XML sitemaps (also sitemap indexes and compressed sitemaps) and internal links.
4. Found pages wait for your approval. Select them and choose **Approve and monitor** or **Reject**.

Discovery is safe by default: only your own domain, at most 50 pages, a depth of at most 2, limits on requests and time, and nothing is monitored without your approval. More pages, a greater depth, subdomains or monitoring straight away must be confirmed with **I confirm a broader discovery than the default**.

> [!NOTE]
> Every check and discovery request uses the same protection as domain monitoring: internal, local and cloud-metadata addresses are refused, also after a redirect or a changed DNS answer.

## Tracking content changes

Turn on **Track content changes** for a page and ServerScope compares its text on every check:

- The page shows what was **added** and **removed**.
- Dates, times, counters and session codes are ignored. With **Compare only this part** you compare one part of the page (a CSS selector); with **Exclude (dynamic blocks, one selector per line)** you leave parts out.
- A change is **never** an outage by itself. Acknowledge an expected change with one click.
- Turn on **Notify on a significant content change** to get a notification (and the `page.changed` webhook event) when a large part of the text changes.

With **Watch headers for changes (configuration drift)** you also follow chosen response headers, so you notice when, for example, a security header disappears after a configuration change.

## The website overview

The **Website & pages** tab starts with an overview of the whole website:

- how many pages are healthy, degraded or down, and which were not checked recently;
- the average response time and the p50, p95 and p99 values;
- availability per day, active incidents and recent changes to content, DNS and certificates.

The page list can be searched, sorted and filtered. Select pages to approve, reject, pause, resume, check or delete them, or change their interval, all at once.

## One cause, one incident

When several pages fail **for the same reason** (for example the whole site returns a TLS error), ServerScope opens **one website incident** with the affected pages underneath. A single page that returns a `404` stays its own incident. See [Dependencies and grouped incidents](dependencies) for grouping across servers and websites.

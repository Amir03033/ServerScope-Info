---
order: 6
icon: status
description: Create a public status page for your servers and domains, decide what is visible, and share the page.
---
# Public status pages

A status page shows customers or colleagues whether your services work, without them needing an account. For each component the page shows the derived status and, if you want, the daily availability over the last 90 days.

Only an **administrator** can manage status pages.

## Creating a status page

1. Open [Administration → Status pages]({app_url}/status-pages) and click **Create status page**.
2. Fill in:
   - **Name**, for example `Acme status`
   - **URL part** (the *slug*): the last part of the URL, lower-case letters, digits and hyphens only (max. 60 characters), for example `acme`
   - **Description**
   - **Accent colour** in the form `#38bdf8`
3. Choose the visibility:
   - **Public (visible without signing in)**
   - **Show availability per day (90 days)**
   - **Show incident titles**; otherwise a generic description is shown
   - optionally an animated **3D picture** of the components above the status (uses WebGL; only public data)
4. Under **Components**, use **Add component** for each server or domain you want to show. Pick the **Monitor** and a **Public name** (for example `Website` instead of `web-01`) and optionally a short description. The order determines the display (up to 50 components).
5. Click **Create status page**.

> [!IMPORTANT]
> The page only shows the names you choose, the status and the availability. Hostnames, IP addresses, URLs and internal notes are **never** shown. Incident titles may contain internal names; only enable **Show incident titles** if that is acceptable.

## Sharing the page

The status page is available at:

```text
{app_url}/status/<slug>
```

There is also a JSON version, for example for your own dashboard or monitor:

```text
{app_url}/status/<slug>.json
```

Both addresses are limited to 60 requests per minute per visitor.

## Status badges

A public status page also has live **badges** for your README or website. The edit screen of the status page shows ready-made Markdown under **Badges**.

```markdown
![Status]({app_url}/status/<slug>/badge.svg)
```

- One badge for the whole page (`/status/<slug>/badge.svg`) or one per component (`/status/<slug>/badge/<component>.svg`).
- Options: `?theme=light` or `?theme=dark`, `?lang=en` or `?lang=nl`, and `?label=` for your own text (up to 40 characters).
- Badges only show the public name and status. They are cached for 60 seconds and do not animate when *reduce motion* is on.
- Badges only exist for public pages.

## Private pages

If **Public** is off, visitors must be logged in to ServerScope; otherwise they are sent to the login page. Use this for an internal status page for your team.

## Status and maintenance

- A component in a **maintenance window** is shown as *Maintenance*.
- Availability is calculated from ServerScope's measurements and checks; it is not a contractual SLA.

## Troubleshooting

| What you see | Fix |
|---|---|
| An error on the URL-deel field | Choose another slug; every slug is unique. `create`, `admin` and `api` are not allowed. |
| Visitors see the login page | The page is not set to **Public**. |
| A component is missing | Empty rows are ignored; check that you selected a monitor and entered a public name. |

---
order: 3
category: integrations
icon: status
description: Create a public status page for your servers and domains, decide what is visible, and share the page.
---
# Public status pages

A status page shows customers or colleagues whether your services work, without them needing an account. For each component the page shows the derived status and, if you want, the daily availability over the last 90 days.

Only an **administrator** can manage status pages.

## Creating a status page

1. Open [Beheer → Statuspagina's]({app_url}/status-pages) (Admin → Status pages) and click **Statuspagina maken** (create status page).
2. Fill in:
   - **Naam** (name), for example `Acme status`
   - **URL-deel** (the *slug*): the last part of the URL, lower-case letters, digits and hyphens only (max. 60 characters), for example `acme`
   - **Omschrijving** (description, optional)
   - **Accentkleur** (accent colour) in the form `#38bdf8`
3. Choose the visibility:
   - **Publiek (zonder inloggen zichtbaar)**: public, visible without logging in
   - **Beschikbaarheid per dag tonen (90 dagen)**: show daily availability (90 days)
   - **Incidenttitels tonen**: show incident titles; otherwise a generic description is shown
4. Under **Onderdelen** (components), use **Onderdeel toevoegen** (add component) for each server or domain you want to show. Pick the **Monitor** and a **Publieke naam** (public name, for example `Website` instead of `web-01`) and optionally a short description. The order determines the display (up to 50 components).
5. Click **Statuspagina maken**.

> [!IMPORTANT]
> The page only shows the names you choose, the status and the availability. Hostnames, IP addresses, URLs and internal notes are **never** shown. Incident titles may contain internal names; only enable **Incidenttitels tonen** if that is acceptable.

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

## Private pages

If **Publiek** is off, visitors must be logged in to ServerScope; otherwise they are sent to the login page. Use this for an internal status page for your team.

## Status and maintenance

- A component in a **maintenance window** is shown as *Onderhoud* (maintenance).
- Availability is calculated from ServerScope's measurements and checks; it is not a contractual SLA.

## Troubleshooting

| What you see | Fix |
|---|---|
| An error on the URL-deel field | Choose another slug; every slug is unique. `create`, `admin` and `api` are not allowed. |
| Visitors see the login page | The page is not set to **Publiek**. |
| A component is missing | Empty rows are ignored; check that you selected a monitor and entered a public name. |

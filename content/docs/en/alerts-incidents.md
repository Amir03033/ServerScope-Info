---
order: 1
category: incidents
icon: bell
description: How alert rules work, how to investigate an incident and follow its timeline, and how notifications are delivered.
---
# Alerts and incidents

ServerScope distinguishes between an **alert** and an **incident**:

- An **alert** is the state of one alert rule on one server (for example “disk usage ≥ 90% on `/var`”).
- An **incident** is the case people work on. It is opened from an alert, an agent going offline, or a domain check (HTTP, SSL, DNS, registration). An administrator can also create an incident manually.

Repeated measurements or failed checks for the same problem update **the same incident** (the *Aantal keer* / occurrence counter); there is no new incident per measurement.

## Alert rules

Alert rules apply to **all servers**. Administrators manage them under [Beheer → Alertregels]({app_url}/alert-rules) (Admin → Alert rules).

| Field | Options |
|---|---|
| Metric | CPU-gebruik (CPU), RAM-gebruik (memory), Schijfgebruik (disk, per mount point) or Agent offline |
| Voorwaarde (condition) | `>=`, `>`, `<=` or `<` |
| Drempelwaarde (threshold) | 0–100 (%) for resources; 2–1440 minutes of silence for *Agent offline* |
| Vereiste duur (required duration, minutes) | 1–1440: how long the condition must hold before the alert fires |
| Ernst van het incident (incident severity) | Waarschuwing (warning) or Kritiek (critical); Agent offline defaults to critical |

There is one rule out of the box: **Agent offline**, 5 minutes, critical.

To create a rule:

1. Open [Alert rules → new rule]({app_url}/alert-rules/create).
2. Give it a recognisable name, for example `Disk almost full`.
3. Choose the metric, condition and threshold, for example *Schijfgebruik* `>=` `90`.
4. Choose the required duration, for example `10` minutes, to ignore short spikes.
5. Choose the severity and save.

Active alerts are listed under [Alerts]({app_url}/alerts). No alerts are raised for a server during its maintenance window.

## Severity and status

| Severity | Meaning |
|---|---|
| Info | For information |
| Waarschuwing (warning) | Needs attention |
| Kritiek (critical) | Act now |

An incident moves through these statuses:

| Status | Meaning | Next step |
|---|---|---|
| Open | New, not picked up yet | Bevestigen (acknowledge), Onderzoek starten (investigate) or Oplossen (resolve) |
| Bevestigd (acknowledged) | Someone has seen it | Investigate or resolve |
| In onderzoek (investigating) | Being worked on | Acknowledge or resolve |
| Opgelost (resolved) | The problem is gone | Heropenen (reopen) or Sluiten (close), administrators |
| Gesloten (closed) | Finally dealt with | – |

**Active** means Open, Acknowledged or Investigating.

## Investigating an incident

1. Open [Incidenten]({app_url}/incidents). Active incidents are shown by default. Filter by state (active, unacknowledged, resolved, closed, all), severity and source, or search by title.
2. Open the incident. The header shows the title, severity, source, the affected server or domain and the times.
3. Click **Bevestigen** (acknowledge) when you pick it up. The time to first response counts towards MTTA in the reports.
4. Assign the incident to a colleague or yourself.
5. Click **Onderzoek starten** (start investigation) and record findings with **Notitie toevoegen** (add note).
6. Click **Incident oplossen** (resolve incident) when the problem is fixed, optionally with an explanation.

> [!TIP]
> If the problem recovers by itself (the server reports again, the site answers again), ServerScope resolves the incident **automatically**. The timeline then shows *Automatisch hersteld* (recovered automatically).

## Following the timeline

Every incident has a timeline with all events in order, with time and (for manual actions) the user:

| Event | When |
|---|---|
| Incident geopend (opened) | The problem was first detected |
| Ernst gewijzigd (severity changed) | Automatically (e.g. a lower expiry threshold) or by an administrator |
| Bevestigd / Onderzoek gestart | A user picked up the incident |
| Toegewezen (assigned) | The incident was assigned to someone |
| Notitie (note) | A user added a note |
| Geëscaleerd (escalated) | The incident stayed unacknowledged too long or another threshold was crossed |
| Automatisch hersteld / Opgelost | The problem was fixed |
| Incident heropend (reopened) | The problem came back |
| Gesloten (closed) | Closed by an administrator |

## Resolved, reopened and escalation

- If a problem recurs within **60 minutes** after recovery, the previous incident is **reopened** instead of opening a new one.
- An open incident that nobody acknowledges is **escalated** after 15 minutes (critical) or 120 minutes (warning). Info incidents do not escalate.
- **Closed** incidents cannot be reopened; a new problem then becomes a new incident.

## How notifications are delivered

Notifications are only sent when an incident is **opened, reopened, escalated or resolved**, never for every failed check. Each user configures this under [Profiel → Meldingen]({app_url}/profile) (Profile → Notifications):

| Setting | Meaning |
|---|---|
| E-mail | Notifications by e-mail (requires the host to have configured e-mail) |
| In de app (in the app) | Notifications under **Meldingen** in the sidebar |
| Herstelmeldingen (recovery notifications) | Also notify when an incident is resolved |
| DNS-wijzigingen (DNS changes) | Notify on changed A, AAAA, CNAME, MX, NS or CAA records |
| Minimale ernst (minimum severity) | Everything, warnings and critical, or critical only |

Use **Testmelding versturen** (send test notification) to check your settings.

To prevent notification floods:

- An incident that reopens within **15 minutes** of the previous notification does not notify again (*suppressed*).
- DNS changes of the same domain are reported at most once per **30 minutes**.
- Each user receives at most **30 e-mails per hour**; above that only the in-app notification remains.

**Meldingen → Verzendgeschiedenis** (delivery history) shows whether each notification was sent, failed or suppressed.

Administrators can also forward events to [Discord](discord-notifications) or to their own system with [webhooks](webhooks).

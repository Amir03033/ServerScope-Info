---
order: 4
icon: bell
description: How alert rules and the four severity levels work, how to work on an incident and follow its timeline, and how notifications are delivered.
---
# Alerts and incidents

ServerScope distinguishes between an **alert** and an **incident**:

- An **alert** is the state of one alert rule on one server (for example “disk usage ≥ 90% on `/var`”).
- An **incident** is the case people work on. It is opened from an alert, an agent going offline, a stopped service, a domain or page check (HTTP, SSL, DNS, registration), a late or failed scheduled job, a capacity forecast or an error spike in the logs. An administrator can also create an incident manually.

Repeated measurements or failed checks for the same problem update **the same incident** (the *Occurrences* counter); there is no new incident per measurement.

## Alert rules

Alert rules apply to **all servers**. Administrators manage them under [Administration → Alert rules]({app_url}/alert-rules).

| Field | Options |
|---|---|
| Metric | CPU usage, RAM usage, Disk usage (per mount point), Agent offline, or errors in the logs per 5 minutes |
| Condition | `>=`, `>`, `<=` or `<` |
| Threshold | 0–100 (%) for resources; 2–1440 minutes of silence for *Agent offline* |
| Required duration (minutes) | 1–1440: how long the condition must hold before the alert fires |
| Incident severity | Low, Medium, High or Critical; Agent offline defaults to Critical |
| Recovery confirmation (measurements) | How many good measurements in a row are needed before the alert counts as recovered; avoids a recovery notification during a brief dip |

There is one rule out of the box: **Agent offline**, 5 minutes, critical.

To create a rule:

1. Open [Alert rules → new rule]({app_url}/alert-rules/create).
2. Give it a recognisable name, for example `Disk almost full`.
3. Choose the metric, condition and threshold, for example *Disk usage* `>=` `90`.
4. Choose the required duration, for example `10` minutes, to ignore short spikes.
5. Choose the severity and save.

Active alerts are listed under [Alerts]({app_url}/alerts). No alerts are raised for a server during its maintenance window.

## Severity and status

One scale of four levels is used for alert rules, incidents, website pages, scheduled jobs, notification preferences and webhooks:

| Severity | Meaning | Escalated when not acknowledged after |
|---|---|---|
| Low | For information | never |
| Medium | Needs attention | 120 minutes |
| High | Act soon | 30 minutes |
| Critical | Act now | 15 minutes |

An incident moves through these statuses:

| Status | Meaning | Next step |
|---|---|---|
| Open | New, not picked up yet | Acknowledge, Start investigation or Resolve |
| Acknowledged | Someone has seen it | Investigate or resolve |
| Investigating | Being worked on | Acknowledge or resolve |
| Resolved | The problem is gone | Reopen or Close (administrators) |
| Closed | Finally dealt with | – |

**Active** means Open, Acknowledged or Investigating.

## Working on an incident

1. Open [Incidents]({app_url}/incidents). Active incidents are shown by default. Filter by state (active, unacknowledged, resolved, closed, all), severity and source, or search by title.
2. Open the incident. The overview shows the title, severity, source, the affected resources, the key figures and a step bar.
3. Click **Acknowledge** when you pick it up. The time to first response counts towards MTTA in the reports. On a phone you can also swipe the incident in the list, or tap **Acknowledge** in a chat message.
4. Assign the incident to a colleague or yourself.
5. Click **Start investigation** and record findings with **Add note**.
6. Click **Resolve incident** when the problem is fixed and describe in a few words what the cause was and what was done. This is required when you resolve an incident by hand.

The incident also shows how quickly the problem was detected (**MTTD**, from the first failed check to the incident), how often the same problem occurred before, an automatic **root cause analysis**, the **Time Machine** and the logs around the incident. See [Investigating incidents and postmortems](incident-investigation).

An incident that is part of a larger outage shows its **root incident** and why it was grouped; see [Dependencies and grouped incidents](dependencies).

> [!TIP]
> If the problem recovers by itself (the server reports again, the site answers again), ServerScope resolves the incident **automatically**. The timeline then shows *Recovered automatically*.

## Following the timeline

Every incident has a timeline with all events in order, with time and (for manual actions) the user:

| Event | When |
|---|---|
| Incident opened | The problem was first detected |
| Severity changed | Automatically (e.g. a lower expiry threshold) or by an administrator |
| Acknowledged / Investigating | A user picked up the incident |
| Assigned | The incident was assigned to someone |
| Note | A user added a note |
| Escalated | The incident stayed unacknowledged too long or another threshold was crossed |
| Recovered automatically / Resolved | The problem was fixed |
| Incident reopened | The problem came back |
| Closed | Closed by an administrator |

Deployments around the incident are shown on the timeline as well.

## Resolved, reopened and escalation

- If a problem recurs within **60 minutes** after recovery, the previous incident is **reopened** instead of opening a new one.
- An open incident that nobody acknowledges is **escalated** after 15 minutes (Critical), 30 minutes (High) or 120 minutes (Medium). Low incidents do not escalate. For a grouped outage only the root incident escalates. Escalations also go to the team's escalation contacts (up to 5).
- **Closed** incidents cannot be reopened; a new problem then becomes a new incident.

## How notifications are delivered

Notifications are only sent when an incident is **opened, reopened, escalated or resolved**, never for every failed check. Each user configures this under [Profile → Notifications]({app_url}/profile):

| Setting | Meaning |
|---|---|
| E-mail | Notifications by e-mail (requires the host to have configured e-mail) |
| In the app | Notifications under **Notifications** in the sidebar |
| Push notifications | On the devices where you turned them on; see [ServerScope as an app](mobile-app) |
| Recovery notifications | Also notify when an incident is resolved |
| DNS changes | Notify on changed A, AAAA, CNAME, MX, NS or CAA records |
| Minimum severity | Everything (low and up), medium and up, high and critical, or critical only |

Notifications arrive in your own language (English or Dutch).

Use **Send test notification** to check your settings.

To prevent notification floods:

- An incident that reopens within **15 minutes** of the previous notification does not notify again (*suppressed*).
- DNS changes of the same domain are reported at most once per **30 minutes**.
- Each user receives at most **30 e-mails per hour**; above that only the in-app notification remains.
- An incident grouped under a root incident sends no notification of its own while the root is active.
- Notifications can be switched off **per resource** (a server, domain, page or service). Incidents for it are still recorded.

**Notifications → Delivery history** shows whether each notification was sent, failed or suppressed.

Administrators can also forward events to [Slack, Teams or Telegram](chat-channels), [Discord](discord-notifications) or to their own system with [webhooks](api-webhooks).

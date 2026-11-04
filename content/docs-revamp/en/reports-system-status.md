---
order: 8
icon: chart
category: monitoring
feature: reports
description: Availability and incident reports with targets, MTTD, MTTA and MTTR, CSV exports and print, plus the system status of ServerScope itself.
---
# Reports and system status

## Reports

Open [Reports]({app_url}/reports) (sidebar group **Reporting**).

1. Choose a period: the last 7, 30 or 90 days, this month, or a custom period.
2. Optionally enter a target in %, for example `99.9`.
3. Show the report.

The report contains:

- **Availability** per server, domain and website page, with downtime, response time (p95 for pages) and the number of incidents.
- **Targets**: a resource's own availability target (set on the page, website, server or domain) wins over the target on the report page.
- **Incident figures**: MTTD (time to detect), MTTA (time to acknowledge), MTTR (time to resolve) and the total duration of incidents.
- **Incidents by severity and source**.
- **Recurring problems**: problems that occurred more than once in the period.
- **Monitoring coverage**: how much of the period was actually monitored.

### How the figures are calculated

| Figure | Calculation |
|---|---|
| Uptime (domains, pages) | Successful checks ÷ performed checks. A *degraded* (slow) check counts as available. |
| Downtime (servers) | Time covered by *Agent offline* incidents. |
| Coverage | Performed checks or received samples ÷ expected ones. Periods without checks (paused, scheduler down) lower the coverage and are **never** counted as available or unavailable. |
| MTTD | First failed check → incident opened. Manual incidents are left out. |
| MTTA / MTTR | Incident opened → first acknowledgement / resolution. |
| Total incident duration | Root incidents only, and only the part inside the period. |

> [!NOTE]
> Checks keep running during maintenance and count normally. For servers no *Agent offline* incident is opened during maintenance, so that time is no downtime. The figures come from ServerScope's own measurements; they are not a contractual SLA.

### Exporting

- **Availability CSV**: one row per server, domain and page.
- **Incidents CSV**: one row per incident with the detection, acknowledgement and resolution times and the root incident.
- **Print / PDF**: a printable page; use your browser's *Save as PDF*.

## System status

Monitoring only works when ServerScope itself works. The page **Administration → System status** shows, for each part, whether it is fine, needs attention or has a problem, with an explanation:

| Part | What is checked |
|---|---|
| Scheduler | Whether the scheduled tasks run every minute |
| Queue | Whether the queue keeps up |
| Checks on time | Whether domain, page and service checks are overdue |
| Notifications and webhooks | Whether notifications and webhooks are delivered |
| Page discovery | Whether discovery runs finish |

The page is re-evaluated every minute. If you host ServerScope yourself, set `SERVERSCOPE_SCHEDULER_PING_URL` to the ping URL of a [scheduled job](scheduled-jobs): you then get an incident as soon as the scheduler stops.

## Data retention

How long history is kept depends on your plan (Free: 14 days). Log lines are kept for 14 days. Reports can only cover periods for which data is still kept.

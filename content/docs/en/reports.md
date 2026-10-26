---
order: 2
category: incidents
icon: chart
description: Availability, response times, MTTA and MTTR per period for your domains and servers, how they are calculated, and the CSV export.
---
# Reports

[Rapporten]({app_url}/reports) (Reports) shows how available your domains and servers were in a period, and how quickly incidents were picked up and fixed.

> [!IMPORTANT]
> Reports show **calculated** availability based on ServerScope's own monitoring data. They are not a contractual SLA statement.

## Choosing a period

| Field | Options |
|---|---|
| Periode (period) | Last 7, 30 (default) or 90 days, this month, or a custom range |
| Van / Tot (from / to) | For a custom range; at most one year |
| Doel (target, %) | The availability target to compare with, 90–100. The default is set by the operator (99.9 unless changed). |

## What the report shows

At the top:

| Figure | Meaning |
|---|---|
| Gemiddelde uptime (average uptime) | Average availability over all domains and servers, compared with the target |
| Incidenten (incidents) | Incidents opened in the period, with the number of critical ones |
| MTTA | Mean time to acknowledge: from opening to the first acknowledgement |
| MTTR | Mean time to resolve: from opening to resolution |

Below that, a table per **domain** and per **server** with uptime, coverage, downtime, the number of incidents and whether the target was met. Domains also show the average response time.

## How it is calculated

**Domains**

- Uptime = successful HTTP checks ÷ checks performed.
- Coverage = checks performed ÷ checks expected for the interval.
- Observed downtime = failed checks × the check interval.
- Missing checks are **never** counted as successful; low coverage means the uptime figure says less.

**Servers**

- Downtime = the time covered by *agent offline* incidents in the period.
- Coverage = measurements received ÷ measurements expected (one per minute) since the server was added.
- Incidents are not opened during a [maintenance window](security-account), so planned maintenance does not count as downtime.

When there is no data for an item, the report shows *geen data* (no data) instead of a verdict.

## Exporting to CSV

Click **CSV exporteren** (export CSV) to download the same report as `serverscope-beschikbaarheid-<from>-<to>.csv`. The columns are (in Dutch):

| Column | Content |
|---|---|
| `type` | `domein` or `server` |
| `naam` | Name |
| `uptime_pct` | Uptime in percent |
| `dekking_pct` | Coverage in percent |
| `downtime_min` | Downtime in minutes |
| `gem_responstijd_ms` | Average response time (domains only) |
| `incidenten` | Number of incidents |
| `controles_of_metingen` | Number of checks (domains) or measurements (servers) |
| `haalt_doel` | `ja` / `nee` (yes / no), empty without data |

Names that start with `=`, `+`, `-`, `@` or a tab are prefixed with `'`, so spreadsheet programs do not run them as formulas.

---
date: 2026-11-01
title: Time Machine, Incident Memory, AI investigation and deployments
status: published
source: Amir03033/ServerScope (release 1 November 2026)
---
For every incident you now see exactly what happened before, during and
after, and ServerScope remembers what you learned from it.

### New

- **Time Machine** per incident: the timeline, measurements and evidence from
  before, during and after the incident. The evidence is kept apart from the
  possible explanations.
- **Deployments (Deploy Judge):** report a deployment through the API
  (`POST /api/v1/deployments`, with an Idempotency-Key). ServerScope compares
  performance before and after the deployment, marks deployments in the
  charts and keeps a deployment history.
- **Global Pulse Lite:** an external probe confirms an HTTP outage from a
  second location before an incident opens. Without a probe everything works
  as before.
- **Incident Memory:**
  - record per incident what the cause was (hypothesis or confirmed), the
    symptoms, the fix and whether it worked;
  - see similar earlier incidents;
  - search also looks through these lessons.
- **AI Incident Investigator** (only when configured): an analysis based on
  the evidence. It never changes anything, and its outcome stays a
  hypothesis.
- **Faster history:** measurements are summarised per hour and per day.

### Improved

- Error pages in the house style.
- A health indicator, and data shows how fresh it is.
- Personal favourites.
- A ⋯ menu on every row.
- A workflow bar on the incident.
- Your own end time for maintenance.
- Filters in the audit log.

### Good to know

- The AI does not determine causes. Only a person marks a cause as
  confirmed.
- A probe only checks when there is an outage to confirm.

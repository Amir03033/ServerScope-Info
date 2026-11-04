---
order: 4
icon: search
category: alerts
feature: root-cause
description: Find the cause of an incident with the root cause analysis, the AI interpretation and the Time Machine, record what you learned and share a postmortem.
---
# Investigating incidents and postmortems

ServerScope helps you from the first alert to the lessons learned. This guide covers the tools on the incident page; the basics of alerts, statuses and notifications are in [Alerts and incidents](alerts-incidents).

## The incident page

The incident page starts with an overview of the key figures (duration, time to detect, time to acknowledge, affected resources) and a step bar that shows where you are: acknowledge, find the likely cause, fix, confirm recovery. A fixed side column keeps the details and actions in view.

## Root cause analysis

Every incident is investigated automatically with fixed rules, on every plan. The analysis gives:

- the **most likely explanation**, with a confidence;
- the **evidence** for and against it;
- **alternatives** and what is still **uncertain**.

"Cause not determined" is a valid outcome: it means the data does not point clearly in one direction.

### AI interpretation (Pro and Premium)

On Pro and Premium an AI interprets the same investigation. The AI can only refer to evidence ServerScope supplied; references to anything else are removed and counted. The page shows the conclusion of the rules and of the AI side by side, with confidence meters, and says when **AI and rules agree**.

> [!IMPORTANT]
> The AI never changes anything and never resolves an incident. Its outcome is a hypothesis; only a person marks a cause as confirmed. Recovery is always verified with live monitoring results.

## Time Machine

**Time Machine** shows what happened before, during and after the incident: the timeline, the measurements and the evidence. Observed facts with their measured values are kept apart from possible explanations, which are suggestions to check, ordered by how directly the evidence relates to the incident (a confirmed dependency first).

Deployments, DNS changes and log errors from the same period appear in the same view.

## Logs around this incident

If the agent follows your log files, the incident shows the errors and warnings on the servers involved, from shortly before the start until the end. See [Logs](logs).

## Incident memory

Record what you learned, so the next similar incident is solved faster:

1. On the incident, click **Record cause and resolution**.
2. Fill in the **Root cause**, the **Cause category** and its **Certainty**: *Hypothesis (not verified)* or *Confirmed cause*.
3. Add the **Symptoms** (comma separated, for example `502 errors, slow login`), the **Resolution steps**, whether the fix worked and, if relevant, the related deployment.
4. Save.

The incident then shows earlier incidents of your team that look alike, and search also looks through these lessons.

## Postmortem

From the incident memory you open a **Postmortem** with one click. It is filled in from what is recorded: the duration, time to detect and acknowledge, the impact, a timeline with deployments, the cause and the fix. You add:

- a **Summary**: what happened, who noticed, how it ended;
- the **Impact** on customers, orders or teams;
- **What went well** and **What could be better**;
- **Action items**, each with an owner.

### Sharing

- **Share → Make a link** creates a public, read-only link for customers or colleagues outside ServerScope. Only the postmortem is visible; internal notes are never included. **Stop sharing** makes the link stop working right away. Only team administrators can share.
- **Markdown** downloads the postmortem as a Markdown file.
- **Print or PDF** opens a printable version.

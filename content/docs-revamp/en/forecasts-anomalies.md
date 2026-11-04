---
order: 7
icon: chart
category: monitoring
feature: predictions
description: How ServerScope spots unusual behaviour and forecasts when a disk or memory will fill up, and how to tune it per server.
---
# Forecasts and anomalies

Besides fixed thresholds, ServerScope looks at the **trend** of your servers. Every hour it compares recent measurements with the server's own history and calculates when resources run out if nothing changes. You find the results on the server page under **Anomalies and forecasts**.

## Anomalies

An **anomaly** is a clear, large deviation from what is normal for *this* server. ServerScope compares CPU, memory, swap, load, network, disk growth, response time and error rate with the past week of the same resource.

- Only large deviations count: relative to normal variation and in absolute terms. A jump from 2% to 4% CPU is not shown.
- By default an anomaly does **not** open an incident.

## Capacity forecasts

A **forecast** shows when a disk or memory will be full if the current trend continues, for example *disk `/var` full in about 5 days*. Every forecast shows the value and trend, the period the trend was calculated over, how confident ServerScope is (with a range) and why it was raised.

Nothing is forecast from too little data, and a forecast with low confidence is shown but never alerts.

> [!IMPORTANT]
> A forecast that crosses a limit within **7 days** with at least medium confidence opens an incident. It is resolved by itself when the trend no longer meets that bar.

## Settings per server

Open the server and choose **Edit**:

- **Predictive notifications (e.g. disk full within 7 days)**: on by default. Turn it off for a server whose disk fills up on purpose, such as a backup server.
- **Anomaly detection sensitivity**: Low (large anomalies only), Medium (default), High or Off.

## Good to know

- Forecasts need agent data. Disk activity, swap and network errors require agent 0.2.0 or newer.
- A forecast is a calculation from past behaviour, not a promise. A sudden peak (a large upload, a log storm) can fill a disk sooner.
- The API returns forecasts and anomalies with `GET /api/v1/predictions` (see [API v1 reference](api-reference)).

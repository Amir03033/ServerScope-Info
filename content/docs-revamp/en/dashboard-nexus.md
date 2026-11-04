---
order: 2
icon: map
category: start
feature: mission-control
description: Mission control, arranging your dashboard, the command palette, the side panel, themes and NEXUS with Skyline, Globe, Time travel and Wall screen.
---
# Dashboard, NEXUS and finding your way

## Mission control

The top of the [Dashboard]({app_url}/dashboard) answers one question: is everything all right?

- A **health score** from 0 to 100, with one sentence of explanation. The score starts at 100; every open problem costs points, and you see which.
- **Everything at a glance**: a tile per server and website.
- **Last 24 hours**: a timeline with incidents, deployments and DNS changes.
- **Focus** shows only what needs attention. Nothing to do? Then it says so.

Below that, **Needs attention** lists what to look at first.

## Arranging your dashboard

Click **Arrange** to make the dashboard your own:

- 14 widgets, such as Health score, Heartbeat, Active incidents, Expiring soon, Response time and Server performance;
- drag a widget by its handle (or use the arrow keys), choose **S**, **M** or **L**, or hide it;
- **Start from a layout…**: Developer, Manager or NOC; **Standard layout** resets it.

Only you see your layout.

### Heartbeat

The **Heartbeat** widget draws a live ECG line per server. The pace follows the CPU and the shape shows the rhythm: steady, irregular, chaos, flat (offline) or at rest. The rhythm, the reason and the figures are also shown as text.

## Finding things quickly

- **⌘K / Ctrl+K** opens the command palette: search servers, websites and incidents, recently viewed items, quick filters ("offline", "slow") and actions such as **Add server** or switching the theme, with a preview on the right.
- **Click** a server, website, incident or scheduled job to open a quick look in the **side panel**. **Hover** to see a card with the key facts. **Shift- or Cmd/Ctrl-click** opens the page directly.
- Lists of servers, websites, incidents and alerts refresh themselves every 30 seconds. A recovery lights up green, an outage red.
- Mark pages as **favourites** to find them back quickly.

## NEXUS

[NEXUS]({app_url}/nexus) is a live map of your infrastructure: servers, services, websites, pages and scheduled jobs, with their [dependencies](dependencies), health, active incidents and impact. Select something to see **what falls over if it fails**. There is a list view for keyboard and screen-reader users, and a **3D** view: servers are towers, services rings, and websites orbit their server. During an incident a pulse runs along the connections.

Under NEXUS you also find:

| View | What it shows |
|---|---|
| [Skyline]({app_url}/nexus/skyline) | Your servers as a city at night: height is CPU, windows are services, roof lights are websites |
| [Globe]({app_url}/nexus/globe) | Your main location and probes on a globe, with the status per location |
| [Time travel]({app_url}/nexus/timelapse) | The last 24 hours or 7 days as a film, with a director's cut and export as video |
| [Wall screen]({app_url}/nexus/wall) | A full-screen NOC view that moves through the scenes and zooms in on a new incident for one minute; also usable as a screensaver |

> [!NOTE]
> The live flows and the 3D world show **measured load**, not real traffic.

## Language and appearance

- ServerScope is in **English** by default. Choose Dutch under **Profile → Language** or in the user menu. E-mails, notifications, Discord and webhooks use the recipient's language.
- The theme toggle switches between system, light and dark. Under **Profile → Appearance** you find the extra themes **Midnight**, **Terminal** and **High contrast**. The choice is stored in your browser.
- Animations are calm and switch off when your device has *reduce motion* turned on.

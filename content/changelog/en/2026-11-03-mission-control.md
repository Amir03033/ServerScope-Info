---
date: 2026-11-03
title: Mission control, side panel, command palette, themes and an app
status: published
source: Amir03033/ServerScope (release 3 November 2026)
---
The dashboard now shows at a glance how things stand, and you can install
ServerScope as an app on your phone and computer.

### New

- **Mission control** at the top of the dashboard:
  - a health score from 0 to 100, with one sentence of explanation and the
    points deducted;
  - a tile per server and website;
  - a timeline of the last 24 hours with incidents, deployments and DNS
    changes;
  - **focus mode** shows only what needs attention.
- **Side panel and hover cards:** click a server, website or incident for a
  quick look at the side. Hover to see a card. Shift- or Cmd/Ctrl-click opens
  the page directly.
- **Command palette 2.0 (⌘K):** recently viewed items, quick filters
  ("offline", "slow"), actions ("Add server", switch theme) and a preview on
  the right.
- **Live lists:** the lists of servers, websites, incidents and alerts
  refresh themselves every 30 seconds. A recovery lights up green, an outage
  red.
- **Extra themes:** Midnight, Terminal and High contrast, under
  **Profile → Appearance**. Light, dark and system remain the default.
- **ServerScope as an app:**
  - install it on your phone and computer;
  - a tab bar at the bottom on phones;
  - **swipe to acknowledge an incident**;
  - **push notifications** per device, under **Profile → Notifications**.
- **Arrange your own dashboard:**
  - 14 widgets you can drag, resize (S/M/L) and hide;
  - presets for Developer, Manager and NOC;
  - the layout is per person.
- **Time travel** (under NEXUS): your infrastructure over the last 24 hours
  or 7 days as a film, with a director's cut and export as video.
- **Wall screen** (under NEXUS): a full-screen NOC view that moves through
  the scenes by itself and zooms in on a new incident for one minute. Can
  also be used as a screensaver.
- **Living logo:** the logo and the tab icon beat in your team's rhythm and
  turn orange or red when there are problems. The tab title shows the number
  of incidents.

### Improved

- Maintenance now lives in one panel on the server page.
- On phones, filters sit behind a button.
- Larger touch targets.
- Calm animations, switched off when you have "reduce motion" turned on.

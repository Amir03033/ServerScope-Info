---
order: 3
icon: phone
category: start
feature: mobile-app
description: Install ServerScope as an app on your phone or computer, acknowledge incidents with a swipe and turn on push notifications.
---
# ServerScope as an app and push notifications

ServerScope works in every browser, and you can also **install it as an app** on your phone and computer. The app opens without browser bars and has a tab bar at the bottom on phones: **Overview**, **Incidents**, **Servers** and **More**.

## Installing

- **iPhone and iPad (Safari):** open ServerScope, tap **Share → Add to Home Screen**.
- **Android (Chrome):** open the menu and choose **Install app** (or *Add to Home screen*).
- **Desktop (Chrome, Edge):** click the install icon in the address bar.

## Swipe to acknowledge

In the incident list on a phone, swipe an incident to **acknowledge** it. It does the same as the button on the incident page.

## Push notifications

Push notifications bring incidents to your phone or computer, also when ServerScope is closed. They follow your notification preferences (such as the minimum severity) and are turned on **per device**.

1. Open **Profile → Notifications** on the device.
2. Under **Push notifications on this device** click **Turn on** and allow notifications when the browser asks.

The page shows on how many devices push is on. Turn it off the same way.

| Message | What to do |
|---|---|
| Blocked in this browser's settings | Allow notifications for ServerScope in the browser or system settings. |
| This browser cannot receive push notifications | On iPhone, add ServerScope to your home screen first, then open it from there. |
| Push is not set up on this ServerScope yet | An administrator still needs to add the keys (see below). |

### For administrators who host ServerScope

Generate the keys once and put them in ServerScope's `.env`:

```sh
php artisan serverscope:push:keys
```

Then set `PUSH_VAPID_PUBLIC_KEY`, `PUSH_VAPID_PRIVATE_KEY` and the subject as shown by the command. After that, every user can turn on push per device.

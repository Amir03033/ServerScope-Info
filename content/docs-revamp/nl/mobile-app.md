---
order: 3
icon: phone
category: start
feature: mobile-app
description: ServerScope als app installeren op je telefoon of computer, incidenten bevestigen met een veeg en pushmeldingen aanzetten.
---
# ServerScope als app en pushmeldingen

ServerScope werkt in elke browser, en je kunt het ook **als app installeren** op je telefoon en computer. De app opent zonder browserbalken en heeft op telefoons onderaan een tabbalk: **Overzicht**, **Incidenten**, **Servers** en **Meer**.

## Installeren

- **iPhone en iPad (Safari):** open ServerScope en tik op **Deel → Zet op beginscherm**.
- **Android (Chrome):** open het menu en kies **App installeren** (of *Toevoegen aan startscherm*).
- **Computer (Chrome, Edge):** klik op het installatie-icoon in de adresbalk.

## Vegen om te bevestigen

Veeg op een telefoon in de incidentenlijst over een incident om het te **bevestigen**. Dat doet hetzelfde als de knop op de incidentpagina.

## Pushmeldingen

Pushmeldingen brengen incidenten naar je telefoon of computer, ook als ServerScope dicht is. Ze volgen je meldingsvoorkeuren (zoals de minimale ernst) en zet je **per apparaat** aan.

1. Open op het apparaat **Profiel → Meldingen**.
2. Klik onder **Pushmeldingen op dit apparaat** op **Aanzetten** en sta meldingen toe als de browser erom vraagt.

De pagina toont op hoeveel apparaten push aanstaat. Uitzetten gaat op dezelfde plek.

| Melding | Wat te doen |
|---|---|
| Geblokkeerd in de instellingen van deze browser | Sta meldingen voor ServerScope toe in de instellingen van je browser of systeem. |
| Deze browser kan geen pushmeldingen ontvangen | Zet ServerScope op een iPhone eerst op je beginscherm en open het van daaruit. |
| Push is op deze ServerScope nog niet ingesteld | Een beheerder moet de sleutels nog instellen (zie hieronder). |

### Voor beheerders die ServerScope zelf hosten

Maak de sleutels één keer aan en zet ze in de `.env` van ServerScope:

```sh
php artisan serverscope:push:keys
```

Zet daarna `PUSH_VAPID_PUBLIC_KEY`, `PUSH_VAPID_PRIVATE_KEY` en het subject zoals het commando toont. Daarna kan elke gebruiker push per apparaat aanzetten.

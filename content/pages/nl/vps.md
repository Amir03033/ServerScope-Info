---
title: Een VPS monitoren
seo_title: VPS monitoren met een lichte agent — ServerScope
description: Bewaak CPU, load, geheugen, schijven en netwerk van je VPS met een agent die alleen leest. Koppelen met één commando op Ubuntu of Debian.
summary: Met ServerScope monitor je een VPS via een kleine agent die elke 60 seconden CPU, load, geheugen, swap, schijven, netwerk, processen en uptime meet. Je koppelt de server met één commando en een eenmalig koppeltoken. De agent voert geen commando's uit en heeft geen open poort nodig.
---
# Een VPS monitoren

## Wat je ziet

De agent stuurt elke 60 seconden via HTTPS:

- CPU, load, geheugen en swap
- schijven en schijfactiviteit
- netwerkverkeer en netwerkfouten
- het aantal processen en de uptime
- optioneel de toestand van systemd-diensten die je kiest, zoals Nginx, MySQL, PHP-FPM of Redis

In het dashboard zie je per server de status (online, offline of in afwachting), de laatste meting en grafieken voor CPU, geheugen, schijven en netwerk.

## Koppelen met één commando

1. Maak in ServerScope een **koppeltoken** aan: **Servers → Server toevoegen → Linux → Koppeltoken aanmaken**. Het is 60 minuten geldig en werkt één keer.
2. Voer het installatiecommando op de VPS uit en plak het token als het script erom vraagt.
3. Het script installeert de agent, wisselt het koppeltoken in voor een eigen agenttoken en wacht tot de eerste meting binnen is.

Vereisten: Ubuntu 22.04 of nieuwer, of Debian 12 of nieuwer, op amd64 of arm64, met systemd en sudo. Lees de volledige stappen in [Een server koppelen met één commando](/nl/docs/connect-server). Past je VPS daar niet bij, dan is er een [handmatige installatie](/nl/docs/server-monitoring) en een [Bash-agent](/nl/docs/bash-agent).

## Wat de agent niet doet

- Hij voert **nooit commando's uit op verzoek van ServerScope**.
- Hij luistert niet op een poort: alleen uitgaand HTTPS-verkeer naar ServerScope op poort 443.
- Hij draait als de gebruiker `serverscope` zonder shell en zonder schrijfrechten op zijn eigen installatie.

Zie [Beveiliging van de agent](/nl/docs/agent-security).

## Waarschuwingen

- Een server die 2 minuten niets stuurt, wordt als **offline** getoond. De standaard alertregel *Agent offline* maakt na 5 minuten stilte een kritiek incident aan.
- Met **alertregels** krijg je een incident als CPU-, RAM- of schijfgebruik te lang boven een drempel blijft.
- Tijdens een **onderhoudsvenster** worden er voor die server geen alerts of incidenten aangemaakt.

Zie [Alerts en incidenten](/nl/docs/alerts-incidents).

## Veelgestelde vragen

### Hoe vaak meet de agent?
Standaard elke 60 seconden. Je kunt het interval met `SERVERSCOPE_INTERVAL` aanpassen, met een minimum van 10 seconden.

### Moet ik een poort openzetten in de firewall?
Nee. De agent maakt alleen uitgaande HTTPS-verbindingen naar ServerScope.

### Kan ik de agent weer verwijderen?
Ja, met `sudo bash serverscope-install.sh --uninstall`. Dat trekt het agenttoken in en verwijdert de service en de installatiemap. De server en zijn historie blijven in ServerScope.

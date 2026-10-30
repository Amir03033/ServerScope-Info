---
title: Wat is ServerScope?
seo_title: Wat is ServerScope? Monitoring van servers, websites en domeinen
description: ServerScope bewaakt Linux-servers via een agent die alleen leest, en websites en domeinen via HTTP-, SSL-, DNS- en RDAP-controles.
summary: ServerScope is een webapplicatie die Linux-servers bewaakt via een kleine agent die alleen leest, en websites en domeinen via HTTP-, SSL-, DNS- en registratiecontroles. Problemen worden incidenten met een tijdlijn, en meldingen gaan per e-mail, in de app, naar Discord of naar een eigen webhook.
---
# Wat is ServerScope?

## Wat ServerScope bewaakt

- **Servers.** Een kleine agent verzamelt elke 60 seconden CPU, load, geheugen, swap, schijven, netwerkverkeer, het aantal processen en de uptime, en stuurt die via HTTPS naar ServerScope. De agent kan ook de toestand van systemd-diensten doorgeven die jij kiest, zoals Nginx, MySQL of PHP-FPM. Zie [Servermonitoring](/nl/docs/server-monitoring) en [Diensten bewaken](/nl/docs/service-checks).
- **Websites en domeinen.** Per domein kies je welke controles aan staan: HTTP-beschikbaarheid, het SSL/TLS-certificaat, DNS-records en de domeinregistratie via RDAP. Zie [Domeinmonitoring](/nl/docs/domain-monitoring).

## Wat er gebeurt bij een probleem

- Een probleem wordt een **incident**, met een ernst (info, waarschuwing of kritiek), een status en een tijdlijn. Herhaalde mislukkingen werken hetzelfde incident bij.
- Meldingen gaan per **e-mail**, **in de app**, naar **Discord** of naar een eigen **webhook**. Per gebruiker stel je in vanaf welke ernst je een melding krijgt.
- Met een **statuspagina** laat je klanten of collega's zien of je diensten werken, zonder dat ze een account nodig hebben.

Zie [Alerts en incidenten](/nl/docs/alerts-incidents) en [Publieke statuspagina's](/nl/docs/status-pages).

## Hoe de agent werkt

- Je koppelt een server met **één commando** en een eenmalig **koppeltoken** (Ubuntu 22.04+ of Debian 12+, amd64 of arm64, met systemd). Zie [Een server koppelen met één commando](/nl/docs/connect-server).
- De agent **voert nooit commando's uit op verzoek van ServerScope** en luistert niet op een poort. Hij maakt alleen uitgaande HTTPS-verbindingen.
- Agent-updates zijn **ondertekend**. Een server installeert alleen een release met een geldige handtekening van een sleutel die niet op de ServerScope-server staat. Zie [Agent-updates via het dashboard](/nl/docs/agent-updates) en [Beveiliging van de agent](/nl/docs/agent-security).

## Voor wie

Voor wie zelf Linux-servers, websites of domeinen beheert en één overzicht wil van metingen, storingen en verlopende certificaten of registraties. Alles hoort bij een **team**; elk team ziet alleen zijn eigen gegevens.

## Taal

De ServerScope-applicatie is Nederlandstalig. De documentatie is er in het Nederlands en het Engels.

## Veelgestelde vragen

### Heeft de agent toegang tot mijn server?
De agent leest systeemgegevens en stuurt ze naar ServerScope. Hij voert geen commando's uit op verzoek van ServerScope en luistert niet op een poort. Hij draait als de gebruiker `serverscope` zonder shell.

### Welke besturingssystemen worden ondersteund?
Het installatiescript ondersteunt Ubuntu 22.04 of nieuwer en Debian 12 of nieuwer, op amd64 of arm64, met systemd. Voor hosts en containers zonder Python of systemd is er een Bash-agent.

### Heeft ServerScope een API?
Ja. Er is een REST API (versie 1) met persoonlijke tokens, en webhooks die gebeurtenissen ondertekend naar je eigen systeem sturen. Zie [API en webhooks](/nl/docs/api-webhooks).

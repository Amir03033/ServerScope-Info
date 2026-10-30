---
title: ServerScope vergeleken
seo_title: Monitoringtools vergelijken: criteria en de antwoorden voor ServerScope
description: Criteria om een tool voor server- en domeinmonitoring te beoordelen, ingevuld voor ServerScope met een bron in de documentatie bij elk antwoord.
summary: Deze pagina geeft criteria waarop je een monitoringtool kunt beoordelen, en de antwoorden voor ServerScope. Elk antwoord verwijst naar de documentatie. Over andere tools doen we geen uitspraken; vul die kolom zelf in.
---
# ServerScope vergeleken

Vergelijk je tools voor server- en domeinmonitoring? Neem deze vragen mee. We vullen alleen ServerScope in, met de bron erbij. Andere tools noemen we niet: hun mogelijkheden veranderen, en we willen geen beweringen doen die we niet kunnen controleren.

## Servers

| Criterium | ServerScope | Bron |
|---|---|---|
| Hoe worden servers gemeten? | Met een agent op de server, die elke 60 seconden meet | [Servermonitoring](/nl/docs/server-monitoring) |
| Kan de dienst commando's op je server uitvoeren? | Nee. De agent voert nooit commando's uit op verzoek van ServerScope | [Beveiliging van de agent](/nl/docs/agent-security) |
| Is er een open poort op de server nodig? | Nee, alleen uitgaand HTTPS-verkeer | [Beveiliging van de agent](/nl/docs/agent-security) |
| Hoe installeer je de agent? | Met één commando en een eenmalig koppeltoken, of handmatig | [Een server koppelen met één commando](/nl/docs/connect-server) |
| Welke systemen? | Ubuntu 22.04+ en Debian 12+, amd64 en arm64, met systemd; een Bash-agent voor systemen zonder Python of systemd | [Een server koppelen](/nl/docs/connect-server), [De Bash-agent](/nl/docs/bash-agent) |
| Zijn agent-updates ondertekend? | Ja, met Ed25519; de sleutel staat niet op de ServerScope-server | [Agent-updates](/nl/docs/agent-updates) |
| Kun je diensten zoals Nginx of MySQL bewaken? | Ja, systemd-diensten die je zelf kiest (vanaf agent 0.2.0) | [Diensten bewaken](/nl/docs/service-checks) |

## Websites en domeinen

| Criterium | ServerScope | Bron |
|---|---|---|
| HTTP-beschikbaarheid | Ja, met verwachte statuscodes, optionele verwachte inhoud, faal- en hersteldrempel | [Domeinmonitoring](/nl/docs/domain-monitoring) |
| SSL-certificaten | Ja: geldigheid en vervaldatum, met waarschuwingen op instelbare momenten | [Domeinmonitoring](/nl/docs/domain-monitoring) |
| DNS-wijzigingen | Ja: A, AAAA, CNAME, MX, NS en CAA, met geschiedenis | [Domeinmonitoring](/nl/docs/domain-monitoring) |
| Verlopen van de domeinregistratie | Ja, via RDAP, als de registry een vervaldatum publiceert | [Domeinmonitoring](/nl/docs/domain-monitoring) |

## Incidenten en meldingen

| Criterium | ServerScope | Bron |
|---|---|---|
| Incidenten met tijdlijn en status | Ja | [Alerts en incidenten](/nl/docs/alerts-incidents) |
| Meldingskanalen | E-mail, in de app, Discord en webhooks | [Alerts en incidenten](/nl/docs/alerts-incidents), [Discord-meldingen](/nl/docs/discord-notifications) |
| Statuspagina's | Ja, publiek of privé, met beschikbaarheid per dag over 90 dagen | [Publieke statuspagina's](/nl/docs/status-pages) |
| API | REST API v1 met tokens (`read`, `write`) en ondertekende webhooks | [API en webhooks](/nl/docs/api-webhooks) |

## Account en team

| Criterium | ServerScope | Bron |
|---|---|---|
| Teams en rollen | Ja: lid, beheerder en eigenaar; elk team ziet alleen zijn eigen gegevens | [Teams en rollen](/nl/docs/teams-and-roles) |
| Tweestapsverificatie | Ja, TOTP met herstelcodes | [Beveiliging en accountbeheer](/nl/docs/security-account) |
| Auditlog | Ja, zonder geheimen | [Beveiliging van de agent](/nl/docs/agent-security) |
| Taal | De applicatie is Nederlandstalig; de documentatie is Nederlands en Engels | [Aan de slag](/nl/docs/getting-started) |

## Wat hier niet staat

- **Prijzen.** Zie de [prijspagina](/nl/pricing).
- **Beschikbaarheidspercentages of klantaantallen.** Die noemen we niet.

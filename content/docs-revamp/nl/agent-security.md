---
order: 30
icon: lock
category: account
description: Wat de agent wel en niet doet, waarom er geen commando's op afstand zijn, hoe tokens worden bewaard, tweestapsverificatie en het auditlog.
---
# Beveiliging van de agent en je account

## Wat de agent doet

De agent leest elke 60 seconden systeemgegevens uit en stuurt die via HTTPS naar ServerScope:

- CPU, load, geheugen en swap
- schijven en schijfactiviteit
- netwerkverkeer en netwerkfouten
- het aantal processen en de uptime
- optioneel de toestand van [diensten die jij kiest](service-checks)
- een SHA-256-controlesom van de eigen bronbestanden, zodat een gewijzigde agent opvalt

## Wat de agent niet doet

- De agent voert **nooit commando's uit op verzoek van ServerScope**. Er is geen manier om via ServerScope iets op je server te laten draaien.
- De agent draait als de gebruiker `serverscope` **zonder shell** en zonder schrijfrechten op zijn eigen installatie.
- De agent luistert niet op een poort: hij maakt alleen uitgaande HTTPS-verbindingen.

### Waarom geen commando's op afstand?

Omdat ServerScope dan nooit een route naar jouw servers is. Ook wie de ServerScope-server zelf zou overnemen, kan via de agent geen commando's op jouw servers uitvoeren. Zelfs [agent-updates](agent-updates) werken zo: de updater ontvangt alleen een versienummer en installeert uitsluitend releases met een geldige handtekening van een sleutel die niet op de ServerScope-server staat.

## Hoe tokens worden bewaard

| Token | Wat ServerScope bewaart |
|---|---|
| Koppeltoken (`sse_…`) voor het koppelen met één commando | Alleen een SHA-256-hash. Eén keer bruikbaar, 60 minuten geldig, intrekbaar. |
| Agenttoken | Alleen een hash. Wordt één keer getoond; roteren of intrekken op de serverpagina. |
| API-token (`ssk_…`) | Alleen een SHA-256-hash. Wordt één keer getoond; kan verlopen en ingetrokken worden. |

Tokens en inloggegevens komen niet in logbestanden, het auditlog, foutmeldingen of de uitvoer van het installatiescript. Op de server staat het agenttoken in `/opt/serverscope-agent/.env`, met eigenaar `root:serverscope` en rechten `640`.

## Tweestapsverificatie

Bescherm je account met een code uit een authenticator-app (TOTP) en bewaar je herstelcodes op een veilige plek. Hoe je het instelt, staat in [Beveiliging en accountbeheer](security-account).

> [!TIP]
> Zet tweestapsverificatie aan voor elke eigenaar en beheerder: zij kunnen webhooks, leden en de monitoringconfiguratie wijzigen.

## Het auditlog

Beheeracties worden vastgelegd in het **auditlog** van je team, zoals het aanmaken, gebruiken en intrekken van koppeltokens, het intrekken van een agenttoken en het wisselen van team. Geheimen worden niet in het auditlog opgeslagen.

---
title: DNS-wijzigingen monitoren
seo_title: DNS-wijzigingen monitoren voor A, AAAA, CNAME, MX, NS en CAA — ServerScope
description: Zie wanneer DNS-records van je domein veranderen, met een geschiedenis en een melding per e-mail, in de app, naar Discord of als webhook.
summary: ServerScope controleert standaard elke 15 minuten of een domeinnaam resolvet, en houdt wijzigingen bij in de records A, AAAA, CNAME, MX, NS en CAA. Een wijziging komt in de DNS-geschiedenis en kan een melding sturen; resolutie die blijft mislukken wordt een kritiek incident.
---
# DNS-wijzigingen monitoren

## Wat er gecontroleerd wordt

- Of de naam **resolvet**.
- **Wijzigingen** in de records A, AAAA, CNAME, MX, NS en CAA.

De controle draait standaard elke 15 minuten, of volgens het HTTP-interval als dat langer is.

## Wat er gebeurt bij een wijziging

- Een DNS-wijziging maakt **geen incident** aan. Ze wordt bewaard in de **DNS-geschiedenis** van het domein.
- Je kunt een **melding** krijgen bij gewijzigde records. Dat stel je per gebruiker in onder **Meldingen**.
- Discord-meldingen en webhooks kunnen ook DNS-wijzigingen doorgeven; bij webhooks is dat de gebeurtenis `dns.changed`.

## Wat er gebeurt als DNS faalt

Mislukt de resolutie het aantal keren uit de **faaldrempel** op rij (standaard 3), dan ontstaat een **kritiek** incident. Na de hersteldrempel wordt het automatisch opgelost.

Lees alles in [Domeinmonitoring](/nl/docs/domain-monitoring), [Discord-meldingen](/nl/docs/discord-notifications) en [API en webhooks](/nl/docs/api-webhooks).

## Veelgestelde vragen

### Wordt een DNS-wijziging een incident?
Nee. Een wijziging komt in de DNS-geschiedenis en kan een melding sturen als je dat aanzet. Alleen als de naam herhaaldelijk niet resolvet, ontstaat een incident.

### Welke recordtypes worden bijgehouden?
A, AAAA, CNAME, MX, NS en CAA.

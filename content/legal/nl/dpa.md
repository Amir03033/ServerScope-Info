---
title: Verwerkersovereenkomst
description: Afspraken over de persoonsgegevens die ServerScope in opdracht van klanten verwerkt.
version: 0.1
changes: Eerste concept.
---
<!-- JURIST: concept op basis van artikel 28 AVG en de code van ServerScope (oktober 2026). Toets in het bijzonder: de toestemming voor subverwerkers (artikel 6), de termijn voor het melden van datalekken (artikel 8), audits (artikel 9) en de verhouding tot de algemene voorwaarden. -->

## 1. Partijen en onderwerp

> [!SUMMARY]
> De klant bepaalt wat er gemonitord wordt; wij verwerken die gegevens alleen voor de klant.

1. Deze verwerkersovereenkomst hoort bij de overeenkomst tussen de klant (**verwerkingsverantwoordelijke**) en {company.name}, {company.address} (**verwerker**), over het gebruik van ServerScope.
2. De verwerker verwerkt persoonsgegevens alleen om ServerScope te leveren, zoals beschreven in bijlage A.

## 2. Duur

> [!SUMMARY]
> Zolang de klant ServerScope gebruikt.

Deze overeenkomst geldt zolang de verwerker voor de klant persoonsgegevens verwerkt.

## 3. Instructies

> [!SUMMARY]
> Wij doen alleen wat de klant via de instellingen van ServerScope vraagt.

1. De verwerker verwerkt persoonsgegevens alleen op schriftelijke instructie van de klant. De instellingen die de klant in ServerScope kiest (zoals wat er wordt bewaakt, wie meldingen krijgt en welke bewaartermijnen gelden) gelden als instructie.
2. Als een instructie volgens de verwerker in strijd is met de wet, meldt hij dat direct aan de klant.

## 4. Geheimhouding

> [!SUMMARY]
> Wie bij ons met de gegevens werkt, moet ze geheimhouden.

Iedereen die namens de verwerker toegang heeft tot de persoonsgegevens, is tot geheimhouding verplicht.

## 5. Beveiliging

> [!SUMMARY]
> De beveiligingsmaatregelen staan in bijlage C.

De verwerker neemt passende technische en organisatorische maatregelen. Bijlage C beschrijft de maatregelen die in ServerScope zijn ingebouwd.

## 6. Subverwerkers

> [!SUMMARY]
> Wij gebruiken alleen de subverwerkers uit bijlage B, en melden wijzigingen vooraf.

1. De klant geeft toestemming voor de subverwerkers in bijlage B.
2. De verwerker meldt een nieuwe subverwerker vooraf aan de klant. De klant kan daartegen bezwaar maken.
3. De verwerker legt subverwerkers dezelfde verplichtingen op als in deze overeenkomst.

## 7. Rechten van betrokkenen

> [!SUMMARY]
> Wij helpen de klant als iemand zijn rechten uitoefent.

De verwerker helpt de klant bij verzoeken van betrokkenen, zoals inzage of verwijdering. Veel daarvan kan de klant zelf in ServerScope doen, bijvoorbeeld servers, domeinen, pagina's en gebruikers verwijderen en bewaartermijnen instellen.

## 8. Datalekken

> [!SUMMARY]
> Bij een datalek waarschuwen wij de klant zonder onredelijke vertraging.

1. De verwerker meldt een inbreuk in verband met persoonsgegevens zonder onredelijke vertraging aan de klant, met de informatie die de klant nodig heeft voor een eventuele melding aan de toezichthouder.
2. De verwerker neemt maatregelen om de gevolgen te beperken.

## 9. Controle

> [!SUMMARY]
> De klant mag controleren of wij ons aan deze afspraken houden.

De verwerker stelt de informatie beschikbaar die nodig is om aan te tonen dat hij zich aan deze overeenkomst houdt, en werkt mee aan redelijke controles.

## 10. Einde

> [!SUMMARY]
> Na afloop verwijderen wij de gegevens, tenzij de wet bewaren verplicht.

Na het einde van de overeenkomst verwijdert de verwerker de persoonsgegevens van de klant, of geeft hij ze op verzoek terug, tenzij de wet bewaren verplicht.

## Bijlage A. Verwerkte gegevens

> [!SUMMARY]
> Welke gegevens ServerScope voor de klant verwerkt, en van wie.

**Categorieën betrokkenen:** gebruikers in het team van de klant, ontvangers van meldingen, en personen van wie gegevens op een door de klant bewaakte webpagina staan.

| Onderdeel | Gegevens |
|---|---|
| Servers (via de agent) | hostname, IP-adres, besturingssysteem, agentversie, CPU, geheugen, swap, schijfgebruik, schijfactiviteit, netwerkverkeer en netwerkfouten, load, aantal processen, uptime, en de status van services die de klant opgeeft. Geen bestandsinhoud, geen logbestanden, geen commando's op afstand. |
| Koppelen van servers | het IP-adres waarvandaan een server is gekoppeld |
| Website- en domeinmonitoring | URL's, HTTP-antwoorden, responstijden, TLS-certificaten, DNS-records, domeinverloop (RDAP) |
| Inhoudsmonitoring (als de klant die aanzet) | de tekst van de bewaakte pagina en de verschillen tussen controles; die tekst kan persoonsgegevens bevatten |
| Meldingen | e-mailadressen van ontvangers en een verzendlog; webhook-adressen versleuteld |
| Gebruikers van het team | naam, e-mailadres, rol, auditlog met IP-adres en tijdstip |

**Bewaartermijnen:** instelbaar per soort gegevens. Op dit moment: metingen {retention.metrics}; domeincontroles {retention.domain_checks}; paginacontroles {retention.page_checks}; paginawijzigingen {retention.page_changes}; verzendlog {retention.notification_logs}; auditlog {retention.audit_logs}.

## Bijlage B. Subverwerkers

> [!SUMMARY]
> Hosting en e-mail.

| Subverwerker | Doel | Land |
|---|---|---|
| {company.host_app} | Hosting van de applicatie en de database | {company.host_app_country} |
| {company.mail_service} | Versturen van e-mail (meldingen, uitnodigingen) | {company.mail_service_country} |

### Externe informatiebronnen

Om domeinen te controleren, raadpleegt ServerScope openbare informatiebronnen. Dit zijn **geen subverwerkers**: ze ontvangen **alleen domeinnamen**, geen persoonsgegevens van gebruikers.

| Bron | Waarvoor |
|---|---|
| DNS-resolvers (standaard Cloudflare 1.1.1.1, Google 8.8.8.8 en Quad9 9.9.9.9; instelbaar) | DNS-records opvragen |
| RDAP: IANA (data.iana.org) en de registries van de domeinextensies | de verloopdatum van een domein opvragen |

Meldingen gaan daarnaast naar bestemmingen die de klant zelf kiest, zoals Discord of een eigen webhook.

## Bijlage C. Beveiligingsmaatregelen

> [!SUMMARY]
> Wat er in ServerScope is ingebouwd om gegevens te beschermen.

- **Teamscheiding:** elk team ziet alleen zijn eigen gegevens; een ID van een ander team geeft "niet gevonden".
- **Tokens en wachtwoorden:** wachtwoorden, API-tokens, agenttokens en koppeltokens worden alleen als hash bewaard en één keer getoond. Koppeltokens zijn 60 minuten geldig en werken één keer.
- **Tweestapsverificatie:** TOTP met herstelcodes.
- **Auditlog:** beheeracties met gebruiker, tijdstip en IP-adres; zonder geheimen.
- **Agent zonder commando's op afstand:** de agent leest alleen gegevens, draait als gebruiker zonder shell en luistert niet op een poort.
- **Ondertekende agent-updates:** releases worden ondertekend met een sleutel die offline wordt bewaard. Servers controleren de handtekening met hun lokale sleutel, en een lokale noodknop zet updates uit.
- **Versleuteling onderweg:** HTTPS; de agent en het installatiescript gebruiken TLS 1.2 of hoger.
- **Versleuteling van geheimen:** webhook-adressen (zoals Discord) worden versleuteld opgeslagen.
- **Bescherming tegen misbruik van controles:** controles naar interne, lokale en cloud-metadata-adressen worden geblokkeerd, ook na redirects en DNS-wijzigingen.
- **Rate limits** op de API, het koppelen en het inloggen.
- **Bewaartermijnen:** instelbaar per soort gegevens.

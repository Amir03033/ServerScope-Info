---
title: Een SSL-certificaat monitoren
seo_title: SSL-certificaat monitoren en waarschuwen voor het verlopen — ServerScope
description: Krijg een waarschuwing voordat je SSL-certificaat verloopt, en een kritiek incident bij een ongeldig certificaat. Standaard 30, 14, 7, 3 en 1 dag vooraf.
summary: ServerScope controleert het SSL/TLS-certificaat van een domein standaard elke 6 uur. Je krijgt een waarschuwing op vaste momenten voordat het verloopt (standaard 30, 14, 7, 3 en 1 dag vooraf) en een kritiek incident als het certificaat ongeldig is.
---
# Een SSL-certificaat monitoren

## Wat er gecontroleerd wordt

Bij een domein met een `https://`-adres controleert ServerScope:

- of het certificaat **geldig** is: niet verlopen, al geldig, voor de juiste hostname, betrouwbaar en niet self-signed;
- wanneer het certificaat **verloopt**.

De controle draait standaard elke 6 uur, of volgens het HTTP-interval als dat langer is.

## Wanneer je een melding krijgt

| Situatie | Incident | Ernst |
|---|---|---|
| Ongeldig certificaat | Direct | Kritiek |
| Een waarschuwingsmoment voor de vervaldatum wordt gepasseerd | "Certificaat van … verloopt over N dagen" | Waarschuwing; kritiek bij 3 dagen of minder |

- De waarschuwingsmomenten stel je per domein in. Standaard zijn dat **30, 14, 7, 3 en 1 dag**.
- Passeert de vervaldatum een volgend moment terwijl het incident nog open is, dan wordt het incident **geëscaleerd**.
- Is het certificaat vernieuwd, dan wordt het incident **automatisch opgelost**.

## Instellen

1. Open in ServerScope **Domeinen → Domein toevoegen**.
2. Vul de domeinnaam in en kies `https://`.
3. Vink onder **Controles** het SSL/TLS-certificaat aan, en eventueel HTTP, DNS en de domeinregistratie.
4. Pas zo nodig de **Certificaatwaarschuwingen (dagen)** aan.

Meldingen gaan per e-mail, in de app, naar Discord of naar een webhook. Lees alles in [Domeinmonitoring](/nl/docs/domain-monitoring) en [Alerts en incidenten](/nl/docs/alerts-incidents).

## Veelgestelde vragen

### Wat gebeurt er als ik het certificaat vernieuw?
Bij een vernieuwd certificaat wordt een open incident automatisch opgelost.

### Kan ik interne adressen controleren?
Nee. ServerScope controleert alleen publieke adressen op de toegestane poorten (standaard 80 en 443). Interne IP-adressen worden geweigerd.

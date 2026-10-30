---
title: Het verlopen van een domeinnaam monitoren
seo_title: Domeinverloop monitoren via RDAP — ServerScope
description: Krijg een waarschuwing voordat de registratie van je domeinnaam verloopt. ServerScope haalt de vervaldatum dagelijks op via RDAP.
summary: ServerScope haalt dagelijks de vervaldatum van je domeinregistratie op via RDAP en waarschuwt op vaste momenten vooraf (standaard 30, 14, 7, 3 en 1 dag). Dat werkt alleen als de registry een vervaldatum publiceert; ServerScope verzint geen datum.
---
# Het verlopen van een domeinnaam monitoren

## Hoe het werkt

- ServerScope vraagt **dagelijks** de vervaldatum van de registratie op bij de registry, via **RDAP**.
- Je krijgt een waarschuwing bij het passeren van een **waarschuwingsmoment**. Standaard zijn dat **30, 14, 7, 3 en 1 dag** vooraf; je stelt ze per domein in.
- Het incident is een **waarschuwing**, en **kritiek** bij 7 dagen of minder.
- Passeert de datum een volgend moment terwijl het incident nog open is, dan wordt het incident **geëscaleerd**. Na verlenging wordt het automatisch opgelost.

## Als de registry geen datum geeft

Niet elke registry publiceert een vervaldatum via RDAP. De registratiestatus heet alleen **verified** als de registry een datum teruggeeft. Anders kan ServerScope niet waarschuwen voor het verlopen van die registratie. We schatten of verzinnen geen datum.

## Instellen

1. Open **Domeinen → Domein toevoegen** en vul de domeinnaam in.
2. Vink onder **Controles** de **Domeinregistratie (RDAP)** aan.
3. Pas zo nodig de **Vervalwaarschuwingen registratie (dagen)** aan.

Lees alles in [Domeinmonitoring](/nl/docs/domain-monitoring).

## Veelgestelde vragen

### Welke gegevens stuurt ServerScope naar de registry?
Alleen de domeinnaam, voor de RDAP-opvraging.

### Waarom zie ik geen vervaldatum?
De registry van dat domein publiceert geen vervaldatum via RDAP. Dan kan ServerScope er niet voor waarschuwen.

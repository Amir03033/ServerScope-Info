---
order: 7
icon: chart
category: monitoring
feature: predictions
description: Hoe ServerScope afwijkend gedrag ziet en voorspelt wanneer een schijf of het geheugen vol raakt, en hoe je dat per server instelt.
---
# Voorspellingen en afwijkingen

Naast vaste drempels kijkt ServerScope naar de **trend** van je servers. Elk uur vergelijkt het recente metingen met de eigen geschiedenis van de server en berekent het wanneer iets opraakt als er niets verandert. De uitkomsten staan op de serverpagina onder **Afwijkingen en prognoses**.

## Afwijkingen

Een **afwijking** is een duidelijke, grote afwijking van wat normaal is voor *deze* server. ServerScope vergelijkt CPU, geheugen, swap, load, netwerk, schijfgroei, responstijd en foutpercentage met de afgelopen week van hetzelfde onderdeel.

- Alleen grote afwijkingen tellen: ten opzichte van de normale schommeling én in absolute zin. Een sprong van 2% naar 4% CPU wordt niet getoond.
- Een afwijking opent standaard **geen** incident.

## Capaciteitsprognoses

Een **prognose** laat zien wanneer een schijf of het geheugen vol raakt als de huidige trend doorzet, bijvoorbeeld *schijf `/var` over ongeveer 5 dagen vol*. Bij elke prognose zie je de waarde en de trend, over welke periode die is berekend, hoe zeker ServerScope is (met een bandbreedte) en waarom de prognose er is.

Met te weinig gegevens wordt niets voorspeld, en een prognose met lage zekerheid wordt wel getoond, maar meldt nooit iets.

> [!IMPORTANT]
> Een prognose die binnen **7 dagen** een grens passeert, met minstens gemiddelde zekerheid, opent een incident. Dat lost vanzelf op als de trend niet meer aan die drempel voldoet.

## Instellingen per server

Open de server en kies **Bewerken**:

- **Voorspellende meldingen (bijv. schijf vol binnen 7 dagen)**: staat standaard aan. Zet het uit voor een server waarvan de schijf bewust volloopt, zoals een backupserver.
- **Gevoeligheid afwijkingsdetectie**: Laag (alleen grote afwijkingen), Gemiddeld (standaard), Hoog of Uit.

## Goed om te weten

- Prognoses hebben agentgegevens nodig. Schijfactiviteit, swap en netwerkfouten vragen agent 0.2.0 of nieuwer.
- Een prognose is een berekening op basis van het verleden, geen belofte. Een plotselinge piek (een grote upload, een stroom logregels) kan een schijf sneller vullen.
- De API geeft prognoses en afwijkingen terug met `GET /api/v1/predictions` (zie [API v1: overzicht](api-reference)).

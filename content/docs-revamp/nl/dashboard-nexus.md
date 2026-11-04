---
order: 2
icon: map
category: start
feature: mission-control
description: Missiecontrole, je dashboard indelen, het opdrachtpalet, het zijpaneel, thema's en NEXUS met Skyline, Wereldbol, Tijdreis en Wandscherm.
---
# Dashboard, NEXUS en de weg vinden

## Missiecontrole

Bovenaan het [Dashboard]({app_url}/dashboard) staat het antwoord op één vraag: is alles in orde?

- Een **gezondheidsscore** van 0 tot 100, met één zin uitleg. De score begint bij 100; elk open probleem kost punten, en je ziet welke.
- **Alles in één oogopslag**: een tegel per server en website.
- **Afgelopen 24 uur**: een tijdlijn met incidenten, deploys en DNS-wijzigingen.
- **Focus** toont alleen wat aandacht nodig heeft. Niets te doen? Dan staat dat er.

Daaronder zie je bij **Aandacht nodig** waar je als eerste naar moet kijken.

## Je dashboard indelen

Klik op **Indelen** om het dashboard naar je hand te zetten:

- 14 widgets, zoals Gezondheidsscore, Hartslag, Actieve incidenten, Verloopt binnenkort, Responstijd en Serverprestaties;
- sleep een widget aan zijn handvat (of gebruik de pijltjestoetsen), kies **S**, **M** of **L**, of verberg hem;
- **Begin met een indeling…**: Ontwikkelaar, Manager of NOC; **Standaardindeling** zet alles terug.

Alleen jij ziet je indeling.

### Hartslag

De widget **Hartslag** tekent per server een live ECG-lijn. Het tempo volgt de CPU en de vorm toont het ritme: rustig, onregelmatig, chaos, vlak (offline) of in rust. Ritme, reden en cijfers staan er ook als tekst bij.

## Snel iets vinden

- **⌘K / Ctrl+K** opent het opdrachtpalet: zoek servers, websites en incidenten, recent bekeken items, snelle filters ("offline", "traag") en acties zoals **Server toevoegen** of het thema wisselen, met rechts een voorbeeld.
- **Klik** op een server, website, incident of geplande taak voor een snelle blik in het **zijpaneel**. Met de muis **erboven** zie je een kaartje met de belangrijkste feiten. **Shift- of Cmd/Ctrl-klik** opent direct de pagina.
- Lijsten van servers, websites, incidenten en alerts verversen zichzelf elke 30 seconden. Herstel licht groen op, een storing rood.
- Markeer pagina's als **favoriet** om ze snel terug te vinden.

## NEXUS

[NEXUS]({app_url}/nexus) is een live kaart van je infrastructuur: servers, services, websites, pagina's en geplande taken, met hun [afhankelijkheden](dependencies), gezondheid, actieve incidenten en impact. Kies iets om te zien **wat er omvalt als het faalt**. Er is een lijstweergave voor wie met het toetsenbord of een schermlezer werkt, en een **3D**-weergave: servers zijn torens, services ringen, en websites draaien in een baan om hun server. Bij een incident loopt een puls langs de verbindingen.

Onder NEXUS vind je ook:

| Weergave | Wat je ziet |
|---|---|
| [Skyline]({app_url}/nexus/skyline) | Je servers als een stad bij nacht: de hoogte is de CPU, de ramen zijn services, de daklichten websites |
| [Wereldbol]({app_url}/nexus/globe) | Je hoofdlocatie en probes op een wereldbol, met de status per locatie |
| [Tijdreis]({app_url}/nexus/timelapse) | De afgelopen 24 uur of 7 dagen als film, met een director's cut en export als video |
| [Wandscherm]({app_url}/nexus/wall) | Een schermvullende NOC-weergave die zelf langs de scènes gaat en een nieuw incident één minuut uitlicht; ook als screensaver te gebruiken |

> [!NOTE]
> De live stromen en de 3D-wereld tonen **gemeten belasting**, geen echt verkeer.

## Taal en weergave

- ServerScope is standaard **Engels**. Kies Nederlands onder **Profiel → Taal** of in het gebruikersmenu. E-mails, meldingen, Discord en webhooks komen in de taal van de ontvanger.
- De themaschakelaar wisselt tussen systeem, licht en donker. Onder **Profiel → Weergave** vind je de extra thema's **Midnight**, **Terminal** en **Hoog contrast**. De keuze wordt in je browser bewaard.
- Animaties zijn rustig en staan uit als je apparaat *beweging beperken* aan heeft.

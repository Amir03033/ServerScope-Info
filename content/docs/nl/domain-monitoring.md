---
order: 2
category: monitoring
icon: globe
description: HTTP-beschikbaarheid, SSL-certificaten, DNS-records en domeinregistratie bewaken, met de instellingen en wanneer er een incident ontstaat.
---
# Domeinmonitoring

Voor elk domein kan ServerScope vier controles uitvoeren. Je kiest per domein welke aan staan:

| Controle | Wat er gecontroleerd wordt | Hoe vaak (standaard) |
|---|---|---|
| **HTTP-beschikbaarheid** | Of de URL antwoordt met een verwachte statuscode (en eventueel verwachte inhoud) | Elke 5 minuten (instelbaar 1–1440) |
| **SSL/TLS-certificaat** | Geldigheid en vervaldatum van het certificaat (alleen bij `https`) | Elke 6 uur (of het HTTP-interval, als dat langer is) |
| **DNS-records** | Of de naam resolvet, en wijzigingen in A, AAAA, CNAME, MX, NS en CAA | Elke 15 minuten (of het HTTP-interval, als dat langer is) |
| **Domeinregistratie (RDAP)** | De vervaldatum van de registratie bij de registry | Dagelijks |

Domeinen toevoegen en wijzigen kan alleen een **beheerder**.

## Een domein toevoegen

1. Open [Domeinen → Domein toevoegen]({app_url}/domains/create).
2. Vul de **Domeinnaam** in, bijvoorbeeld `example.com` of `www.example.nl`. Een volledige URL mag ook; alleen de hostname wordt bewaard. Internationale domeinnamen worden ondersteund.
3. Kies bij **Te controleren URL** `https://` of `http://` en eventueel een pad (standaard `/`).
4. Vink onder **Controles** aan wat je wilt bewaken.
5. Controleer de instellingen hieronder en klik op **Domein toevoegen**.

De eerste resultaten verschijnen binnen een paar minuten op de domeinpagina. Met **Nu controleren** start je direct een controle (maximaal één keer per minuut).

## Instellingen

| Veld | Standaard | Betekenis |
|---|---|---|
| Interval (minuten) | `5` | Hoe vaak de HTTP-controle draait (1–1440). |
| Time-out (seconden) | `10` | Maximale wachttijd per verzoek (2–30). |
| Faaldrempel | `3` | Aantal opeenvolgende mislukte controles voordat er een incident ontstaat (1–10). Geldt voor HTTP en DNS. |
| Hersteldrempel | `2` | Aantal geslaagde controles op rij voordat de storing als hersteld geldt (1–10). |
| Verwachte statuscodes | `200-399` | Bijvoorbeeld `200` of `200-299,301`. |
| Verwachte inhoud | – | Optionele tekst die op de pagina moet staan. Hoofdlettergevoelig; alleen de eerste 1 MB wordt doorzocht. |
| Redirects volgen | aan | Maximaal 5 redirects; elke stap wordt opnieuw gevalideerd. |
| Certificaatwaarschuwingen (dagen) | `30, 14, 7, 3, 1` | Momenten waarop je gewaarschuwd wordt voordat het certificaat verloopt. |
| Vervalwaarschuwingen registratie (dagen) | `30, 14, 7, 3, 1` | Idem voor de domeinregistratie. |

> [!NOTE]
> ServerScope controleert alleen **publieke** adressen op de toegestane poorten (standaard 80 en 443). Interne IP-adressen worden geweigerd, ook als een redirect of DNS-record ernaar verwijst.

## Wanneer ontstaat er een incident?

| Controle | Incident | Ernst |
|---|---|---|
| HTTP | Na *Faaldrempel* mislukte controles op rij: “… is onbereikbaar” | Kritiek |
| SSL | Ongeldig certificaat (verlopen, nog niet geldig, verkeerde hostname, onbetrouwbaar of self-signed) | Kritiek |
| SSL-vervaldatum | Bij het passeren van een waarschuwingsmoment: “Certificaat van … verloopt over N dagen” | Waarschuwing; kritiek bij 3 dagen of minder |
| DNS | Na *Faaldrempel* mislukte resoluties op rij | Kritiek |
| Registratie | Bij het passeren van een waarschuwingsmoment | Waarschuwing; kritiek bij 7 dagen of minder |

- Herhaalde mislukkingen werken hetzelfde incident bij; er komt geen nieuw incident per controle.
- Passeert een vervaldatum een volgend (lager) waarschuwingsmoment terwijl het incident nog open is, dan wordt het incident **geëscaleerd**.
- Bij herstel (na de *Hersteldrempel*, of een vernieuwd certificaat) wordt het incident automatisch opgelost.
- **DNS-wijzigingen** maken geen incident aan, maar worden bewaard in de DNS-geschiedenis en kunnen een melding sturen (instelbaar per gebruiker).

> [!WARNING]
> Niet elke registry publiceert een vervaldatum via RDAP. De registratiestatus heet alleen **verified** als de registry een datum teruggeeft; anders kan ServerScope niet waarschuwen voor het verlopen van die registratie.

## Pauzeren en onderhoud

- **Pauzeren** stopt alle controles van een domein; **Hervatten** zet ze weer aan.
- Een **Onderhoudsvenster** (start, einde en reden bij *Bewerken*) laat de controles doorlopen, maar er worden geen incidenten aangemaakt. Tijden zijn in de tijdzone Europe/Amsterdam.

## Problemen oplossen

| Wat je ziet | Wat je kunt doen |
|---|---|
| HTTP faalt met een statuscode buiten de verwachting | Pas **Verwachte statuscodes** aan, of controleer of de site een redirect geeft terwijl **Redirects volgen** uit staat. |
| *Deze URL is niet toegestaan* of een geblokkeerd adres | De hostname wijst naar een intern of gereserveerd IP-adres, of naar een poort die niet is toegestaan. |
| Time-outs | Verhoog de **Time-out** of controleer of de server bereikbaar is vanaf internet. |
| Verwachte inhoud niet gevonden | De tekst is hoofdlettergevoelig en moet in de eerste 1 MB van het antwoord staan. |
| Geen registratiedatum | De registry publiceert geen RDAP-vervaldatum (zie de waarschuwing hierboven). |

Meldingen over deze incidenten volgen de regels in [Alerts en incidenten](alerts-incidents).

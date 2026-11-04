---
order: 4
icon: bell
description: Hoe alertregels en de vier ernstniveaus werken, hoe je aan een incident werkt en de tijdlijn volgt, en hoe meldingen worden verstuurd.
---
# Alerts en incidenten

ServerScope maakt onderscheid tussen een **alert** en een **incident**:

- Een **alert** is de toestand van één alertregel op één server (bijvoorbeeld “schijfgebruik ≥ 90% op `/var`”).
- Een **incident** is het werkdossier dat mensen oppakken. Het ontstaat uit een alert, een agent die offline is, een gestopte service, een domein- of paginacontrole (HTTP, SSL, DNS, registratie), een geplande taak die te laat is of faalt, een capaciteitsprognose of een piek in fouten in de logs. Een beheerder kan ook handmatig een incident aanmaken.

Herhaalde metingen of mislukte controles van hetzelfde probleem werken **hetzelfde incident** bij (teller *Aantal keer*); er komt geen nieuw incident per meting.

## Alertregels

Alertregels gelden voor **alle servers**. Beheerders beheren ze onder [Beheer → Alertregels]({app_url}/alert-rules).

| Veld | Mogelijkheden |
|---|---|
| Metric | CPU-gebruik, RAM-gebruik, Schijfgebruik (per mountpoint), Agent offline, of fouten in de logs per 5 minuten |
| Voorwaarde | `>=`, `>`, `<=` of `<` |
| Drempelwaarde | 0–100 (%) voor resources; 2–1440 minuten stilte voor *Agent offline* |
| Vereiste duur (minuten) | 1–1440: hoe lang de voorwaarde moet gelden voordat de alert afgaat |
| Ernst van het incident | Laag, Gemiddeld, Hoog of Kritiek (Agent offline standaard Kritiek) |
| Herstelbevestiging (metingen) | Hoeveel goede metingen op rij nodig zijn voordat de alert als hersteld geldt; voorkomt een herstelmelding bij een korte dip |

Er is standaard één regel: **Agent offline**, 5 minuten, kritiek.

Je maakt een regel zo:

1. Open [Alertregels → nieuwe regel]({app_url}/alert-rules/create).
2. Geef een herkenbare naam, bijvoorbeeld `Schijf bijna vol`.
3. Kies de metric, voorwaarde en drempel, bijvoorbeeld *Schijfgebruik* `>=` `90`.
4. Kies de vereiste duur, bijvoorbeeld `10` minuten, om korte pieken te negeren.
5. Kies de ernst en sla op.

Actieve alerts zie je onder [Alerts]({app_url}/alerts). Tijdens een onderhoudsvenster van een server worden er geen alerts aangemaakt.

## Ernst en status

Eén schaal met vier niveaus geldt voor alertregels, incidenten, websitepagina's, geplande taken, meldingsvoorkeuren en webhooks:

| Ernst | Betekenis | Geëscaleerd als niet bevestigd na |
|---|---|---|
| Laag | Ter informatie | nooit |
| Gemiddeld | Aandacht nodig | 120 minuten |
| Hoog | Snel handelen | 30 minuten |
| Kritiek | Direct handelen | 15 minuten |

Een incident doorloopt deze statussen:

| Status | Betekenis | Volgende stap |
|---|---|---|
| Open | Nieuw, nog niet opgepakt | Bevestigen, Onderzoek starten of Oplossen |
| Bevestigd | Iemand heeft het gezien | Onderzoek starten of Oplossen |
| In onderzoek | Er wordt aan gewerkt | Bevestigen of Oplossen |
| Opgelost | Het probleem is weg | Heropenen of Sluiten (beheerder) |
| Gesloten | Definitief afgehandeld | – |

**Actief** betekent: Open, Bevestigd of In onderzoek.

## Aan een incident werken

1. Open [Incidenten]({app_url}/incidents). Standaard zie je de actieve incidenten. Filter op status (actief, niet bevestigd, opgelost, gesloten, alle), ernst en bron, of zoek op titel.
2. Open het incident. Het overzicht toont titel, ernst, bron, de getroffen onderdelen, de kerncijfers en een stappenbalk.
3. Klik op **Bevestigen** zodra je het oppakt. De tijd tot de eerste reactie telt mee voor de MTTA in de rapporten. Op een telefoon kun je ook over het incident in de lijst vegen, of op **Bevestigen** tikken in een chatbericht.
4. Wijs het incident toe aan een collega of jezelf.
5. Klik op **Onderzoek starten** en leg je bevindingen vast met **Notitie toevoegen**.
6. Klik op **Incident oplossen** wanneer het probleem verholpen is en beschrijf kort wat de oorzaak was en wat er is gedaan. Dat is verplicht als je een incident met de hand oplost.

Het incident toont ook hoe snel het probleem werd gedetecteerd (**MTTD**, van de eerste mislukte controle tot het incident), hoe vaak hetzelfde probleem eerder voorkwam, een automatische **oorzaakanalyse**, de **Time Machine** en de logs rond het incident. Zie [Incidenten onderzoeken en nabeschouwingen](incident-investigation).

Een incident dat bij een grotere storing hoort, toont zijn **hoofdincident** en waarom het is gegroepeerd; zie [Afhankelijkheden en gegroepeerde incidenten](dependencies).

> [!TIP]
> Herstelt het probleem vanzelf (de server stuurt weer data, de site antwoordt weer), dan lost ServerScope het incident **automatisch** op. Je ziet dan *Automatisch hersteld* in de tijdlijn.

## De tijdlijn volgen

Elk incident heeft een tijdlijn met alle gebeurtenissen in volgorde, met tijdstip en (bij handmatige acties) de gebruiker:

| Gebeurtenis | Wanneer |
|---|---|
| Incident geopend | Het probleem werd voor het eerst gedetecteerd |
| Ernst gewijzigd | Automatisch (bijv. een lagere vervaldrempel) of door een beheerder |
| Bevestigd / Onderzoek gestart | Een gebruiker heeft het incident opgepakt |
| Toegewezen | Het incident is aan iemand toegewezen |
| Notitie | Een gebruiker heeft een notitie toegevoegd |
| Geëscaleerd | Het incident bleef te lang onbevestigd of een volgende drempel werd gepasseerd |
| Automatisch hersteld / Opgelost | Het probleem is verholpen |
| Incident heropend | Het probleem keerde terug |
| Gesloten | Afgehandeld door een beheerder |

Deploys rond het incident staan ook op de tijdlijn.

## Opgelost, heropend en escalatie

- Keert een probleem terug binnen **60 minuten** na herstel, dan wordt het vorige incident **heropend** in plaats van een nieuw incident te maken.
- Een open incident dat niet bevestigd is, wordt **geëscaleerd** na 15 minuten (Kritiek), 30 minuten (Hoog) of 120 minuten (Gemiddeld). Incidenten met ernst Laag escaleren niet. Bij een gegroepeerde storing escaleert alleen het hoofdincident. Escalaties gaan ook naar de escalatiecontacten van het team (maximaal 5).
- **Gesloten** incidenten kunnen niet meer heropend worden; een nieuw probleem wordt dan een nieuw incident.

## Hoe meldingen worden verstuurd

Meldingen gaan alleen uit bij **openen, heropenen, escaleren en oplossen** van een incident, nooit voor elke mislukte controle. Elke gebruiker stelt dit zelf in onder [Profiel → Meldingen]({app_url}/profile):

| Instelling | Betekenis |
|---|---|
| E-mail | Meldingen per e-mail (vereist dat de beheerder e-mail heeft ingesteld) |
| In de app | Meldingen onder **Meldingen** in de zijbalk |
| Pushmeldingen | Op de apparaten waar je ze hebt aangezet; zie [ServerScope als app](mobile-app) |
| Herstelmeldingen | Ook een melding wanneer een incident is opgelost |
| DNS-wijzigingen | Melding bij gewijzigde A-, AAAA-, CNAME-, MX-, NS- of CAA-records |
| Minimale ernst | Alles (laag en hoger), Gemiddeld en hoger, Hoog en kritiek, of Alleen kritiek |

Meldingen komen in je eigen taal (Engels of Nederlands).

Gebruik **Testmelding versturen** om je instellingen te controleren.

Om te veel meldingen te voorkomen:

- Een incident dat binnen **15 minuten** na de vorige melding heropent, geeft geen nieuwe melding (*onderdrukt*).
- DNS-wijzigingen van hetzelfde domein worden maximaal één keer per **30 minuten** gemeld.
- Per gebruiker gaan er maximaal **30 e-mails per uur** uit; daarboven blijft alleen de melding in de app.
- Een incident dat onder een hoofdincident is gegroepeerd, stuurt geen eigen melding zolang het hoofdincident actief is.
- Meldingen kun je **per onderdeel** uitzetten (een server, domein, pagina of service). Incidenten worden dan nog wel vastgelegd.

Onder **Meldingen → Verzendgeschiedenis** zie je per melding of die verzonden, mislukt of onderdrukt is.

Beheerders kunnen gebeurtenissen daarnaast doorsturen naar [Slack, Teams of Telegram](chat-channels), [Discord](discord-notifications) of naar een eigen systeem met [webhooks](api-webhooks).

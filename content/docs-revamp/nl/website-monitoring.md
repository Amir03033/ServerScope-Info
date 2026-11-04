---
order: 4
icon: globe
category: monitoring
feature: website-monitoring
description: Een website pagina voor pagina bewaken, pagina's automatisch ontdekken, wijzigingen in de inhoud volgen en het websiteoverzicht lezen.
---
# Websitemonitoring

Een domeincontrole bewaakt één URL. **Websitemonitoring** gaat verder: je voegt de pagina's toe die ertoe doen, zoals `/prijzen`, `/login` of `/api/health`, en elke pagina krijgt eigen controles, drempels, meldingen en een eigen geschiedenis.

Open een domein onder [Domeinen]({app_url}/domains) en gebruik de tabbladen **Domein**, **Website & pagina's** en **Ontdekken**. Teameigenaren en -beheerders voegen pagina's toe en starten een ontdekking.

> [!NOTE]
> Hoeveel pagina's per website je kunt bewaken, hangt af van je plan (Free: 15). Zie de prijspagina.

## Pagina's toevoegen

1. Open het domein en kies het tabblad **Website & pagina's**.
2. Klik op **Pagina's toevoegen**.
3. Vul onder **URL's (één per regel)** per regel één pagina in: een pad zoals `/prijzen` of een volledige URL op hetzelfde domein.
4. Kies de controles en instellingen (zie hieronder) en sla op.

Een nieuwe pagina heeft nog geen status tot de eerste controle is uitgevoerd.

## Wat er per pagina wordt gecontroleerd

| Instelling | Standaard | Betekenis |
|---|---|---|
| Interval | 5 minuten | Hoe vaak de pagina wordt gecontroleerd. |
| Time-out | 10 seconden | Maximale wachttijd per verzoek. |
| Faaldrempel / Hersteldrempel | 2 / 2 | Mislukte controles op rij voor een incident; geslaagde controles op rij voor herstel. |
| Verwachte statuscodes | `200-399` | Bijvoorbeeld `200` of `200-299,301`. |
| Maximale responstijd (ms) | – | Trager is **verminderd**: zichtbaar, maar het telt niet als storing. |
| Verwachte eind-URL na redirects | – | Waar de pagina moet uitkomen, bijvoorbeeld na een login-redirect. |
| Moet bevatten / Mag niet bevatten | – | Tekst die er moet staan, of juist **niet** (bijvoorbeeld `Fatal error`). |
| Headercontroles | – | Een header moet aanwezig of afwezig zijn, gelijk zijn aan of een waarde bevatten, bijvoorbeeld `Strict-Transport-Security`. |
| Ernst bij storing | Hoog | De ernst van het incident als de pagina faalt. |
| Beschikbaarheidsdoel (%) | doel van de website of het rapport | Gebruikt in de [rapporten](reports-system-status). |

Mislukt een controle, dan legt ServerScope vast **waarom**: een netwerkfout, een time-out, een DNS-fout, een TLS-fout, een geblokkeerd doel, een redirectfout, een onverwachte statuscode, of een mislukte inhouds- of headercontrole.

> [!TIP]
> **Pauzeren** stopt de controles van een pagina zonder de geschiedenis kwijt te raken. Gebruik **Nu controleren** na een deploy.

## Pagina's automatisch ontdekken

Op het tabblad **Ontdekken** zoekt ServerScope pagina's voor je:

1. Controleer de instellingen: **Max. pagina's** (standaard 50), **Max. linkdiepte**, **Alleen deze paden** en **Uitsluiten** (patronen met `*`), **Extra sitemap-URL's** en **Subdomeinen meenemen**.
2. Klik op **Nu ontdekken**. De ontdekking loopt op de achtergrond.
3. ServerScope leest `robots.txt`, XML-sitemaps (ook sitemapindexen en gecomprimeerde sitemaps) en interne links.
4. Gevonden pagina's wachten op je goedkeuring. Selecteer ze en kies **Goedkeuren en monitoren** of **Afwijzen**.

Ontdekken is standaard veilig: alleen je eigen domein, maximaal 50 pagina's, een diepte van maximaal 2, grenzen aan het aantal verzoeken en de duur, en niets wordt bewaakt zonder je goedkeuring. Meer pagina's, een grotere diepte, subdomeinen of direct monitoren bevestig je met **Ik bevestig een bredere ontdekking dan de standaard**.

> [!NOTE]
> Elke controle en elk ontdekkingsverzoek gebruikt dezelfde bescherming als de domeinmonitoring: interne, lokale en cloud-metadata-adressen worden geweigerd, ook na een redirect of een gewijzigd DNS-antwoord.

## Wijzigingen in de inhoud volgen

Zet **Inhoudswijzigingen volgen** aan voor een pagina en ServerScope vergelijkt de tekst bij elke controle:

- De pagina laat zien wat er is **toegevoegd** en **verwijderd**.
- Datums, tijden, tellers en sessiecodes worden genegeerd. Met **Alleen dit deel vergelijken** vergelijk je één deel van de pagina (een CSS-selector); met **Uitsluiten (dynamische blokken, één selector per regel)** laat je delen weg.
- Een wijziging is **nooit** vanzelf een storing. Een verwachte wijziging bevestig je met één klik.
- Zet **Melden bij significante inhoudswijziging** aan om een melding (en de webhookgebeurtenis `page.changed`) te krijgen als een groot deel van de tekst verandert.

Met **Headers volgen op wijzigingen (configuratiedrift)** volg je ook gekozen response-headers, zodat je merkt als bijvoorbeeld een beveiligingsheader na een configuratiewijziging verdwijnt.

## Het websiteoverzicht

Het tabblad **Website & pagina's** begint met een overzicht van de hele website:

- hoeveel pagina's gezond, verminderd of onbereikbaar zijn, en welke niet recent zijn gecontroleerd;
- de gemiddelde responstijd en de p50-, p95- en p99-waarden;
- beschikbaarheid per dag, actieve incidenten en recente wijzigingen aan inhoud, DNS en certificaten.

In de paginalijst kun je zoeken, sorteren en filteren. Selecteer pagina's om ze in één keer goed te keuren, af te wijzen, te pauzeren, te hervatten, te controleren, te verwijderen of hun interval te wijzigen.

## Eén oorzaak, één incident

Falen meerdere pagina's **om dezelfde reden** (bijvoorbeeld een TLS-fout op de hele site), dan opent ServerScope **één websiteincident** met de getroffen pagina's eronder. Een enkele pagina met een `404` blijft een eigen incident. Zie [Afhankelijkheden en gegroepeerde incidenten](dependencies) voor groeperen over servers en websites heen.

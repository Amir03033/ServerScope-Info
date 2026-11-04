---
order: 6
icon: link
category: monitoring
feature: correlation
description: Vastleggen welke website op welke server draait, zodat één storing één incident wordt met de getroffen onderdelen eronder, en alles zien in NEXUS.
---
# Afhankelijkheden en gegroepeerde incidenten

Valt een server uit, dan vallen alle websites erop ook uit. Zonder context krijg je een stroom losse meldingen. Met **afhankelijkheden** weet ServerScope hoe je onderdelen samenhangen en opent het **één incident** voor de oorzaak, met de getroffen websites, pagina's en services eronder.

## Een afhankelijkheid vastleggen

Teameigenaren en -beheerders leggen afhankelijkheden vast.

1. Open [Afhankelijkheden]({app_url}/dependencies) in de zijbalk (groep **Monitoring**).
2. Kies onder **Afhankelijkheid toevoegen** het onderdeel en waar het van afhangt. Onderdelen zijn servers, websites, websitepagina's en services.
3. Voeg eventueel een omschrijving toe, bijvoorbeeld `draait op` of `database`, en notities.
4. Sla op.

Lees een afhankelijkheid als: *het onderdeel werkt alleen als het onderdeel waarvan het afhangt werkt*. Een typische keten is server → Nginx → website → MySQL. Pagina's horen automatisch bij hun website en services bij hun server; die leg je niet vast.

> [!NOTE]
> ServerScope weigert een afhankelijkheid die een kringloop zou maken (A hangt af van B, B van A).

## Voorgestelde relaties

ServerScope stelt zelf relaties voor, bijvoorbeeld als een domein in DNS naar het IP-adres van een van je servers wijst. Voorstellen staan op de server- en domeinpagina en worden **pas gebruikt** als je ze bevestigt.

Hetzelfde paneel toont wat er mogelijk geraakt wordt bij een storing; handig vóór onderhoud. Voor het hele plaatje open je [NEXUS](dashboard-nexus): de kaart toont elke bevestigde afhankelijkheid en wat er omvalt als iets faalt.

## Hoe incidenten worden gegroepeerd

Een incident wordt alleen onder een ander incident (het **hoofdincident**) gegroepeerd als:

- er een **bevestigde** afhankelijkheid tussen de twee onderdelen is, of het om hetzelfde onderdeel gaat, **en**
- het hoofdincident de storing kan verklaren, **en**
- beide kort na elkaar begonnen (binnen 30 minuten).

Storingen die alleen toevallig tegelijk gebeuren, worden **nooit** samengevoegd. Bij elk gegroepeerd incident staat waarom het is gegroepeerd, met het pad van de afhankelijkheid. Groeperen is geen bewijs van de oorzaak: elk gegroepeerd incident herstelt op basis van zijn eigen controles. Een beheerder kan een incident uit de groep halen.

## Wat dit betekent voor meldingen

- Zolang het hoofdincident actief is, stuurt een gegroepeerd incident **geen eigen melding**: je krijgt één e-mail, pushmelding of chatbericht voor de storing.
- Algemene webhooks ontvangen wel elk incident, met `parent_id` en `correlation`, zodat je eigen systeem dezelfde keuze kan maken.
- Alleen het hoofdincident escaleert.

## Tips

- Begin met het voor de hand liggende: welke websites op welke server draaien.
- Voeg services (Nginx, PHP-FPM, MySQL) toe zodra de agent ze meldt; zie [Diensten bewaken](service-checks).
- Controleer na een storing de reden van groeperen op de incidentpagina en pas afhankelijkheden zo nodig aan.

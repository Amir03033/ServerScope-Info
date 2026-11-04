---
order: 4
icon: search
category: alerts
feature: root-cause
description: Vind de oorzaak van een incident met de oorzaakanalyse, de AI-interpretatie en de Time Machine, leg vast wat je leerde en deel een nabeschouwing.
---
# Incidenten onderzoeken en nabeschouwingen

ServerScope helpt je van de eerste melding tot de geleerde lessen. Deze handleiding gaat over de hulpmiddelen op de incidentpagina; de basis van alerts, statussen en meldingen staat in [Alerts en incidenten](alerts-incidents).

## De incidentpagina

De incidentpagina begint met een overzicht van de kerncijfers (duur, tijd tot detectie, tijd tot bevestiging, getroffen onderdelen) en een stappenbalk die laat zien waar je bent: bevestigen, de waarschijnlijke oorzaak vinden, oplossen, herstel bevestigen. Een vaste zijkolom houdt de details en acties in beeld.

## Oorzaakanalyse

Elk incident wordt automatisch onderzocht met vaste regels, op alle plannen. De analyse geeft:

- de **meest waarschijnlijke verklaring**, met een betrouwbaarheid;
- het **bewijs** voor en tegen;
- **alternatieven** en wat nog **onzeker** is.

"Oorzaak niet vastgesteld" is een geldige uitkomst: de gegevens wijzen dan niet duidelijk één kant op.

### AI-interpretatie (Pro en Premium)

Op Pro en Premium interpreteert een AI hetzelfde onderzoek. De AI kan alleen verwijzen naar bewijs dat ServerScope heeft aangeleverd; verwijzingen naar iets anders worden verwijderd en geteld. De pagina toont de conclusie van de regels en van de AI naast elkaar, met betrouwbaarheidsmeters, en zegt het als **AI en regels het eens zijn**.

> [!IMPORTANT]
> De AI verandert nooit iets en lost nooit een incident op. De uitkomst is een hypothese; alleen een mens markeert een oorzaak als bevestigd. Herstel wordt altijd gecontroleerd met live monitoringresultaten.

## Time Machine

De **Time Machine** laat zien wat er gebeurde voor, tijdens en na het incident: de tijdlijn, de metingen en het bewijs. Waargenomen feiten met hun gemeten waarden staan apart van mogelijke verklaringen; die zijn suggesties om te controleren, geordend op hoe direct het bewijs met het incident samenhangt (een bevestigde afhankelijkheid eerst).

Deploys, DNS-wijzigingen en logfouten uit dezelfde periode staan in hetzelfde overzicht.

## Logs rond dit incident

Volgt de agent je logbestanden, dan toont het incident de fouten en waarschuwingen op de betrokken servers, van kort vóór het begin tot het einde. Zie [Logs](logs).

## Incidentgeheugen

Leg vast wat je leerde, zodat het volgende vergelijkbare incident sneller is opgelost:

1. Klik bij het incident op **Oorzaak en oplossing vastleggen**.
2. Vul de **Oorzaak** in, de **Soort oorzaak** en de **Zekerheid**: *Vermoeden (niet geverifieerd)* of *Bevestigde oorzaak*.
3. Voeg de **Symptomen** toe (gescheiden door komma's, bijvoorbeeld `502-fouten, trage login`), de **Oplossingsstappen**, of de oplossing werkte en, als dat speelt, de bijbehorende deployment.
4. Sla op.

Het incident toont dan eerdere incidenten van je team die erop lijken, en zoeken doorzoekt ook deze lessen.

## Nabeschouwing

Vanuit het incidentgeheugen open je met één klik een **Nabeschouwing**. Die is ingevuld met wat er is vastgelegd: de duur, de tijd tot detectie en bevestiging, de impact, een tijdlijn met deploys, de oorzaak en de oplossing. Zelf vul je aan:

- een **Samenvatting**: wat er gebeurde, wie het merkte, hoe het afliep;
- de **Impact** op klanten, bestellingen of teams;
- **Wat ging goed** en **Wat kan beter**;
- **Actiepunten**, elk met een eigenaar.

### Delen

- **Delen → Link maken** maakt een openbare, alleen-lezen link voor klanten of collega's buiten ServerScope. Alleen de nabeschouwing is zichtbaar; interne notities gaan nooit mee. **Delen stoppen** laat de link direct niet meer werken. Alleen teambeheerders kunnen delen.
- **Markdown** downloadt de nabeschouwing als Markdown-bestand.
- **Afdrukken of pdf** opent een afdrukbare versie.

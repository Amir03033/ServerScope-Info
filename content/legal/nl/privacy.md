---
title: Privacyverklaring
description: Welke persoonsgegevens ServerScope verwerkt, waarom, hoe lang, en welke rechten je hebt.
version: 0.1
changes: Eerste concept.
---
<!-- JURIST: dit is een concept op basis van de code van ServerScope en info.serverscope.nl (oktober 2026). Toets in het bijzonder de grondslagen (hoofdstuk 4), de rolverdeling (hoofdstuk 2) en doorgifte buiten de EER (hoofdstuk 7). -->

## 1. Wie wij zijn

> [!SUMMARY]
> {company.name} maakt ServerScope. Vragen over privacy stuur je naar {company.privacy_email}.

Deze privacyverklaring geldt voor de website info.serverscope.nl en voor de ServerScope-applicatie. Verantwoordelijk is:

{company.name}, {company.address}.

Voor vragen over privacy of om je rechten uit te oefenen: {company.privacy_email}.

## 2. Wanneer wij verantwoordelijk zijn en wanneer de klant

> [!SUMMARY]
> Voor je account, de website, de wachtlijst en het contactformulier zijn wij verantwoordelijk. Voor wat een klant met ServerScope bewaakt, is de klant verantwoordelijk en verwerken wij in opdracht.

**Wij zijn verwerkingsverantwoordelijke** voor:

- accounts en teams in ServerScope;
- beveiliging van de dienst (sessies en het auditlog);
- de website info.serverscope.nl, de wachtlijst en het contactformulier.

**Wij zijn verwerker** voor de gegevens die een klant met ServerScope laat verzamelen: metingen van servers, controles van websites en domeinen, en meldingen aan ontvangers die de klant opgeeft. Daarvoor sluiten wij met de klant een verwerkersovereenkomst. Wil je weten wat een klant met jouw gegevens doet, neem dan contact op met die klant.

## 3. Welke gegevens wij verwerken

> [!SUMMARY]
> We verwerken alleen wat nodig is om ServerScope te laten werken en te beveiligen. De agent leest geen bestanden en voert geen commando's uit.

### 3.1 Accounts

Naam, e-mailadres, wachtwoord (alleen opgeslagen als hash), gegevens voor tweestapsverificatie (de geheime sleutel versleuteld, de herstelcodes als hash), je meldingsvoorkeuren, de teams waarvan je lid bent en je rol daarin.

### 3.2 Sessies in de applicatie

Als je bent ingelogd in de ServerScope-applicatie, bewaren we bij je sessie het IP-adres en de user agent van je browser.

### 3.3 Auditlog

Bij beheeracties in de applicatie bewaren we wie de actie uitvoerde, wat er gebeurde, het tijdstip en het IP-adres. Geheimen zoals tokens en wachtwoorden worden nooit in het auditlog opgeslagen.

### 3.4 Servers van klanten (via de agent)

De agent stuurt: hostname, IP-adres, besturingssysteem, agentversie, CPU, geheugen, swap, schijfgebruik, schijfactiviteit, netwerkverkeer en netwerkfouten, load, het aantal processen, uptime, en de status van services die de klant zelf opgeeft. De agent leest **geen bestandsinhoud en geen logbestanden** en voert **geen commando's op afstand** uit.

Bij het koppelen van een server met een koppeltoken bewaren we het IP-adres waarvandaan de server is gekoppeld.

### 3.5 Website- en domeinmonitoring

URL's, HTTP-antwoorden, responstijden, TLS-certificaten, DNS-records en de verloopdatum van domeinen (via RDAP). Bij **inhoudsmonitoring** bewaren we de tekst van de bewaakte pagina en de verschillen tussen controles. Staan er persoonsgegevens op die pagina, dan worden die dus ook bewaard.

### 3.6 Meldingen

E-mailadressen van ontvangers en een log van verzonden meldingen. Webhook-adressen van de klant (bijvoorbeeld van Discord) worden versleuteld bewaard.

### 3.7 Website info.serverscope.nl

- **Wachtlijst:** je e-mailadres, taal, gekozen abonnement, de toestemmingstekst die je zag en het tijdstip van je toestemming.
- **Contactformulier:** je naam, e-mailadres, onderwerp en bericht. Die sturen we per e-mail door; de website bewaart het bericht niet.
- **Bezoekersteller:** alleen het pad van de pagina, de dag en het aantal weergaven. Geen IP-adres, geen cookie en niets over jou.
- **"Was dit nuttig?":** alleen je antwoord en je eventuele opmerking.

De website bewaart van bezoekers geen IP-adres of user agent.

## 4. Waarom en op welke grondslag

> [!SUMMARY]
> Voor je account omdat we de dienst moeten leveren, voor beveiliging omdat dat een gerechtvaardigd belang is, en voor de wachtlijst omdat je toestemming geeft.

| Verwerking | Doel | Grondslag |
|---|---|---|
| Accounts en teams | De dienst leveren | Uitvoering van de overeenkomst |
| Sessies en auditlog | Beveiliging en misbruik voorkomen | Gerechtvaardigd belang |
| Koppel-IP van servers | Beveiliging van het koppelen | Gerechtvaardigd belang |
| Wachtlijst | Je laten weten wanneer registratie opengaat | Toestemming |
| Contactformulier | Je vraag beantwoorden | Gerechtvaardigd belang, of de voorbereiding van een overeenkomst |
| Monitoringgegevens van klanten | Monitoring voor de klant | In opdracht van de klant (verwerkersovereenkomst) |

Je toestemming voor de wachtlijst kun je altijd intrekken met de persoonlijke link die je na het aanmelden kreeg, of door een e-mail aan {company.privacy_email}.

## 5. Hoe lang wij gegevens bewaren

> [!SUMMARY]
> Bewaartermijnen zijn per soort gegevens ingesteld. Staat er geen termijn, dan worden de gegevens niet automatisch verwijderd.

| Gegevens | Bewaartermijn |
|---|---|
| Wachtlijst | {retention.waitlist} |
| Contactberichten (in onze mailbox) | {retention.contact} |
| Accounts na opzegging | {retention.accounts} |
| Metingen van servers | {retention.metrics} |
| Domeincontroles | {retention.domain_checks} |
| Paginacontroles | {retention.page_checks} |
| Paginawijzigingen (inhoud) | {retention.page_changes} |
| Verzendlog van meldingen | {retention.notification_logs} |
| Gelezen meldingen | {retention.notifications} |
| Auditlog | {retention.audit_logs} |
| Service-events | {retention.service_events} |
| Voorspellingen | {retention.predictions} |

## 6. Met wie wij gegevens delen

> [!SUMMARY]
> Alleen met onze hostingprovider en e-maildienst. We verkopen niets en gebruiken geen tracking van derden.

- **Hosting van de applicatie:** {company.host_app} ({company.host_app_country}).
- **Hosting van deze website:** {company.host_site} ({company.host_site_country}).
- **E-mail:** {company.mail_service} ({company.mail_service_country}), voor meldingen, uitnodigingen en het contactformulier.

Daarnaast gaan meldingen naar de bestemmingen die een klant zelf instelt, zoals Discord of een eigen webhook.

We gebruiken geen advertentie- of analysediensten van derden.

## 7. Doorgifte buiten de Europese Economische Ruimte

> [!SUMMARY]
> Dat hangt af van waar onze dienstverleners zitten; dat staat in hoofdstuk 6.

<!-- JURIST: als een hosting- of e-maildienst buiten de EER zit, hier de doorgiftegrondslag vermelden (adequaatheidsbesluit of standaardcontractbepalingen). -->

We geven persoonsgegevens alleen door buiten de EER als daarvoor een geldige grondslag is volgens de AVG.

## 8. Beveiliging

> [!SUMMARY]
> Wachtwoorden en tokens bewaren we alleen als hash, verbindingen gaan via HTTPS, en elke klant ziet alleen zijn eigen gegevens.

- Wachtwoorden, API-tokens, agenttokens en koppeltokens worden alleen als hash bewaard.
- Tweestapsverificatie (TOTP met herstelcodes) is beschikbaar.
- Alle verbindingen gaan via HTTPS; de agent gebruikt TLS 1.2 of hoger.
- Elk team ziet alleen zijn eigen gegevens.
- Beheeracties worden vastgelegd in het auditlog.
- Webhook-adressen worden versleuteld bewaard.

## 9. Je rechten

> [!SUMMARY]
> Je mag je gegevens inzien, laten verbeteren of verwijderen, en een klacht indienen bij de Autoriteit Persoonsgegevens.

Je hebt het recht op inzage, rectificatie, verwijdering, beperking van de verwerking, overdraagbaarheid en bezwaar, en het recht om je toestemming in te trekken. Stuur je verzoek naar {company.privacy_email}.

Ben je het niet eens met hoe wij met je gegevens omgaan? Dan kun je een klacht indienen bij de Autoriteit Persoonsgegevens (autoriteitpersoonsgegevens.nl).

## 10. Wijzigingen

> [!SUMMARY]
> Als deze verklaring verandert, staat de nieuwe versie hier, met de ingangsdatum en een overzicht van de wijzigingen.

Eerdere versies blijven beschikbaar via de link "Eerdere versies" bovenaan deze pagina.

# Juridische teksten — CONCEPT-ONDERSTEUNING, geen juridisch advies

Dit is een checklist voor wat in de algemene voorwaarden en de privacyverklaring moet komen, afgeleid uit wat
ServerScope daadwerkelijk doet. De definitieve teksten moeten door een jurist (of een betrouwbare sjabloondienst)
worden opgesteld en beoordeeld. Niets hiervan is op de site gepubliceerd.

## Gegevens die ServerScope verwerkt (uit de code)
- Account: naam, e-mailadres, wachtwoord (gehasht), taal, optioneel 2FA-geheim, auditlog (acties, tijdstip).
- Team: teamnaam, leden en rollen, uitnodigingen (e-mail, 7 dagen bewaard tot gebruik/verloop).
- Monitoring: servernamen, hostnamen, IP-adressen, metrics (CPU/geheugen/schijf), domeinen en gecontroleerde pagina's,
  uptime-, TLS- en DNS-resultaten, incidenten, webhook-URL's (Discord-URL versleuteld).
- Betaling: betaalstatus, plan, Mollie-klant-/abonnements-ID, factuurgegevens (bedrijfsnaam, adres, btw-nummer).
  Kaart-/bankgegevens komen nooit in ServerScope; die staan bij Mollie.
- Wachtlijst (Info): e-mailadres, taal, gekozen plan, toestemmingstekst en -moment.

## Verwerkers / ontvangers om te noemen
Hosting (Plesk/provider), mailprovider (MAIL_*), Mollie (betalingen), eventueel Discord (door de klant ingestelde webhooks).

## Bewaartermijnen om vast te leggen
Metrics en domeincontroles: Free 30 dagen, Pro 365 dagen, Premium onbeperkt (config `serverscope.plans`). Overige
historie: volgens `SERVERSCOPE_RETAIN_*`. Facturen: wettelijke bewaarplicht (fiscaal 7 jaar) — verwijder die dus niet op verzoek.

## Punten voor de algemene voorwaarden
- Wie is de aanbieder (naam, KvK, btw, adres) — pas in te vullen na registratie.
- Abonnementen, prijzen incl./excl. btw, verlenging per automatische incasso, opzegging (loopt door tot einde periode),
  gevolgen van downgrade (gegevens blijven, limieten en bewaartermijn veranderen).
- Mislukte betaling: coulanceperiode (nu 7 dagen), daarna terug naar Free.
- Beschikbaarheid: geen SLA-belofte zolang er geen SLA is; monitoring is indicatief.
- Toegestaan gebruik: alleen eigen of toegestane systemen monitoren (de SSRF-/doelbeperkingen staan in de app).
- Herroepingsrecht: bij consumenten 14 dagen bij digitale dienst, tenzij uitdrukkelijk afgezien bij directe levering;
  bij zakelijke klanten niet van toepassing. Bepaal of je consumenten toelaat.
- Aansprakelijkheid, toepasselijk recht (NL), geschillen.

## Privacyverklaring (AVG)
Grondslagen (uitvoering overeenkomst, toestemming voor wachtlijst), doelen, bewaartermijnen, rechten (inzage, correctie,
verwijdering, bezwaar), contactgegevens, klachtrecht bij de AP. Voor klantgegevens in monitoring ben jij verwerker
voor je klanten: overweeg een verwerkersovereenkomst die zij kunnen accepteren.

## Nog niet in de app
Een checkbox "ik ga akkoord met de voorwaarden" bij registratie en de bijbehorende pagina's. Bouw ik zodra de
definitieve teksten er zijn, zodat er geen voorlopige juridische tekst live gaat.

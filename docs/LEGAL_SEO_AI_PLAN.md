# Plan: juridische pagina's, SEO en vindbaarheid door AI

Status: **wacht op akkoord**. Er is nog geen code gewijzigd.
Branch: `claude/legal-seo-ai`, vanaf de revamp-branch (`claude/relaxed-hypatia-rm4ddm`, PR #1 is nog open).
Merge PR #1 eerst; daarna wordt deze branch een eigen, kleinere pull request.

---

## 0. Wat ik in de code heb nagekeken

Ik heb jouw lijst met verwerkingen vergeleken met de ServerScope-repository
(`main`, commit 9e868b8) en de code van de info-site. Op een paar punten
klopt de lijst niet helemaal of is hij niet compleet:

| Punt | Wat de code laat zien | Gevolg |
|---|---|---|
| Thema-keuze op de info-site | Dit is **geen cookie**. De keuze staat in `localStorage` (`ss-theme`), alleen in de browser, en gaat nooit naar de server. | De cookieverklaring noemt het correct als lokale opslag, niet als cookie. |
| Sessies op de info-site | `SESSION_DRIVER=database`. Laravel slaat dan bij **elke bezoeker** het IP-adres en de user agent op in de tabel `sessions`, niet alleen bij ingelogde gebruikers. | **Beslissing nodig (vraag 1).** |
| Cookies op de info-site | De sessiecookie heet `{APP_NAME}-session` (standaard `laravel-session`), plus `XSRF-TOKEN`. In `/admin` zijn het dezelfde cookies. | Zo vermeld ik ze. |
| Cookies in de ServerScope-app | Dezelfde sessie- en XSRF-cookie. Daarnaast `remember_web_*`, maar alleen als iemand bij het inloggen "onthoud mij" aanvinkt. | Aparte paragraaf voor de app (serverscope.nl). |
| Koppeltokens | `enrollment_tokens.used_ip`: het IP-adres waarvandaan de server is gekoppeld. Plus `reported`: hostname, besturingssysteem, architectuur en agentversie. | Klopt met jouw lijst. |
| Auditlog | `audit_logs.ip_address` plus gebruiker, actie en tijdstip. | Klopt. |
| Servers | `hostname`, `ip_address`, `operating_system`, `agent_version`, `last_seen_at`. | Klopt. |
| Inhoudsmonitoring | `monitored_pages.content_snapshot` (genormaliseerde tekst, ingekort) en de tabel `page_changes` met de verschillen. | Klopt; wordt expliciet genoemd. |
| Verzendlog van meldingen | `notification_logs.recipient` (e-mailadres van de ontvanger). | Klopt. |
| **Externe diensten die de app zelf raadpleegt** | **DNS-lookups** gaan standaard naar 1.1.1.1 (Cloudflare), 8.8.8.8 (Google) en 9.9.9.9 (Quad9), instelbaar met `SERVERSCOPE_DNS_NAMESERVERS`. **RDAP** gaat naar `data.iana.org` en de registries. Discord en webhook-adressen kiest de klant zelf. | Deze ontvangen **domeinnamen**, geen accountgegevens. Ze komen in de verwerkersovereenkomst als "externe bronnen die worden geraadpleegd", niet als subverwerker. **Vraag 6.** |
| Bewaartermijnen | Instelbaar per soort met `SERVERSCOPE_RETAIN_*`: metingen, domeincontroles, meldingslog, gelezen meldingen, auditlog, paginacontroles, paginawijzigingen, ontdekrondes, service-events en voorspellingen. Standaard is er geen termijn: alles wordt bewaard. | Plaatshouders per soort in Filament. Is een veld leeg, dan zegt de tekst "zolang het account bestaat, tenzij anders ingesteld". Ik noem geen getal dat jij niet hebt ingevuld. |

---

## 1. Deel 1: juridische pagina's

### Datamodel (alle migraties additief)

```
legal_documents   id, key (privacy|cookies|imprint|terms|dpa|aup|disclosure),
                  locale, slug, title, sort, timestamps
                  unique(key, locale), unique(locale, slug)
legal_versions    id, legal_document_id, version (bijv. "1.0"), effective_at (datum),
                  body (Markdown), changes (Markdown: wat er veranderd is),
                  status (draft|published), published_at, legal_reviewed (bool),
                  created_by, updated_by, timestamps
legal_revisions   id, legal_version_id, user_id, reason, body, changes, meta, created_at
                  (wie wijzigde wat; terugzetten, net als bij documentatie)
company_settings  via de bestaande tabel `settings`, onder de sleutels `company.*`
```

- **Publiceren** van een versie maakt die de geldende versie vanaf
  `effective_at`. Oudere gepubliceerde versies blijven openbaar onder
  "eerdere versies".
- **Concept-melding.** Bovenaan elk concept staat in Filament een duidelijke
  oranje balk: "Concept: nog niet juridisch gecontroleerd". De balk
  verdwijnt pas als je het vinkje "Juridisch gecontroleerd" aanzet. Op de
  site staat deze tekst nooit.
- **Publiceren is geblokkeerd** zolang een bedrijfsgegeven dat de tekst
  gebruikt leeg is. Filament toont dan welke velden ontbreken.

### Plaatshouders in de teksten

Teksten gebruiken `{company.name}`, `{company.legal_form}`,
`{company.address}`, `{company.kvk}`, `{company.vat}`, `{company.email}`,
`{company.privacy_email}`, `{company.security_email}`, `{company.host}`,
`{company.host_country}`, `{company.mail_service}`,
`{company.mail_service_country}`, `{company.audience}` en
`{retention.*}`. De site vult ze automatisch in.

Elk document heeft zijn eigen lijst verplichte velden. Een voorbeeld:
voor het colofon zijn naam, rechtsvorm, adres en KvK-nummer verplicht.

### Pagina's en URL's

| Document | NL | EN |
|---|---|---|
| Privacyverklaring | `/nl/privacy` | `/en/privacy` |
| Cookieverklaring | `/nl/cookies` | `/en/cookies` |
| Colofon / bedrijfsgegevens | `/nl/colofon` | `/en/imprint` |
| Algemene voorwaarden (concept) | `/nl/voorwaarden` | `/en/terms` |
| Verwerkersovereenkomst (concept) | `/nl/verwerkersovereenkomst` | `/en/dpa` |
| Acceptable use policy | `/nl/acceptabel-gebruik` | `/en/acceptable-use` |
| Responsible disclosure | `/nl/responsible-disclosure` | `/en/responsible-disclosure` |
| security.txt (RFC 9116) | `/.well-known/security.txt` | |

- Eerdere versies staan onder `/{taal}/{slug}/versies` en
  `/{taal}/{slug}/versie/{versie}`.
- De taalwissel en hreflang koppelen de NL- en EN-slug aan elkaar.
- De **voorwaarden** en de **verwerkersovereenkomst** blijven concept tot jij
  ze publiceert; tot die tijd geven die URL's 404. De andere vijf documenten
  komen ook eerst als concept; je publiceert ze na controle.

### Inhoud van de concepten

Ik schrijf de teksten zelf, eerlijk en in gewone taal, en baseer ze alleen
op de tabel in §0 en jouw lijst.

- **Privacyverklaring:**
  - de rolverdeling: ServerScope is verwerkingsverantwoordelijke voor
    accounts, de info-site, de wachtlijst en het contactformulier, en
    verwerker voor de monitoringgegevens van klanten;
  - per verwerking: welke gegevens, het doel, de grondslag, de bewaartermijn
    (uit Filament), en de ontvangers (hosting, e-maildienst);
  - de rechten van betrokkenen, en het recht om een klacht in te dienen bij
    de Autoriteit Persoonsgegevens;
  - de voorgestelde grondslagen markeer ik als "laten toetsen door een
    jurist".
- **Cookieverklaring:**
  - alleen functionele cookies (sessie en CSRF; in de app ook "onthoud
    mij"), plus de thema-keuze in `localStorage`;
  - de bezoekersteller zonder cookies;
  - waarom er geen banner nodig is: de cookiewet (artikel 11.7a
    Telecommunicatiewet) geldt niet voor cookies die strikt noodzakelijk zijn
    voor de dienst. Dit markeer ik als "laten toetsen door een jurist".
- **Colofon:** alleen velden uit Filament.
- **Algemene voorwaarden:**
  - genummerde artikelen, met bij elk hoofdstuk een kader "In het kort";
  - onderwerpen: definities, de dienst, account en team, abonnementen
    (prijzen uit de prijspagina, verder plaatshouders), beschikbaarheid
    zonder beloofd uptimepercentage, acceptabel gebruik (verwijzing naar de
    AUP), gegevens en privacy (verwijzing naar de verwerkersovereenkomst),
    aansprakelijkheid, beëindiging, wijzigingen, en toepasselijk recht;
  - de keuzes daarin zijn **plaatshouders** die jij of een jurist invult. Ik
    kies niets voor je.
- **Verwerkersovereenkomst:**
  - artikelen volgens artikel 28 AVG;
  - bijlage A: verwerkte gegevens en categorieën betrokkenen (uit §0);
  - bijlage B: subverwerkers (hosting en e-maildienst uit Filament; eventueel
    meer, zie vraag 6);
  - bijlage C: beveiligingsmaatregelen, alleen wat in de code staat:
    gehashte tokens, 2FA, auditlog, geen remote commands, ondertekende
    releases, TLS 1.2+, SSRF-bescherming, versleutelde webhook-adressen,
    teamscheiding, en instelbare bewaartermijnen.
- **Acceptable use policy:** de drie verboden uit je opdracht (monitoren
  zonder toestemming, checks misbruiken voor aanvallen of scans, limieten
  omzeilen). Ik voeg alleen dingen toe die rechtstreeks uit de functies
  volgen, en die laat ik apart zien.
- **Responsible disclosure:**
  - wat je kunt melden en hoe, wat je mag verwachten, en wat je niet mag
    doen (geen DoS, geen social engineering, geen gegevens van anderen
    inzien);
  - over een beloning zeg ik niets, tenzij je die zelf invult.
- **security.txt** bevat:
  - `Contact:` (security-contactadres);
  - `Expires:` (een datum die je in Filament instelt; maximaal een jaar
    vooruit, met een waarschuwing 30 dagen ervoor);
  - `Preferred-Languages: nl, en`;
  - `Canonical:`;
  - `Policy:` (de link naar de responsible-disclosure-pagina).

  Zonder contactadres of verloopdatum geeft deze URL 404.
- **Waar ook security.txt hoort:** de ServerScope-app (serverscope.nl) heeft
  een eigen `/.well-known/security.txt` nodig. Die staat in een andere
  repository; dat noem ik als vervolgstap.

### Ontwerp van de juridische pagina's

- Een smalle tekstkolom (ongeveer 68 tekens), genummerde artikelen en per
  hoofdstuk een kader **"In het kort"**. In Markdown schrijf je dat als
  `> [!SUMMARY]`. Het kader vermeldt altijd: "De volledige tekst hieronder
  geldt."
- Bovenaan: de versie, de ingangsdatum, de link "Eerdere versies" en een
  knop **Afdrukken / Opslaan als PDF** (via de browser).
- Een meelopende inhoudsopgave (die bestaat al voor de documentatie), een
  ankerlink met kopieerknop per artikel, en een printstijl: geen navigatie,
  links uitgeschreven, en pagina-einden tussen hoofdstukken.
- Een "Wijzigingen per versie"-overzicht onderaan, en een aparte
  versiepagina.
- De footer krijgt een nieuwe kolom "Juridisch" met alleen gepubliceerde
  documenten.
- Onder de wachtlijst en het contactformulier komt een zin met een link naar
  de privacyverklaring. Die verschijnt pas zodra de privacyverklaring
  gepubliceerd is.

---

## 2. Deel 2: SEO

- **Title en description per pagina, beheerbaar in Filament:**
  - een nieuwe tabel `page_meta` (route en taal, title, description,
    noindex) voor alle vaste pagina's: home, functies, prijzen, changelog,
    status, contact, wachtlijst en het documentatie-overzicht;
  - documentatie, juridische pagina's en landingspagina's hebben eigen
    SEO-velden;
  - Filament toont een **lengte-indicator**: groen bij 30–60 tekens voor de
    title en 70–160 voor de description, oranje erbuiten.
- **Technisch:**
  - canonical-URL's, zonder querystrings;
  - hreflang nl/en met x-default, ook voor pagina's met een verschillende
    slug per taal;
  - de sitemap krijgt de nieuwe pagina's, `lastmod` is gecontroleerd, en
    concepten en noindex-pagina's staan er niet in;
  - een nette **404-pagina** in de huisstijl, met de zoekfunctie en links;
  - breadcrumbs, zichtbaar én als JSON-LD.
  - Dubbele inhoud:
    - `/` geeft een 301 naar `/nl`;
    - de Markdown-versies hebben een canonical naar de HTML-versie (via een
      `Link`-header);
    - `?q=` krijgt noindex, follow.
- **Gestructureerde data (JSON-LD):**

  | Type | Waar | Opmerking |
  |---|---|---|
  | Organization | elke pagina | alleen velden die zijn ingevuld; `sameAs` alleen als je links opgeeft |
  | WebSite | elke pagina | met SearchAction naar `/{taal}/docs?q={search_term_string}` |
  | SoftwareApplication | home, functies, "Wat is ServerScope" | zonder `offers`, `aggregateRating` of `review` |
  | BreadcrumbList | alle pagina's behalve home | |
  | TechArticle | documentatie | met `dateModified` en `inLanguage` |
  | FAQPage | alleen als er echt zichtbare vragen op de pagina staan | |

  Eerlijk om te weten:
  - Google toont voor SoftwareApplication zonder prijs en beoordelingen
    geen rich result. De gegevens zijn wel geldig en nuttig voor
    zoekmachines en AI.
  - FAQ-rich results toont Google sinds 2023 vrijwel alleen voor
    overheids- en zorgsites.

  Ik schrijf tests die de structuur en de verplichte velden controleren.
  Validatie met validator.schema.org en de Rich Results Test van Google kan
  hier niet (geen internet); die doe ik met jou op staging. **Vraag 7.**
- **Deelafbeeldingen (Open Graph en Twitter):**
  - per pagina een automatisch gemaakte afbeelding van 1200×630 in de
    huisstijl: titel, sectie, het ServerScope-merk en het licht thema;
  - gemaakt met PHP's **GD**-extensie en het lettertype Inter als TTF (SIL
    OFL), en gecachet in `storage`;
  - is GD niet beschikbaar, dan valt de site terug op één vaste
    deelafbeelding;
  - er is geen Composer- of npm-pakket nodig. **Vraag 8:** GD ontbreekt in
    mijn testomgeving, en de TTF moet ik downloaden van de officiële
    Inter-release.
- **Landingspagina's:** zie §4. Ik schrijf ze pas na je akkoord.
- **Test:** een test die alle publieke pagina's opvraagt (vaste pagina's,
  documentatie, juridische pagina's en landingspagina's) en faalt bij een
  ontbrekende of dubbele title, een ontbrekende description of canonical,
  of ongeldige JSON-LD.
- **Lighthouse SEO:** ik meet vóór en na; het doel blijft 100.

---

## 3. Deel 3: vindbaar en leesbaar voor AI

- **`/llms.txt`** volgens het voorstel op llmstxt.org:
  - een H1, een samenvatting van één alinea, en secties met links;
  - alleen gepubliceerde documentatie en functies met de status
    **beschikbaar**;
  - wordt automatisch opgebouwd uit de database en bijgewerkt vanuit
    Filament.

  **`/llms-full.txt`** bevat de volledige Markdown van die pagina's.
  Voorstel: `/llms.txt` en `/llms-full.txt` in het Engels met links naar
  de EN-documentatie, en `/nl/llms.txt` en `/nl/llms-full.txt` in het
  Nederlands.
- **Markdown per pagina:**
  - `/{taal}/docs/{slug}.md` geeft de platte Markdown terug
    (`text/markdown`), met titel, korte omschrijving en bron-URL bovenaan
    en `{app_url}` ingevuld;
  - de header `Link: <…html-url>; rel="canonical"` wijst naar de
    HTML-versie;
  - de HTML-pagina verwijst ernaar met
    `<link rel="alternate" type="text/markdown">`;
  - hetzelfde voor juridische pagina's en landingspagina's.
- **`robots.txt` per crawler, met een schakelaar per crawler in Filament:**

  | Crawler | Van | Wat het doet | Voorstel |
  |---|---|---|---|
  | OAI-SearchBot | OpenAI | index voor ChatGPT-zoekresultaten | toestaan |
  | ChatGPT-User | OpenAI | haalt een pagina op als een gebruiker erom vraagt | toestaan |
  | PerplexityBot | Perplexity | zoekindex | toestaan |
  | Perplexity-User | Perplexity | ophalen op verzoek van een gebruiker | toestaan |
  | Claude-SearchBot | Anthropic | zoekindex voor Claude | toestaan |
  | Claude-User | Anthropic | ophalen op verzoek van een gebruiker | toestaan |
  | **ClaudeBot** | Anthropic | **verzamelt trainingsdata** (staat in je lijst bij de zoekcrawlers, maar volgens Anthropic is dit de trainingscrawler) | jouw keuze |
  | GPTBot | OpenAI | trainingsdata | jouw keuze |
  | Google-Extended | Google | geen crawler, maar een token: mag Gemini je pagina's voor training en grounding gebruiken? Google Search zelf verandert niet. | jouw keuze |
  | CCBot | Common Crawl | open webarchief, veel gebruikt voor training | jouw keuze |
  | Applebot-Extended | Apple | token voor training (Apple-zoekfuncties blijven werken) | jouw keuze |

  Het verschil:
  - **Zoek- en gebruikerscrawlers** halen pagina's op om antwoorden te
    geven met een link naar je site. Ze leveren dus bezoekers en
    bronvermelding op.
  - **Trainingscrawlers** verzamelen tekst om modellen te trainen, zonder
    directe link of bezoek.

  Mijn voorstel: zoek- en gebruikerscrawlers **toestaan**. Voor
  trainingscrawlers geldt: de site is openbare productinformatie zonder
  persoonsgegevens, dus toestaan maakt het waarschijnlijker dat modellen
  ServerScope kennen. Het is een principiële keuze, dus de standaard kies
  jij (**vraag 3**).

  Altijd geldt:
  - `/admin`, de voorbeeldweergave en `/waitlist/remove` blijven voor
    iedereen uitgesloten;
  - op staging is alles `Disallow: /`, ongeacht de schakelaars;
  - robots.txt is een verzoek, geen afscherming.
- **Inhoud die AI goed kan citeren:**
  - een pagina **"Wat is ServerScope"** (`/nl/wat-is-serverscope`,
    `/en/what-is-serverscope`): bovenaan een antwoord van twee zinnen,
    daarna feiten in vaste bewoordingen, met links naar de documentatie;
  - een **begrippenlijst** (agent, koppeltoken, incident, alertregel,
    statuspagina, team …) voor vaste terminologie; de definities komen uit
    de bestaande documentatie;
  - een veld **"In het kort"** (korte antwoorden) bij documentatie en
    landingspagina's, bovenaan getoond. Bij de bestaande 16 pagina's blijft
    het **leeg**, zodat hun inhoud ongewijzigd blijft; je vult het zelf in.
  - **"ServerScope vergeleken"** (`/nl/vergelijken`, `/en/compare`): een
    tabel met criteria waarop je een monitoringtool kunt beoordelen (agent
    of agentloos, remote commands, ondertekende updates, statuspagina's,
    API, teams, zelf hosten …), ingevuld **alleen voor ServerScope**, met
    een link naar de bron in de documentatie. Ik noem geen concurrenten en
    doe geen beweringen over andere tools (**vraag 4**).
- **Geen trucs:** geen verborgen tekst, geen aparte inhoud voor bots en geen
  keyword stuffing. Een test controleert dat een bot dezelfde HTML krijgt
  als een browser.

---

## 4. Voorgestelde landingspagina's

Op echte zoekvragen, alleen met beweringen die in de documentatie staan.

| # | NL-zoekvraag en slug | Kern | Bronnen in de documentatie |
|---|---|---|---|
| 1 | "VPS monitoren" `/nl/vps-monitoren` | agent, metingen, koppelen met één commando | server-monitoring, connect-server |
| 2 | "Linux server monitoring agent" `/nl/linux-server-monitoring` | wat de agent wel en niet doet, Ubuntu en Debian, amd64 en arm64 | connect-server, agent-security, service-checks |
| 3 | "SSL-certificaat verloopt monitoren" `/nl/ssl-certificaat-monitoren` | geldigheid, keten, verlooptermijnen, wijzigingen | domain-monitoring |
| 4 | "uptime monitoring" `/nl/uptime-monitoring` | HTTP-checks per pagina, drempels, incidenten | domain-monitoring, alerts-incidents |
| 5 | "DNS-wijzigingen monitoren" `/nl/dns-wijzigingen-monitoren` | recordtypes, wijzigingsgeschiedenis, `dns.changed`-melding | domain-monitoring, api-webhooks |
| 6 | "domeinnaam verloopt melding" `/nl/domeinverloop-monitoren` | RDAP, geen verzonnen datums | domain-monitoring |
| 7 | "statuspagina maken" `/nl/statuspagina` | openbare en privé-pagina's, wat zichtbaar is | status-pages |
| 8 | "Discord melding server down" `/nl/discord-meldingen-server` | Discord-webhook, gebeurtenissen, geen @everyone | discord-notifications |
| 9 | "schijf vol voorspellen" `/nl/schijfruimte-voorspellen` | capaciteitsprognose met bandbreedte en betrouwbaarheid | changelog 26 oktober (hiervoor nog geen eigen handleiding) |
| 10 | "nginx/mysql service monitoren" `/nl/services-monitoren` | systemd-diensten, toestanden | service-checks |

Over "uptime monitoring **Nederland**": ik zeg alleen iets over Nederland
als jij hostingprovider en land invult, en dan uit die velden. Verder zijn
de app en de documentatie Nederlandstalig.

Elke pagina krijgt een eigen Engelse versie met een Engelse slug, en wordt
beheerd in Filament. Het datamodel is `pages` (sleutel, taal,
vertaalsleutel, slug, titel, "In het kort", Markdown, optionele zichtbare
vragen, SEO-velden, status, functie en revisies). Hangt een pagina aan een
functie die verborgen is, dan verdwijnt die pagina ook.

---

## 5. Bedrijfsgegevens die ik van je nodig heb (in Filament in te vullen)

Verplicht (blokkeren publicatie):

| Veld | Nodig voor |
|---|---|
| Bedrijfsnaam | alle documenten |
| Rechtsvorm (bijv. eenmanszaak, bv) | colofon, voorwaarden, verwerkersovereenkomst |
| Adres (straat en nummer, postcode, plaats, land) | colofon, privacy, voorwaarden, verwerkersovereenkomst |
| KvK-nummer | colofon, voorwaarden |
| Btw-nummer (leeg laten mag als je niet btw-plichtig bent; dan een vinkje) | colofon |
| Algemeen contactadres (e-mail) | colofon, voorwaarden |
| Privacy-contactadres | privacy, cookies, verwerkersovereenkomst |
| **Security-contactadres** (nieuw, voor security.txt; mag hetzelfde zijn) | responsible disclosure, security.txt |
| Hostingprovider en land: info-site | privacy, verwerkersovereenkomst |
| Hostingprovider en land: ServerScope-app (als dat een andere is) | privacy, verwerkersovereenkomst |
| E-maildienst en land | privacy, verwerkersovereenkomst |
| Doelgroep: zakelijk, consumenten of beide | voorwaarden en privacy (bij consumenten gelden andere regels; dit blijft voor de jurist) |

Optioneel:

- telefoonnummer;
- links naar sociale profielen (voor `sameAs`);
- toepasselijk recht en bevoegde rechter;
- maximale aansprakelijkheid;
- opzegtermijn;
- betalingstermijn;
- bewaartermijnen per soort gegevens:
  - wachtlijst;
  - contactberichten in de mailbox;
  - accounts na opzegging;
  - de `SERVERSCOPE_RETAIN_*`-soorten;
- de verloopdatum van security.txt.

---

## 6. Vragen voor je akkoord

1. **Sessies op de info-site.** Bij database-sessies slaat Laravel van
   **iedere** bezoeker het IP-adres en de user agent op. Voorstel: de
   info-site op `SESSION_DRIVER=file` zetten. Dan worden IP-adres en user
   agent niet meer opgeslagen, en de cookieverklaring en privacyverklaring
   worden eenvoudiger. Akkoord, of laten zoals het is en in de
   privacyverklaring vermelden?
2. **Branch.** Is het goed dat ik op `claude/legal-seo-ai` werk, met een
   eigen pull request na PR #1?
3. **Standaard voor trainingscrawlers** (ClaudeBot, GPTBot, Google-Extended,
   CCBot, Applebot-Extended): toestaan of blokkeren? De zoek- en
   gebruikerscrawlers stel ik voor toe te staan.
4. **Vergelijkingspagina** zonder namen van concurrenten (alleen criteria en
   ServerScope zelf): akkoord? Wil je wel namen, dan heb ik per bewering een
   openbare bron van jou nodig.
5. **Landingspagina's:** welke van de 10 in §4 wil je? Mijn advies: 1, 3, 4,
   5, 6 en 7, plus "Wat is ServerScope", de begrippenlijst en de
   vergelijkingspagina.
6. **Subverwerkers en externe bronnen:** wil je in de bijlage van de
   verwerkersovereenkomst naast hosting en e-maildienst ook de standaard
   DNS-resolvers (Cloudflare, Google, Quad9) en RDAP noemen als "externe
   bronnen"? Juridisch zijn het waarschijnlijk geen subverwerkers, maar
   transparant is het wel. Gebruik je in productie andere resolvers, dan
   kun je dat in Filament aanpassen.
7. **Offline schema.org-controle:** mag ik de officiële schema.org-woordenlijst
   (`schemaorg-current-https.jsonld`, ongeveer 1 MB, CC BY-SA 3.0) als
   testbestand opnemen? Dan controleert een test ook offline of alle
   gebruikte types en eigenschappen bestaan. Het is geen pakket, alleen een
   databestand.
8. **Deelafbeeldingen met GD:**
   - Staat de PHP-extensie GD aan op Plesk (PHP 8.4)? Dat zie je onder PHP
     Settings → extensions.
   - Mag ik Inter als TTF (OFL, ongeveer 300 kB) toevoegen?

   Zonder GD gebruikt de site één vaste deelafbeelding.

Er zijn **geen nieuwe Composer- of npm-pakketten** nodig.

---

## 7. Volgorde van commits (na akkoord)

1. Bedrijfsgegevens-instellingen (Filament) en de plaatshouder-renderer, met tests.
2. Datamodel voor juridische documenten, versies en revisies, met tests.
3. Filament: juridische documenten, de concept-balk, de publicatieblokkade en de versies.
4. De juridische concepten (NL en EN) en de import ervan.
5. De publieke juridische pagina's: ontwerp, "In het kort", inhoudsopgave, print/PDF en versies.
6. security.txt, footerlinks, en privacylinks bij de formulieren.
7. (Na vraag 1) de sessiedriver van de info-site.
8. SEO: `page_meta`, de lengte-indicator, de 404-pagina en canonical/hreflang, met de test op title, description en canonical.
9. JSON-LD (alle types) met tests.
10. Deelafbeeldingen (GD met terugval).
11. Markdown-versies, `llms.txt` en `llms-full.txt`.
12. robots.txt per crawler, met de schakelaars in Filament.
13. Het `pages`-model, plus "Wat is ServerScope", de begrippenlijst en de vergelijkingspagina.
14. De goedgekeurde landingspagina's.
15. Zelfcontrole: Lighthouse, JSON-LD, links en reduced motion.
16. Oplevering, met schermafbeeldingen van de voorwaarden op desktop en mobiel.

Na elke stap draai ik de tests, `docs:verify` en `docs:check-links`, en
controleer ik in de browser.

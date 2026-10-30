# Oplevering revamp info.serverscope.nl

Branch: `claude/relaxed-hypatia-rm4ddm`. Niets staat op `main` en niets staat live.
Plan en beginsituatie: [REVAMP_PLAN.md](REVAMP_PLAN.md).
Schermafbeeldingen: map [oplevering/](oplevering/).

---

## 1. Wat er gemaakt is

### De site

- **Nieuw ontwerp.** Rustige grijstinten met één ingetogen blauw als accent
  (`#2F5FD0` licht, `#7AA2F7` donker). Er is een licht en een donker thema.
  Standaard volgt de site de instelling van het systeem. Met een knop in de
  kopregel kies je zelf systeem, licht of donker. Die keuze wordt alleen in
  je eigen browser bewaard, niet als cookie.
- **Lettertypen.** Inter en JetBrains Mono staan op onze eigen server. Er
  gaat geen verzoek meer naar fonts.bunny.net. `npm run build` werkt nu ook
  zonder internet.
- **Animaties.** Ze gebruiken alleen beweging en doorzichtigheid, zodat er
  niets verspringt. Met "beweging beperken" (reduced motion) staan ze
  allemaal uit.
  - Blokken komen rustig in beeld tijdens het scrollen.
  - In de hero loopt een langzame hartslaglijn.
  - Een terminal "typt" het echte installatiecommando. De tekst staat ook
    zonder JavaScript gewoon in de pagina.
  - Tellers lopen op tot echte getallen uit de database: het aantal
    beschikbare functies, het aantal handleidingen, 2 distributies
    (Ubuntu, Debian) en 2 architecturen (amd64, arm64).
  - In "In drie stappen" loopt een lijn mee met het scrollen.
- **Kleine functies:**
  - zoeken met ⌘K of Ctrl+K (of `/`), helemaal met het toetsenbord te
    bedienen;
  - kopieerknoppen met de bevestiging "Gekopieerd";
  - een voortgangsbalkje tijdens het lezen;
  - een inhoudsopgave die meeloopt en de actieve sectie markeert;
  - een link naar elke sectie;
  - de datum "Laatst bijgewerkt";
  - "Was dit nuttig?" onder elke handleiding.
- **Functies uit Filament.** De homepage, Functionaliteiten en Prijzen tonen
  de functies uit Filament. Wat "binnenkort" heet, krijgt dat label. Wat
  "verborgen" is, staat nergens: niet in de navigatie, de zoekfunctie, de
  sitemap, de prijstabel of de changelog. Ook de installatie-terminal volgt
  de functie "Koppelen met één commando".
- **Prijspagina** `/nl/pricing` en `/en/pricing`:
  - Free, Pro en Ultra met de tekst "Prijs volgt" zolang er geen prijs is
    ingevuld;
  - een schakelaar maandelijks/jaarlijks;
  - het label "Aanbevolen";
  - een vergelijkingstabel.
  - Zolang registratie uit staat, gaan de knoppen naar de **eigen
    wachtlijst** van de site, met het gekozen abonnement al ingevuld. Vul je
    in de instellingen een externe wachtlijst-URL in, dan gaan ze daarheen.
- **Wachtlijst** `/nl/waitlist` en `/en/waitlist`, zonder diensten van
  derden:
  - bewaart alleen het e-mailadres, de taal, het gekozen abonnement, de
    getoonde toestemmingstekst en het tijdstip van toestemming;
  - een verplicht vinkje voor toestemming, een honeypot tegen spam en
    maximaal 5 aanmeldingen per minuut;
  - verwijderen op verzoek: na het aanmelden krijgt iemand een
    persoonlijke verwijderlink (alleen de hash wordt bewaard), en jij kunt
    een aanmelding in Filament verwijderen;
  - in Filament onder **Inhoud → Wachtlijst** (alleen beheerders): bekijken,
    filteren, verwijderen en exporteren als CSV.
- **Contactformulier** mailt nu echt naar `CONTACT_MAIL_TO` uit `.env`, met
  de afzender als antwoordadres, een honeypot en de bestaande limiet van 5
  berichten per minuut. Ontbreekt het adres, dan zegt de pagina dat het
  formulier niet beschikbaar is. Lukt het versturen niet, dan krijgt de
  bezoeker een foutmelding. Berichten worden niet op de site bewaard.
- **Echte status.** De vaste tekst "Alle systemen operationeel" is weg.
  - `/status` en het statusbadge in de hero en de footer halen
    `https://serverscope.nl/status/serverscope.json` op via onze eigen
    server, met een cache van 60 seconden.
  - De browser van een bezoeker praat nooit rechtstreeks met ServerScope.
  - Komt er geen geldig antwoord, dan staat er "Status momenteel onbekend"
    met een link naar de statuspagina. Er staat nooit "operationeel" als
    ServerScope dat niet zelf meldt.
- **Changelog.** Te zien zijn 0.1.0 en de update van 26 oktober, die
  ongewijzigd uit `docs/CHANGELOG.nl.md` en `docs/CHANGELOG.en.md` van
  ServerScope komt. Daarnaast staan er twee **concepten** klaar (Teams en
  rollen; Een server koppelen met één commando). Die zijn niet
  gepubliceerd. Fase 3 heeft geen changelogregel.
- **Sitemap.**
  - `/sitemap.xml` bevat NL/EN-alternatieven en alleen zichtbare pagina's.
  - Op staging is de sitemap leeg.
  - `robots.txt` noemt de sitemap alleen in productie.
- **Bezoekersteller zonder cookies.** Hoe dat werkt, staat in hoofdstuk 7.

### Documentatie

- Alle 16 bestaande pagina's (8 onderwerpen × NL/EN) staan in Filament, met
  exact dezelfde inhoud. Het bewijs staat in hoofdstuk 3.
- **Nieuwe handleidingen**, 7 × NL/EN. Ze zijn alleen geschreven uit de
  ServerScope-repository: `agent/README.md` (main en de fase 3-branch),
  `docs/API.md`, `docs/AUDIT.md` §5–6, `docs/RELEASE_KEYS.md` en de
  foutmeldingen van `install.sh`.

  | Pagina | Functie in Filament |
  |---|---|
  | Teams en rollen | Teams en rollen |
  | Een server koppelen met één commando | Koppelen met één commando |
  | Diensten bewaken | Diensten bewaken |
  | De Bash-agent | Bash-agent |
  | Agent-updates via het dashboard | Agent-updates |
  | Beveiliging van de agent en je account | — (altijd zichtbaar) |
  | API v1: overzicht | REST API v1 |

  Alle functies starten op **beschikbaar**.
- **De Bash-agent:** tot er een download is, vraagt de handleiding klanten
  om het script via het contactformulier aan te vragen. Er staat geen
  download-URL in.
- **Concept-revisies** voor "Aan de slag" en "Beveiliging en
  accountbeheer" (NL en EN). Ze kloppen met de nieuwe functies: de rollen
  eigenaar, beheerder en lid, en "er zijn nog geen abonnementen actief".
  De gepubliceerde tekst blijft ongewijzigd tot je in Filament bij de
  revisies op **Concept publiceren** klikt. `docs:verify` blijft dus
  slagen.

- De vingerafdruk van de releasesleutel is een plaatshouder. Vul hem in
  onder **Instellingen → Site-instellingen**; daarna verschijnt hij
  overal.
- De handmatige installatie blijft gewoon in de bestaande handleiding
  "Servermonitoring" staan.

### Filament-panel (`/admin`)

- **Navigatie** in vier groepen:
  - **Inhoud:** blog en redirects.
  - **Documentatie:** pagina's, categorieën en feedback.
  - **Product:** functies, abonnementen, vergelijkingsregels en changelog.
  - **Instellingen:** site-instellingen en gebruikers.
- **Dashboard** met:
  - aantallen;
  - recent gewijzigde pagina's;
  - concepten en geplande pagina's;
  - feedback per pagina;
  - functies per status (status direct te wijzigen);
  - meest bekeken pagina's.
- **Documentatie beheren:**
  - Markdown-editor met codeblokken.
  - **Voorbeeld:** opent de niet-opgeslagen versie in het echte
    site-ontwerp, alleen voor ingelogde gebruikers.
  - Status concept of gepubliceerd, en **geplande publicatie** met een
    datum in de toekomst.
  - Categorie, volgorde, functie, slug, vertaalsleutel, SEO-titel en
    -omschrijving, en noindex.
  - **Revisiegeschiedenis:** wie wijzigde wat en wanneer, het verschil met
    de huidige versie, en de knop "Terugzetten".
  - Wijzig je een slug, dan maakt de site automatisch een 301-redirect.
- **Overal** zoeken, filteren en bulkacties (publiceren, concept maken,
  categorie wijzigen, verwijderen en herstellen, functiestatus wijzigen).
  Met Ctrl+K of ⌘K zoek je door het hele panel.
- **Rollen:**
  - **Beheerder** mag alles.
  - **Redacteur** mag inhoud, documentatie en changelog beheren. Gebruikers,
    instellingen, prijzen en de status van functies kan een redacteur niet
    zien of wijzigen.
  - Bestaande beheerders zijn automatisch "beheerder" geworden.
- **Uitstraling:** dezelfde accentkleur als de site, het contrast is
  gecontroleerd, en de avatars zijn initialen. De externe dienst
  ui-avatars.com wordt niet meer gebruikt.

### Schermafbeeldingen

Elk bestand toont van links naar rechts: desktop licht, desktop donker,
mobiel licht, mobiel donker.

| Bestand | Pagina |
|---|---|
| `oplevering/home.jpg` | Homepage |
| `oplevering/docs.jpg` | Handleiding "Een server koppelen" |
| `oplevering/pricing.jpg` | Prijzen |
| `oplevering/features.jpg` | Functionaliteiten (EN) |
| `oplevering/admin-dashboard-light.jpg` en `-dark.jpg` | Filament-dashboard |
| `oplevering/admin-doc-edit-light.jpg` en `-dark.jpg` | Pagina bewerken |

---

## 2. Lighthouse vóór en na

Mobiel, Lighthouse 12, lokaal gemeten met dezelfde methode.

| Pagina | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|
| `/nl` | 100 → **99** | 95 → **100** | 96 → **100** | 100 → **100** |
| `/nl/docs` | 98 → **99** | 96 → **100** | 96 → **100** | 100 → **100** |
| `/nl/docs/getting-started` | 99 → **98** | 96 → **100** | 96 → **100** | 100 → **100** |
| `/nl/features` | 98 → **99** | 95 → **100** | 96 → **100** | 100 → **100** |
| `/nl/pricing` (nieuw) | — → **99** | — → **100** | — → **100** | — → **100** |
| `/nl/docs/connect-server` (nieuw) | — → **99** | — → **100** | — → **100** | — → **100** |
| `/en/status` | — → **100** | — → **100** | — → **100** | — → **100** |

Kerncijfers van de homepage: LCP 1,8 s, CLS 0,001, TBT 0 ms.

Eerlijk om te weten:

- De meting vooraf was te gunstig. Het externe lettertype werd hier
  geblokkeerd en dus nooit gedownload. Nu worden de lettertypen echt
  geladen. Met een preload bleef de score toch 98–100.
- Lokaal draait PHP's ingebouwde server, zonder compressie en zonder
  HTTP/2. Meet daarom op staging opnieuw. Plesk comprimeert normaal, en dan
  worden de cijfers eerder beter dan slechter.

---

## 3. Bewijs dat alle bestaande documentatie compleet in Filament staat

De import is herhaalbaar: een tweede of derde keer draaien geeft
"ongewijzigd" en maakt geen dubbele pagina's. Pagina's die je na de import
in Filament hebt aangepast of verwijderd, overschrijft de import nooit.

**Controle 1, in Laravel:** `php artisan docs:verify`

- vergelijkt de aantallen per taal;
- vergelijkt per pagina de SHA-256 van de tekst en van de getoonde HTML;
- vergelijkt titel, omschrijving, volgorde en icoon;
- controleert of de pagina zichtbaar is.

Het resultaat komt ook in `storage/app/private/docs-verify.json`.

```
NL: 8 bestanden, 8 records ja
EN: 8 bestanden, 8 records ja
Alle 16 pagina's staan in de database met exact dezelfde inhoud.
```

**Controle 2, zonder PHP:** `python3 tools/verify_docs_import.py .`

Dit script leest de SQLite-database rechtstreeks uit en vergelijkt elk
bestand byte voor byte met de database:

```
nl: 8 bestanden, 8 records
en: 8 bestanden, 8 records
nl/alerts-incidents         573608b3ae1a2234 = 573608b3ae1a2234 published
nl/api-webhooks             46998bf2eba410f6 = 46998bf2eba410f6 published
nl/discord-notifications    f3554b2eead07bfe = f3554b2eead07bfe published
nl/domain-monitoring        97945797768586e2 = 97945797768586e2 published
nl/getting-started          e80705b0d723cc8f = e80705b0d723cc8f published
nl/security-account         d4dbc838bbee5718 = d4dbc838bbee5718 published
nl/server-monitoring        9db8f35b01390a10 = 9db8f35b01390a10 published
nl/status-pages             530cd96787df787e = 530cd96787df787e published
en/alerts-incidents         80286b0c38f3d919 = 80286b0c38f3d919 published
en/api-webhooks             bb0c6bea6148150b = bb0c6bea6148150b published
en/discord-notifications    fe690caa74af2781 = fe690caa74af2781 published
en/domain-monitoring        9318114b08f29594 = 9318114b08f29594 published
en/getting-started          84b8dd3df33efc25 = 84b8dd3df33efc25 published
en/security-account         8164713f3aa6e8c6 = 8164713f3aa6e8c6 published
en/server-monitoring        3244ee1a75658943 = 3244ee1a75658943 published
en/status-pages             9e74cf9e23625bee = 9e74cf9e23625bee published
RESULTAAT: alles identiek
```

**Controle 3, de testsuite.** Alle 110 tests slagen. Ze bewijzen onder meer:

- elk bestand staat byte voor byte in de database;
- de import is herhaalbaar;
- één gewijzigd teken wordt gevonden;
- alle oude documentatietests slagen ook vanuit de database;
- alle 32 oude URL's geven 200 (vóór en na de import);
- de nieuwe migraties laten bestaande blogposts, releases en gebruikers
  ongemoeid (`MigrationSafetyTest`).

De Markdown-bestanden in `content/docs` blijven in de repository staan als
bron en back-up.

**Oude URL's:** alle routes werken nog (`/nl`, `/en`, `/features`,
`/docs`, `/docs/{slug}`, `/blog`, `/changelog`, `/contact`, `/status`),
`/` blijft een redirect naar `/nl`, en `/admin` werkt. Er is niets
verplaatst, dus er waren geen redirects nodig.

---

## 4. Op staging zetten en controleren

Staging-adres: `staginginfo.serverscope.nl`. De code gebruikt geen vast
adres; dat komt uit `APP_URL`.

1. **Plesk → Websites & Domains → het staging-subdomein:**
   - Documentroot: `/httpdocs/public` (de map `public` van het project).
   - PHP 8.4.
2. **Git:** koppel de repository, branch `claude/relaxed-hypatia-rm4ddm`.
3. **`.env`** op de server. Neem `.env.example` als basis en vul in:

   ```
   APP_ENV=staging
   APP_DEBUG=false
   APP_URL=https://staginginfo.serverscope.nl
   STAGING=true
   STAGING_USER=<kies een naam>
   STAGING_PASSWORD=<lang wachtwoord>
   SERVERSCOPE_APP_URL=https://serverscope.nl
   CONTACT_MAIL_TO=<adres dat contactberichten ontvangt>
   ```

   Vul ook je databasegegevens en de `MAIL_*`-instellingen (SMTP) in, en
   draai daarna `php artisan key:generate`.

4. **Deploy-acties** in Plesk Git ("Enable additional deployment actions"):

   ```
   COMPOSER_ALLOW_SUPERUSER=1 /opt/plesk/php/8.4/bin/php /usr/lib/plesk-9.0/composer.phar install --no-dev --optimize-autoloader
   /opt/plesk/php/8.4/bin/php artisan migrate --force
   /opt/plesk/php/8.4/bin/php artisan site:defaults
   /opt/plesk/php/8.4/bin/php artisan docs:verify
   /opt/plesk/php/8.4/bin/php artisan docs:check-links
   /opt/plesk/php/8.4/bin/php artisan optimize
   ```

   Op de server is **geen npm nodig**: `public/build` staat in git, net als
   bij de ServerScope-app. Wie CSS of JavaScript wijzigt, draait lokaal
   `npm run build` en commit `public/build` mee; een test bewaakt dat de
   build compleet is.

5. **Beheerder aanmaken:** `php artisan info:admin jij@voorbeeld.nl`. Een
   redacteur maak je met `--role=editor`, of in Filament onder
   **Instellingen → Gebruikers**.
6. **Controleren:**
   - Open de site: de browser vraagt om gebruiker en wachtwoord, en
     bovenaan staat een rode balk "STAGING".
   - Stuur een bericht via het contactformulier en controleer of het
     aankomt op `CONTACT_MAIL_TO`.
   - Meld je aan op de wachtlijst, bekijk de aanmelding in Filament
     (**Inhoud → Wachtlijst**), exporteer de CSV en verwijder de aanmelding
     met de persoonlijke link.
   - `curl -I https://<staging>/nl` toont `X-Robots-Tag: noindex, nofollow`.
   - `https://<staging>/robots.txt` geeft `Disallow: /`.
   - `php artisan docs:verify` en `php artisan docs:check-links` zijn
     beide groen.
   - `php artisan about` toont onder "ServerScope Info" dat Staging aan
     staat.
   - Loop de pagina's door: desktop en mobiel, licht en donker, Ctrl+K,
     een handleiding, Prijzen, Wachtlijst, Status, en `/admin`.

**Veiligheidsnet:**

- Staat `STAGING=true` per ongeluk aan in productie, dan zie je dat meteen:
  bezoekers krijgen een wachtwoordvraag, elke pagina en het panel hebben
  de rode balk "STAGING staat aan terwijl APP_ENV=production", en er komt
  elk uur een kritieke regel in het log.
- Ontbreken gebruiker of wachtwoord, dan gaat de site op slot (503) in
  plaats van open.
- Elke omgeving die niet `production` is, krijgt altijd noindex.

---

## 5. Bij de livegang per functie de status instellen

Ga naar **Product → Functies**. Per functie kies je in de kolom
"Status op de site":

- **Beschikbaar:** normaal tonen.
- **Binnenkort:** tonen met het label "binnenkort".
- **Verborgen:** nergens tonen. Directe links naar de handleiding geven
  dan 404.

Je kunt ook meerdere functies tegelijk aanvinken en een bulkactie
"Status: …" kiezen. Handleidingen, regels in de prijstabel en
changelog-items die bij een functie horen, volgen die status automatisch.

Startwaarden: alle functies staan op **beschikbaar**, ook teams, koppelen
met één commando, de Bash-agent en agent-updates. Zet een functie op
"binnenkort" of "verborgen" als die nog niet live gaat.

Checklist voor de livegang:

1. Zet de functies op de juiste status.
2. **Product → Changelog:** publiceer de concepten "Teams en rollen" en
   "Een server koppelen met één commando" als die functies live gaan.
3. **Documentatie → Pagina's → "Aan de slag" en "Beveiliging en
   accountbeheer"** (NL en EN): open het tabblad Revisiegeschiedenis en
   klik bij de concept-revisie op **Concept publiceren** zodra teams live
   zijn. Pas de tekst eerst aan als abonnementen dan al live zijn.
4. **Product → Abonnementen:** vul prijzen en limieten in en kies welk
   abonnement "Aanbevolen" is. Lege velden tonen "Prijs volgt" of
   "Volgt".
5. **Instellingen → Site-instellingen:**
   - registratie open of dicht;
   - registratie-URL, en eventueel een externe wachtlijst-URL (leeg = de
     eigen wachtlijst);
   - schakelaar maandelijks/jaarlijks;
   - de URL van de statuspagina;
   - de vingerafdruk van de releasesleutel;
   - de bezoekersteller aan of uit.
6. Zet `CONTACT_MAIL_TO` en de `MAIL_*`-instellingen in de `.env` van
   productie.
7. Draai `php artisan docs:check-links`. Heb je een functie verborgen, dan
   ziet dit commando links ernaartoe vanuit andere pagina's.
8. Zet in productie `STAGING=false` (of laat het weg) en `APP_ENV=production`.

---

## 6. Wat niet af of niet getest is

**Aandachtspunten voor jou:**

- **Het statusbadge is niet live getest.** Deze omgeving kan
  serverscope.nl niet bereiken. Getest is het met nagebootste antwoorden:
  operationeel, verminderd, onzin en time-out. Controleer op staging dat
  `https://serverscope.nl/status/serverscope.json` openbaar en bereikbaar
  is.
- **E-mail is niet echt verstuurd.** Het contactformulier is getest met een
  nagebootste mailer; het echte versturen hangt af van je SMTP-instellingen
  op staging.
- **Wachtlijst-aanmelders krijgen geen bevestigingsmail.** De site mailt ze
  ook niet als registratie opengaat. Dat doe je zelf met de CSV-export.

**Niet getest:**

- **MySQL.** Alles is getest op SQLite. De migraties zijn additief en
  gebruiken niets bijzonders, maar ze zijn niet op MySQL gedraaid.
  Gebruikt staging MySQL, controleer dan `php artisan migrate`.
- **Andere browsers.** Getest is alleen in Chromium (desktop, mobiel 390
  px, licht en donker, reduced motion). Safari en Firefox niet.
  - Zonder ondersteuning voor scroll-gestuurde animaties staat de lijn bij
    "In drie stappen" gewoon stil; dat is bedoeld.

**Bekend en niet aangepakt:**

- **Tracking en cookies:**
  - Laravel zet een functionele sessiecookie. Dat was al zo, en die is
    nodig voor formulieren.
  - De beperking op het aantal feedback-, contact- en wachtlijstverzoeken gebruikt het
    IP-adres maximaal één minuut in de cache. Er wordt niets opgeslagen in
    de database.
- **Code:**
  - `composer audit` meldt een bestaande kwetsbaarheid in `symfony/mime`
    (CVE-2026-45070, e-mailheaders). Die is bewust niet in deze branch
    bijgewerkt; zie de vervolgstappen.
  - `tempest/highlight` staat vast op 2.12.1 (MIT). Nieuwere versies
    vragen PHP 8.4, en het project belooft PHP 8.3. Er is geen kleur voor
    shell-code ingebouwd, daarom heb ik een kleine eigen shell-kleuring
    geschreven.
  - Laravel Boost is niet geïnstalleerd, zoals afgesproken.

**Vervolgstappen:**

- **symfony/mime bijwerken** (CVE-2026-45070) in een aparte, kleine branch
  en pull request: `composer update symfony/mime --with-dependencies`, de
  tests draaien, en het contactformulier op staging controleren. Nu het
  contactformulier mailt, is dit belangrijker geworden.

**Suggesties voor later** (niet gebouwd, conform je akkoord):

- veelgestelde vragen in Filament;
- een interactief architectuurschema (agent → ServerScope → meldingen);
- een "Hoe werkt het" die stap voor stap meebeweegt tijdens het scrollen;
- een uitgebreide demo-weergave van het dashboard. Het bestaande voorbeeld
  is gebleven en draagt nu duidelijk het label "Voorbeeld met fictieve
  gegevens";
- een printvriendelijke weergave verder uitwerken. Er is nu alleen een
  basisopmaak voor printen;
- in Filament zien welke zoektermen niets opleverden.

---

## 7. Hoe de bezoekersteller werkt

- Bij elke geslaagde paginaweergave telt de site één op bij de rij
  (pad, dag).
- De tabel `page_views` heeft alleen deze kolommen: `id`, `path`, `day` en
  `views`.
- Er wordt geen IP-adres, browser, verwijzende pagina of cookie
  opgeslagen.
- Bots en vooraf geladen pagina's (prefetch) tellen niet mee. Dat bepaalt
  de site alleen aan de hand van de verzoekheaders, en die worden niet
  bewaard.
- Het panel telt niet mee.
- Je zet de teller aan of uit in **Instellingen → Site-instellingen**.
- Je ziet de cijfers op het dashboard: "Meest bekeken pagina's (30 dagen)".

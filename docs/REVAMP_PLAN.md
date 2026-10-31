# Revamp info.serverscope.nl — stap A: onderzoek en plan

Status: **goedgekeurd en uitgevoerd** (zie [OPLEVERING.md](OPLEVERING.md)). Afwijkingen van dit plan: geen tabel voor veelgestelde vragen (idee niet goedgekeurd), geen `laravel/boost`, `tempest/highlight` vastgezet op 2.12.1 (PHP 8.3).
Branch: `claude/relaxed-hypatia-rm4ddm`. Niets gaat naar `main` of live.

---

## 1. Beginsituatie (gemeten op 30 oktober 2026)

| Onderdeel | Situatie |
|---|---|
| Framework | Laravel 13.38, PHP ^8.3 (getest met 8.4) |
| Admin | Filament 5.10.3 met Livewire 4.4.8, één panel op `/admin` |
| Markdown | league/commonmark 2.10 (GFM, heading-permalinks) |
| Frontend | Tailwind CSS 4 via Vite 8, geen JS-framework; `resources/js/app.js` (112 regels: mobiel menu, kopieerknoppen, scroll-reveal met IntersectionObserver) |
| Thema | alleen donker (`#080d18`/`#0b1220`), veel kleur (sky, violet, emerald, amber, verloop-tekst) |
| Lettertypen | **twee**: Instrument Sans (bij de build gedownload via `laravel-vite-plugin/fonts`, bunny) én Manrope die de layout **tijdens het bezoek** van `fonts.bunny.net` laadt (verzoek aan een derde partij) |
| Talen | NL en EN, URL's `/{nl,en}/...`; `lang/*/site.php` en `changelog.php` worden gegenereerd door `tools/gen_lang.py` |
| Database | SQLite standaard; tabellen `users` (+ `is_admin`), `posts` (blog), `releases` (changelog) |
| Rollen | alleen `users.is_admin`; aanmaken met `php artisan info:admin {email}` |
| Tests | PHPUnit, **49 tests / 349 asserts, allemaal groen** |
| Deploy | geen deployconfiguratie in de repository (ServerScope zelf draait op Plesk; voor de info-site moet ik dat van je horen) |
| robots.txt | alles toegestaan; geen noindex-mechanisme |

### Hoe inhoud nu is opgeslagen

| Inhoud | Opslag | Aantal |
|---|---|---|
| Documentatie | **Markdown-bestanden** `content/docs/{nl,en}/{slug}.md` met front matter (`order`, `icon`, `description`), placeholder `{app_url}`, GitHub-callouts `> [!NOTE]` | **16 bestanden** (8 slugs × 2 talen) |
| Changelog | DB-tabel `releases`; leeg → terugval op `lang/{nl,en}/changelog.php` | 1 versie (0.1.0, 3 regels) in het taalbestand |
| Blog | DB-tabel `posts`, beheerd in Filament | onbekend wat er op productie staat |
| Pagina's (home, features, status, contact) | Blade + taalbestanden | — |
| `lang/en/introduction.md` | wordt nergens gebruikt | 1 |

De 8 documentatie-slugs: `getting-started`, `server-monitoring`,
`domain-monitoring`, `alerts-incidents`, `discord-notifications`,
`status-pages`, `api-webhooks`, `security-account`.

### Lighthouse vóór (lokaal, mobiele emulatie, Lighthouse 12)

| Pagina | Performance | Accessibility | Best practices | SEO |
|---|---|---|---|---|
| `/nl` | 100 | 95 | 96 | 100 |
| `/nl/docs` | 98 | 96 | 96 | 100 |
| `/nl/docs/getting-started` | 99 | 96 | 96 | 100 |
| `/nl/features` | 98 | 95 | 96 | 100 |

Gevonden problemen: **onvoldoende kleurcontrast** op alle pagina's;
console-fout door het externe lettertype. Kanttekening: lokaal gemeten,
het externe lettertype kon hier niet laden, dus echte netwerkcijfers
volgen op staging (vóór en na met dezelfde methode).

### Bevindingen die raken aan je harde regels

- **Regel 5 (niets verzinnen):** `/status` toont nu hard gecodeerd
  "alle systemen operationeel" met een groene pulse (wel met de noot
  "voorbeeldgegevens"). De homepage toont een dashboard met voorbeeldcijfers.
  Voorstel: status vervangen door een echte bron of een neutrale pagina;
  de demo duidelijk als "voorbeeld" labelen.
- **Regel 9 (AVG):** Manrope wordt bij elk bezoek van `fonts.bunny.net`
  geladen. Voorstel: lettertypen zelf hosten (geen derde partij meer).
- **Build:** `npm run build` haalt Instrument Sans tijdens de build op bij
  bunny. Zonder internet (of als bunny plat ligt) faalt de build.
  Voorstel: lettertypebestanden in de repository.
- **CLAUDE.md** vraagt om `laravel/boost` te installeren. Dat is een nieuw
  Composer-pakket, dus dat doe ik pas met jouw akkoord (zie §3).

### Bronnen voor nieuwe documentatie (ServerScope-repository, alleen lezen)

- `main`: `agent/README.md` (installatie met één commando, handmatig,
  service-checks, integriteit, tokenrotatie, Bash-agent), `docs/API.md`,
  `docs/REPORTS.md`, `docs/AUDIT.md` (§5 teams, §6 koppelen met één
  commando), `docs/CHANGELOG.{nl,en}.md` (één grote release van 26 oktober).
- Fase 3: branch `claude/admiring-darwin-oiqti5` (nog niet gemerged):
  `docs/RELEASE_KEYS.md`, `docs/FASE3_STAPPENPLAN.md`, `agent/updater/*`,
  `--disable-updates`. De vingerafdruk van de releasesleutel is daar nog
  niet ingevuld; daarom komt er op de site een plaatshouder die jij in
  Filament invult.
- ServerScope kent **geen** abonnementen; Free/Pro/Premium zijn volledig
  plaatshouders.

---

## 2. Bestaande documentatie naar Filament, zonder verlies

1. **Additieve migratie**: nieuwe tabellen (zie §5). De Markdown-bestanden
   blijven in de repository staan als bron en back-up.
2. **Import** met `php artisan docs:import`:
   - sleutel = (`locale`, `slug`) → `updateOrCreate`, dus **herhaalbaar
     zonder dubbele records**;
   - de body wordt **byte voor byte** opgeslagen (alles na de front matter,
     inclusief `{app_url}` en callouts); front-matter-velden in eigen kolommen;
   - per record bewaren we `source_path` en `source_sha256`;
   - is een record na de import in Filament gewijzigd, dan wordt het **niet**
     overschreven; de import meldt dat (optie `--force` alleen bewust);
   - NL en EN worden gekoppeld via een `translation_key` (= slug).
3. **Bewijs** met `php artisan docs:verify` (ook als test in de testsuite):
   - aantallen per taal: bestanden = records (16 = 16);
   - per pagina: SHA-256 van de body uit het bestand = SHA-256 van de body
     in de database;
   - per pagina: SHA-256 van de **gerenderde HTML** via de oude
     bestandsservice = via de nieuwe databaseservice;
   - titel, beschrijving, volgorde en icoon gelijk;
   - uitvoer als tabel, ook weggeschreven als `storage/app/docs-verify.json`
     voor de oplevering.
4. **Overschakelen**: de site leest pas uit de database als `docs:verify`
   groen is; tot die tijd valt `DocumentationService` terug op de bestanden.
5. **Changelog**: het bestaande 0.1.0-item uit `changelog.php` gaat via
   dezelfde aanpak (idempotent, met verificatie) naar `releases`.
6. **URL's**: alle bestaande URL's blijven exact gelijk
   (`/{locale}/docs/{slug}` enzovoort). Een test vraagt elke oude URL op
   (alle routes × beide talen × alle 8 slugs) en verwacht 200. Wijzigt
   later een slug in Filament, dan wordt er automatisch een
   **301-redirect** aangemaakt (tabel `redirects`, ook beheerbaar).

---

## 3. Pakketten

Uitgangspunt: **geen nieuwe runtime-pakketten nodig.** Animaties, smooth
scroll, zoeken, inhoudsopgave en voortgangsbalk bouw ik met CSS en een
paar kilobyte eigen JavaScript.

| Pakket | Soort | Grootte | Waarom | Advies |
|---|---|---|---|---|
| geen animatiebibliotheek (GSAP, Motion) | — | 0 kB | CSS-transities, `IntersectionObserver` en CSS scroll-driven animations met terugval volstaan | — |
| geen smooth-scroll-bibliotheek (Lenis) | — | 0 kB | kaapt het native scrollen; slecht voor toetsenbord en reduced-motion | afgeraden |
| `laravel/boost` | Composer, **dev** | ~1 MB in `vendor`, niets in productie | staat in CLAUDE.md; geeft mij Laravel-hulpmiddelen | **jouw keuze** |
| `tempest/highlight` | Composer | ~400 kB in `vendor`, 0 kB JS naar de browser | syntax-highlighting van codeblokken op de server | optioneel; zonder pakket: stijlvolle maar monochrome codeblokken |
| Lettertypen Inter (variabel) + JetBrains Mono | bestanden (OFL), geen pakket | ~45 kB + ~20 kB woff2 (Latijns subset) | zelf hosten, geen derde partij, build zonder internet | aanbevolen |

Lighthouse draai ik via `npx` in een tijdelijke map; het komt niet in het
project.

---

## 4. Ontwerpvoorstel

### Kleuren (CSS-variabelen, licht en donker, volgt `prefers-color-scheme`, met handmatige schakelaar)

| Rol | Licht | Donker |
|---|---|---|
| Achtergrond | `#FAFAFA` | `#0A0A0B` |
| Vlak/kaart | `#FFFFFF` | `#111113` |
| Lijn | `#E4E4E7` | `#232326` |
| Tekst | `#18181B` | `#EDEDEF` |
| Tekst gedempt | `#52525B` (7,1:1) | `#A1A1AA` (7,4:1) |
| **Accent (één)** | `#2563EB`-achtig ingetogen blauw, afgezwakt naar `#2F5FD0` (≥ 4,5:1 op wit) | `#7AA2F7` (≥ 4,5:1 op bijna-zwart) |

Statuskleuren (groen/oranje/rood) alleen in kleine statusbadges, nooit als
decoratie. Alle combinaties worden met een contrastcheck in de tests
bewaakt (WCAG AA).

### Typografie en raster

- Inter (variabel) voor tekst, JetBrains Mono voor code, zelf gehost.
- Schaal: 14 / 16 / 18 / 22 / 30 / 44 / 60 px; koppen strak (−0,02em),
  regelafstand tekst 1,7, maximale regellengte 70 tekens.
- 12-koloms raster, max. 1200 px, ruime witruimte; dunne lijnen (1 px),
  zachte schaduwen alleen bij hover/verhoogde kaarten.

### Animaties (alleen `transform` en `opacity`; alles uit bij reduced-motion)

| Waar | Wat |
|---|---|
| Hele site | rustig infaden + 8 px inschuiven bij het scrollen, gespreid per groep (bestaand mechanisme, verfijnd) |
| Hero | subtiel bewegend element: langzaam pulserend "heartbeat"-lijntje / netwerkgrid dat traag verschuift (CSS, pauzeert buiten beeld) |
| Secties | zachte verlopen tussen secties, licht parallax van achtergronddetails via CSS scroll-driven animations (met terugval: statisch) |
| Terminal | het echte installatiecommando uit `agent/README.md` wordt "getypt"; de tekst staat al in de HTML (leesbaar zonder JS), kopieerknop erbij |
| Tellers | tellen op tot **echte** getallen uit de database (aantal beschikbare functies, aantal documentatiepagina's, ondersteunde systemen = 2 OS × 2 architecturen) |
| "Hoe werkt het" | drie stappen; de actieve stap licht op tijdens het scrollen (sticky) |
| Hover/focus | zachte lift van 1–2 px, duidelijke focusring in de accentkleur |
| Docs | leesvoortgangsbalk (transform: scaleX), meelopende inhoudsopgave, kopieerknoppen met "Gekopieerd ✓" |

### Kleine functies

⌘K / Ctrl+K-zoekvenster (zoekindex als klein JSON-bestand per taal, werkt
ook via `/docs?q=` zonder JS), kopieerknoppen, voortgangsbalk,
meelopende inhoudsopgave, "Was dit nuttig?" (ja/nee + optioneel korte
opmerking, geen cookies, geen IP-opslag), ankerlinks kopiëren bij koppen,
"Laatst bijgewerkt"-datum.

---

## 5. Datamodel (alle migraties additief; bestaande tabellen alleen kolommen erbij)

```
doc_categories   id, locale, translation_key, name, slug, description, icon, sort, timestamps
docs             id, locale, translation_key, doc_category_id?, slug, title, description,
                 body (longText, Markdown), icon, sort,
                 status (draft|published), published_at?,
                 seo_title?, seo_description?, noindex (bool),
                 feature_id? (pagina alleen zichtbaar als de functie het toelaat),
                 source_path?, source_sha256?, imported_at?,
                 created_by?, updated_by?, timestamps, soft deletes
                 unique(locale, slug)
doc_revisions    id, doc_id, user_id?, title, description, body, meta (json), created_at
                 → bij elke opslag; "Terugzetten" maakt een nieuwe revisie
doc_feedback     id, doc_id, helpful (bool), comment? (max 500), locale, created_at
                 (geen IP, geen user agent, geen cookies)
features         id, key, name (json nl/en), summary (json), icon, category,
                 status (available|coming_soon|hidden), sort, timestamps
plans            id, key (free|pro|premium), name (json), tagline (json),
                 price_monthly?, price_yearly?, currency, price_note (json)?,
                 is_recommended, cta_label (json), cta_url?, sort, is_visible, timestamps
plan_rows        id, group (json), label (json), feature_id?, type (limit|boolean|text), sort
plan_row_values  id, plan_id, plan_row_id, value?  unique(plan_id, plan_row_id)
releases         (bestaat) + title (json)?, body (Markdown)?, status, feature_id?   ← kolommen erbij
faqs             id, locale, translation_key, question, answer (Markdown), category?, sort, status
redirects        id, from_path (uniek), to_path, status_code (301), hits, timestamps
settings         key (uniek), value (json)   ← o.a. registration_open, waitlist_url,
                 contact_url, billing_toggle, status_slug, release_fingerprint
users            (bestaat) + role (admin|editor), standaard editor;
                 migratie zet is_admin=true → admin. is_admin blijft bestaan.
```

Zichtbaarheidsregels (regel 6), op één plek (`FeatureGate`):
- `available` → normaal tonen;
- `coming_soon` → tonen met label "binnenkort"; gekoppelde documentatie
  zichtbaar als preview met banner (of verborgen, instelbaar per pagina);
- `hidden` → nergens tonen: niet in navigatie, zoekindex, sitemap,
  prijstabel of changelog; directe URL geeft 404.

Prijsknoppen: zolang `registration_open` uit staat, wijzen alle knoppen
naar de wachtlijst- of contact-URL uit de instellingen.

---

## 6. Filament-panel

- Navigatiegroepen: **Inhoud** (pagina's/blog, veelgestelde vragen,
  redirects), **Documentatie** (pagina's, categorieën, feedback),
  **Product** (functies, abonnementen, vergelijkingsregels, changelog),
  **Instellingen** (site-instellingen, gebruikers).
- Dashboard-widgets: recent gewijzigd, concepten, feedback "Was dit
  nuttig?" (per pagina, percentage), functies per status.
- Overal zoeken, filters (taal, status, categorie), bulkacties
  (publiceren, concept maken, status wijzigen, verwijderen met bevestiging).
- Markdown-editor van Filament (codeblokken, toolbar) + actie
  **"Voorbeeld"** die de pagina rendert met exact dezelfde renderer en
  stijl als de site, ook voor concepten (alleen voor ingelogde gebruikers).
- Revisies: tabblad met wie/wanneer/wat (diff), knop "Terugzetten".
- Rollen: **beheerder** (alles, gebruikers, instellingen) en **redacteur**
  (inhoud en documentatie; geen gebruikers, instellingen, prijzen of
  functiestatus). Via Laravel-policies, zonder extra pakket.
- Uitstraling: zelfde neutrale tinten en accentkleur, Inter, eigen logo.

---

## 7. Mijn extra ideeën (alleen bouwen na akkoord)

1. **Echte statusbadge** op basis van `/status/{slug}.json` van ServerScope
   (route bestaat), server-side opgehaald, 60 s gecachet, nette terugval
   "Status niet beschikbaar". Vervangt de huidige nep-statuspagina.
2. **"Hoe werkt het" in drie stappen** (agent installeren → ServerScope
   verzamelt → melding), meebewegend bij scrollen.
3. **Interactief architectuurschema** (SVG, toetsenbordbedienbaar):
   agent → HTTPS → ServerScope → e-mail/app/Discord/webhook.
4. **Veelgestelde vragen** in Filament, met FAQ-structured data voor SEO.
5. **Demo-dashboard** met fictieve data, duidelijk gelabeld "Voorbeeld".
6. **Sitemap.xml met hreflang** uit de database (alleen zichtbare inhoud).
7. **Eigen bezoekersteller zonder cookies**: alleen pad + dag + aantal,
   geen IP, geen user agent. Plus **"zoektermen zonder resultaat"** in het
   dashboard, zodat je ziet welke documentatie ontbreekt.
8. **Linkcontrole** `php artisan docs:check-links` voor kapotte interne
   links en ankers (ook in de tests).
9. **Geplande publicatie** (publish_at in de toekomst).
10. **Printvriendelijke** documentatie en "kopieer link naar deze sectie".
11. **Engels blijft volwaardig**: elke nieuwe pagina NL + EN, met een
    Filament-indicator "vertaling ontbreekt".

---

## 8. Staging

- Alles achter `.env`-vlaggen, standaard veilig:
  `SITE_NOINDEX=true` → `X-Robots-Tag: noindex, nofollow` op elke response,
  `<meta name="robots">` en een `robots.txt` met `Disallow: /`.
- `STAGING_BASIC_AUTH_USER/PASSWORD` → HTTP Basic Auth-middleware op de hele
  site (behalve `/up`). Alternatief: wachtwoordbeveiliging van Plesk.
- Een testsuite die controleert dat noindex aan staat zodra
  `APP_ENV=staging`.

---

## 9. Volgorde van commits (stap B)

1. Fonts zelf hosten, build zonder internet; `noindex` + basic-auth voor staging
2. Datamodel documentatie (migraties, modellen, factories)
3. `docs:import` + `docs:verify` + tests (bewijs)
4. Site leest documentatie uit de database (met bestandsterugval) + redirects
5. Functies + `FeatureGate` + statusregels
6. Changelog-uitbreiding + import bestaande changelog
7. Abonnementen + vergelijkingstabel (datamodel, Filament)
8. Rollen beheerder/redacteur + policies
9. Filament: navigatiegroepen, thema, lijsten met zoeken/filters/bulkacties
10. Filament: revisies, voorbeeldweergave, dashboard-widgets
11. Ontwerpsysteem: tokens, licht/donker, typografie, componenten
12. Layout, navigatie, footer, themaschakelaar
13. Homepage met animaties, terminal, tellers
14. Documentatie-UI: zoeken ⌘K, inhoudsopgave, voortgang, feedback
15. Prijspagina + changelogpagina
16. Nieuwe documentatie: teams en rollen; server koppelen; handmatig + Bash-agent
17. Nieuwe documentatie: agent-updates (binnenkort); beveiliging; API v1 + service-checks
18. Goedgekeurde extra ideeën (elk eigen commit)
19. Zelfcontrole: toegankelijkheid, Lighthouse na, alle oude URL's, importbewijs
20. Opleverdocument met schermafbeeldingen

Na elke stap: `php artisan test`, browsercontrole met Playwright op
desktop en mobiel (375 px), licht/donker en reduced-motion, en een check
op horizontaal scrollen.

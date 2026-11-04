---
date: 2026-11-04
title: Automatische deploys, logs, nabeschouwingen, geplande taken en chatkanalen (agent 0.4.0)
status: published
source: Amir03033/ServerScope PR #40–#45 (4 november 2026)
---
ServerScope ziet nu zelf wanneer je deployt, leest mee in je logbestanden,
bewaakt je cron-taken en meldt incidenten ook in Slack, Microsoft Teams en
Telegram.

### Nieuw

- **Automatische deploys:**
  - De **agent** ziet zelf wanneer de code in een map verandert
    (`SERVERSCOPE_DEPLOY_PATHS`). Hij leest alleen `.git/HEAD` en voert geen
    git-commando's uit.
  - **GitHub koppelen** (een ondertekende webhook) voegt de titel van de
    commit of pull request, de auteur en de link toe. Een push of een
    GitHub-deployment kan ook zelf als deploy tellen.
  - Je vindt het onder **Deployments → Automatische deploys**.
- **Logs** (nieuwe pagina):
  - de agent volgt de logbestanden die je kiest (`SERVERSCOPE_LOG_FILES`) en
    stuurt alleen fouten en waarschuwingen, met stacktrace;
  - hij herkent Laravel, nginx, Apache, PHP-FPM en syslog;
  - gelijke fouten worden gegroepeerd tot **problemen**, met een verloop;
  - in de weergave **Stroom** zie je alle losse regels;
  - bij elk incident staat **Logs rond dit incident**;
  - er is een nieuwe alertregel voor een piek in fouten per 5 minuten.
- **Nabeschouwing** (postmortem) met één klik vanuit Incident Memory:
  - vooraf ingevuld met duur, detectie- en bevestigingstijd, impact, een
    tijdlijn met deploys, de oorzaak en de oplossing;
  - zelf vul je de samenvatting, lessen en actiepunten aan;
  - delen kan via een **openbare, alleen-lezen link** die je kunt intrekken,
    als Markdown of als pdf.
- **Geplande taken** (cron-bewaking):
  - elke cron-taak, backup of import krijgt een **geheime ping-URL**;
  - komt de melding niet op tijd (volgens het schema plus speling), of meldt
    de taak een fout, dan opent er een incident;
  - de volgende goede melding lost het incident op;
  - een schema is een vaste tussenpoos of een cron-expressie met tijdzone;
  - je ziet de laatste runs, de duur, de exitcode en de uitvoer (zonder
    wachtwoorden of tokens);
  - kant-en-klare voorbeelden voor crontab, de Laravel-planner en Plesk.
- **Slack, Microsoft Teams en Telegram** naast Discord, onder
  **Webhooks → Chat koppelen**. Berichten in de opmaak van het kanaal, met
  knoppen **Bekijk incident** en **Bevestigen**.
- **De planner bewaken:** met `SERVERSCOPE_SCHEDULER_PING_URL` meldt de
  ServerScope-planner zich elke minuut bij een geplande taak. Zo merk je het
  als de planner stopt.
- **Geplande taken in NEXUS**, als ringen rond hun server, met status en
  incidenten. Ze staan ook in de lijst van NEXUS en in het zijpaneel van de
  server.

### Verbeterd

- **Een vernieuwde incidentpagina:** één overzicht met de kerncijfers, een
  rustige stappenbalk, de oorzaakanalyse met de conclusie van de regels en
  van de AI naast elkaar (met betrouwbaarheidsmeters en "AI en regels zijn
  het eens"), en een vaste zijkolom.
- **Een vernieuwde Logs-pagina:** cijfers en grafiek in één kaart, een tabel
  met problemen, een stroom in console-stijl en filters op één regel (op de
  telefoon achter een knop).
- **Hoverkaarten met meer inhoud:**
  - **server:** alle cijfers, gestopte services, te late geplande taken,
    fouten in de logs van het afgelopen uur en de laatste deploy;
  - **website:** de laatste fout, beschikbaarheid in de laatste 24 uur, de
    laatste controle en de laatste deploy;
  - **incident:** de omschrijving, aan wie het is toegewezen, de
    waarschijnlijke of vastgelegde oorzaak, wie het bevestigde en de
    oplossing;
  - **geplande taak** (nieuw): status, hoe laat, de laatste runs, de laatste
    fout en het open incident.

### Opgelost

- Een time-out bij een domein- of paginacontrole liet de controle vastlopen,
  waardoor er geen storing werd gemeld. Nu wordt hij gewoon als "Time-out"
  gemeld.
- De hartslag (ECG) op het dashboard was op telefoons onzichtbaar.
- Het levende logo pulseert nu ook op iPhone (Safari).

### Beveiliging

- Webhook-URL's en bottokens van chatkanalen worden versleuteld opgeslagen,
  gemaskeerd getoond en uit foutmeldingen gefilterd.
- Logregels worden twee keer ontdaan van wachtwoorden, tokens en
  e-mailadressen: door de agent en door ServerScope.
- Een ping-URL is een geheim. Je maakt met één klik een nieuwe.
- **Bevestigen** in een chatbericht opent een pagina waarvoor je moet
  inloggen; de link zelf verandert niets.

### Goed om te weten

- Deploydetectie en het volgen van logs vragen **agent 0.4.0**. De Bash-agent
  ondersteunt ze niet.
- Logregels worden standaard 14 dagen bewaard.

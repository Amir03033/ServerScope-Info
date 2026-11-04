---
order: 5
icon: clock
category: monitoring
feature: scheduled-jobs
description: Cron-taken, backups en imports bewaken met een geheime ping-URL. Voorbeelden voor crontab, de Laravel-planner en Plesk.
---
# Geplande taken

Backups, imports en andere cron-taken falen stil: er gebeurt niets, en niemand merkt het. Met **geplande taken** roept de taak een geheime URL aan als hij klaar is. Komt die melding niet op tijd, of meldt de taak een fout, dan opent ServerScope een incident met de logs en deploys eromheen. De volgende goede melding lost het weer op.

Open [Geplande taken]({app_url}/heartbeats) in de zijbalk (groep **Monitoring**).

## Een taak aanmaken

1. Klik op **Nieuwe taak**.
2. Geef hem een **Naam**, bijvoorbeeld `Nachtelijke backup`.
3. Kies eventueel de **Server** waarop hij draait: dan horen logs en deploys van die server bij een incident.
4. Kies onder **Hoe vaak draait de taak?**:
   - **Vaste tussenpoos**, bijvoorbeeld elk uur of elke dag, of
   - **Cron-schema**: de expressie precies zoals in je crontab (vijf velden, bijvoorbeeld `0 3 * * *` voor elke nacht om 03:00) en de **Tijdzone**.
5. Stel de **Speling** in: zo lang mag de taak later zijn (of langer duren) voordat het een incident wordt.
6. Kies de **Ernst van het incident** en klik op **Taak aanmaken**.

De taakpagina toont de **Ping-URL** en kant-en-klare voorbeelden. De bewaking begint bij de eerste melding.

> [!WARNING]
> Iedereen met de ping-URL kan meldingen sturen: behandel hem als een wachtwoord. Met **Nieuwe URL** in het ⋯-menu vervang je hem; de oude werkt direct niet meer.

## Melden

De ping-URL is `{app_url}/api/ping/{token}`. Hij accepteert `GET`, `POST` en `HEAD`:

| URL | Betekenis |
|---|---|
| `/api/ping/{token}` | De taak is gelukt |
| `/api/ping/{token}/start` | De taak is gestart (om de duur te meten) |
| `/api/ping/{token}/fail` | De taak is mislukt: opent direct een incident |
| `/api/ping/{token}/{0-255}` | De taak eindigde met deze exitcode (0 = gelukt, anders mislukt) |

Het antwoord is `OK` (200), of `Not found` (404) bij een onbekend token. Bij een `POST` wordt de body bewaard als uitvoer van de taak: maximaal 2 KB, zonder wachtwoorden, tokens of e-mailadressen.

### Achter je cron-regel

Meld alleen als het commando slaagt (`&&`):

```sh
0 3 * * * /usr/local/bin/backup.sh && curl -fsS -m 10 --retry 3 -o /dev/null {app_url}/api/ping/{token}
```

### Start, resultaat en uitvoer

Meet de duur, meld de exitcode en stuur de laatste regels uitvoer mee:

```sh
curl -fsS -m 10 --retry 3 -o /dev/null {app_url}/api/ping/{token}/start
output=$(/usr/local/bin/backup.sh 2>&1); code=$?
curl -fsS -m 10 --retry 3 -o /dev/null --data-raw "$(printf '%s' "$output" | tail -c 2000)" {app_url}/api/ping/{token}/$code
```

### Laravel-planner

Voor taken in `routes/console.php`:

```php
Schedule::command('backup:run')->daily()
    ->pingBefore('{app_url}/api/ping/{token}/start')
    ->pingOnSuccess('{app_url}/api/ping/{token}')
    ->pingOnFailure('{app_url}/api/ping/{token}/fail');
```

### Plesk

*Run a PHP script* in Plesk kan geen `curl` draaien, en *Run a command* draait in een afgeschermde shell zonder PHP. Wil je de planner van een Laravel-app op Plesk bewaken, gebruik dan een omgevingsvariabele in die app (zie hieronder).

## De ServerScope-planner bewaken

Host je ServerScope zelf, maak dan een taak met een interval van 1 minuut en zet de ping-URL in de `.env` van ServerScope:

```sh
SERVERSCOPE_SCHEDULER_PING_URL={app_url}/api/ping/{token}
```

De planner meldt zich dan elke minuut, en je krijgt een incident zodra hij stopt.

## De taakpagina

- **Laatste runs**: groen gelukt, rood mislukt, oudste links.
- **Meldingen**: alles wat de taak meldde, met de duur, exitcode en uitvoer.
- **Pauzeren** bewaart meldingen maar opent geen incident; na **Hervatten** wordt de taak bewaakt vanaf de volgende melding.
- Geplande taken staan ook in [NEXUS](dashboard-nexus), als ringen rond hun server, en in het zijpaneel van de server.

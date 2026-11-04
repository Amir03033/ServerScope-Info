---
order: 20
icon: code
category: api
feature: api
description: Alle endpoints van de REST API v1, ook deployments, rechten, foutcodes, herhaalbare schrijfverzoeken en de openbare ping-, badge- en webhook-URL's.
---
# API v1: overzicht

Dit is het overzicht van de REST API, versie 1. Hoe je een token maakt en webhooks ontvangt, staat in [API en webhooks](api-webhooks).

## Basis

- Basis-URL: `{app_url}/api/v1`
- Stuur een persoonlijk token als bearer-token en vraag JSON:

  ```http
  Authorization: Bearer ssk_…
  Accept: application/json
  ```

- Elk token kan lezen (`read`). Schrijven vraagt de bevoegdheid `write`.
- Maximaal 120 verzoeken per minuut per token; daarboven HTTP 429.
- Lijsten zijn gepagineerd met `?page=2&per_page=50` (maximaal 100).
- Tijden zijn ISO 8601 in UTC.
- Een token ziet alleen de gegevens van zijn eigen team.
- API-tokens horen bij **Pro en Premium**; Free heeft geen API-tokens.

## Rechten

Elk token kan lezen. Schrijven vraagt de bevoegdheid `write`. Configuratiewijzigingen (pagina's, ontdekken, afhankelijkheden) vragen daarnaast een token van een beheerder; anders volgt 403. Acties op incidenten volgen de webinterface: iedere gebruiker mag bevestigen, onderzoeken, oplossen, becommentariëren en toewijzen.

## Endpoints

| Methode | Pad | Recht | Opmerkingen |
|---|---|---|---|
| GET | `/servers` | read | `status` = online/offline/pending/maintenance, `environment` |
| GET | `/servers/{id}` | read | met de laatste metingen |
| GET | `/servers/{id}/metrics` | read | `from`, `to` (ISO 8601), `limit` ≤ 1440 |
| GET | `/servers/{id}/services` | read | gemelde diensten; `stale` zolang de agent offline is |
| GET | `/domains` | read | `status` = healthy/warning/critical/unknown |
| GET | `/domains/{id}` | read | HTTP-, SSL-, DNS- en registratiestatus |
| GET | `/domains/{id}/checks` | read | `type` = http/ssl/dns/expiry |
| GET | `/domains/{id}/pages` | read | bewaakte pagina's |
| POST | `/domains/{id}/pages` | write + beheerder | `urls` (één per regel) en optioneel pagina-instellingen |
| GET | `/pages/{id}` | read | toestand en instellingen van een pagina |
| PUT | `/pages/{id}` | write + beheerder | `url` en instellingen |
| DELETE | `/pages/{id}` | write + beheerder | verwijdert de pagina en haar controles |
| POST | `/pages/{id}/pause` · `/resume` · `/approve` · `/reject` | write + beheerder | |
| GET | `/pages/{id}/checks` | read | `result` = up/degraded/down |
| POST | `/domains/{id}/discovery` | write + beheerder | start het ontdekken van pagina's (202) |
| GET | `/domains/{id}/discovery-runs` | read | resultaten van ontdekrondes |
| GET | `/dependencies` | read | bevestigde afhankelijkheden |
| POST | `/dependencies` | write + beheerder | `dependent_type`, `dependent_id`, `dependency_type`, `dependency_id`, `label` |
| DELETE | `/dependencies/{id}` | write + beheerder | |
| GET | `/incidents` | read | `state` = active/resolved/closed/all, `severity` |
| GET | `/incidents/{id}` | read | met tijdlijn, onderliggende incidenten en getroffen onderdelen |
| POST | `/incidents/{id}/acknowledge` | write | |
| POST | `/incidents/{id}/investigate` | write | |
| POST | `/incidents/{id}/resolve` | write | optioneel `notes` |
| POST | `/incidents/{id}/comments` | write | `message` |
| POST | `/incidents/{id}/assign` | write | `user_id` (of `null`) |
| GET | `/predictions` | read | `kind` = anomaly/forecast, `status` = active/resolved |
| GET | `/deployments` | read | filters `application`, `environment` |
| GET | `/deployments/{id}` | read | met `analysis` (voor/na) |
| POST | `/deployments` | write | een deploy melden (zie hieronder) |

Ernstniveaus in de API: `info` (laag), `warning` (gemiddeld), `high` en `critical`.

## Deployments

`POST /deployments` meldt een deploy, zodat ServerScope voor en na kan vergelijken (Deploy Judge). Deploys die de agent of GitHub ziet, hebben geen API-aanroep nodig; zie [Deployments](deployments).

| Veld | Verplicht | Regels |
|---|---|---|
| `application` | ja | maximaal 100 tekens |
| `environment` | nee | `[A-Za-z0-9._-]`, maximaal 50, standaard `production` |
| `version` | nee | maximaal 100 |
| `commit_sha` | nee | 7 tot 64 hextekens |
| `deployed_at` | nee | ISO 8601, binnen de laatste 30 dagen en maximaal 5 minuten vooruit |
| `source`, `actor` | nee | bijvoorbeeld `github-actions` en een gebruikersnaam |
| `url` | nee | link naar de run of release |
| `external_id` | nee | dezelfde waarde nog eens geeft de bestaande deploy terug (200) |
| `resources` | ja | 1 tot 50 items `{"type": "server"\|"domain"\|"page"\|"service", "id": n}`, alleen van je eigen team |

```sh
curl -sS -X POST {app_url}/api/v1/deployments \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Idempotency-Key: deploy-shop-1f3a9c2" \
  -d '{"application":"shop","environment":"production","version":"1.4.2","commit_sha":"1f3a9c2","resources":[{"type":"server","id":3},{"type":"domain","id":7}]}'
```

## Foutcodes

Fouten hebben de vorm `{"message": "...", "errors": {"veld": ["..."]}}`.

| Code | Betekenis |
|---|---|
| 401 | Token ontbreekt of is ongeldig |
| 403 | Bevoegdheid of rol ontbreekt |
| 404 | Niet gevonden |
| 409 | Ongeldige overgang van een incident, of een gelijktijdig dubbel verzoek |
| 422 | Validatiefout |
| 429 | Te veel verzoeken |

## Herhaalbare schrijfverzoeken

Stuur bij een schrijfverzoek een `Idempotency-Key` mee (8 tot 128 tekens). Herhaal je hetzelfde verzoek met dezelfde sleutel (per token, binnen 24 uur), dan krijg je het opgeslagen antwoord terug met `Idempotent-Replayed: true` en wordt het niet nog eens uitgevoerd. Dezelfde sleutel met een andere inhoud of een ander pad geeft 422; een gelijktijdig dubbel verzoek geeft 409.

```sh
curl -s -X POST "{app_url}/api/v1/domains/3/pages" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" -H "Accept: application/json" \
  -H "Idempotency-Key: deploy-2026-10-28-pages" \
  -d urls=$'/\n/pricing\n/api/health'
```

> [!NOTE]
> De agent gebruikt eigen endpoints (`/api/agent/…`) met agenttokens per server. Die tokens werken niet voor deze API, en je roept die endpoints nooit zelf aan.

## Openbare URL's (zonder token)

| Methode | URL | Waarvoor |
|---|---|---|
| GET | `/install.sh` | Installatiescript voor de agent; zie [Een server koppelen met één commando](connect-server) |
| GET/POST/HEAD | `/api/ping/{token}` · `/start` · `/fail` · `/{0-255}` | Meldingen van een [geplande taak](scheduled-jobs) |
| POST | `/api/webhooks/github/{uuid}` | GitHub-webhook van een gekoppelde repository; zie [Deployments](deployments) |
| GET | `/status/{slug}` en `/status/{slug}.json` | Openbare statuspagina, ook als JSON |
| GET | `/status/{slug}/badge.svg` · `/status/{slug}/badge/{onderdeel}.svg` | Statusbadges; zie [Statuspagina's](status-pages) |
| GET | `/postmortems/{token}` | Een gedeelde nabeschouwing (alleen-lezen, intrekbaar, niet geïndexeerd) |
| GET | `/up` | Gezondheidscontrole van de applicatie, voor uptime-tools |

Het ping-token (32 tekens, 0-9 en a-f) is het enige geheim van een geplande taak; een ping-URL accepteert maximaal 120 verzoeken per minuut.

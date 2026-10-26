---
order: 1
category: developers
icon: code
description: Authenticeer met een persoonlijk API-token en gebruik de REST API (v1) om servers, metingen, domeinen en incidenten op te vragen en incidenten bij te werken.
---
# API-referentie

ServerScope heeft een **REST API (versie 1)** om monitoringgegevens op te vragen en incidenten bij te werken vanuit je eigen tools: dashboards, chatbots, runbooks of automatisering zoals n8n.

> [!NOTE]
> De voorbeeldantwoorden op deze pagina laten de **vorm** van elk antwoord zien. De waarden zijn voorbeelden, geen gegevens uit een echte omgeving.

## Snel aan de slag

1. Maak een token aan onder [Profiel → API-tokens]({app_url}/profile/api-tokens) (zie [Een token aanmaken](#een-token-aanmaken)).
2. Bewaar het in een omgevingsvariabele, bijvoorbeeld `SERVERSCOPE_API_TOKEN`. Zet het nooit in broncode.
3. Vraag de actieve incidenten op:

```curl
curl -s "{app_url}/api/v1/incidents" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"
```

```php
<?php
// De HTTP-client van Laravel; elke HTTP-client werkt op dezelfde manier.
use Illuminate\Support\Facades\Http;

$incidents = Http::withToken(getenv('SERVERSCOPE_API_TOKEN'))
    ->acceptJson()
    ->get('{app_url}/api/v1/incidents')
    ->throw()
    ->json('data');
```

```javascript
const response = await fetch('{app_url}/api/v1/incidents', {
  headers: {
    Authorization: `Bearer ${process.env.SERVERSCOPE_API_TOKEN}`,
    Accept: 'application/json',
  },
});

if (!response.ok) throw new Error(`ServerScope API: ${response.status}`);
const { data: incidents } = await response.json();
```

```python
import os, requests

response = requests.get(
    "{app_url}/api/v1/incidents",
    headers={"Authorization": f"Bearer {os.environ['SERVERSCOPE_API_TOKEN']}", "Accept": "application/json"},
    timeout=10,
)
response.raise_for_status()
incidents = response.json()["data"]
```

## Een token aanmaken

1. Open [Profiel → API-tokens]({app_url}/profile/api-tokens).
2. Geef het token een **Naam** die zegt waar het gebruikt wordt, bijvoorbeeld `grafana` of `n8n`.
3. Kies de **Bevoegdheden**. `read` zit er altijd bij; voeg `write` alleen toe als het token incidenten moet wijzigen.
4. Kies de **Geldigheid**: 30 dagen, 90 dagen, 1 jaar of onbeperkt.
5. Klik op **Token aanmaken** en kopieer het token. Het begint met `ssk_`.

> [!WARNING]
> Het token wordt **één keer** getoond; ServerScope bewaart alleen een SHA-256-hash. Een token werkt met de rechten van jouw account, beperkt tot de gekozen bevoegdheden. Trek een token direct in als het mogelijk is uitgelekt, en plak het nooit in een browsertool die je niet zelf beheert.

## Authenticatie

Stuur het token bij elk verzoek mee als bearer-token en vraag om JSON:

```http
GET /api/v1/servers HTTP/1.1
Host: serverscope.nl
Authorization: Bearer ssk_…
Accept: application/json
```

| Bevoegdheid | Geeft toegang tot |
|---|---|
| `read` | Alle `GET`-endpoints |
| `write` | De `POST`-endpoints voor incidenten (bevestigen, onderzoeken, oplossen, notitie) |

De monitoringagent gebruikt een eigen endpoint (`POST /api/agent/metrics`) met agenttokens per server. Agenttokens werken niet voor deze API, en API-tokens werken niet voor de agent.

## Afspraken

| Onderwerp | Regel |
|---|---|
| Basis-URL | `{app_url}/api/v1` |
| Formaat | Alleen JSON; stuur `Accept: application/json` mee |
| Tijdstempels | ISO 8601, bijvoorbeeld `2026-10-26T10:00:00+00:00` |
| Paginering | `?page=2&per_page=50` (1–100, standaard 25). Lijsten geven `data`, `links` en `meta` terug. |
| Limiet | Standaard 120 verzoeken per minuut per token (de beheerder kan dit aanpassen); daarboven `429` |
| Taal | De eigen foutmeldingen van ServerScope (401, 403, 409, 429) zijn Nederlandstalig; validatiemeldingen volgen de taalinstelling van de applicatie |

Een gepagineerde lijst ziet er zo uit:

```json
{
  "data": [ … ],
  "links": { "first": "…?page=1", "last": "…?page=3", "prev": null, "next": "…?page=2" },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 25, "total": 61, "…": "…" }
}
```

## Endpoints

| Methode | Pad | Bevoegdheid | Omschrijving |
|---|---|---|---|
| `GET` | `/servers` | read | Servers opvragen |
| `GET` | `/servers/{id}` | read | Eén server met de laatste meting |
| `GET` | `/servers/{id}/metrics` | read | Metingen in een tijdvenster |
| `GET` | `/domains` | read | Domeinen opvragen |
| `GET` | `/domains/{id}` | read | Eén domein met HTTP-, SSL-, DNS- en registratiestatus |
| `GET` | `/domains/{id}/checks` | read | Controlegeschiedenis van een domein |
| `GET` | `/incidents` | read | Incidenten opvragen |
| `GET` | `/incidents/{id}` | read | Eén incident met tijdlijn |
| `POST` | `/incidents/{id}/acknowledge` | write | Bevestigen |
| `POST` | `/incidents/{id}/investigate` | write | Onderzoek starten |
| `POST` | `/incidents/{id}/resolve` | write | Oplossen |
| `POST` | `/incidents/{id}/comments` | write | Notitie toevoegen |

## Servers

### Servers opvragen

`GET /servers`

| Parameter | Type | Omschrijving |
|---|---|---|
| `status` | string | `online`, `offline`, `pending` of `maintenance` |
| `environment` | string | `production`, `staging`, `development`, `testing` of `other` |
| `per_page` | integer | 1–100, standaard 25 |

Gesorteerd op naam. Elke server ziet er zo uit:

```json
{
  "id": 3,
  "name": "web-01",
  "hostname": "web-01.example.com",
  "ip_address": "203.0.113.10",
  "environment": "production",
  "operating_system": "Ubuntu 24.04",
  "agent_version": "1.0.0",
  "status": "online",
  "last_seen_at": "2026-10-26T10:00:00+00:00",
  "maintenance": { "active": false, "starts_at": null, "ends_at": null },
  "latest_metrics": {
    "collected_at": "2026-10-26T10:00:00+00:00",
    "cpu_percent": 23.4,
    "memory_percent": 61.2,
    "load_1": 0.42,
    "uptime_seconds": 1762310
  },
  "created_at": "2026-09-24T08:12:00+00:00"
}
```

`latest_metrics` is `null` zolang er nog geen meting is binnengekomen.

### Eén server

`GET /servers/{id}` geeft `{"data": { … }}` met dezelfde velden.

### Metingen

`GET /servers/{id}/metrics`

| Parameter | Type | Omschrijving |
|---|---|---|
| `from` | datum | Begin van het venster; standaard een uur geleden |
| `to` | datum | Einde van het venster, na `from`; standaard nu |
| `limit` | integer | 1–1440, standaard 120. De nieuwste metingen in het venster, oudste eerst. |

Dit endpoint is niet gepagineerd:

```json
{
  "data": [
    {
      "collected_at": "2026-10-26T09:59:00+00:00",
      "cpu_percent": 21.7,
      "memory_used_bytes": 2621440000,
      "memory_total_bytes": 4294967296,
      "load_1": 0.38,
      "load_5": 0.41,
      "load_15": 0.35,
      "disks": [ … ],
      "network_rx_bytes": 918273645,
      "network_tx_bytes": 129384756
    }
  ]
}
```

## Domeinen

### Domeinen opvragen

`GET /domains`

| Parameter | Type | Omschrijving |
|---|---|---|
| `status` | string | `healthy`, `warning`, `critical` of `unknown` (alleen actieve domeinen) |
| `per_page` | integer | 1–100, standaard 25 |

```json
{
  "id": 7,
  "name": "example.com",
  "display_name": "example.com",
  "url": "https://example.com/",
  "status": "healthy",
  "monitoring_active": true,
  "http": { "enabled": true, "status": "up", "last_status_code": 200, "last_response_ms": 182, "consecutive_failures": 0, "last_checked_at": "2026-10-26T10:00:00+00:00" },
  "ssl": { "enabled": true, "status": "valid", "issuer": "…", "valid_to": "2026-12-06T23:59:59+00:00", "days_remaining": 41 },
  "dns": { "enabled": true, "status": "ok", "last_changed_at": null },
  "registration": { "enabled": true, "status": "verified", "expires_at": "2027-03-01T00:00:00+00:00", "registrar": "…", "source": "rdap", "checked_at": "2026-10-26T03:00:00+00:00" },
  "created_at": "2026-09-24T08:30:00+00:00"
}
```

`registration.status` is alleen `verified` als de registry een vervaldatum publiceert.

### Controlegeschiedenis

`GET /domains/{id}/checks`

| Parameter | Type | Omschrijving |
|---|---|---|
| `type` | string | `http`, `ssl`, `dns` of `expiry` |
| `per_page` | integer | 1–100, standaard 50 |

Nieuwste eerst, met een eigen `meta` (`current_page`, `last_page`, `per_page`, `total`) en `links` (`next`, `prev`):

```json
{
  "data": [
    { "type": "http", "result": "…", "success": true, "response_time_ms": 182, "http_status_code": 200, "error_code": null, "message": null, "checked_at": "2026-10-26T10:00:00+00:00" }
  ],
  "meta": { "current_page": 1, "last_page": 12, "per_page": 50, "total": 583 },
  "links": { "next": "…?page=2", "prev": null }
}
```

## Incidenten

### Incidenten opvragen

`GET /incidents`

| Parameter | Type | Omschrijving |
|---|---|---|
| `state` | string | `active` (standaard: open, bevestigd of in onderzoek), `resolved`, `closed` of `all` |
| `severity` | string | `info`, `warning` of `critical` |
| `per_page` | integer | 1–100, standaard 25 |

Nieuwste eerst:

```json
{
  "id": 12,
  "title": "shop.example.com is onbereikbaar",
  "description": "…",
  "severity": "critical",
  "status": "acknowledged",
  "source": "http",
  "subject": { "type": "domain", "id": 7, "name": "shop.example.com" },
  "occurrence_count": 4,
  "reopened_count": 0,
  "assigned_to": 2,
  "opened_at": "2026-10-26T10:02:00+00:00",
  "last_occurrence_at": "2026-10-26T10:08:00+00:00",
  "acknowledged_at": "2026-10-26T10:04:00+00:00",
  "resolved_at": null,
  "closed_at": null,
  "resolution_notes": null
}
```

`source` is `alert`, `agent`, `http`, `ssl`, `ssl_expiry`, `dns`, `expiry` of `manual`.

### Eén incident

`GET /incidents/{id}` voegt `events` toe, de tijdlijn:

```json
"events": [
  { "type": "opened", "message": "…", "user_id": null, "created_at": "2026-10-26T10:02:00+00:00" },
  { "type": "acknowledged", "message": "…", "user_id": 2, "created_at": "2026-10-26T10:04:00+00:00" }
]
```

### Bevestigen, onderzoeken of oplossen

`POST /incidents/{id}/acknowledge`, `POST /incidents/{id}/investigate`, `POST /incidents/{id}/resolve`

| Parameter | Type | Omschrijving |
|---|---|---|
| `notes` | string | Optioneel, maximaal 5000 tekens (bij `resolve`: de oplossing) |

Geeft het bijgewerkte incident terug, inclusief `events`. Alleen deze overgangen zijn toegestaan:

| Van | Naar |
|---|---|
| `open` | `acknowledged`, `investigating`, `resolved` |
| `acknowledged` | `investigating`, `resolved` |
| `investigating` | `acknowledged`, `resolved` |

Elke andere overgang geeft `409 Conflict`.

```curl
curl -s -X POST "{app_url}/api/v1/incidents/12/resolve" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Schijf opgeruimd en logrotatie hersteld."}'
```

```php
$incident = Http::withToken(getenv('SERVERSCOPE_API_TOKEN'))
    ->acceptJson()
    ->post('{app_url}/api/v1/incidents/12/resolve', ['notes' => 'Schijf opgeruimd en logrotatie hersteld.'])
    ->throw()
    ->json('data');
```

```javascript
const response = await fetch('{app_url}/api/v1/incidents/12/resolve', {
  method: 'POST',
  headers: {
    Authorization: `Bearer ${process.env.SERVERSCOPE_API_TOKEN}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({ notes: 'Schijf opgeruimd en logrotatie hersteld.' }),
});
```

```python
response = requests.post(
    "{app_url}/api/v1/incidents/12/resolve",
    headers={"Authorization": f"Bearer {os.environ['SERVERSCOPE_API_TOKEN']}", "Accept": "application/json"},
    json={"notes": "Schijf opgeruimd en logrotatie hersteld."},
    timeout=10,
)
```

### Notitie toevoegen

`POST /incidents/{id}/comments`

| Parameter | Type | Omschrijving |
|---|---|---|
| `message` | string | **Verplicht**, maximaal 5000 tekens |

Geeft `201 Created`:

```json
{ "data": { "type": "comment", "message": "php-fpm herstart.", "created_at": "2026-10-26T10:11:00+00:00" } }
```

## Foutmeldingen

Fouten zijn JSON met een `message` en, bij validatiefouten (`422`), een `errors`-object met de meldingen per veld:

```json
{
  "message": "…",
  "errors": { "per_page": ["…"] }
}
```

Gebruik de HTTP-statuscode, niet de tekst van de melding, om te bepalen wat je doet.

| Status | Betekenis | Wat te doen |
|---|---|---|
| `401` | Token ontbreekt, is ongeldig, verlopen of ingetrokken (*Ontbrekend of ongeldig API-token.*) | Controleer de `Authorization`-header; maak zo nodig een nieuw token |
| `403` | Het token mist de bevoegdheid, bijvoorbeeld `write` (*Dit token mist de bevoegdheid 'write'.*) | Maak een token met de juiste bevoegdheden |
| `404` | Het object bestaat niet | Controleer het id |
| `409` | Overgang van het incident niet toegestaan | Vraag het incident eerst op en controleer de `status` |
| `422` | Validatie mislukt | Zie `errors` |
| `429` | Te veel verzoeken (*Te veel verzoeken.*) | Wacht en probeer opnieuw met een oplopende wachttijd |

## Goede gewoonten

- Gebruik één token per koppeling, met alleen de bevoegdheden die nodig zijn en een vervaldatum.
- Bewaar tokens in een secret store of omgevingsvariabele, nooit in een repository of in frontendcode.
- Wacht en probeer opnieuw bij `429` en `5xx`; herhaal andere `4xx`-fouten niet zonder het verzoek aan te passen.
- Wil je dat ServerScope jou aanroept in plaats van te pollen? Gebruik [webhooks](webhooks).

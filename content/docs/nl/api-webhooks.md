---
order: 7
icon: code
description: De REST API gebruiken met een persoonlijk token, en ondertekende webhooks ontvangen en controleren.
---
# API en webhooks

ServerScope heeft een **REST API** (versie 1) om gegevens op te halen en incidenten te behandelen, en **webhooks** die gebeurtenissen naar je eigen systeem sturen.

## API-token aanmaken

1. Open [Profiel → API-tokens]({app_url}/profile/api-tokens).
2. Geef het token een **Naam**, bijvoorbeeld `n8n` of `grafana`.
3. Kies de **Bevoegdheden**: `read` zit er altijd in; voeg `write` alleen toe als het token incidenten moet bijwerken.
4. Kies de **Geldigheid**: 30 dagen, 90 dagen, 1 jaar of onbeperkt.
5. Klik op **Token aanmaken** en kopieer het token (begint met `ssk_`).

> [!WARNING]
> Het token wordt één keer getoond; ServerScope bewaart alleen een SHA-256-hash. Een token heeft dezelfde rechten als jouw account, beperkt tot de gekozen bevoegdheden. Trek een token direct in als het is uitgelekt.

## Authenticatie en conventies

- Basis-URL: `{app_url}/api/v1`
- Stuur het token als bearer-token en vraag JSON:

  ```http
  Authorization: Bearer ssk_…
  Accept: application/json
  ```

- Alleen JSON. Lijsten zijn gepagineerd met `?page=2&per_page=50` (maximaal 100) en geven `data`, `links` en `meta` terug.
- Tijden zijn ISO 8601 in UTC.
- Maximaal 120 verzoeken per minuut per token.

## Endpoints

| Methode | Pad | Bevoegdheid | Opmerkingen |
|---|---|---|---|
| GET | `/servers` | read | filter `status` = online/offline/pending/maintenance, `environment` |
| GET | `/servers/{id}` | read | inclusief de laatste metingen |
| GET | `/servers/{id}/metrics` | read | `from`, `to` (ISO 8601), `limit` ≤ 1440 (standaard het laatste uur, 120 metingen) |
| GET | `/domains` | read | filter `status` = healthy/warning/critical/unknown |
| GET | `/domains/{id}` | read | HTTP-, SSL-, DNS- en registratiestatus |
| GET | `/domains/{id}/checks` | read | `type` = http/ssl/dns/expiry |
| GET | `/incidents` | read | `state` = active (standaard)/resolved/closed/all, `severity` |
| GET | `/incidents/{id}` | read | inclusief de tijdlijn |
| POST | `/incidents/{id}/acknowledge` | write | |
| POST | `/incidents/{id}/investigate` | write | |
| POST | `/incidents/{id}/resolve` | write | optioneel `notes` |
| POST | `/incidents/{id}/comments` | write | `message` (verplicht) |

## Voorbeelden

Actieve incidenten ophalen:

```sh
curl -s "{app_url}/api/v1/incidents?state=active" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"
```

Een incident bevestigen:

```sh
curl -s -X POST "{app_url}/api/v1/incidents/12/acknowledge" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"
```

Een notitie toevoegen:

```sh
curl -s -X POST "{app_url}/api/v1/incidents/12/comments" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"message": "Herstart van php-fpm uitgevoerd."}'
```

## Foutmeldingen

Fouten hebben de vorm `{"message": "...", "errors": {"veld": ["..."]}}`:

| Status | Betekenis |
|---|---|
| 401 | Token ontbreekt of is ongeldig (*Ontbrekend of ongeldig API-token.*) |
| 403 | Het token mist de bevoegdheid, bijvoorbeeld `write` |
| 404 | Het object bestaat niet |
| 409 | Ongeldige statusovergang van een incident (bijvoorbeeld een gesloten incident oplossen) |
| 422 | Validatiefout; zie `errors` |
| 429 | Te veel verzoeken; wacht even |

> [!NOTE]
> De monitoringagent gebruikt een eigen endpoint (`POST /api/agent/metrics`) met agenttokens per server. Agenttokens werken niet voor deze API, en API-tokens niet voor de agent.

## Webhooks

Beheerders stellen webhooks in onder [Beheer → Webhooks]({app_url}/webhooks) met **Webhook toevoegen**: een naam, een publieke `https`-URL en de gebeurtenissen. Na het aanmaken zie je **één keer** het ondertekeningsgeheim (begint met `whsec_`).

Gebeurtenissen: `incident.opened`, `incident.reopened`, `incident.resolved`, `incident.escalated` (drempel gepasseerd, ernst verhoogd of niet op tijd bevestigd), `dns.changed` en `test`.

Elke levering is een `POST` met een JSON-body:

```json
{"id": "<uuid van de levering>", "event": "incident.opened", "created_at": "2026-10-26T10:00:00+00:00", "data": {"incident": {"id": 12, "title": "…", "severity": "critical", "status": "open"}}}
```

Met deze headers:

| Header | Inhoud |
|---|---|
| `X-ServerScope-Event` | De gebeurtenis, bijvoorbeeld `incident.opened` |
| `X-ServerScope-Delivery` | Unieke id; gebruik die om dubbele leveringen te negeren |
| `X-ServerScope-Timestamp` | Unix-tijd van verzending |
| `X-ServerScope-Signature` | `sha256=<hex>` met HMAC-SHA256(geheim, `timestamp + "." + ruwe body`) |

### Handtekening controleren

1. Lees de **ruwe** body, nog vóór het parsen van de JSON.
2. Bereken `sha256=` + HMAC-SHA256 met je geheim over `timestamp + "." + body`.
3. Vergelijk met `X-ServerScope-Signature` met een vergelijking in constante tijd.
4. Weiger leveringen waarvan de timestamp meer dan 5 minuten afwijkt.

PHP:

```php
$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
$valid = abs(time() - (int) $timestamp) < 300 && hash_equals($expected, $signatureHeader);
```

Python:

```python
import hashlib, hmac, time

expected = "sha256=" + hmac.new(secret.encode(), f"{timestamp}.".encode() + raw_body, hashlib.sha256).hexdigest()
valid = abs(time.time() - int(timestamp)) < 300 and hmac.compare_digest(expected, signature_header)
```

### Afleverregels

- Alleen publieke `https`-adressen op toegestane poorten; dit wordt bij opslaan én vóór elke poging gecontroleerd. Redirects worden niet gevolgd.
- Elke `2xx` is gelukt. Netwerkfouten, time-outs, `408`, `429` en `5xx` worden tot 6 keer opnieuw geprobeerd (1 min, 5 min, 15 min, 1 uur, 6 uur); andere antwoorden falen direct.
- Na 20 mislukte leveringen op rij wordt de webhook uitgeschakeld.
- Op de webhookpagina zie je elke levering; mislukte leveringen kun je **Opnieuw** versturen. Met **Testlevering** stuur je een `test`-gebeurtenis.
- **Geheim vernieuwen** maakt een nieuw geheim; het oude werkt direct niet meer.

Wil je naar Discord sturen? Gebruik dan de aparte [Discord-koppeling](discord-notifications); die zet de gebeurtenissen om naar Discord-berichten.

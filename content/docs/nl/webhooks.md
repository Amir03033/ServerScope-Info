---
order: 2
category: integrations
icon: webhook
description: Stuur incident- en DNS-gebeurtenissen als ondertekende JSON naar je eigen systeem, controleer de handtekening en begrijp nieuwe pogingen en het leveringslog.
---
# Webhooks

Een webhook stuurt een `POST`-verzoek naar je eigen URL zodra er iets gebeurt in ServerScope, zoals een incident dat opent of een DNS-record dat wijzigt. Zo koppel je ticketing-, chat-, piket- of automatiseringstools.

Alleen **beheerders** beheren webhooks. Wil je berichten in een Discord-kanaal? Gebruik dan de [Discord-koppeling](discord-notifications): die gebruikt dezelfde gebeurtenissen, maar plaatst leesbare berichten.

## Een webhook aanmaken

1. Open [Beheer → Webhooks]({app_url}/webhooks) en klik op **Webhook toevoegen**.
2. Vul een **naam** en de openbare `https`-**URL** van je ontvanger in en kies de **gebeurtenissen**.
3. Sla op. Het **ondertekeningsgeheim** wordt **één keer** getoond; het begint met `whsec_`. Bewaar het bij je ontvanger.
4. Klik op **Testlevering** om een `test`-gebeurtenis te sturen en controleer dat je ontvanger met `2xx` antwoordt.

## Gebeurtenissen

| Gebeurtenis | Wanneer |
|---|---|
| `incident.opened` | Er is een nieuw incident geopend |
| `incident.reopened` | Een opgelost incident kwam terug |
| `incident.resolved` | Een incident is opgelost (handmatig of automatisch) |
| `incident.escalated` | Er is een drempel overschreden, de ernst is verhoogd of het incident is niet op tijd bevestigd |
| `dns.changed` | DNS-records van een bewaakt domein zijn gewijzigd |
| `test` | Verstuurd met **Testlevering** |

## Opbouw van een verzoek

Elke levering is een `POST` met een JSON-body:

```json
{
  "id": "<delivery uuid>",
  "event": "incident.opened",
  "created_at": "2026-10-26T10:00:00+00:00",
  "data": { "incident": { "id": 12, "title": "…", "severity": "critical", "status": "open", "…": "…" } }
}
```

| Header | Inhoud |
|---|---|
| `X-ServerScope-Event` | De gebeurtenis, bijvoorbeeld `incident.opened` |
| `X-ServerScope-Delivery` | Uniek leverings-id; bewaar het om dubbele leveringen te negeren |
| `X-ServerScope-Timestamp` | Unix-tijd van verzending |
| `X-ServerScope-Signature` | `sha256=<hex>`: HMAC-SHA256 met je geheim over `timestamp + "." + ruwe body` |

## De handtekening controleren

Controleer een levering altijd voordat je erop reageert:

1. Lees de **ruwe** body van het verzoek, voordat je de JSON verwerkt.
2. Bereken `sha256=` + de hex-HMAC-SHA256 van `timestamp + "." + body` met je geheim.
3. Vergelijk die met `X-ServerScope-Signature` met een vergelijking in **constante tijd**.
4. Weiger leveringen waarvan de tijdstempel meer dan 5 minuten van je eigen klok afwijkt.
5. Antwoord snel met `2xx` en doe traag werk daarna.

```php
<?php
$secret = getenv('SERVERSCOPE_WEBHOOK_SECRET');
$body = file_get_contents('php://input');
$timestamp = $_SERVER['HTTP_X_SERVERSCOPE_TIMESTAMP'] ?? '';
$signature = $_SERVER['HTTP_X_SERVERSCOPE_SIGNATURE'] ?? '';

$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);

if (! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300 || ! hash_equals($expected, $signature)) {
    http_response_code(401);
    exit;
}

$event = json_decode($body, true);
http_response_code(204);
```

```javascript
// Node.js (Express): bewaar de ruwe body voor de controle van de handtekening.
import crypto from 'node:crypto';
import express from 'express';

const app = express();

app.post('/serverscope', express.raw({ type: 'application/json' }), (req, res) => {
  const timestamp = req.get('X-ServerScope-Timestamp') ?? '';
  const signature = req.get('X-ServerScope-Signature') ?? '';
  const expected = 'sha256=' + crypto
    .createHmac('sha256', process.env.SERVERSCOPE_WEBHOOK_SECRET)
    .update(`${timestamp}.`)
    .update(req.body)
    .digest('hex');

  const fresh = Math.abs(Date.now() / 1000 - Number(timestamp)) <= 300;
  const valid = signature.length === expected.length
    && crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));

  if (!fresh || !valid) return res.sendStatus(401);

  const event = JSON.parse(req.body);
  res.sendStatus(204);
});
```

```python
import hashlib, hmac, os, time

def verify(raw_body: bytes, timestamp: str, signature: str) -> bool:
    secret = os.environ["SERVERSCOPE_WEBHOOK_SECRET"].encode()
    expected = "sha256=" + hmac.new(secret, f"{timestamp}.".encode() + raw_body, hashlib.sha256).hexdigest()
    return timestamp.isdigit() and abs(time.time() - int(timestamp)) <= 300 and hmac.compare_digest(expected, signature)
```

## Levering en nieuwe pogingen

- Alleen openbare `https`-bestemmingen op toegestane poorten worden geaccepteerd. Dat wordt gecontroleerd bij het opslaan en opnieuw vóór elke poging; redirects worden niet gevolgd.
- Elk `2xx`-antwoord telt als afgeleverd.
- Bij netwerkfouten, time-outs, `408`, `429` en `5xx` volgen tot 6 pogingen in totaal, na 1 minuut, 5 minuten, 15 minuten, 1 uur en 6 uur. Andere antwoorden mislukken direct.
- Na **20 mislukte leveringen achter elkaar** wordt de webhook uitgeschakeld.

> [!NOTE]
> Nieuwe pogingen vereisen een queue-worker op de ServerScope-server. Draait jouw ServerScope-omgeving zonder, dan wordt een mislukte levering niet automatisch opnieuw geprobeerd; gebruik dan **Opnieuw** in het leveringslog.

## Leveringslog

De webhookpagina toont elke levering met status en foutmelding. Mislukte leveringen kun je opnieuw versturen met **Opnieuw**.

## Het geheim vernieuwen

**Geheim vernieuwen** maakt een nieuw geheim aan en toont het één keer. Het oude geheim werkt **direct** niet meer, dus werk je ontvanger meteen bij.

## Problemen oplossen

| Symptoom | Waarschijnlijke oorzaak |
|---|---|
| De URL wordt geweigerd bij het opslaan | Geen `https`, geen openbaar adres of een poort die niet is toegestaan |
| Elke levering faalt op de handtekening | De body is verwerkt of opnieuw gecodeerd vóór de controle; gebruik de ruwe bytes |
| Leveringen worden geweigerd als te oud | De klok van je server loopt verkeerd; synchroniseer met NTP |
| Dezelfde gebeurtenis komt twee keer binnen | Een nieuwe poging na een time-out; ontdubbel op `X-ServerScope-Delivery` |
| De webhook heeft zichzelf uitgeschakeld | 20 fouten achter elkaar; herstel de ontvanger en schakel hem weer in |

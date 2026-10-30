---
title: Acceptable use policy
description: Wat je wel en niet mag doen met ServerScope.
version: 0.1
changes: Eerste concept.
---
<!-- JURIST: toets de gevolgen in hoofdstuk 4 en de verhouding tot de algemene voorwaarden. -->

## 1. Waarom deze regels

> [!SUMMARY]
> ServerScope doet controles op servers, websites en domeinen. Die mogen alleen gebruikt worden voor systemen waarvoor je toestemming hebt.

ServerScope voert HTTP-, TLS-, DNS- en RDAP-controles uit en ontvangt metingen van agents. Deze regels gelden voor iedereen die ServerScope gebruikt, en vullen de algemene voorwaarden aan.

## 2. Wat niet mag

> [!SUMMARY]
> Niets bewaken zonder toestemming, ServerScope niet gebruiken om aan te vallen of te scannen, en limieten niet omzeilen.

### 2.1 Alleen bewaken met toestemming

Je bewaakt alleen servers, websites en domeinen die van jou zijn, of waarvoor de eigenaar je toestemming heeft gegeven.

### 2.2 Geen aanvallen of scans

Je gebruikt ServerScope niet om systemen aan te vallen, te overbelasten of te scannen op kwetsbaarheden. Dat geldt ook voor het ontdekken van pagina's en voor het instellen van intervallen of aantallen pagina's met dat doel.

### 2.3 Limieten niet omzeilen

Je omzeilt geen limieten van je abonnement of van de dienst, bijvoorbeeld door meerdere accounts of teams te gebruiken om meer te bewaken dan is toegestaan, of door de API anders te gebruiken dan bedoeld.

### 2.4 Geen misbruik van meldingen

Je gebruikt meldingen, webhooks en statuspagina's niet om spam te versturen of om anderen lastig te vallen.

## 3. Hoe ServerScope zelf misbruik beperkt

> [!SUMMARY]
> ServerScope blokkeert controles naar interne netwerken en heeft limieten op het ontdekken van pagina's.

- Controles naar interne, lokale en cloud-metadata-adressen worden geblokkeerd, ook na een redirect.
- Het ontdekken van pagina's is standaard beperkt tot je eigen domein en maximaal 50 pagina's, en gevonden pagina's wachten op jouw goedkeuring.
- De API heeft een limiet per token.

## 4. Gevolgen

> [!SUMMARY]
> Bij misbruik kunnen we controles stoppen of een account blokkeren.

Als we redelijkerwijs vermoeden dat deze regels worden overtreden, kunnen we controles stoppen, een account of team (tijdelijk) blokkeren en, als de wet dat vraagt, de bevoegde instanties informeren.

## 5. Misbruik melden

Zie je dat ServerScope wordt misbruikt? Mail {company.email}.

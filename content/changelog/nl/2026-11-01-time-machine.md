---
date: 2026-11-01
title: Time Machine, Incident Memory, AI-onderzoek en deploys
status: published
source: Amir03033/ServerScope (release 1 november 2026)
---
Bij een incident zie je nu precies wat er voor, tijdens en na gebeurde, en
ServerScope onthoudt wat je ervan leert.

### Nieuw

- **Time Machine** per incident: de tijdlijn, metingen en het bewijs van
  vóór, tijdens en na het incident. Het bewijs staat apart van de mogelijke
  verklaringen.
- **Deploys (Deploy Judge):** meld een deploy via de API
  (`POST /api/v1/deployments`, met een Idempotency-Key). ServerScope
  vergelijkt de prestaties vóór en na de deploy, toont deploys in de
  grafieken en houdt een deploygeschiedenis bij.
- **Global Pulse Lite:** een externe probe bevestigt een HTTP-storing vanaf
  een tweede locatie voordat er een incident opent. Zonder probe werkt alles
  zoals voorheen.
- **Incident Memory:**
  - leg per incident vast wat de oorzaak was (hypothese of bevestigd), de
    symptomen, de oplossing en of die werkte;
  - zie vergelijkbare eerdere incidenten;
  - zoeken doorzoekt ook deze lessen.
- **AI Incident Investigator** (alleen als die is ingesteld): een analyse op
  basis van het bewijs. Hij verandert nooit iets, en de uitkomst blijft een
  hypothese.
- **Snellere historie:** metingen worden per uur en per dag samengevat.

### Verbeterd

- Foutpagina's in de huisstijl.
- Een gezondheidsindicator, en bij gegevens zie je hoe vers ze zijn.
- Persoonlijke favorieten.
- Een ⋯-menu per rij.
- Een workflowbalk op het incident.
- Een eigen eindtijd voor onderhoud.
- Filters in het auditlog.

### Goed om te weten

- De AI stelt geen oorzaken vast. Alleen een mens markeert een oorzaak als
  bevestigd.
- Een probe controleert alleen als er een storing is om te bevestigen.

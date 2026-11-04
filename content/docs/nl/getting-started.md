---
order: 1
icon: book
description: Wat ServerScope doet, hoe je je aanmeldt of bij een team komt, hoe je de weg vindt en wat je als eerste instelt.
---
# Aan de slag

ServerScope bewaakt je **servers** (via een kleine agent die alleen leest), de **services** daarop, je **domeinen** (HTTP, SSL-certificaten, DNS en domeinregistratie), complete **websites** pagina voor pagina en je **geplande taken**. Het ziet **deploys** en volgt je **logbestanden**. Problemen worden **incidenten**: gerelateerde storingen worden er één, elk incident wordt automatisch onderzocht, en meldingen gaan per e-mail, in de app, als pushmelding, naar Slack, Microsoft Teams, Telegram, Discord of je eigen webhook.

De applicatie draait op [{app_url}]({app_url}). Deze website (documentatie en informatie) staat daar los van en heeft geen eigen accounts.

> [!NOTE]
> ServerScope is er in het **Engels** (standaard) en het **Nederlands**. Wissel onder **Profiel → Taal** of in het gebruikersmenu. Deze handleidingen noemen de Nederlandse namen van knoppen en menu's.

## Een account krijgen

Je krijgt op twee manieren toegang:

- **Zelf aanmelden.** Kies een plan (Free, Pro of Premium) op de prijspagina en maak een account aan. Je bevestigt je e-mailadres en accepteert de voorwaarden en de privacyverklaring. Je bent dan eigenaar van een nieuw team. Is aanmelden nog gesloten, dan biedt de prijspagina de wachtlijst aan.
- **Een uitnodiging accepteren.** Een teameigenaar of -beheerder nodigt je uit onder **Beheer → Gebruikers**. Je krijgt een e-mail met een link die **7 dagen** geldig is; je accepteert de uitnodiging zelf. Wie je uitnodigde, kan de uitnodiging intrekken of opnieuw sturen.

Kies een wachtwoord van minimaal 12 tekens met hoofdletters, kleine letters en cijfers. Vergeten? Gebruik **Wachtwoord vergeten** op de inlogpagina.

> [!TIP]
> Beheer je ServerScope zelf en is er nog geen enkele gebruiker? Maak de eerste beheerder aan op de server met `php artisan serverscope:create-admin`.

## Inloggen

1. Ga naar [{app_url}/login]({app_url}/login) en vul je e-mailadres en wachtwoord in.
2. Heb je tweestapsverificatie aangezet, vul dan de code uit je authenticator-app in (of een herstelcode).
3. Je komt op het **Dashboard**.

Te veel mislukte pogingen worden tijdelijk geblokkeerd; wacht een minuut en probeer het opnieuw.

## De weg vinden

De zijbalk heeft deze groepen:

| Groep | Onderdelen |
|---|---|
| (bovenaan) | Dashboard |
| Monitoring | Servers, Domeinen, NEXUS (met Skyline, Wereldbol, Tijdreis en Wandscherm), Afhankelijkheden, Deployments, Logs, Geplande taken |
| Incidenten | Incidenten, Alerts |
| Rapportage | Rapporten |
| Beheer (eigenaren en beheerders) | Alertregels, Statuspagina's, Webhooks, Gebruikers, Abonnement en betaling, Auditlog, Systeemstatus |
| Account | Meldingen, Profiel |

- Het **Dashboard** begint met missiecontrole: een gezondheidsscore, een tegel per server en website, de afgelopen 24 uur en **Aandacht nodig**. Zie [Dashboard, NEXUS en de weg vinden](dashboard-nexus).
- **⌘K / Ctrl+K** zoekt servers, websites en incidenten en voert acties uit.
- Het getal naast **Incidenten** en **Alerts** is het aantal open incidenten en actieve alerts.
- Ben je lid van meerdere teams, dan wissel je met de teamwisselaar.
- Op een telefoon kun je [ServerScope als app installeren](mobile-app).

## Teams en rollen

Alles in ServerScope hoort bij een **team**, en je rol geldt per team:

| | Lid | Beheerder | Eigenaar |
|---|---|---|---|
| Servers, domeinen, metrics, incidenten en rapporten van het team bekijken | ✓ | ✓ | ✓ |
| Incidenten bevestigen, onderzoeken, oplossen, toewijzen en becommentariëren | ✓ | ✓ | ✓ |
| Eigen meldingen, wachtwoord, 2FA en API-tokens beheren | ✓ | ✓ | ✓ |
| Servers, domeinen, pagina's, geplande taken en alertregels toevoegen of wijzigen | | ✓ | ✓ |
| Incidenten handmatig aanmaken, ernst wijzigen, sluiten of heropenen | | ✓ | ✓ |
| Webhooks en chatkanalen, statuspagina's, leden, auditlog | | ✓ | ✓ |
| De rol van een eigenaar wijzigen of een eigenaar verwijderen | | | ✓ |

Meer in [Teams en rollen](teams-and-roles). Wat je team kan toevoegen, hangt af van het plan; op Free bijvoorbeeld 1 server en 3 domeinen, zonder API-tokens en webhooks.

## Volgende stappen

1. [Een server koppelen met één commando](connect-server)
2. [Een domein bewaken](domain-monitoring) en [de pagina's ervan](website-monitoring)
3. [Je cron-taken bewaken](scheduled-jobs)
4. [Begrijpen hoe alerts en incidenten werken](alerts-incidents)
5. [Afhankelijkheden vastleggen](dependencies), zodat één storing één incident wordt
6. [Meldingen naar Slack, Teams of Telegram](chat-channels) of [Discord](discord-notifications) sturen

# Aan de slag

ServerScope bewaakt je **servers** (via een kleine agent) en je **domeinen** (HTTP-beschikbaarheid, SSL-certificaten, DNS en domeinregistratie). Problemen worden **incidenten** met een tijdlijn, en meldingen gaan per e-mail, in de app, naar Discord of naar je eigen webhook.

De applicatie draait op [{app_url}]({app_url}). Deze website (documentatie en informatie) staat daar los van en heeft geen eigen accounts.

> [!NOTE]
> De ServerScope-applicatie is Nederlandstalig. De Engelse handleidingen noemen de Nederlandse knoppen en menunamen, zodat je ze in de app terugvindt.

## Een account krijgen

Er is **geen openbare registratie**. Accounts worden aangemaakt door een beheerder van jouw ServerScope-omgeving:

1. Een beheerder opent **Beheer → Gebruikers** en vult naam en e-mailadres in (optioneel met beheerdersrechten).
2. Je ontvangt een e-mail met een link om zelf een wachtwoord in te stellen. De link is **60 minuten** geldig.
3. Stel een wachtwoord in van minimaal 12 tekens met hoofdletters, kleine letters en cijfers.
4. Log in op [{app_url}/login]({app_url}/login).

Is de link verlopen? Gebruik **Wachtwoord vergeten** op de inlogpagina; je ontvangt dan een nieuwe link.

> [!TIP]
> Beheer je ServerScope zelf en is er nog geen enkele gebruiker? Maak de eerste beheerder aan op de server met `php artisan serverscope:create-admin`.

## Inloggen

1. Ga naar [{app_url}/login]({app_url}/login) en vul je e-mailadres en wachtwoord in.
2. Heb je tweestapsverificatie ingeschakeld, voer dan de code uit je authenticator-app in (of een herstelcode).
3. Je komt op het **Dashboard**.

Te veel mislukte pogingen worden tijdelijk geblokkeerd; wacht dan een minuut.

## De weg vinden in het dashboard

De zijbalk heeft drie groepen:

| Groep | Onderdelen |
|---|---|
| Hoofdmenu | Dashboard, Servers, Domeinen, Incidenten, Alerts, Rapporten |
| Beheer (alleen eigenaren en beheerders) | Alertregels, Statuspagina's, Webhooks, Gebruikers, Auditlog |
| Account | Meldingen, Profiel |

- Het **Dashboard** toont de status van servers en domeinen en de actieve incidenten.
- Bovenaan staat een **zoekveld** voor servers, domeinen en incidenten.
- Het getal naast **Incidenten** en **Alerts** is het aantal open incidenten en actieve alerts.
- Onder in de zijbalk staat een link naar deze documentatie.

## Rollen

Alles in ServerScope hoort bij een **team**, en je rol geldt per team. Er zijn drie rollen:

| | Lid | Beheerder | Eigenaar |
|---|---|---|---|
| Servers, domeinen, metrics, incidenten en rapporten van het team bekijken | ✓ | ✓ | ✓ |
| Incidenten bevestigen, onderzoeken, oplossen, toewijzen en becommentariëren | ✓ | ✓ | ✓ |
| Eigen meldingen, wachtwoord, 2FA en API-tokens beheren | ✓ | ✓ | ✓ |
| Servers, domeinen en alertregels toevoegen of wijzigen | | ✓ | ✓ |
| Incidenten handmatig aanmaken, ernst wijzigen, sluiten of heropenen | | ✓ | ✓ |
| Webhooks (incl. Discord), statuspagina's, leden en auditlog | | ✓ | ✓ |
| De rol van een eigenaar wijzigen of een eigenaar verwijderen | | | ✓ |

Meer over teams, rollen en van team wisselen: [Teams en rollen](teams-and-roles).

## Volgende stappen

1. [Een server toevoegen en de agent installeren](server-monitoring)
2. [Een domein bewaken](domain-monitoring)
3. [Begrijpen hoe alerts en incidenten werken](alerts-incidents)
4. [Meldingen naar Discord sturen](discord-notifications)

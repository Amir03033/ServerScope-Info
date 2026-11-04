---
order: 60
icon: logs
category: servers
feature: logs
description: Laat de agent je logbestanden volgen, zie fouten gegroepeerd per probleem, vind de logs rond een incident en krijg een melding bij een piek in fouten.
---
# Logs

De agent kan de logbestanden volgen die jij kiest en stuurt **alleen fouten en waarschuwingen**, met hun stacktrace. ServerScope groepeert gelijke fouten tot **problemen**, zodat honderd dezelfde regels één rij met een verloop worden. Open [Logs]({app_url}/logs) in de zijbalk.

## Instellen (agent 0.4.0)

1. Vertel de agent welke bestanden hij moet volgen, in `/opt/serverscope-agent/.env`, als `naam=bestand` (meerdere gescheiden door komma's):

   ```sh
   SERVERSCOPE_LOG_FILES=app=/var/www/shop/storage/logs/laravel.log,nginx=/var/log/nginx/error.log
   ```

2. Laat de agent ze lezen. nginx-logs horen bij de groep `adm`; geef voor app-logs leesrechten:

   ```sh
   sudo usermod -aG adm serverscope
   sudo setfacl -m u:serverscope:r /var/www/shop/storage/logs/laravel.log
   sudo setfacl -d -m u:serverscope:r /var/www/shop/storage/logs
   ```

   De laatste regel maakt ook nieuwe logbestanden in die map leesbaar (bijvoorbeeld dagelijkse Laravel-logs).

3. Herstart de agent:

   ```sh
   sudo systemctl restart serverscope-agent
   ```

Nieuwe fouten staan er binnen een minuut. De agent herkent de opmaak van Laravel, nginx, Apache, PHP-FPM en syslog.

> [!IMPORTANT]
> Alleen nieuwe regels worden gelezen en er wordt niets geschreven. Wachtwoorden, tokens en e-mailadressen worden **twee keer** verwijderd: door de agent vóór het versturen en door ServerScope bij het opslaan. Logregels blijven 14 dagen bewaard.

> [!NOTE]
> Logs volgen vraagt agent **0.4.0** of nieuwer. De Bash-agent ondersteunt het niet.

## De Logs-pagina lezen

- Bovenaan: het aantal fouten, waarschuwingen en problemen, en hoeveel servers logregels sturen, vergeleken met de vorige periode (1 uur, 24 uur of 7 dagen), met een grafiek van de activiteit.
- **Problemen**: één rij per probleem, met het verloop, hoe vaak het voorkwam en wanneer het laatst is gezien. **Regels tonen** opent de losse regels.
- **Stroom**: elke losse regel, nieuwste eerst, in console-stijl. **Alle regels zoals deze** springt naar de regels van hetzelfde probleem.
- Filter op server, bron en niveau (fouten en waarschuwingen, alleen fouten of alleen waarschuwingen), of zoek in het bericht.

## Logs rond een incident

Elke incidentpagina heeft een onderdeel **Logs rond dit incident**: de fouten en waarschuwingen op de betrokken servers, van een paar minuten vóór het begin tot het einde. Vaak is dat de snelste weg naar de oorzaak.

## Een melding bij een piek in fouten

Gebruik **Piekmelding** op de Logs-pagina om een alertregel te maken die afgaat als het aantal fouten per 5 minuten plotseling stijgt. Je stelt hem bij onder **Beheer → Alertregels**, zoals elke andere regel.

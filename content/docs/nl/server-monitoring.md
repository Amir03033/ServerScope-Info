---
order: 2
icon: server
description: De agent met de hand installeren, instellen (services, deploys, logs), controleren dat er data binnenkomt en veelvoorkomende problemen oplossen.
---
# Servermonitoring

Een server stuurt zijn metingen zelf naar ServerScope met de **ServerScope-agent**: een klein Python-programma dat elke 60 seconden CPU, load, geheugen, swap, schijven, schijfactiviteit, netwerkverkeer en -fouten, het aantal processen en de uptime verzamelt en via HTTPS verstuurt. Hij kan ook [services](service-checks) melden, [deploys](deployments) zien en [logbestanden](logs) volgen. De agent leest alleen gegevens, voert geen opdrachten op afstand uit en heeft geen open poort nodig; alleen uitgaand verkeer naar ServerScope op poort 443.

> [!TIP]
> De snelste weg is het installatiescript: zie [Een server koppelen met één commando](connect-server). Dat zet ook [ondertekende agent-updates](agent-updates) klaar. Deze handleiding beschrijft de **handmatige installatie** en de instellingen die voor allebei gelden.

Voor deze handleiding moet je eigenaar of beheerder van het team zijn, en heb je root-toegang (sudo) op de server nodig.

## Vereisten

- Ubuntu of Debian met `systemd`
- Python 3 met `venv` (`sudo apt install python3-venv`)
- Uitgaand HTTPS-verkeer naar `{app_url}`
- Een gesynchroniseerde klok (NTP)
- De agentbestanden: de map `agent/` uit de ServerScope-broncode (`serverscope_agent/`, `requirements.txt` en `serverscope-agent.service`)

> [!NOTE]
> Bij een handmatige installatie kopieer je de agentbestanden zelf naar de server, zoals hieronder beschreven. Geen Python of systemd, bijvoorbeeld in een container? Gebruik de [Bash-agent](bash-agent).

## 1. De server toevoegen

1. Open [Servers → Server toevoegen]({app_url}/servers/add) en kies **Handmatig registreren**.
2. Vul in:
   - **Servernaam** (verplicht, max. 100 tekens), bijvoorbeeld `Production VPS`
   - **Hostname** (verplicht), bijvoorbeeld `server.example.com`
   - **IP-adres** en **Besturingssysteem** (optioneel, ter informatie)
   - **Omgeving**: Productie, Staging, Ontwikkeling, Test of Overig
   - **Naam agentcredential** (standaard `Primary agent`)
   - **Notities** (optioneel)
3. Klik op **Server registreren**.
4. Kopieer het **agenttoken** dat nu verschijnt.

> [!WARNING]
> Het agenttoken wordt **één keer** getoond. ServerScope bewaart alleen een hash. Ben je het kwijt, genereer dan een nieuw token op de serverpagina (zie *Token roteren*).

De server staat nu op **In afwachting** totdat de eerste meting binnenkomt.

## 2. De agent installeren

Voer deze stappen uit op de server, in de map waar je de agentbestanden hebt neergezet.

1. Maak een systeemgebruiker en de installatiemap aan:

   ```sh
   sudo useradd --system --home /opt/serverscope-agent --shell /usr/sbin/nologin serverscope
   sudo mkdir -p /opt/serverscope-agent
   sudo cp -r serverscope_agent requirements.txt /opt/serverscope-agent/
   ```

2. Installeer de Python-afhankelijkheden in een eigen virtual environment:

   ```sh
   cd /opt/serverscope-agent
   sudo python3 -m venv .venv
   sudo .venv/bin/pip install -r requirements.txt
   ```

3. Maak het configuratiebestand aan. Vervang `<token>` door het token uit stap 1:

   ```sh
   sudo tee /opt/serverscope-agent/.env >/dev/null <<'ENV'
   SERVERSCOPE_URL={app_url}
   SERVERSCOPE_TOKEN=<token>
   SERVERSCOPE_INTERVAL=60
   ENV
   ```

4. Bescherm het bestand, zodat alleen root en de agent het token kunnen lezen:

   ```sh
   sudo chown -R root:serverscope /opt/serverscope-agent
   sudo chmod 640 /opt/serverscope-agent/.env
   ```

5. Installeer en start de systemd-service:

   ```sh
   sudo cp serverscope-agent.service /etc/systemd/system/
   sudo systemctl daemon-reload
   sudo systemctl enable --now serverscope-agent
   ```

6. Zorg dat de klok gesynchroniseerd blijft:

   ```sh
   sudo timedatectl set-ntp true
   ```

De meegeleverde service draait als gebruiker `serverscope`, met onder andere `NoNewPrivileges`, `ProtectSystem=strict` en een alleen-lezen installatiemap.

## 3. Configuratie

De agent leest `/opt/serverscope-agent/.env`:

| Variabele | Verplicht | Betekenis |
|---|---|---|
| `SERVERSCOPE_URL` | ja | Basis-URL van ServerScope. Moet met `https://` beginnen. |
| `SERVERSCOPE_TOKEN` | ja | Het agenttoken van deze server. |
| `SERVERSCOPE_INTERVAL` | nee | Seconden tussen metingen. Standaard `60`, minimaal `10`. |
| `SERVERSCOPE_SERVICES` | nee | Services om te bewaken, bijvoorbeeld `nginx,php8.4-fpm,mysql` (agent 0.2.0+). Zie [Diensten bewaken](service-checks). |
| `SERVERSCOPE_DEPLOY_PATHS` | nee | `naam=/pad,naam2=/pad2`: git-checkouts (of bare repositories) voor deploydetectie (agent 0.4.0+). Zie [Deployments](deployments). |
| `SERVERSCOPE_LOG_FILES` | nee | `naam=/pad/bestand.log,…`: logbestanden om te volgen (agent 0.4.0+). Zie [Logs](logs). |

Herstart na een wijziging: `sudo systemctl restart serverscope-agent`.

## 4. Controleren of er data binnenkomt

1. Volg het log van de agent:

   ```sh
   journalctl -u serverscope-agent -f
   ```

   Je ziet `ServerScope agent started.` en daarna elke minuut `Metrics submitted successfully.`

2. Test het token los van de agent (optioneel):

   ```sh
   curl -s -H "Authorization: Bearer <token>" {app_url}/agent/ping
   ```

   Een geldig token geeft `{"authenticated":true,"server_id":…,"server_name":"…"}`.

3. Open de server in [Servers]({app_url}/servers). De status wordt **Online**, *Laatste meting* loopt mee en de grafieken vullen zich: CPU, geheugen, swap, schijven, schijfactiviteit, netwerkverkeer en netwerkfouten. Op het dashboard begint de hartslag van de server te kloppen.

## Diagnose van de server

Bovenaan de serverpagina legt de **diagnose** in gewone woorden uit wat er aan de hand is. ServerScope onderscheidt situaties die er van buiten hetzelfde uitzien: een gestopte service (de agent meldt zich nog), een agent die niet reageert (websites op de server werken nog) en een server die echt onbereikbaar is (de websites erop vallen ook uit). Ook ontbrekende gegevens, een verouderde agent en de integriteit van de agent (een bekende build, een onbekende build of niet geverifieerd) staan erbij.

## Problemen oplossen

| Wat je ziet | Oorzaak en oplossing |
|---|---|
| `Metrics submission failed.` in het log | De agent kan ServerScope niet bereiken. Test met de `curl`-opdracht hierboven en controleer firewall/proxy voor uitgaand verkeer op poort 443. |
| `Metrics submission failed: HTTP 401, token rejected; …` (of `ping` antwoordt met HTTP 401) | Het token is fout, ingetrokken of geroteerd, of hoort bij een ander ServerScope-adres. Genereer een nieuw token en zet het in `.env`. |
| `Metrics submission failed: HTTP 404, endpoint not found; …` | `SERVERSCOPE_URL` klopt niet; gebruik de basis-URL zonder pad. |
| `Metrics submission failed: HTTP 422, rejected fields: …` | ServerScope weigerde een deel van de meting, meestal omdat de klok niet klopt (zie hieronder). Het log noemt de velden, nooit waarden of tokens. |
| `Metrics submission failed: HTTP 429, rate limited; …` | Verhoog `SERVERSCOPE_INTERVAL`. |
| `SERVERSCOPE_URL must be a valid HTTPS base URL.` | Gebruik `{app_url}` zonder pad en met `https://`. |
| `SERVERSCOPE_INTERVAL must be at least 10 seconds.` | Kies een interval van 10 seconden of meer. |
| Service start niet, of `Permission denied` op `.env` | Controleer eigenaar en rechten (`root:serverscope`, `640`) en `systemctl status serverscope-agent`. |
| Server blijft **In afwachting** of wordt **Offline** terwijl de agent draait (en het log `Metrics submission failed.` toont) | Controleer de klok: metingen die meer dan 30 seconden in de toekomst of meer dan 10 minuten in het verleden liggen, worden geweigerd. |

> [!IMPORTANT]
> Een server die 2 minuten niets stuurt, wordt als **Offline** getoond. De standaard alertregel *Agent offline* maakt na 5 minuten stilte een **kritiek** incident aan. Zie [Alerts en incidenten](alerts-incidents).

## Token roteren

Op de serverpagina, onder **Agentcredentials**:

1. Klik op **Token roteren**. Alle actieve tokens van deze server worden ingetrokken en je krijgt één nieuw token te zien.
2. Zet het nieuwe token in `/opt/serverscope-agent/.env`.
3. Herstart: `sudo systemctl restart serverscope-agent`.

Het oude token werkt direct niet meer. Je kunt ook een **Extra token** aanmaken (bijvoorbeeld voor een migratie) en losse tokens intrekken.

## Onderhoud

Plan je werk aan een server, gebruik dan het paneel **onderhoud** op de serverpagina: start onderhoud nu voor een gekozen duur, of kies zelf een start- en eindtijd. Tijdens onderhoud worden er voor de resourcemetingen, agent offline en servicecontroles van de server geen nieuwe alerts of incidenten geopend.

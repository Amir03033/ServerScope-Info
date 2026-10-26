---
order: 2
icon: server
description: Een server toevoegen, de agent installeren en configureren, controleren dat er data binnenkomt en veelvoorkomende problemen oplossen.
---
# Servermonitoring

Een server stuurt zijn metingen zelf naar ServerScope met de **ServerScope-agent**: een klein Python-programma dat elke 60 seconden CPU, load, geheugen, swap, schijven, netwerktellers en uptime verzamelt en via HTTPS verstuurt. De agent voert geen opdrachten uit en heeft geen open poort nodig; alleen uitgaand verkeer naar ServerScope op poort 443.

Voor deze handleiding heb je een **beheerdersaccount** nodig en root-toegang (sudo) op de server.

## Vereisten

- Ubuntu of Debian met `systemd`
- Python 3 met `venv` (`sudo apt install python3-venv`)
- Uitgaand HTTPS-verkeer naar `{app_url}`
- Een gesynchroniseerde klok (NTP)
- De agentbestanden: de map `agent/` uit de ServerScope-broncode (`serverscope_agent/`, `requirements.txt` en `serverscope-agent.service`)

> [!NOTE]
> Er is (nog) geen installatiescript of pakket. Je kopieert de agentbestanden zelf naar de server, zoals hieronder beschreven.

## 1. De server toevoegen

1. Open [Servers → Server toevoegen]({app_url}/servers/create).
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

3. Open de server in [Servers]({app_url}/servers). De status wordt **Online**, *Laatste meting* loopt mee en de grafieken voor CPU, geheugen, schijven en netwerk vullen zich.

## Problemen oplossen

| Wat je ziet | Oorzaak en oplossing |
|---|---|
| `Metrics submission failed.` in het log | De agent kan ServerScope niet bereiken of het token wordt geweigerd. Test met de `curl`-opdracht hierboven en controleer firewall/proxy voor uitgaand verkeer op poort 443. |
| `ping` antwoordt met *Ongeldige of niet meer geldige agentcredential.* (HTTP 401) | Het token is fout, ingetrokken of geroteerd. Genereer een nieuw token en zet het in `.env`. |
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

Plan je werk aan een server, start dan een **onderhoudsvenster** op de serverpagina (5 minuten tot 7 dagen) of vul een start- en eindtijd in bij **Bewerken**. Tijdens onderhoud worden er voor die server geen alerts of incidenten aangemaakt.

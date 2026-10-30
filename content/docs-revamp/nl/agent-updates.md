---
order: 40
icon: shield
category: servers
feature: agent-updates
description: Ondertekende agent-releases, de updater, automatisch bijwerken, terugzetten, de lokale noodknop en het controleren van de vingerafdruk.
---
# Agent-updates via het dashboard

Vanaf agentversie **0.3.0** kun je de agent bijwerken vanuit het dashboard. Elke release is **ondertekend**. Een server installeert een release alleen als de handtekening klopt met de publieke sleutel die hij bij de installatie heeft opgeslagen.

## Ondertekende releases

Elke agentrelease wordt ondertekend met één privésleutel die alleen **offline** bestaat, bij de beheerder van ServerScope. Die sleutel staat niet op de ServerScope-server, niet in de repository en niet in CI. Daardoor kan zelfs een volledig overgenomen ServerScope-server geen release maken die jouw servers accepteren.

De handtekening is Ed25519. Je server bewaart de publieke sleutel in `/etc/serverscope/release.pub`.

## De updater

De agent draait als de gebruiker `serverscope` zonder schrijfrechten op zijn eigen installatie en kan zichzelf dus niet bijwerken. Daarvoor is er een klein apart programma, `/usr/local/sbin/serverscope-updater`. Het draait als root via de systemd-timer `serverscope-updater.timer`, elke 5 minuten.

Als een teambeheerder op **Bijwerken** klikt (of **automatisch bijwerken** aan staat voor de server), dan:

1. krijgt de updater alleen een verzoeknummer en een versienummer: geen URL's, scripts of commando's. Hij downloadt die release van vaste paden;
2. controleert hij de handtekening met de **lokale** sleutel, daarna het manifest, de SHA-256 en de inhoud van het pakket, en de versie. Een versie die gelijk aan of lager is dan de geïnstalleerde of de hoogste ooit geïnstalleerde versie weigert hij (geen downgrades), net als een versie die hier al eerder mislukte;
3. installeert hij de nieuwe versie naast de huidige, schakelt hij in één keer over en herstart hij de agent;
4. wacht hij minstens 3 minuten op een meting van de nieuwe versie;
5. **zet hij de vorige versie terug** als die meting niet komt;
6. meldt hij het resultaat: gelukt, mislukt en teruggezet, of geweigerd (met een vaste reden, zoals `bad_signature`).

**Automatisch bijwerken** staat standaard uit en zet je per server aan.

Logboek en status op de server:

```sh
journalctl -u serverscope-updater
```

```sh
sudo serverscope-updater --status
```

## De noodknop op je eigen server

Wil je dat er niets meer wordt bijgewerkt? Dat regel je lokaal; ServerScope kan dit niet terugdraaien:

```sh
sudo bash serverscope-install.sh --disable-updates
```

Dit maakt `/etc/serverscope/updates-disabled` aan en stopt de timer. Zolang dat bestand bestaat, doet de updater helemaal niets, ook geen netwerkverzoek. Weer aanzetten:

```sh
sudo bash serverscope-install.sh --enable-updates
```

## De vingerafdruk controleren

De vingerafdruk van de releasesleutel (SHA-256 van de publieke sleutel, DER) is:

`{release_fingerprint}`

Vergelijk die met de regel `Releasesleutel: vingerafdruk …` die het installatiescript toont, en op een geïnstalleerde server met:

```sh
openssl pkey -pubin -in /etc/serverscope/release.pub -outform DER | sha256sum
```

> [!WARNING]
> Bij een **eerste** installatie komt de publieke sleutel uit het installatiescript, en dat komt van de ServerScope-server. Controleer bij een eerste installatie daarom altijd de vingerafdruk hierboven. **Bestaande** installaties kan ServerScope via de updater niet overnemen: zij houden de sleutel die ze bij de installatie hebben opgeslagen.

## Bestaande installaties (agent 0.2.0)

Die blijven werken zonder dat je iets doet. Wil je ondertekende updates, draai dan het installatiescript nog een keer op de server, met hetzelfde commando als voor een nieuwe server. Je hebt geen nieuw koppeltoken nodig en er komt geen nieuwe server bij:

```sh
curl --proto '=https' --tlsv1.2 -fsSL {app_url}/install.sh -o serverscope-install.sh && sudo bash serverscope-install.sh
```

Handmatige installaties en de Bash-agent worden nooit door de updater aangeraakt.

## Verwijderen

`sudo bash serverscope-install.sh --uninstall` verwijdert ook de updater, `/etc/serverscope` en `/var/lib/serverscope-updater`.

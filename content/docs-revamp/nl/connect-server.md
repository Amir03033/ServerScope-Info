---
order: 10
icon: terminal
category: servers
feature: one-command-install
description: Een Linux-server koppelen met één commando en een eenmalig koppeltoken. Vereisten, wat het script doet, opnieuw draaien, verwijderen en problemen oplossen.
---
# Een server koppelen met één commando

Je koppelt een Linux-server aan ServerScope met één commando. Het script installeert de agent, wisselt een eenmalig koppeltoken in voor een eigen agenttoken en wacht tot de eerste meting binnen is.

## Vereisten

- **Ubuntu 22.04 of nieuwer**, of **Debian 12 of nieuwer**
- Processor **amd64** of **arm64**
- **systemd** (de agent draait als systemd-service)
- Een gebruiker met **sudo**-rechten
- Uitgaand HTTPS-verkeer van de server naar [{app_url}]({app_url})
- In ServerScope: de rol **eigenaar** of **beheerder** in je team

Past je server niet bij deze vereisten? Bekijk dan de [handmatige installatie](server-monitoring).

## 1. Een koppeltoken aanmaken

Open in ServerScope **Servers → Server toevoegen → Linux → Koppeltoken aanmaken**. De pagina toont het installatiecommando en een koppeltoken.

- Het token is **60 minuten** geldig en werkt **één keer**.
- Je kunt een token op dezelfde pagina **intrekken**.
- ServerScope bewaart alleen een hash van het token.

## 2. Het commando uitvoeren

Voer op de server uit:

```sh
curl --proto '=https' --tlsv1.2 -fsSL {app_url}/install.sh -o serverscope-install.sh && sudo bash serverscope-install.sh
```

Wil je het script eerst lezen? Open [{app_url}/install.sh]({app_url}/install.sh) in je browser. Het installeert software als root.

## 3. Het koppeltoken plakken

Het script vraagt om het koppeltoken. De invoer is verborgen. Het token zit bewust niet in het commando, zodat het niet in je shellgeschiedenis of in de proceslijst terechtkomt.

> [!TIP]
> Automatiseer je de installatie? Zet het token dan in de omgevingsvariabele `SERVERSCOPE_ENROLL_TOKEN`, bijvoorbeeld uit een secret store. Typ het niet op de commandoregel.

Het script wacht op de eerste meting. Daarna staat de server als **online** in het dashboard van het team dat het token maakte. De pagina in ServerScope laat live zien wanneer de server verbonden is.

## Wat het script doet

- Het stopt op alles behalve Ubuntu 22.04+ of Debian 12+ op amd64 of arm64 met systemd.
- Het installeert `python3`, `python3-venv`, `python3-psutil`, `python3-requests` en `python3-dotenv` uit de pakketbronnen van je distributie (geen pip, geen PyPI) en maakt een virtuele Python-omgeving.
- Het maakt de systeemgebruiker `serverscope` zonder shell aan (als die nog niet bestaat).
- Het downloadt het agentpakket en de bijbehorende SHA-256-controlesom van ServerScope, alleen via HTTPS (TLS 1.2 of hoger), en controleert de controlesom.
- Het installeert de agent in `/opt/serverscope-agent`, met de configuratie in `/opt/serverscope-agent/.env` (eigenaar `root:serverscope`, rechten `640`).
- Het wisselt het koppeltoken in voor een normaal agenttoken, start de service `serverscope-agent` en wacht maximaal 90 seconden op de eerste meting.

> [!NOTE]
> De controlesom komt van dezelfde server als het pakket. Ze beschermt tegen een beschadigde of afgebroken download, niet tegen een gecompromitteerde ServerScope-server.

## Opnieuw draaien

Het script opnieuw draaien is veilig:

- Een installatie die door het script is gemaakt en een werkend agenttoken heeft, wordt alleen bijgewerkt. Er komt geen tweede server bij.
- Is het agenttoken ingetrokken, dan vraagt het script om een nieuw koppeltoken en **maakt het een nieuwe server aan**. Verwijder de oude server daarna in het dashboard; de historie blijft tot je dat doet.
- Een agent die je **handmatig** hebt geïnstalleerd, laat het script ongemoeid.

## Verwijderen

```sh
sudo bash serverscope-install.sh --uninstall
```

Dit trekt het eigen agenttoken in bij ServerScope, stopt en verwijdert de service, verwijdert `/opt/serverscope-agent` en verwijdert de gebruiker `serverscope` als het script die heeft gemaakt. De server en zijn historie blijven in ServerScope. De Python-pakketten uit apt blijven staan.

## Problemen oplossen

Het script stopt met een melding in het Nederlands en een oplossing. De meest voorkomende:

| Melding | Oplossing |
|---|---|
| Ubuntu/Debian-versie of besturingssysteem wordt niet ondersteund | Gebruik Ubuntu 22.04+ of Debian 12+, of de [handmatige installatie](server-monitoring). |
| Processorarchitectuur wordt niet ondersteund | Alleen amd64 en arm64. |
| systemd draait niet op dit systeem | De agent draait als systemd-service; zonder systemd kan het script hem niet installeren. |
| Dit script moet als root draaien | Start het met `sudo bash serverscope-install.sh`. |
| Het koppeltoken is ongeldig, verlopen, al gebruikt of ingetrokken | Maak een nieuw koppeltoken aan en draai het script opnieuw. |
| Te veel koppelpogingen | Wacht een minuut en probeer het opnieuw. |
| ServerScope is niet bereikbaar | Controleer DNS, firewall en proxy: de server moet ServerScope via HTTPS kunnen bereiken. |
| De checksum van het agentpakket klopt niet | Probeer het opnieuw. Blijft dit gebeuren, neem dan contact op. |
| De agent draait, maar ServerScope heeft binnen 90 seconden nog geen meting ontvangen | Controleer de klok (`timedatectl`; zet NTP aan met `sudo timedatectl set-ntp true`) en het logboek: `journalctl -u serverscope-agent -n 50`. Het script opnieuw draaien is veilig. |
| Er staat al een handmatig geïnstalleerde agent | Werk die bij volgens de handmatige instructie, of verwijder hem eerst. |

Het logboek van de agent bekijk je met:

```sh
journalctl -u serverscope-agent -n 50
```

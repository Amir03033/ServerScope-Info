---
order: 20
icon: cpu
category: servers
feature: service-checks
description: Diensten zoals Nginx, MySQL, PHP-FPM en Redis laten bewaken door de agent (vanaf versie 0.2.0).
---
# Diensten bewaken

Vanaf agentversie **0.2.0** kan de agent naast de servermetingen ook de toestand van diensten doorgeven, zoals Nginx, Apache, MySQL, PostgreSQL, Redis, PHP-FPM en andere systemd-diensten. Je kiest zelf welke; de agent gaat er niet van uit dat iets bestaat.

## Instellen

Zet in `/opt/serverscope-agent/.env` de systemd-units die je wilt bewaken, gescheiden door komma's:

```sh
SERVERSCOPE_SERVICES=nginx,mysql,php8.3-fpm,redis-server
```

Herstart daarna de agent:

```sh
sudo systemctl restart serverscope-agent
```

## Hoe de agent het controleert

De agent voert per dienst dit uit:

```sh
systemctl show <unit> --property=LoadState,ActiveState,SubState
```

Dat is alleen-lezen, zonder shell, met een time-out van 3 seconden. Zonder systemd zoekt de agent een proces met dezelfde naam.

De agent meldt per dienst een van deze toestanden:

| Toestand | Betekenis |
|---|---|
| `running` | De dienst draait |
| `starting` | De dienst start op |
| `stopped` | De dienst is gestopt |
| `failed` | De dienst is mislukt |
| `not_found` | Er bestaat geen dienst met deze naam |
| `unknown` | De toestand kon niet worden bepaald |

## Incidenten

ServerScope opent een incident nadat een dienst het ingestelde aantal keren achter elkaar niet goed is gemeld. Een gestopte dienst wordt apart gemeld van een server die onbereikbaar is of een agent die niets meer stuurt.

Via de API vraag je de laatst gemelde toestanden op met `GET /servers/{id}/services`. Zolang de agent offline is, is de status daar `stale`.

> [!NOTE]
> Agent 0.1.0 blijft werken. Voor diensten en de andere nieuwe servergegevens is versie 0.2.0 nodig.

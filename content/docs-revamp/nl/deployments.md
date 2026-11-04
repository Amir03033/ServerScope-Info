---
order: 50
icon: flag
category: servers
feature: deployments
description: Laat de agent en GitHub je deploys vanzelf vastleggen, meld ze via de API en zie wat er voor en na veranderde.
---
# Deployments

ServerScope houdt een geschiedenis bij van je deploys, toont ze in de grafieken en op de tijdlijn van incidenten, en vergelijkt de prestaties **vóór en na** elke deploy (Deploy Judge). Een deploy kan op drie manieren binnenkomen:

| Manier | Wat je doet | Het beste voor |
|---|---|---|
| De agent op je server | Wijs de agent de map van de app aan | Deployen met `git pull` of een script op de server |
| GitHub | Koppel de repository met een webhook | Titels, auteurs en links van commits; platforms die bij elke push deployen |
| De API | Roep `POST /api/v1/deployments` aan vanuit je pipeline | Elk ander deploygereedschap |

Open [Deployments]({app_url}/deployments) voor de geschiedenis en [Deployments → Automatische deploys]({app_url}/deployments/sources) om de agent en GitHub in te stellen.

## Op je server (agent 0.4.0)

De agent leest op welke commit een git-map staat. Hij leest alleen `.git/HEAD` en voert geen git-commando's uit. Verandert de commit, dan is dat een deploy van die app op die server, op het moment dat het gebeurde.

1. Zet de map in `/opt/serverscope-agent/.env`, als `naam=map` (meerdere gescheiden door komma's):

   ```sh
   SERVERSCOPE_DEPLOY_PATHS=shop=/var/www/shop
   ```

2. Geef de agent leesrechten op de git-map:

   ```sh
   sudo setfacl -R -m u:serverscope:rX /var/www/shop/.git
   ```

3. Herstart de agent:

   ```sh
   sudo systemctl restart serverscope-agent
   ```

Je volgende deploy staat er binnen een minuut. De pagina **Automatische deploys** toont elke gevolgde map met branch, commit en status (*Wordt gevolgd*, *Map niet gevonden*, *Geen leesrechten* of *Geen git-map*).

> [!TIP]
> Op Plesk met Git-uitrol is de repository meestal een bare repository in `/var/www/vhosts/<domein>/git/<naam>.git`. Gebruik dat pad.

> [!NOTE]
> Deploydetectie vraagt agent **0.4.0** of nieuwer. De Bash-agent ondersteunt het niet.

## GitHub

GitHub voegt de titel van de commit of pull request, de auteur en een link toe aan elke deploy, en kan ook zelf deploys melden.

1. Klik onder **Automatische deploys → GitHub** op **Repository koppelen**.
2. Vul de **Repository** in (`eigenaar/naam`), de **Applicatienaam** (dezelfde naam als in `SERVERSCOPE_DEPLOY_PATHS`) en de omgeving.
3. Kies **Wanneer is het een deployment?**
   - **Alleen als de server verandert**: GitHub vult details aan; de agent ziet de echte deploy.
   - **Bij elke push naar de branch**: voor platforms die bij elke push automatisch deployen (Forge, Ploi, Vercel en dergelijke).
4. Koppel eventueel servers en websites en klik op **Koppelen**.
5. ServerScope toont **één keer** de **webhook-URL** en een **geheim**. Open in GitHub de repository → **Settings → Webhooks → Add webhook** en vul in:
   - Payload URL: de webhook-URL (`{app_url}/api/webhooks/github/…`)
   - Content type: `application/json`
   - Secret: het geheim
   - Events: *Let me select individual events* → **Pushes**, **Pull requests** en **Deployment statuses**

De eerste `ping` van GitHub markeert de koppeling als verbonden. Een geslaagde **deployment status** van GitHub telt altijd als deploy. Met **Nieuw geheim** of **Ontkoppelen** in het ⋯-menu van de repository beheer je de koppeling; al vastgelegde deploys blijven staan.

> [!NOTE]
> ServerScope controleert bij elke levering de handtekening van GitHub (`X-Hub-Signature-256`) en negeert leveringen voor een andere repository.

## Via de API

Meld een deploy vanuit elke pipeline met een token met het recht `write` (Pro en Premium):

```sh
curl -sS -X POST {app_url}/api/v1/deployments \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Idempotency-Key: deploy-shop-1f3a9c2" \
  -d '{"application":"shop","environment":"production","version":"1.4.2","commit_sha":"1f3a9c2","resources":[{"type":"server","id":3},{"type":"domain","id":7}]}'
```

Alle velden staan in het [API v1-overzicht](api-reference#deployments).

## Voor en na

Elke deploypagina vergelijkt de metingen van de gekoppelde servers en websites in de minuten vóór en na de deploy (Deploy Judge). Elke meting krijgt een uitkomst: **Beter**, **Slechter**, **Geen duidelijke verandering** of **Te weinig gegevens**, en een incident dat kort na de deploy opende staat erbij. Deploys staan ook:

- als markering in de grafieken van servers en websites;
- op de tijdlijn van het dashboard (laatste 24 uur);
- op de tijdlijn van een incident, in de [Time Machine](incident-investigation) en in een nabeschouwing, zodat je meteen ziet of een incident vlak na een deploy begon.

Een verandering na een deploy bewijst niet dat de deploy de oorzaak is: verkeer, andere wijzigingen of problemen elders kunnen hetzelfde doen. Zie het als een aanwijzing.

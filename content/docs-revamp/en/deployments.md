---
order: 50
icon: flag
category: servers
feature: deployments
description: Let the agent and GitHub record your deployments automatically, report them through the API, and see what changed before and after.
---
# Deployments

ServerScope keeps a history of your deployments, marks them in the charts and on incident timelines, and compares performance **before and after** each deployment (Deploy Judge). A deployment can reach ServerScope in three ways:

| Way | What you do | Best for |
|---|---|---|
| The agent on your server | Point the agent at the app folder | Deploying with `git pull` or a script on the server |
| GitHub | Connect the repository with a webhook | Adding commit titles, authors and links; platforms that deploy on every push |
| The API | Call `POST /api/v1/deployments` from your pipeline | Any other deploy tool |

Open [Deployments]({app_url}/deployments) for the history and [Deployments → Automatic deployments]({app_url}/deployments/sources) to set up the agent and GitHub.

## On your server (agent 0.4.0)

The agent reads which commit a git folder is on. It only reads `.git/HEAD` and runs no git commands. When the commit changes, that is a deployment of that app on that server, at the moment it happened.

1. Add the folder to `/opt/serverscope-agent/.env`, as `name=folder` (several separated by commas):

   ```sh
   SERVERSCOPE_DEPLOY_PATHS=shop=/var/www/shop
   ```

2. Give the agent read access to the git folder:

   ```sh
   sudo setfacl -R -m u:serverscope:rX /var/www/shop/.git
   ```

3. Restart the agent:

   ```sh
   sudo systemctl restart serverscope-agent
   ```

Your next deployment shows up within a minute. The **Automatic deployments** page lists each watched folder with its branch, commit and state (*Watching*, *Folder not found*, *No read access* or *Not a git folder*).

> [!TIP]
> On Plesk with Git deployment the repository is usually a bare repository in `/var/www/vhosts/<domain>/git/<name>.git`. Use that path.

> [!NOTE]
> Deploy detection needs agent **0.4.0** or newer. The Bash agent does not support it.

## GitHub

GitHub adds the commit or pull request title, the author and a link to every deployment, and can report deployments itself.

1. Under **Automatic deployments → GitHub** click **Connect a repository**.
2. Enter the **Repository** (`owner/name`), the **Application name** (the same name as in `SERVERSCOPE_DEPLOY_PATHS`) and the environment.
3. Choose **When is it a deployment?**
   - **Only when the server changes**: GitHub adds details; the agent sees the real deployment.
   - **Every push to the branch**: for platforms that deploy automatically on every push (Forge, Ploi, Vercel and the like).
4. Optionally link servers and websites, then click **Connect**.
5. ServerScope shows the **webhook URL** and a **secret**, once. In GitHub open the repository → **Settings → Webhooks → Add webhook** and fill in:
   - Payload URL: the webhook URL (`{app_url}/api/webhooks/github/…`)
   - Content type: `application/json`
   - Secret: the secret
   - Events: *Let me select individual events* → **Pushes**, **Pull requests** and **Deployment statuses**

GitHub's first `ping` marks the connection as connected. A successful GitHub **deployment status** always counts as a deployment. Use **New secret** or **Disconnect** in the repository's ⋯ menu; deployments already recorded stay.

> [!NOTE]
> ServerScope checks GitHub's signature (`X-Hub-Signature-256`) on every delivery and ignores deliveries for another repository.

## Through the API

Report a deployment from any pipeline with a token that has the `write` ability (Pro and Premium):

```sh
curl -sS -X POST {app_url}/api/v1/deployments \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Idempotency-Key: deploy-shop-1f3a9c2" \
  -d '{"application":"shop","environment":"production","version":"1.4.2","commit_sha":"1f3a9c2","resources":[{"type":"server","id":3},{"type":"domain","id":7}]}'
```

All fields are in the [API v1 reference](api-reference#deployments).

## Before and after

Every deployment page compares the measurements of the linked servers and websites in the minutes before and after the deployment (Deploy Judge). Each measurement gets a result: **Better**, **Worse**, **No significant change** or **Not enough data**, and an incident that opened shortly after the deployment is shown with it. Deployments also appear:

- as markers in the server and website charts;
- on the dashboard timeline (last 24 hours);
- on the incident timeline, in the [Time Machine](incident-investigation) and in a postmortem, so you see at once whether an incident started right after a deployment.

A change after a deployment does not prove the deployment caused it: traffic, other changes or upstream problems can do the same. Treat it as a clue.

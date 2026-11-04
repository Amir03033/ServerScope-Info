<?php

namespace App\Services\Site;

/**
 * ServerScope features as described in the ServerScope repository
 * (docs/AUDIT.md, docs/CHANGELOG.*.md, agent/README.md). The status here is
 * only the starting value for a fresh database; the owner decides the real
 * status per feature in the admin panel.
 *
 * Starting values: every feature starts as "available" (owner's choice,
 * October 2026); set "coming_soon" or "hidden" per feature in the panel.
 */
class FeatureCatalog
{
    /** key => [status, category, icon, [nl name, en name], [nl summary, en summary]] */
    public const FEATURES = [
        'server-monitoring' => ['available', 'servers', 'server', ['Servermonitoring', 'Server monitoring'], ['CPU, load, geheugen, swap, schijven en netwerk via een lichte agent die alleen leest.', 'CPU, load, memory, swap, disks and network through a lightweight, read-only agent.']],
        'service-checks' => ['available', 'servers', 'cpu', ['Diensten bewaken', 'Service checks'], ['Nginx, Apache, MySQL, PostgreSQL, Redis, PHP-FPM en andere systemd-diensten (agent 0.2.0).', 'Nginx, Apache, MySQL, PostgreSQL, Redis, PHP-FPM and other systemd services (agent 0.2.0).']],
        'one-command-install' => ['available', 'servers', 'terminal', ['Koppelen met één commando', 'One-command install'], ['Een Linux-server koppelen met één commando en een eenmalig koppeltoken.', 'Connect a Linux server with one command and a one-time enrollment token.']],
        'bash-agent' => ['available', 'servers', 'terminal', ['Bash-agent', 'Bash agent'], ['Voor hosts en containers zonder Python of systemd.', 'For hosts and containers without Python or systemd.']],
        'agent-updates' => ['available', 'servers', 'shield', ['Agent-updates via het dashboard', 'Agent updates from the dashboard'], ['Ondertekende agent-releases, bijwerken vanuit het dashboard en terugzetten.', 'Signed agent releases, updating from the dashboard and rolling back.']],
        'website-monitoring' => ['available', 'websites', 'globe', ['Websitemonitoring per pagina', 'Website monitoring per page'], ['Statuscode, responstijd, inhoud en headers per pagina, met automatisch ontdekken.', 'Status code, response time, content and headers per page, with discovery.']],
        'domain-monitoring' => ['available', 'websites', 'globe', ['HTTP-beschikbaarheid', 'HTTP availability'], ['HTTP- en HTTPS-controles met drempels voor storing en herstel.', 'HTTP and HTTPS checks with failure and recovery thresholds.']],
        'ssl-monitoring' => ['available', 'websites', 'lock', ['SSL-certificaten', 'SSL certificates'], ['Geldigheid, keten, verloop en wijzigingen van certificaten.', 'Validity, chain, expiry and changes of certificates.']],
        'dns-monitoring' => ['available', 'websites', 'network', ['DNS-bewaking', 'DNS monitoring'], ['A, AAAA, CNAME, MX, TXT, NS en CAA, met wijzigingsgeschiedenis.', 'A, AAAA, CNAME, MX, TXT, NS and CAA, with change history.']],
        'domain-expiry' => ['available', 'websites', 'clock', ['Domeinregistratie', 'Domain expiry'], ['Verloopdatum van de domeinregistratie via RDAP.', 'Domain registration expiry via RDAP.']],
        'incidents' => ['available', 'incidents', 'alert', ['Incidenten', 'Incidents'], ['Tijdlijn, toewijzing, opmerkingen, MTTA, MTTR en MTTD.', 'Timeline, assignment, comments, MTTA, MTTR and MTTD.']],
        'correlation' => ['available', 'incidents', 'link', ['Slimme incidenten', 'Smart incidents'], ['Gerelateerde storingen worden één incident, alleen via afhankelijkheden die jij bevestigt.', 'Related outages become one incident, only through dependencies you confirm.']],
        'predictions' => ['available', 'incidents', 'chart', ['Voorspellingen en afwijkingen', 'Forecasts and anomalies'], ['Waarschuwt vooraf als een schijf of het geheugen vol dreigt te raken.', 'Warns in advance when a disk or memory is about to fill up.']],
        'notifications' => ['available', 'incidents', 'bell', ['Meldingen en escalatie', 'Notifications and escalation'], ['E-mail en meldingen in de app, met escalatie van onbevestigde incidenten.', 'E-mail and in-app notifications, with escalation of unacknowledged incidents.']],
        'discord' => ['available', 'incidents', 'chat', ['Discord-meldingen', 'Discord notifications'], ['Incidenten en herstel als bericht in een Discord-kanaal.', 'Incidents and recoveries as messages in a Discord channel.']],
        'webhooks' => ['available', 'integrations', 'code', ['Webhooks', 'Webhooks'], ['Ondertekende webhooks met herhaalpogingen en afleverlog.', 'Signed webhooks with retries and a delivery log.']],
        'api' => ['available', 'integrations', 'code', ['REST API v1', 'REST API v1'], ['Servers, domeinen, incidenten en meer via de API met tokens.', 'Servers, domains, incidents and more through the token-based API.']],
        'status-pages' => ['available', 'integrations', 'pulse', ['Statuspagina\'s', 'Status pages'], ['Openbare of privé-statuspagina\'s met alleen de gegevens die jij kiest.', 'Public or private status pages with only the data you choose.']],
        'reports' => ['available', 'integrations', 'chart', ['Rapporten', 'Reports'], ['Beschikbaarheid, responstijd en incidenten per periode, met CSV-export.', 'Availability, response time and incidents per period, with CSV export.']],
        'maintenance' => ['available', 'integrations', 'clock', ['Onderhoudsvensters', 'Maintenance windows'], ['Geen alerts tijdens gepland onderhoud.', 'No alerts during planned maintenance.']],
        'teams' => ['available', 'account', 'users', ['Teams en rollen', 'Teams and roles'], ['Eigenaar, beheerder en lid; elk team ziet alleen zijn eigen data.', 'Owner, admin and member; every team only sees its own data.']],
        'two-factor' => ['available', 'account', 'lock', ['Tweestapsverificatie', 'Two-factor authentication'], ['TOTP-codes en herstelcodes.', 'TOTP codes and recovery codes.']],
        'audit-log' => ['available', 'account', 'list', ['Auditlog', 'Audit log'], ['Beheeracties worden vastgelegd, zonder geheimen.', 'Administrative actions are recorded, without secrets.']],

        // Added November 2026. Kept at the end so existing sort values stay unique.
        'deployments' => ['available', 'servers', 'flag', ['Automatische deploys', 'Automatic deployments'], ['De agent en GitHub melden deploys; Deploy Judge vergelijkt de prestaties vóór en na (agent 0.4.0).', 'The agent and GitHub report deployments; Deploy Judge compares performance before and after (agent 0.4.0).']],
        'logs' => ['available', 'servers', 'logs', ['Logs', 'Logs'], ['Fouten en waarschuwingen uit je logbestanden, gegroepeerd per probleem en zonder wachtwoorden of tokens (agent 0.4.0).', 'Errors and warnings from your log files, grouped by issue and without passwords or tokens (agent 0.4.0).']],
        'scheduled-jobs' => ['available', 'servers', 'clock', ['Geplande taken', 'Scheduled jobs'], ['Cron-taken, backups en imports melden zich via een geheime ping-URL; blijft een melding uit, dan krijg je een incident.', 'Cron jobs, backups and imports check in on a secret ping URL; when a check-in is missing, you get an incident.']],
        'probes' => ['available', 'websites', 'globe', ['Bevestiging vanaf een tweede locatie', 'Second-location confirmation'], ['Een externe probe bevestigt een HTTP-storing voordat er een incident opent.', 'An external probe confirms an HTTP outage before an incident opens.']],
        'root-cause' => ['available', 'incidents', 'search', ['Oorzaakanalyse', 'Root cause analysis'], ['Elk incident wordt onderzocht met vaste regels: de meest waarschijnlijke verklaring, met bewijs voor en tegen.', 'Every incident is investigated with fixed rules: the most likely explanation, with evidence for and against.']],
        'ai-analysis' => ['available', 'incidents', 'sparkle', ['AI-analyse van incidenten', 'AI incident analysis'], ['Een AI-interpretatie van het onderzoek, alleen op basis van het bewijs. Een hypothese, geen zekerheid.', 'An AI interpretation of the investigation, based only on the evidence. A hypothesis, not a certainty.']],
        'time-machine' => ['available', 'incidents', 'history', ['Time Machine', 'Time Machine'], ['Metingen, tijdlijn en bewijs van vóór, tijdens en na een incident.', 'Measurements, timeline and evidence from before, during and after an incident.']],
        'incident-memory' => ['available', 'incidents', 'book', ['Incident Memory en nabeschouwingen', 'Incident Memory and postmortems'], ['Leg oorzaak en oplossing vast, vind vergelijkbare incidenten en deel een nabeschouwing via een link.', 'Record cause and fix, find similar incidents and share a postmortem through a link.']],
        'chat-channels' => ['available', 'incidents', 'chat', ['Slack, Teams en Telegram', 'Slack, Teams and Telegram'], ['Incidenten in je chatkanaal, met knoppen om ze te bekijken of te bevestigen.', 'Incidents in your chat channel, with buttons to view or acknowledge them.']],
        'push' => ['available', 'incidents', 'phone', ['Pushmeldingen', 'Push notifications'], ['Incidenten als melding op je telefoon of computer, per apparaat aan te zetten.', 'Incidents as notifications on your phone or computer, switched on per device.']],
        'status-badges' => ['available', 'integrations', 'status', ['Statusbadges', 'Status badges'], ['Een live statusbadge voor je README of website, ook per onderdeel.', 'A live status badge for your README or website, also per component.']],
        'mission-control' => ['available', 'overview', 'monitor', ['Missiecontrole', 'Mission control'], ['Gezondheidsscore, tegels, een tijdlijn van 24 uur en een focusmodus bovenaan het dashboard.', 'Health score, tiles, a 24-hour timeline and focus mode at the top of the dashboard.']],
        'dashboard-widgets' => ['available', 'overview', 'chart', ['Dashboard zelf indelen', 'Your own dashboard'], ['14 widgets om te verslepen, te vergroten en te verbergen, met een live hartslag per server.', '14 widgets to drag, resize and hide, with a live heartbeat per server.']],
        'nexus' => ['available', 'overview', 'map', ['NEXUS', 'NEXUS'], ['Een live kaart van servers, services, websites en hun afhankelijkheden, ook in 3D, met Skyline, Wereldbol, Tijdreis en Wandscherm.', 'A live map of servers, services, websites and their dependencies, also in 3D, with Skyline, Globe, Time travel and Wall screen.']],
        'command-palette' => ['available', 'overview', 'search', ['Opdrachtpalet en zijpaneel', 'Command palette and side panel'], ['Zoeken en acties met ⌘K, en een snelle blik op elke server, website of incident.', 'Search and actions with ⌘K, and a quick look at every server, website or incident.']],
        'mobile-app' => ['available', 'overview', 'phone', ['ServerScope als app', 'ServerScope as an app'], ['Installeerbaar op telefoon en computer, met een tabbalk en vegen om te bevestigen.', 'Installable on phone and computer, with a tab bar and swipe to acknowledge.']],
        'themes' => ['available', 'overview', 'sun', ['Thema\'s en twee talen', 'Themes and two languages'], ['Licht, donker, Midnight, Terminal en Hoog contrast; Engels of Nederlands.', 'Light, dark, Midnight, Terminal and High contrast; English or Dutch.']],
    ];

    public const CATEGORIES = [
        'servers' => ['Servers', 'Servers'],
        'websites' => ['Websites en domeinen', 'Websites and domains'],
        'incidents' => ['Incidenten en meldingen', 'Incidents and alerts'],
        'integrations' => ['Rapporten en integraties', 'Reports and integrations'],
        'account' => ['Account en beveiliging', 'Account and security'],
        'overview' => ['Dashboard en NEXUS', 'Dashboard and NEXUS'],
    ];
}

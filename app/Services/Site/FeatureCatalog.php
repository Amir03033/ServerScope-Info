<?php

namespace App\Services\Site;

/**
 * ServerScope features as described in the ServerScope repository
 * (docs/AUDIT.md, docs/CHANGELOG.*.md, agent/README.md). The status here is
 * only the starting value for a fresh database; the owner decides the real
 * status per feature in the admin panel.
 *
 * Starting values: what the current site already documents and what the
 * published changelog of 26 October announces is "available"; everything
 * that is new on this site is "coming_soon".
 */
class FeatureCatalog
{
    /** key => [status, category, icon, [nl name, en name], [nl summary, en summary]] */
    public const FEATURES = [
        'server-monitoring' => ['available', 'servers', 'server', ['Servermonitoring', 'Server monitoring'], ['CPU, load, geheugen, swap, schijven en netwerk via een lichte agent die alleen leest.', 'CPU, load, memory, swap, disks and network through a lightweight, read-only agent.']],
        'service-checks' => ['available', 'servers', 'cpu', ['Diensten bewaken', 'Service checks'], ['Nginx, Apache, MySQL, PostgreSQL, Redis, PHP-FPM en andere systemd-diensten (agent 0.2.0).', 'Nginx, Apache, MySQL, PostgreSQL, Redis, PHP-FPM and other systemd services (agent 0.2.0).']],
        'one-command-install' => ['coming_soon', 'servers', 'terminal', ['Koppelen met één commando', 'One-command install'], ['Een Linux-server koppelen met één commando en een eenmalig koppeltoken.', 'Connect a Linux server with one command and a one-time enrollment token.']],
        'bash-agent' => ['coming_soon', 'servers', 'terminal', ['Bash-agent', 'Bash agent'], ['Voor hosts en containers zonder Python of systemd.', 'For hosts and containers without Python or systemd.']],
        'agent-updates' => ['coming_soon', 'servers', 'shield', ['Agent-updates via het dashboard', 'Agent updates from the dashboard'], ['Ondertekende agent-releases, bijwerken vanuit het dashboard en terugzetten.', 'Signed agent releases, updating from the dashboard and rolling back.']],
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
        'teams' => ['coming_soon', 'account', 'users', ['Teams en rollen', 'Teams and roles'], ['Eigenaar, beheerder en lid; elk team ziet alleen zijn eigen data.', 'Owner, admin and member; every team only sees its own data.']],
        'two-factor' => ['available', 'account', 'lock', ['Tweestapsverificatie', 'Two-factor authentication'], ['TOTP-codes en herstelcodes.', 'TOTP codes and recovery codes.']],
        'audit-log' => ['available', 'account', 'list', ['Auditlog', 'Audit log'], ['Beheeracties worden vastgelegd, zonder geheimen.', 'Administrative actions are recorded, without secrets.']],
    ];

    public const CATEGORIES = [
        'servers' => ['Servers', 'Servers'],
        'websites' => ['Websites en domeinen', 'Websites and domains'],
        'incidents' => ['Incidenten en meldingen', 'Incidents and alerts'],
        'integrations' => ['Rapporten en integraties', 'Reports and integrations'],
        'account' => ['Account en beveiliging', 'Account and security'],
    ];
}

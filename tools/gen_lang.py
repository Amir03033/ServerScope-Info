"""Generates lang/{nl,en}/{site,changelog}.php from one data source (keeps locales in key-parity)."""
import os

def php(v, ind=0):
    pad = '    ' * ind
    if isinstance(v, dict):
        if not v: return '[]'
        out = ['[']
        for k, val in v.items():
            out.append(f"{pad}    {php_s(k)} => {php(val, ind+1)},")
        out.append(pad + ']')
        return '\n'.join(out)
    if isinstance(v, (list, tuple)):
        if all(isinstance(x, str) for x in v) and len(v) <= 3 and sum(len(x) for x in v) < 60:
            return '[' + ', '.join(php_s(x) for x in v) + ']'
        out = ['[']
        for x in v:
            out.append(f"{pad}    {php(x, ind+1)},")
        out.append(pad + ']')
        return '\n'.join(out)
    return php_s(v)

def php_s(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"

SITE = {
 'nl': {
  'skip': 'Naar de inhoud',
  'meta': {'description': 'Beheer je servers, bekijk metrics en volg waarschuwingen vanuit één overzichtelijk platform.'},
  'nav': {'label': 'Hoofdnavigatie', 'menu': 'Menu', 'features': 'Functionaliteiten', 'docs': 'Documentatie',
          'blog': 'Blog', 'changelog': 'Changelog', 'status': 'Status', 'contact': 'Contact',
          'switch_to': 'English', 'home': 'Home', 'login': 'Inloggen', 'get_started': 'Aan de slag',
          'app_hint': 'Opent de ServerScope-applicatie'},
  'footer': {'tagline': 'Eén overzichtelijke plek voor serverinformatie, metrics en waarschuwingen.',
             'rights': 'Alle rechten voorbehouden.', 'product': 'Product', 'resources': 'Meer lezen',
             'app': 'Applicatie'},
  'home': {
    'title': 'ServerScope — Krijg inzicht in je servers',
    'description': 'Beheer je servers, bekijk metrics en volg waarschuwingen vanuit één overzichtelijk platform.',
    'headline': 'Je servers, helder in beeld.',
    'intro': 'Krijg inzicht in je serverinfrastructuur met een overzichtelijke omgeving voor serverbeheer, metrics en waarschuwingen.',
    'primary': 'Ontdek de functionaliteiten', 'secondary': 'Lees de documentatie',
    'preview': 'Infrastructuuroverzicht', 'illustrative': 'Illustratief voorbeeld. Geen live data.', 'online': 'Online',
    'cpu': 'CPU-gebruik', 'memory': 'Geheugengebruik', 'load': 'Load average', 'uptime': 'Uptime',
    'section_title': 'Je infrastructuur. Eén helder overzicht.',
    'section_text': 'ServerScope brengt serverinformatie overzichtelijk samen.',
    'features': [
      ['server', 'Serverbeheer', 'Beheer geregistreerde servers vanuit één centrale omgeving.'],
      ['chart', 'Metrics en inzicht', 'Breng beschikbare servermetingen overzichtelijk in beeld.'],
      ['bell', 'Waarschuwingen', 'Houd belangrijke meldingen en hun geschiedenis bij.'],
    ],
    'cta_title': 'Krijg meer grip op je serveromgeving.',
    'cta_text': 'Bekijk de documentatie en ontdek hoe ServerScope is opgebouwd.',
    'cta': 'Bekijk de documentatie',
  },
  'features': {
    'title': 'Functionaliteiten — ServerScope', 'headline': 'Wat ServerScope kan bieden',
    'intro': 'Eén omgeving om servers te beheren, metrics te bekijken en waarschuwingen te volgen.',
    'steps_title': 'Zo is het platform bedoeld te werken',
    'steps': [
      ['Registreer een server', 'Voeg een server toe en geef hem een herkenbare naam.'],
      ['Verbind de agent', 'Een kleine agent stuurt servergegevens via HTTPS naar ServerScope.'],
      ['Volg het dashboard', 'Metrics, systeeminformatie en waarschuwingen staan bij elkaar in één overzicht.'],
    ],
    'note': 'De website beschrijft het platform. Welke monitoringfuncties daadwerkelijk operationeel zijn, wordt apart vastgesteld.',
    'cta': 'Lees de documentatie',
    'items': [
      ['server', 'Serverbeheer', 'Registreer servers en houd basisgegevens zoals hostnaam, IP-adres en besturingssysteem bij elkaar.'],
      ['cpu', 'CPU en load', 'Volg het processorgebruik en de load average over een gekozen periode.'],
      ['memory', 'Geheugen', 'Zie hoeveel geheugen in gebruik is en hoe dat in de tijd verloopt.'],
      ['network', 'Netwerkactiviteit', 'Houd download- en uploadverkeer per server in de gaten.'],
      ['bell', 'Waarschuwingen en regels', 'Stel regels in en bewaar de geschiedenis van de waarschuwingen die ze activeren.'],
      ['globe', 'Twee talen', 'Website en documentatie zijn er in het Nederlands en Engels; de applicatie zelf is Nederlandstalig.'],
    ],
  },
  'status': {
    'title': 'Status — ServerScope', 'headline': 'Status',
    'intro': 'De toestand van ServerScope en de bijbehorende diensten.',
    'all_ok': 'Alle systemen operationeel',
    'note': 'Voorbeeldgegevens. Deze pagina toont nog geen live servicestatus.',
    'services': [['Website', 'Operationeel'], ['Dashboard', 'Operationeel'], ['Agentverbinding (HTTPS)', 'Operationeel'], ['Waarschuwingen', 'Operationeel']],
    'history': 'Incidentgeschiedenis', 'none': 'Geen incidenten gemeld.',
  },
  'contact': {
    'title': 'Contact — ServerScope', 'headline': 'Neem contact op',
    'intro': 'Een vraag of suggestie? Stuur een bericht en je krijgt snel antwoord.',
    'name': 'Naam', 'subject': 'Onderwerp', 'email': 'E-mailadres', 'message': 'Bericht', 'send': 'Bericht versturen',
    'success': 'Bericht verzonden. Bedankt, we reageren zo snel mogelijk.',
    'errors': 'Controleer de gemarkeerde velden.',
    'aside_title': 'Voordat je schrijft',
    'aside': 'Voor hoe ServerScope werkt is de documentatie meestal het snelste antwoord.',
    'docs': 'Open de documentatie',
  },
  'blog': {
    'title': 'Blog — ServerScope', 'headline': 'Blog', 'fallback_title': 'Blog',
    'intro': 'Nieuws en artikelen over ServerScope.',
    'empty_title': 'Nog geen artikelen',
    'empty': 'De eerste artikelen komen eraan. Intussen legt de documentatie uit hoe ServerScope werkt.',
    'docs': 'Lees de documentatie', 'read': 'Lees artikel', 'all': 'Alle artikelen',
  },
  'docs': {
    'title': 'Documentatie — ServerScope', 'headline': 'Documentatie', 'fallback_title': 'Documentatie',
    'intro': 'Handleidingen en technische documentatie voor ServerScope.',
    'empty_title': 'Nog geen documentatie in deze taal',
    'empty': 'De documentatie wordt nog geschreven. Kom snel terug.',
    'all': 'Alle documentatie',
    'search_label': 'Zoek in de documentatie', 'search_placeholder': 'Zoek, bijv. agent, SSL of Discord',
    'search_button': 'Zoeken', 'results': 'Zoekresultaten voor', 'no_results': 'Geen resultaten. Probeer een ander woord.',
    'clear': 'Wis zoekopdracht', 'home': 'Home', 'on_this_page': 'Op deze pagina',
    'previous': 'Vorige', 'next': 'Volgende', 'copy': 'Kopiëren', 'copied': 'Gekopieerd',
    'guides': 'Handleidingen', 'step': 'Stap',
    'app_title': 'Klaar om te beginnen?', 'app_text': 'Log in op ServerScope en volg de stappen in je eigen omgeving.',
    'help_title': 'Kom je er niet uit?', 'help_text': 'Neem contact op en beschrijf wat je ziet; vermeld nooit tokens of wachtwoorden.',
  },
  'changelog': {
    'title': 'Updates — ServerScope', 'headline': 'Updates',
    'intro': 'Wijzigingen, nieuwe functies en verbeteringen, nieuwste bovenaan.',
    'badges': {'new': 'Nieuw', 'improved': 'Verbeterd', 'fixed': 'Opgelost'},
  },
 },
 'en': {
  'skip': 'Skip to content',
  'meta': {'description': 'Manage servers, inspect metrics and track alerts from one clear platform.'},
  'nav': {'label': 'Main navigation', 'menu': 'Menu', 'features': 'Features', 'docs': 'Documentation',
          'blog': 'Blog', 'changelog': 'Changelog', 'status': 'Status', 'contact': 'Contact',
          'switch_to': 'Nederlands', 'home': 'Home', 'login': 'Log in', 'get_started': 'Get started',
          'app_hint': 'Opens the ServerScope application'},
  'footer': {'tagline': 'One clear place for server information, metrics and alerts.',
             'rights': 'All rights reserved.', 'product': 'Product', 'resources': 'Resources',
             'app': 'Application'},
  'home': {
    'title': 'ServerScope — Understand your infrastructure',
    'description': 'Manage servers, inspect metrics and track alerts from one clear platform.',
    'headline': 'Your servers, clearly in view.',
    'intro': 'Get insight into your server infrastructure with one clear environment for server management, metrics and alerts.',
    'primary': 'Explore the features', 'secondary': 'Read the documentation',
    'preview': 'Infrastructure overview', 'illustrative': 'Illustrative example. No live data.', 'online': 'Online',
    'cpu': 'CPU usage', 'memory': 'Memory usage', 'load': 'Load average', 'uptime': 'Uptime',
    'section_title': 'Your infrastructure. One clear overview.',
    'section_text': 'ServerScope brings server information together in one place.',
    'features': [
      ['server', 'Server management', 'Manage registered servers from one central environment.'],
      ['chart', 'Metrics and insight', 'Show available server measurements in a clear overview.'],
      ['bell', 'Alerts', 'Keep track of important notifications and their history.'],
    ],
    'cta_title': 'Get a better grip on your server environment.',
    'cta_text': 'Read the documentation and see how ServerScope is built.',
    'cta': 'Read the documentation',
  },
  'features': {
    'title': 'Features — ServerScope', 'headline': 'What ServerScope can offer',
    'intro': 'One environment to manage servers, view metrics and follow alerts.',
    'steps_title': 'How the platform is designed to work',
    'steps': [
      ['Register a server', 'Add a server and give it a recognisable name.'],
      ['Connect the agent', 'A small agent sends server data to ServerScope over HTTPS.'],
      ['Follow the dashboard', 'Metrics, system information and alerts appear in one overview.'],
    ],
    'note': 'The website describes the platform. Which monitoring features are operational is determined separately.',
    'cta': 'Read the documentation',
    'items': [
      ['server', 'Server management', 'Register servers and keep their basic details, such as hostname, IP address and operating system, together.'],
      ['cpu', 'CPU and load', 'Follow processor usage and the load average over a selected period.'],
      ['memory', 'Memory', 'See how much memory is in use and how that changes over time.'],
      ['network', 'Network activity', 'Keep an eye on download and upload traffic per server.'],
      ['bell', 'Alerts and rules', 'Define rules and keep a history of the alerts they trigger.'],
      ['globe', 'Two languages', 'The website and documentation are available in Dutch and English; the application itself is in Dutch.'],
    ],
  },
  'status': {
    'title': 'Status — ServerScope', 'headline': 'Status',
    'intro': 'The state of ServerScope and its services.',
    'all_ok': 'All systems operational',
    'note': 'Example data. This page does not show live service status yet.',
    'services': [['Website', 'Operational'], ['Dashboard', 'Operational'], ['Agent connection (HTTPS)', 'Operational'], ['Alerts', 'Operational']],
    'history': 'Incident history', 'none': 'No incidents reported.',
  },
  'contact': {
    'title': 'Contact — ServerScope', 'headline': 'Get in touch',
    'intro': 'Have a question or a suggestion? Send a message and we will get back to you.',
    'name': 'Name', 'subject': 'Subject', 'email': 'Email address', 'message': 'Message', 'send': 'Send message',
    'success': 'Message sent. Thank you, we will get back to you soon.',
    'errors': 'Please check the highlighted fields.',
    'aside_title': 'Before you write',
    'aside': 'For how ServerScope works, the documentation is usually the quickest answer.',
    'docs': 'Open the documentation',
  },
  'blog': {
    'title': 'Blog — ServerScope', 'headline': 'Blog', 'fallback_title': 'Blog',
    'intro': 'News and articles about ServerScope.',
    'empty_title': 'No articles yet',
    'empty': 'The first articles are on their way. Meanwhile, the documentation explains how ServerScope works.',
    'docs': 'Read the documentation', 'read': 'Read article', 'all': 'All articles',
  },
  'docs': {
    'title': 'Documentation — ServerScope', 'headline': 'Documentation', 'fallback_title': 'Documentation',
    'intro': 'Guides and technical documentation for ServerScope.',
    'empty_title': 'No documentation in this language yet',
    'empty': 'Documentation is still being written. Come back soon.',
    'all': 'All documentation',
    'search_label': 'Search the documentation', 'search_placeholder': 'Search, e.g. agent, SSL or Discord',
    'search_button': 'Search', 'results': 'Search results for', 'no_results': 'No results. Try another word.',
    'clear': 'Clear search', 'home': 'Home', 'on_this_page': 'On this page',
    'previous': 'Previous', 'next': 'Next', 'copy': 'Copy', 'copied': 'Copied',
    'guides': 'Guides', 'step': 'Step',
    'app_title': 'Ready to start?', 'app_text': 'Log in to ServerScope and follow the steps in your own environment.',
    'help_title': 'Stuck?', 'help_text': 'Get in touch and describe what you see; never include tokens or passwords.',
  },
  'changelog': {
    'title': 'Updates — ServerScope', 'headline': 'Updates',
    'intro': 'Changes, new features and improvements, newest first.',
    'badges': {'new': 'New', 'improved': 'Improved', 'fixed': 'Fixed'},
  },
 },
}

CHANGELOG = {
 'nl': {'entries': [{'version': '0.1.0', 'date': '2026-09-24', 'items': [
     ['new', 'Eerste versie van het dashboard met CPU, geheugen, load average, schijf en netwerk.'],
     ['new', 'Agentverbinding via HTTPS.'],
     ['new', 'Nederlandse en Engelse website.']]}]},
 'en': {'entries': [{'version': '0.1.0', 'date': '2026-09-24', 'items': [
     ['new', 'First version of the dashboard with CPU, memory, load average, disk and network.'],
     ['new', 'Agent connection over HTTPS.'],
     ['new', 'Dutch and English website.']]}]},
}

# --- Landing page copy (replaces the old 3-item "features" list on the home page) ---
HOME_EXTRA = {
 'nl': {
  'badge': 'Serverbeheer · Metrics · Waarschuwingen',
  'points': ['Agent via HTTPS', 'Nederlands en Engels', 'Eén helder overzicht'],
  'servers_label': 'Servers',
  'bento_title': 'Alles wat je van je servers wilt weten',
  'bento_text': 'Eén omgeving voor servers, metrics en waarschuwingen.',
  'metrics_title': 'Metrics die je meteen begrijpt',
  'metrics_text': 'CPU, geheugen, load average en netwerk over een gekozen periode, in één oogopslag.',
  'alerts_title': 'Waarschuwingen met geschiedenis',
  'alerts_text': 'Stel regels in en bewaar de geschiedenis van de waarschuwingen die ze activeren.',
  'alert_1': 'CPU-gebruik boven 90%', 'alert_1_state': 'Opgelost',
  'alert_2': 'Geheugengebruik boven 85%', 'alert_2_state': 'Actief',
  'servers_title': 'Al je servers bij elkaar',
  'servers_text': 'Registreer servers en houd hostnaam, IP-adres en besturingssysteem bij elkaar.',
  'agent_title': 'Lichte agent, veilige verbinding',
  'agent_text': 'Een kleine agent stuurt servergegevens via HTTPS naar ServerScope.',
  'lang_title': 'Nederlands en Engels',
  'lang_text': 'Website en documentatie zijn er in het Nederlands en Engels; de applicatie zelf is Nederlandstalig.',
  'steps_title': 'In drie stappen naar overzicht',
  'tagline': 'Real-time server monitoring', 'range': 'Laatste 24 uur', 'add_server': 'Server toevoegen',
  'disk': 'Schijfruimte', 'network': 'Netwerk', 'used': 'gebruikt', 'monitoring': 'Monitoring status',
  'menu': ['Dashboard', 'Servers', 'Metrics', 'Alerts', 'Alertregels'],
 },
 'en': {
  'badge': 'Server management · Metrics · Alerts',
  'points': ['Agent over HTTPS', 'Dutch and English', 'One clear overview'],
  'servers_label': 'Servers',
  'bento_title': 'Everything you want to know about your servers',
  'bento_text': 'One environment for servers, metrics and alerts.',
  'metrics_title': 'Metrics you understand at a glance',
  'metrics_text': 'CPU, memory, load average and network over a selected period, in one view.',
  'alerts_title': 'Alerts with history',
  'alerts_text': 'Define rules and keep a history of the alerts they trigger.',
  'alert_1': 'CPU usage above 90%', 'alert_1_state': 'Resolved',
  'alert_2': 'Memory usage above 85%', 'alert_2_state': 'Active',
  'servers_title': 'All your servers together',
  'servers_text': 'Register servers and keep hostname, IP address and operating system together.',
  'agent_title': 'Light agent, secure connection',
  'agent_text': 'A small agent sends server data to ServerScope over HTTPS.',
  'lang_title': 'Dutch and English',
  'lang_text': 'The website and documentation are available in Dutch and English; the application itself is in Dutch.',
  'steps_title': 'Overview in three steps',
  'tagline': 'Real-time server monitoring', 'range': 'Last 24 hours', 'add_server': 'Add server',
  'disk': 'Disk space', 'network': 'Network', 'used': 'used', 'monitoring': 'Monitoring status',
  'menu': ['Dashboard', 'Servers', 'Metrics', 'Alerts', 'Alert rules'],
 },
}
for _l in SITE:
    SITE[_l]['home'].pop('features', None)
    SITE[_l]['home'].update(HOME_EXTRA[_l])

root = os.path.join(os.path.dirname(__file__), '..', 'lang')
for loc in ('nl', 'en'):
    for name, data in (('site', SITE[loc]), ('changelog', CHANGELOG[loc])):
        with open(os.path.join(root, loc, name + '.php'), 'w', encoding='utf-8') as f:
            f.write('<?php\n\n// Generated by tools/gen_lang.py - edit the generator or keep nl/en key sets identical.\n\nreturn ' + php(data) + ';\n')
print('ok')

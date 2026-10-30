---
date: 2026-10-29
title: Een server koppelen met één commando
status: draft
feature: one-command-install
source: Amir03033/ServerScope agent/README.md en docs/AUDIT.md §6 (fase 2)
---
Een Linux-server koppel je nu met **één commando**, zonder handmatig
bestanden te kopiëren of tokens in te vullen.

### Nieuw

- **Servers → Server toevoegen → Linux:** maak een koppeltoken aan en
  kopieer het installatiecommando. Het token is 60 minuten geldig, werkt
  één keer en kun je intrekken.
- Het script installeert de agent op **Ubuntu 22.04+ of Debian 12+** (amd64
  of arm64), vraagt om het koppeltoken (verborgen invoer) en wacht op de
  eerste meting. De pagina laat live zien wanneer de server verbonden is.
- **Opnieuw draaien is veilig:** een werkende installatie wordt alleen
  bijgewerkt, niet opnieuw gekoppeld.
- **Verwijderen** met `--uninstall`: de agent trekt zijn eigen toegang in en
  ruimt zijn bestanden op. De server en de historie blijven in ServerScope.

### Beveiliging

- Van een koppeltoken wordt alleen een hash opgeslagen. Tokens en
  inloggegevens komen nooit in logs, het auditlog of de uitvoer van het
  script.
- Alleen HTTPS (TLS 1.2+). Het agentpakket wordt gecontroleerd met een
  SHA-256-controlesom.
- De agent voert nooit commando's uit op verzoek van de server.

### Goed om te weten

- De handmatige installatie en alle bestaande agents blijven werken.

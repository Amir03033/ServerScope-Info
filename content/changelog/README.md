# Changelog sources

Imported with `php artisan site:defaults` (part of `changelog` import,
repeatable, never overwrites entries edited in the admin panel).

- `*/2026-10-26-website-monitoring.md`: copied unchanged (after the
  heading) from Amir03033/ServerScope `docs/CHANGELOG.{nl,en}.md`,
  commit 9e868b8 (2026-10-29).
- `*/draft-*.md`: teams (phase 1) and one-command enrollment (phase 2),
  written from `docs/AUDIT.md` §5/§6 and `agent/README.md`. Published on
  the live site; the file names are kept because they are the import keys.
- `*/2026-10-29-…` to `*/2026-11-04-…`: the releases of 29 October to
  4 November 2026 (PR #6 to #45 of Amir03033/ServerScope), written from
  `SERVERSCOPE-UPDATES-VOOR-DOCS.md` in that repository.
- Entries that were typed into the admin panel before the import existed
  (v0.2.0, v0.3.0, v0.4.0) are tidied by the migration
  `2026_11_04_100000_tidy_changelog_entries` (duplicate item, dates, the
  v0.4.0 entry that repeats 26 October becomes a draft).

The importer never changes the date or status of an existing entry; the
migration above moves the teams and one-command entries to 27 and
28 October.
- Version 0.1.0 comes from `lang/{nl,en}/changelog.php`.

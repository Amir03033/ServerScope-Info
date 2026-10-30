"""Independent check without PHP: every content/docs file vs the docs table (SQLite).

Usage: python3 tools/verify_docs_import.py /path/to/project   (MySQL: use php artisan docs:verify)
"""
import hashlib, pathlib, re, sqlite3, sys
root = pathlib.Path(sys.argv[1]); db = sqlite3.connect(root / 'database/database.sqlite')
ok = True; rows = []
for locale in ('nl', 'en'):
    files = sorted((root / 'content/docs' / locale).glob('*.md'))
    count = db.execute("select count(*) from docs where locale=? and source_path like 'content/docs/%' and deleted_at is null", (locale,)).fetchone()[0]
    print(f"{locale}: {len(files)} bestanden, {count} records")
    ok &= len(files) == count
    for f in files:
        raw = f.read_bytes()
        m = re.match(rb'---\r?\n(.*?)\r?\n---\r?\n', raw, re.S)
        body = raw[m.end():] if m else raw
        rec = db.execute("select body, status from docs where locale=? and slug=?", (locale, f.stem)).fetchone()
        same = rec is not None and rec[0].encode() == body
        ok &= same and rec[1] == 'published'
        rows.append(f"{locale}/{f.stem:24} {hashlib.sha256(body).hexdigest()[:16]} {'=' if same else '≠'} {hashlib.sha256(rec[0].encode()).hexdigest()[:16] if rec else '-'} {rec[1] if rec else ''}")
print('\n'.join(rows)); print('RESULTAAT:', 'alles identiek' if ok else 'AFWIJKING'); sys.exit(0 if ok else 1)

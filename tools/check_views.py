"""Static checks for the Blade views (substitute for the PHP toolchain that is not installed here)."""
import re, sys, pathlib, importlib.util
root = pathlib.Path(__file__).resolve().parent.parent
spec = importlib.util.spec_from_file_location('g', root / 'tools/gen_lang.py'); g = importlib.util.module_from_spec(spec); spec.loader.exec_module(g)
errors = []

def flat(d, p=''):
    for k, v in d.items():
        if isinstance(v, dict): yield from flat(v, p + k + '.')
        else: yield p + k
keys = {l: set('site.' + k for k in flat(g.SITE[l])) for l in g.SITE}
if keys['nl'] != keys['en']: errors.append(f"key parity: {keys['nl'] ^ keys['en']}")
ck = {l: set(flat(g.CHANGELOG[l])) for l in g.CHANGELOG}
if ck['nl'] != ck['en']: errors.append('changelog key parity')

def resolvable(k):
    return any(kk == k or kk.startswith(k + '.') for kk in keys['nl'])

pairs = [('@if','@endif'),('@foreach','@endforeach'),('@php','@endphp'),('@section','@endsection'),('@error','@enderror'),('@isset','@endisset')]
components = {p.name[:-10] for p in (root/'resources/views/components').glob('*.blade.php')}
views = sorted((root/'resources/views').rglob('*.blade.php'))
for v in views:
    s = v.read_text(encoding='utf-8'); rel = v.relative_to(root)
    # inline @section('a', x) has no end tag
    inline_sections = len(re.findall(r"@section\(\s*'[^']+'\s*,", s))
    for o, c in pairs:
        n_open = len(re.findall(re.escape(o) + r'\b(?!\w)', s)) 
        n_close = len(re.findall(re.escape(c), s))
        if o == '@section': n_open -= inline_sections
        if o == '@php': continue  # mixed inline/block usage
        if n_open != n_close: errors.append(f'{rel}: {o} x{n_open} vs {c} x{n_close}')
    for comp in re.findall(r'<x-([a-z-]+)', s):
        if comp not in components and comp != 'slot': errors.append(f'{rel}: unknown component x-{comp}')
    # translation keys: closures bound to a prefix + direct trans() calls
    for prefix in re.findall(r"trans\('([a-z.]+)\.' \. \$key", s):
        for k in re.findall(r"\$t\('([a-z_.]+)'\)", s):
            if not resolvable(prefix + '.' + k) and not prefix.startswith('site.nav'):
                errors.append(f'{rel}: missing key {prefix}.{k}')
    for k in re.findall(r"trans\('(site\.[a-z_]+(?:\.[a-z_]+)*)'", s):
        if not resolvable(k): errors.append(f'{rel}: missing key {k}')
    if s.count('{{') != s.count('}}'): errors.append(f'{rel}: unbalanced {{{{ }}}}')
    if re.search(r"\$isEn", s): errors.append(f'{rel}: leftover $isEn inline translation')
print('views checked:', len(views)); print('components:', sorted(components))
nav_keys = {k.split('.')[2] for k in keys['nl'] if k.startswith('site.nav.')}
for slug in ['features','docs','blog','changelog','status','contact','label','menu','switch_to']:
    if slug not in nav_keys: errors.append(f'missing nav.{slug}')
print('FAILED' if errors else 'PASSED'); [print(' -', e) for e in errors]; sys.exit(1 if errors else 0)

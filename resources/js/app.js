/*
 * ServerScope info site. Plain JavaScript, no dependencies. Everything is
 * progressive enhancement: without JavaScript every page is complete and
 * usable. Motion only runs when the visitor allows it.
 */

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const motionOk = () => !reducedMotion.matches;
const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);

/* ------------------------------------------------------------------ */
/* Theme: system (default), light or dark. Stored only in this browser. */
/* ------------------------------------------------------------------ */

const themeButton = document.querySelector('[data-theme-toggle]');

function storedTheme() {
    try {
        return localStorage.getItem('ss-theme') || 'system';
    } catch {
        return 'system';
    }
}

function applyTheme(theme) {
    if (theme === 'light' || theme === 'dark') {
        document.documentElement.setAttribute('data-theme', theme);
    } else {
        document.documentElement.removeAttribute('data-theme');
    }

    if (themeButton) {
        const labels = JSON.parse(themeButton.dataset.labels || '{}');
        themeButton.setAttribute('aria-label', `${themeButton.dataset.labelPrefix}: ${labels[theme] || theme}`);
        themeButton.querySelectorAll('[data-theme-icon]').forEach((icon) => {
            icon.classList.toggle('hidden', icon.dataset.themeIcon !== theme);
        });
    }
}

if (themeButton) {
    applyTheme(storedTheme());

    themeButton.addEventListener('click', () => {
        const order = ['system', 'light', 'dark'];
        const next = order[(order.indexOf(storedTheme()) + 1) % order.length];

        try {
            if (next === 'system') {
                localStorage.removeItem('ss-theme');
            } else {
                localStorage.setItem('ss-theme', next);
            }
        } catch {
            // Storage blocked: the switch still works for this page.
        }

        applyTheme(next);
    });
}

/* Close the mobile menu after choosing a link or pressing Escape. */
document.querySelectorAll('[data-mobile-menu]').forEach((menu) => {
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => menu.removeAttribute('open')));
    menu.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            menu.removeAttribute('open');
            menu.querySelector('summary')?.focus();
        }
    });
});

/* Show the right shortcut for this platform. */
document.querySelectorAll('[data-shortcut-label]').forEach((label) => {
    label.textContent = isMac ? '⌘ K' : 'Ctrl K';
});

/* ------------------------------------------------------------------ */
/* Copy buttons: code blocks in documentation and the terminal.         */
/* ------------------------------------------------------------------ */

const copyIcon = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>';
const checkIcon = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>';

function confirmCopy(button, label, copied) {
    const text = button.querySelector('span') || button;
    const icon = button.querySelector('svg');

    text.textContent = copied;
    if (icon) icon.outerHTML = checkIcon;
    button.classList.add('is-done');

    clearTimeout(button._ssTimer);
    button._ssTimer = setTimeout(() => {
        text.textContent = label;
        const current = button.querySelector('svg');
        if (current) current.outerHTML = copyIcon;
        button.classList.remove('is-done');
    }, 1800);
}

async function copy(text) {
    if (!navigator.clipboard) return false;

    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        return false;
    }
}

document.querySelectorAll('[data-copy-label] pre').forEach((pre) => {
    const prose = pre.closest('[data-copy-label]');
    const code = pre.querySelector('code');

    if (!code || !navigator.clipboard) return;

    const label = prose.dataset.copyLabel;
    const copied = prose.dataset.copiedLabel;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'ss-copy';
    button.innerHTML = `${copyIcon}<span>${label}</span>`;
    button.setAttribute('aria-label', label);

    button.addEventListener('click', async () => {
        if (await copy(code.innerText.replace(/\n$/, ''))) confirmCopy(button, label, copied);
    });

    pre.classList.add('has-copy');
    pre.appendChild(button);
});

document.querySelectorAll('[data-copy-text]').forEach((button) => {
    if (!navigator.clipboard) {
        button.hidden = true;
        return;
    }

    button.addEventListener('click', async () => {
        if (await copy(button.dataset.copyText)) confirmCopy(button, button.dataset.copyLabel, button.dataset.copiedLabel);
    });
});

/* "Link to this section" on documentation headings. */
document.querySelectorAll('[data-anchor-label] .heading-permalink').forEach((link) => {
    const label = link.closest('[data-anchor-label]').dataset.anchorLabel;
    link.removeAttribute('aria-hidden');
    link.setAttribute('aria-label', label);
    link.setAttribute('title', label);
});

/* ------------------------------------------------------------------ */
/* Scroll reveals. Content is visible by default; only elements still   */
/* below the fold are prepared, and only when motion is allowed. Only   */
/* opacity and transform change, so nothing shifts the layout.          */
/* ------------------------------------------------------------------ */

const revealables = document.querySelectorAll('[data-reveal]');

if (revealables.length && 'IntersectionObserver' in window && motionOk()) {
    const viewportBottom = window.innerHeight;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            const element = entry.target;
            element.classList.add('is-visible');
            observer.unobserve(element);

            // Hand the element back to its own hover transitions.
            element.addEventListener('transitionend', function done(event) {
                if (event.target === element && event.propertyName === 'opacity') {
                    element.classList.remove('ss-reveal', 'is-visible');
                    element.style.removeProperty('--reveal-delay');
                    element.removeEventListener('transitionend', done);
                }
            });
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    revealables.forEach((element) => {
        // Already on screen: leave it alone (no flash, no delay).
        if (element.getBoundingClientRect().top < viewportBottom) return;

        const group = element.closest('[data-reveal-group]');
        const index = group ? Array.prototype.indexOf.call(group.querySelectorAll('[data-reveal]'), element) : 0;

        element.style.setProperty('--reveal-delay', `${Math.min(index, 6) * 60}ms`);
        element.classList.add('ss-reveal');
        observer.observe(element);
    });

    // Stop animating if the user switches reduced motion on mid-visit.
    reducedMotion.addEventListener?.('change', (event) => {
        if (event.matches) {
            document.querySelectorAll('.ss-reveal').forEach((element) => element.classList.add('is-visible'));
        }
    });
}

/* ------------------------------------------------------------------ */
/* Counters: only real numbers rendered by the server, counted up once. */
/* ------------------------------------------------------------------ */

const counters = document.querySelectorAll('[data-count]');

if (counters.length && 'IntersectionObserver' in window && motionOk()) {
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            const element = entry.target;
            const target = Number(element.dataset.count);
            counterObserver.unobserve(element);

            if (!Number.isFinite(target) || target <= 0) return;

            const start = performance.now();
            const duration = Math.min(1400, 500 + target * 25);

            const step = (now) => {
                const progress = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - progress, 3);
                element.textContent = String(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            };

            element.textContent = '0';
            requestAnimationFrame(step);
        });
    }, { threshold: 0.6 });

    counters.forEach((element) => counterObserver.observe(element));
}

/* ------------------------------------------------------------------ */
/* Terminal: types the real command once it is on screen.               */
/* ------------------------------------------------------------------ */

document.querySelectorAll('[data-terminal]').forEach((terminal) => {
    const target = terminal.querySelector('[data-typing]');

    if (!target || !motionOk() || !('IntersectionObserver' in window)) return;

    const text = target.textContent;
    // Keep the box at its final height while typing (no layout shift).
    const pre = target.closest('pre');
    pre.style.minHeight = `${pre.offsetHeight}px`;
    target.textContent = '';
    target.setAttribute('aria-hidden', 'true');
    pre.setAttribute('aria-label', `${pre.getAttribute('aria-label') || ''} ${text}`.trim());

    const observer = new IntersectionObserver((entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) return;
        observer.disconnect();

        let index = 0;
        const tick = () => {
            index = Math.min(text.length, index + (Math.random() < 0.15 ? 1 : 2));
            target.textContent = text.slice(0, index);

            if (index < text.length) {
                setTimeout(tick, 18 + Math.random() * 30);
            } else {
                target.removeAttribute('aria-hidden');
            }
        };

        setTimeout(tick, 450);
    }, { threshold: 0.4 });

    observer.observe(terminal);
});

/* ------------------------------------------------------------------ */
/* Documentation: reading progress and the table of contents.           */
/* ------------------------------------------------------------------ */

const progressBar = document.querySelector('[data-reading-progress]');
const article = document.querySelector('[data-article]');

if (progressBar && article) {
    let ticking = false;

    const update = () => {
        const rect = article.getBoundingClientRect();
        const total = rect.height - window.innerHeight;
        const progress = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;
        progressBar.style.setProperty('--progress', progress.toFixed(4));
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });
    update();
}

const toc = document.querySelector('[data-toc]');

if (toc && 'IntersectionObserver' in window) {
    const links = new Map();
    toc.querySelectorAll('a[href^="#"]').forEach((link) => {
        const heading = document.getElementById(decodeURIComponent(link.hash.slice(1)));
        if (heading) links.set(heading, link);
    });

    const visible = new Set();
    const setActive = (heading) => {
        links.forEach((link, h) => {
            const active = h === heading;
            link.classList.toggle('is-active', active);
            if (active) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => (entry.isIntersecting ? visible.add(entry.target) : visible.delete(entry.target)));

        const headings = [...links.keys()];
        const first = headings.find((h) => visible.has(h));

        if (first) {
            setActive(first);
        } else {
            // Between headings: the last one above the viewport stays active.
            const above = headings.filter((h) => h.getBoundingClientRect().top < 120);
            if (above.length) setActive(above[above.length - 1]);
        }
    }, { rootMargin: '-80px 0px -60% 0px' });

    links.forEach((_, heading) => observer.observe(heading));
}

/* ------------------------------------------------------------------ */
/* "Was this helpful?" without a page reload.                           */
/* ------------------------------------------------------------------ */

document.querySelectorAll('[data-feedback-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const data = new FormData(form);
        if (event.submitter?.name) data.set(event.submitter.name, event.submitter.value);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: data,
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error(String(response.status));

            const thanks = form.parentElement.querySelector('[data-feedback-thanks]');
            form.hidden = true;
            thanks.classList.remove('hidden');
            thanks.setAttribute('tabindex', '-1');
            thanks.focus();
        } catch {
            form.submit();
        }
    });
});

/* ------------------------------------------------------------------ */
/* Pricing: monthly / yearly switch.                                    */
/* ------------------------------------------------------------------ */

document.querySelectorAll('[data-billing-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', (event) => {
        const button = event.target.closest('[data-billing]');
        if (!button) return;

        const period = button.dataset.billing;
        toggle.querySelectorAll('[data-billing]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
        document.querySelectorAll('[data-price]').forEach((price) => {
            price.hidden = price.dataset.price !== period;
        });
    });
});

/* ------------------------------------------------------------------ */
/* Live status badge: when the server had nothing cached yet, ask this  */
/* site (never ServerScope directly) and update the label.              */
/* ------------------------------------------------------------------ */

const pendingBadges = document.querySelectorAll('[data-status-badge][data-status-pending]');

if (pendingBadges.length && window.fetch) {
    fetch('/status.json', { headers: { Accept: 'application/json' } })
        .then((response) => (response.ok ? response.json() : null))
        .then((data) => {
            if (!data?.state) return;

            const dotClass = { operational: 'ss-dot-ok', degraded: 'ss-dot-warn', maintenance: 'ss-dot-warn', partial_outage: 'ss-dot-warn', major_outage: 'ss-dot-bad' }[data.state] || '';

            pendingBadges.forEach((badge) => {
                const labels = JSON.parse(badge.dataset.labels || '{}');
                badge.querySelector('[data-status-text]').textContent = labels[data.state] || labels.unknown;
                const dot = badge.querySelector('[data-status-dot]');
                dot.className = `ss-dot ${dotClass}${data.state === 'operational' ? ' ss-live-dot' : ''}`;
            });
        })
        .catch(() => {});
}

/* ------------------------------------------------------------------ */
/* ⌘K / Ctrl+K documentation search.                                    */
/* ------------------------------------------------------------------ */

const dialog = document.querySelector('[data-search-dialog]');

if (dialog && typeof dialog.showModal === 'function') {
    const input = dialog.querySelector('[data-search-input]');
    const list = dialog.querySelector('[data-search-results]');
    const status = dialog.querySelector('[data-search-status]');
    const labels = JSON.parse(dialog.dataset.labels || '{}');
    let index = null;
    let results = [];
    let selected = 0;
    let opener = null;

    const escapeHtml = (value) => value.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const highlight = (value, terms) => {
        let html = escapeHtml(value);
        terms.forEach((term) => {
            const safe = escapeHtml(term).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            html = html.replace(new RegExp(`(${safe})`, 'gi'), '<mark>$1</mark>');
        });
        return html;
    };

    const loadIndex = async () => {
        if (index) return index;

        try {
            const response = await fetch(dialog.dataset.indexUrl, { headers: { Accept: 'application/json' } });
            index = response.ok ? await response.json() : [];
        } catch {
            index = [];
        }

        return index;
    };

    const render = () => {
        const query = input.value.trim().toLowerCase();
        const terms = query.split(/\s+/).filter((t) => t.length >= 2);

        if (!terms.length) {
            results = (index || []).slice(0, 8);
        } else {
            results = (index || [])
                .map((item) => {
                    const title = item.t.toLowerCase();
                    const desc = item.d.toLowerCase();
                    const text = item.x.toLowerCase();
                    let score = 0;

                    for (const term of terms) {
                        const hit = (title.includes(term) ? 6 : 0) + (desc.includes(term) ? 3 : 0) + (text.includes(term) ? 1 : 0);
                        if (!hit) return null;
                        score += hit;
                    }

                    return { item, score };
                })
                .filter(Boolean)
                .sort((a, b) => b.score - a.score)
                .slice(0, 8)
                .map((r) => r.item);
        }

        selected = 0;
        list.innerHTML = '';

        if (!results.length) {
            list.innerHTML = `<li class="px-3 py-6 text-center text-sm text-muted">${escapeHtml(terms.length ? labels.none : labels.hint)}</li>`;
            status.textContent = terms.length ? labels.none : '';
            input.removeAttribute('aria-activedescendant');
            return;
        }

        results.forEach((item, i) => {
            const li = document.createElement('li');
            li.id = `ss-result-${i}`;
            li.setAttribute('role', 'option');
            li.className = 'ss-result rounded-lg';
            li.innerHTML = `<a href="${escapeHtml(item.u)}" class="block rounded-lg px-3 py-2.5" tabindex="-1">
                <span class="flex items-center justify-between gap-3"><span class="font-medium text-fg">${highlight(item.t, terms)}</span>${item.c ? `<span class="shrink-0 text-xs text-subtle">${escapeHtml(item.c)}</span>` : ''}</span>
                ${item.d ? `<span class="mt-0.5 block truncate text-sm text-muted">${highlight(item.d, terms)}</span>` : ''}
            </a>`;
            li.addEventListener('mousemove', () => select(i));
            list.appendChild(li);
        });

        status.textContent = `${results.length}`;
        select(0);
    };

    const select = (i) => {
        selected = i;
        list.querySelectorAll('[role="option"]').forEach((option, n) => option.setAttribute('aria-selected', String(n === i)));
        input.setAttribute('aria-activedescendant', `ss-result-${i}`);
        list.querySelector(`#ss-result-${i}`)?.scrollIntoView({ block: 'nearest' });
    };

    const open = async () => {
        if (dialog.open) return;

        opener = document.activeElement;
        dialog.showModal();
        input.focus();
        input.select();
        await loadIndex();
        render();
    };

    const close = () => {
        if (dialog.open) dialog.close();
    };

    dialog.addEventListener('close', () => opener?.focus?.());

    document.querySelectorAll('[data-search-open]').forEach((button) => button.addEventListener('click', open));
    dialog.querySelector('[data-search-close]')?.addEventListener('click', close);
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) close();
    });

    input.addEventListener('input', render);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && results.length) {
            event.preventDefault();
            select((selected + 1) % results.length);
        } else if (event.key === 'ArrowUp' && results.length) {
            event.preventDefault();
            select((selected - 1 + results.length) % results.length);
        } else if (event.key === 'Enter' && results[selected]) {
            event.preventDefault();
            window.location.href = results[selected].u;
        }
    });

    document.addEventListener('keydown', (event) => {
        const editing = /^(INPUT|TEXTAREA|SELECT)$/.test(event.target.tagName) || event.target.isContentEditable;

        if ((event.key === 'k' || event.key === 'K') && (event.metaKey || event.ctrlKey)) {
            event.preventDefault();
            dialog.open ? close() : open();
        } else if (event.key === '/' && !editing && !dialog.open) {
            event.preventDefault();
            open();
        }
    });
}

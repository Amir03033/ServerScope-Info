/*
 * ServerScope Info: progressive enhancement only. Every page works and
 * shows all of its content without this script; it adds the mobile menu
 * behaviour, scroll reveals, code-block tools, the docs search dialog and
 * a few small interface details.
 */

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

/* ------------------------------------------------------------------ */
/* Header: subtle background once the page has scrolled               */
/* ------------------------------------------------------------------ */

const header = document.querySelector('[data-header]');

if (header) {
    const update = () => header.toggleAttribute('data-scrolled', window.scrollY > 8);

    update();
    window.addEventListener('scroll', update, { passive: true });
}

/* ------------------------------------------------------------------ */
/* Mobile menu                                                        */
/* ------------------------------------------------------------------ */

const menuToggle = document.querySelector('[data-menu-toggle]');
const menuPanel = menuToggle ? document.getElementById(menuToggle.getAttribute('aria-controls')) : null;

if (menuToggle && menuPanel) {
    const desktop = window.matchMedia('(min-width: 64rem)');

    const setOpen = (open, { focus = true } = {}) => {
        menuToggle.setAttribute('aria-expanded', String(open));
        menuPanel.toggleAttribute('data-open', open);
        menuPanel.inert = !open;

        if (open && focus) {
            menuPanel.querySelector('a, button')?.focus({ preventScroll: true });
        } else if (!open && focus && menuPanel.contains(document.activeElement)) {
            menuToggle.focus({ preventScroll: true });
        }
    };

    // The panel starts closed and out of the tab order once the script runs.
    menuPanel.inert = true;

    menuToggle.addEventListener('click', () => setOpen(menuToggle.getAttribute('aria-expanded') !== 'true'));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (menuToggle.getAttribute('aria-expanded') === 'true'
            && !menuPanel.contains(event.target)
            && !menuToggle.contains(event.target)) {
            setOpen(false, { focus: false });
        }
    });

    menuPanel.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false, { focus: false })));

    desktop.addEventListener?.('change', (event) => {
        if (event.matches) {
            setOpen(false, { focus: false });
        }
    });
}

/* ------------------------------------------------------------------ */
/* Scroll reveals                                                     */
/*                                                                    */
/* Content is visible by default; only when this script runs, motion  */
/* is allowed and IntersectionObserver exists are elements that are   */
/* still below the fold prepared for a short fade/rise. Only opacity  */
/* and transform change, so nothing shifts the layout.                */
/* ------------------------------------------------------------------ */

const revealables = document.querySelectorAll('[data-reveal]');

if (revealables.length && 'IntersectionObserver' in window && !reducedMotion.matches) {
    const viewportBottom = window.innerHeight;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

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
        if (element.getBoundingClientRect().top < viewportBottom) {
            return;
        }

        const group = element.closest('[data-reveal-group]');
        const index = group ? Array.prototype.indexOf.call(group.querySelectorAll('[data-reveal]'), element) : 0;

        element.style.setProperty('--reveal-delay', `${Math.min(index, 6) * 70}ms`);
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
/* Code blocks: language label, copy button, tabs for alternatives    */
/* ------------------------------------------------------------------ */

const LANGUAGE_NAMES = {
    sh: 'Shell', bash: 'Shell', shell: 'Shell', console: 'Shell', curl: 'cURL',
    php: 'PHP', js: 'JavaScript', javascript: 'JavaScript', ts: 'TypeScript', typescript: 'TypeScript',
    json: 'JSON', python: 'Python', py: 'Python', http: 'HTTP', yaml: 'YAML', yml: 'YAML',
    ini: 'INI', env: '.env', dotenv: '.env', text: 'Text', plaintext: 'Text', xml: 'XML', html: 'HTML',
};

const languageOf = (code) => (code?.className.match(/language-([\w-]+)/) || [])[1]?.toLowerCase() || '';

const storage = {
    get(key) {
        try { return window.localStorage.getItem(key); } catch { return null; }
    },
    set(key, value) {
        try { window.localStorage.setItem(key, value); } catch { /* private mode: ignore */ }
    },
};

const copyButton = (getText, labels) => {
    const button = document.createElement('button');

    button.type = 'button';
    button.className = 'ss-copy';
    button.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg><span></span>';

    const text = button.querySelector('span');
    const live = document.createElement('span');

    live.className = 'sr-only';
    live.setAttribute('aria-live', 'polite');
    text.textContent = labels.copy;
    button.append(live);

    let timer;

    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(getText().replace(/\n$/, ''));
        } catch {
            return; // Clipboard blocked: the code stays selectable.
        }

        text.textContent = labels.copied;
        live.textContent = labels.copied;
        button.classList.add('is-done');
        clearTimeout(timer);
        timer = setTimeout(() => {
            text.textContent = labels.copy;
            live.textContent = '';
            button.classList.remove('is-done');
        }, 1800);
    });

    return button;
};

const enhanceCode = (container) => {
    const labels = {
        copy: container.dataset.copyLabel || 'Copy',
        copied: container.dataset.copiedLabel || 'Copied',
        tabs: container.dataset.tabsLabel || 'Code examples',
    };
    const canCopy = Boolean(navigator.clipboard);
    const blocks = Array.from(container.querySelectorAll('pre')).filter((pre) => pre.querySelector('code'));
    const done = new Set();

    blocks.forEach((pre) => {
        if (done.has(pre)) {
            return;
        }

        // Consecutive code blocks in different languages become one tab set.
        const group = [pre];
        let next = pre.nextElementSibling;

        while (next && next.tagName === 'PRE' && next.querySelector('code')) {
            group.push(next);
            next = next.nextElementSibling;
        }

        const languages = group.map((item) => languageOf(item.querySelector('code')));
        const asTabs = group.length > 1 && languages.every(Boolean) && new Set(languages).size === group.length;
        const members = asTabs ? group : [pre];

        members.forEach((item) => done.add(item));

        const wrapper = document.createElement('div');
        const head = document.createElement('div');

        wrapper.className = 'ss-code';
        head.className = 'ss-code-head';
        pre.before(wrapper);
        wrapper.append(head, ...members);

        let active = members[0];

        if (asTabs) {
            const tablist = document.createElement('div');
            const preferred = storage.get('ss-code-lang');
            const id = `code-${Math.random().toString(36).slice(2, 8)}`;

            tablist.className = 'ss-code-tabs';
            tablist.setAttribute('role', 'tablist');
            tablist.setAttribute('aria-label', labels.tabs);

            const tabs = members.map((item, index) => {
                const lang = languages[index];
                const tab = document.createElement('button');

                tab.type = 'button';
                tab.className = 'ss-code-tab';
                tab.id = `${id}-tab-${index}`;
                tab.dataset.lang = lang;
                tab.textContent = LANGUAGE_NAMES[lang] || lang;
                tab.setAttribute('role', 'tab');
                tab.setAttribute('aria-controls', `${id}-panel-${index}`);
                item.id = `${id}-panel-${index}`;
                item.setAttribute('role', 'tabpanel');
                item.setAttribute('aria-labelledby', tab.id);
                item.tabIndex = 0;
                tablist.append(tab);

                return tab;
            });

            const select = (index, { remember = false, focus = false } = {}) => {
                tabs.forEach((tab, i) => {
                    tab.setAttribute('aria-selected', String(i === index));
                    tab.tabIndex = i === index ? 0 : -1;
                    members[i].hidden = i !== index;
                });
                active = members[index];

                if (focus) {
                    tabs[index].focus();
                }

                if (remember) {
                    storage.set('ss-code-lang', languages[index]);
                    document.dispatchEvent(new CustomEvent('ss:code-lang', { detail: languages[index] }));
                }
            };

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => select(index, { remember: true }));
                tab.addEventListener('keydown', (event) => {
                    const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];

                    if (step) {
                        event.preventDefault();
                        select((index + step + tabs.length) % tabs.length, { remember: true, focus: true });
                    }
                });
            });

            document.addEventListener('ss:code-lang', (event) => {
                const index = languages.indexOf(event.detail);

                if (index !== -1) {
                    select(index);
                }
            });

            select(Math.max(0, languages.indexOf(preferred)));
            head.append(tablist);
        } else {
            const label = document.createElement('span');
            const lang = languages[0];

            label.className = 'ss-code-lang';
            label.textContent = LANGUAGE_NAMES[lang] || lang || '';
            head.append(label);
        }

        if (canCopy) {
            head.append(copyButton(() => active.querySelector('code').textContent, labels));
        }
    });
};

document.querySelectorAll('[data-code-blocks]').forEach(enhanceCode);

/* Syntax highlighting: loaded on demand, only on pages with code. */
const highlightable = document.querySelectorAll('pre code[class*="language-"]');

if (highlightable.length) {
    import('./highlight.js')
        .then(({ highlight }) => highlightable.forEach(highlight))
        .catch(() => { /* Plain code is fine. */ });
}

/* ------------------------------------------------------------------ */
/* Table of contents: highlight the section being read                */
/* ------------------------------------------------------------------ */

const tocLinks = Array.from(document.querySelectorAll('[data-toc] a[href^="#"]'));

if (tocLinks.length) {
    const targets = tocLinks
        .map((link) => [link, document.getElementById(decodeURIComponent(link.hash.slice(1)))])
        .filter(([, target]) => target);
    let queued = false;

    const mark = () => {
        queued = false;

        // The current section is the last heading above the reading line;
        // at the very end of the page, the last heading that is in view.
        const atEnd = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
        const line = atEnd ? window.innerHeight : 140;
        let current = null;

        targets.forEach(([link, target]) => {
            if (target.getBoundingClientRect().top <= line) {
                current = link;
            }
        });

        tocLinks.forEach((link) => {
            link.toggleAttribute('data-active', link === current);

            if (link === current) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const schedule = () => {
        if (!queued) {
            queued = true;
            window.requestAnimationFrame(mark);
        }
    };

    mark();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
}

/* ------------------------------------------------------------------ */
/* Reading progress for long articles                                 */
/* ------------------------------------------------------------------ */

const progressBar = document.querySelector('[data-progress]');
const progressTarget = progressBar ? document.querySelector(progressBar.dataset.progress) : null;

if (progressBar && progressTarget) {
    let queued = false;

    const update = () => {
        queued = false;

        const rect = progressTarget.getBoundingClientRect();
        const total = rect.height - window.innerHeight;
        const ratio = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;

        progressBar.style.setProperty('--progress', ratio.toFixed(4));
    };

    const schedule = () => {
        if (!queued) {
            queued = true;
            window.requestAnimationFrame(update);
        }
    };

    update();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
}

/* ------------------------------------------------------------------ */
/* Documentation search dialog (Ctrl/⌘ K or "/")                      */
/* Without JavaScript the triggers are plain links to the search page. */
/* ------------------------------------------------------------------ */

const searchDialog = document.getElementById('docs-search-dialog');

if (searchDialog && typeof searchDialog.showModal === 'function') {
    const input = searchDialog.querySelector('input[type="search"]');
    const list = searchDialog.querySelector('[data-search-results]');
    const status = searchDialog.querySelector('[data-search-status]');
    const endpoint = searchDialog.dataset.endpoint;
    const messages = {
        hint: searchDialog.dataset.hint,
        loading: searchDialog.dataset.loading,
        empty: searchDialog.dataset.empty,
        error: searchDialog.dataset.error,
    };
    let controller;
    let timer;
    let activeIndex = -1;

    const options = () => Array.from(list.querySelectorAll('[role="option"]'));

    const setActive = (index) => {
        const items = options();

        activeIndex = items.length ? (index + items.length) % items.length : -1;
        items.forEach((item, i) => item.setAttribute('aria-selected', String(i === activeIndex)));

        if (activeIndex >= 0) {
            input.setAttribute('aria-activedescendant', items[activeIndex].id);
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    };

    const render = (results) => {
        list.replaceChildren(...results.map((result, index) => {
            const item = document.createElement('a');
            const title = document.createElement('span');
            const meta = document.createElement('span');
            const snippet = document.createElement('span');

            item.href = result.url;
            item.id = `search-option-${index}`;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.className = 'block rounded-lg px-3 py-2.5 outline-none aria-selected:bg-sky-400/10 hover:bg-white/5';
            title.className = 'block text-sm font-semibold text-white';
            meta.className = 'mt-0.5 block font-mono text-[11px] uppercase tracking-wider text-slate-500';
            snippet.className = 'mt-1 block text-[13px] leading-relaxed text-slate-400';
            title.textContent = result.title;
            meta.textContent = result.category || '';
            snippet.textContent = result.snippet;
            item.append(title, meta, snippet);
            item.addEventListener('mousemove', () => setActive(index));

            return item;
        }));
        setActive(results.length ? 0 : -1);
    };

    const search = (query) => {
        controller?.abort();
        clearTimeout(timer);

        if (query.trim().length < 2) {
            render([]);
            status.textContent = messages.hint;
            return;
        }

        status.textContent = messages.loading;
        timer = setTimeout(async () => {
            controller = new AbortController();

            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('q', query);

                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });

                if (!response.ok) {
                    throw new Error(String(response.status));
                }

                const { data } = await response.json();

                render(data);
                status.textContent = data.length ? `${data.length}` : messages.empty;
                status.toggleAttribute('data-count', data.length > 0);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    render([]);
                    status.textContent = messages.error;
                }
            }
        }, 140);
    };

    const open = () => {
        if (!searchDialog.open) {
            searchDialog.showModal();
            input.select();
            search(input.value);
        }
    };

    document.querySelectorAll('[data-search-open]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            open();
        });
    });

    searchDialog.querySelector('[data-search-close]')?.addEventListener('click', () => searchDialog.close());

    searchDialog.addEventListener('click', (event) => {
        if (event.target === searchDialog) {
            searchDialog.close();
        }
    });

    input.addEventListener('input', () => search(input.value));

    input.addEventListener('keydown', (event) => {
        // Escape in a search field would only clear it; close the dialog instead.
        if (event.key === 'Escape') {
            event.preventDefault();
            searchDialog.close();
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            options()[activeIndex]?.click();
        }
    });

    document.addEventListener('keydown', (event) => {
        const typing = event.target.closest?.('input, textarea, select, [contenteditable="true"]');

        if ((event.key === 'k' && (event.metaKey || event.ctrlKey)) || (event.key === '/' && !typing && !searchDialog.open)) {
            event.preventDefault();
            open();
        }
    });
}

/* ------------------------------------------------------------------ */
/* Forms: prevent double submits, show a busy state                   */
/* ------------------------------------------------------------------ */

document.querySelectorAll('form[data-submit-once]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[type="submit"]');

        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.querySelector('[data-busy-label]')?.removeAttribute('hidden');
            button.querySelector('[data-idle-label]')?.setAttribute('hidden', '');
        }
    });
});

// Restore buttons when the page comes back from the back/forward cache.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('form[data-submit-once] [type="submit"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.querySelector('[data-busy-label]')?.setAttribute('hidden', '');
        button.querySelector('[data-idle-label]')?.removeAttribute('hidden');
    });
});

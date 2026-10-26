const menuToggle = document.querySelector('#menu-toggle');
const mobileMenu = document.querySelector('#mobile-menu');

if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', () => {
        const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';

        menuToggle.setAttribute('aria-expanded', String(!isExpanded));
        mobileMenu.classList.toggle('hidden', isExpanded);
    });

    mobileMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            menuToggle.setAttribute('aria-expanded', 'false');
        });
    });
}

/*
 * Copy buttons for documentation code blocks. Progressive enhancement:
 * without JavaScript (or clipboard access) the code stays selectable.
 */
document.querySelectorAll('[data-copy-label]').forEach((prose) => {
    const label = prose.dataset.copyLabel;
    const copied = prose.dataset.copiedLabel;

    prose.querySelectorAll('pre').forEach((pre) => {
        const code = pre.querySelector('code');

        if (!code || !navigator.clipboard) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ss-copy';
        button.textContent = label;

        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(code.innerText.replace(/\n$/, ''));
                button.textContent = copied;
                button.classList.add('is-done');
            } catch {
                return;
            }

            setTimeout(() => {
                button.textContent = label;
                button.classList.remove('is-done');
            }, 1800);
        });

        pre.classList.add('has-copy');
        pre.appendChild(button);
    });
});

/*
 * Scroll reveals. Content is visible by default; only when this script
 * runs, motion is allowed and IntersectionObserver exists are elements
 * that are still below the fold prepared for a short fade/rise. Only
 * opacity and transform change, so nothing shifts the layout.
 */
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const revealables = document.querySelectorAll('[data-reveal]');

if (revealables.length && 'IntersectionObserver' in window && !reducedMotion.matches) {
    const viewportBottom = window.innerHeight;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
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
            }
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

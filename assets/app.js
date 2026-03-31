const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function motionAllowed() {
    return !prefersReducedMotion.matches;
}

function createPageLoader() {
    let root = null;
    let progress = 0;
    let trickleTimer = null;

    const ensureRoot = () => {
        if (root) {
            return root;
        }

        root = document.createElement('div');
        root.className = 'page-loader';
        root.setAttribute('aria-hidden', 'true');
        root.innerHTML = '<div class="page-loader__bar"></div><div class="page-loader__glow"></div>';
        document.body.prepend(root);

        return root;
    };

    const setProgress = (value) => {
        progress = Math.max(0, Math.min(100, value));
        ensureRoot().style.setProperty('--loader-progress', `${progress}%`);
    };

    const clearTrickle = () => {
        if (!trickleTimer) {
            return;
        }

        window.clearInterval(trickleTimer);
        trickleTimer = null;
    };

    const trickle = (limit) => {
        clearTrickle();

        trickleTimer = window.setInterval(() => {
            if (progress >= limit) {
                clearTrickle();
                return;
            }

            const step = progress < 30 ? 7 : progress < 60 ? 4 : 2;
            setProgress(Math.min(limit, progress + step));
        }, 180);
    };

    return {
        begin(initial = 16, options = {}) {
            const { dimPage = false, trickleMax = 82 } = options;

            ensureRoot().classList.add('is-active');
            ensureRoot().classList.remove('is-complete');
            document.body.classList.toggle('is-page-loading', dimPage);
            setProgress(initial);
            trickle(trickleMax);
        },

        set(value) {
            setProgress(value);
        },

        done(options = {}) {
            const { immediate = false } = options;
            clearTrickle();
            setProgress(100);

            window.setTimeout(() => {
                if (!root) {
                    return;
                }

                root.classList.remove('is-active');
                document.body.classList.remove('is-page-loading');
                setProgress(0);
            }, immediate ? 0 : 320);
        },
    };
}

const pageLoader = createPageLoader();

function prepareButtonLabel(button) {
    if (!button || button.dataset.loadingReady === 'true') {
        return;
    }

    const label = document.createElement('span');
    label.className = 'btn-label';

    while (button.firstChild) {
        label.appendChild(button.firstChild);
    }

    button.appendChild(label);
    button.dataset.loadingReady = 'true';
}

function pulseElement(element, className = 'is-updating', duration = 480) {
    if (!element || !motionAllowed()) {
        return;
    }

    element.classList.remove(className);
    void element.offsetWidth;
    element.classList.add(className);

    window.setTimeout(() => {
        element.classList.remove(className);
    }, duration);
}

function resolveLoadingText(button) {
    prepareButtonLabel(button);

    const label = button.querySelector('.btn-label');
    const original = button.dataset.originalText || label.textContent.trim();
    button.dataset.originalText = original;

    const normalized = original.toLowerCase();

    if (normalized.includes('salvar')) {
        return 'Salvando...';
    }

    if (normalized.includes('excluir')) {
        return 'Excluindo...';
    }

    if (normalized.includes('aplicar')) {
        return 'Aplicando...';
    }

    if (normalized.includes('entrar') || normalized.includes('acessar')) {
        return 'Entrando...';
    }

    if (normalized.includes('cadastrar')) {
        return 'Cadastrando...';
    }

    return 'Carregando...';
}

function setButtonLoading(button) {
    if (!button || button.classList.contains('is-loading')) {
        return;
    }

    prepareButtonLabel(button);

    const label = button.querySelector('.btn-label');
    label.textContent = resolveLoadingText(button);
    button.classList.add('is-loading');
    button.disabled = true;
}

function resetButtonLoading(button) {
    if (!button) {
        return;
    }

    prepareButtonLabel(button);

    const label = button.querySelector('.btn-label');
    label.textContent = button.dataset.originalText || label.textContent;
    button.classList.remove('is-loading');
    button.disabled = false;
}

function isInternalNavigation(link) {
    const rawHref = link.getAttribute('href');

    if (!rawHref || rawHref.startsWith('#') || rawHref.startsWith('javascript:') || rawHref.startsWith('mailto:') || rawHref.startsWith('tel:')) {
        return false;
    }

    if (link.target && link.target !== '_self') {
        return false;
    }

    if (link.hasAttribute('download')) {
        return false;
    }

    const url = new URL(link.href, window.location.href);

    return url.origin === window.location.origin;
}

function confirmDelete(nome) {
    const item = nome ? ` o medicamento "${nome}"` : ' este medicamento';
    return window.confirm(`Deseja realmente excluir${item}?`);
}

function setupPageLoadingStates() {
    pageLoader.begin(16, { dimPage: false, trickleMax: 58 });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        if (!isInternalNavigation(link)) {
            return;
        }

        pageLoader.begin(22, { dimPage: true, trickleMax: 84 });
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            form.classList.add('form-is-submitting');

            const submitter = event.submitter instanceof HTMLElement
                ? event.submitter
                : form.querySelector('button[type="submit"]');

            if (submitter && submitter.tagName === 'BUTTON') {
                setButtonLoading(submitter);
            }

            pageLoader.begin(26, { dimPage: true, trickleMax: 90 });

            window.setTimeout(() => {
                form.dataset.submitting = 'false';
                form.classList.remove('form-is-submitting');

                if (submitter && submitter.tagName === 'BUTTON') {
                    resetButtonLoading(submitter);
                }
            }, 6000);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        pageLoader.set(70);
    });

    window.addEventListener('load', () => {
        pageLoader.done();
    });

    window.addEventListener('pageshow', () => {
        pageLoader.done({ immediate: true });
    });
}

function setupAutoHideAlerts() {
    const alerts = document.querySelectorAll('[data-autohide]');

    alerts.forEach((alert) => {
        window.setTimeout(() => {
            alert.classList.add('is-hiding');
            window.setTimeout(() => alert.remove(), 220);
        }, 4500);
    });
}

function setupPasswordToggle() {
    const toggle = document.querySelector('[data-toggle-password]');
    const input = document.querySelector('.password-field input');

    if (!toggle || !input) {
        return;
    }

    toggle.addEventListener('click', () => {
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        toggle.textContent = visible ? 'Mostrar' : 'Ocultar';
        pulseElement(toggle, 'is-updating', 320);
    });
}

function setupResultCount() {
    const countTarget = document.querySelector('[data-result-count]');
    const rows = document.querySelectorAll('[data-med-row]');

    if (!countTarget || rows.length === 0) {
        return;
    }

    countTarget.textContent = String(rows.length);
}

function setupStockPreview() {
    const stockInput = document.querySelector('[data-stock-input]');
    const minStockInput = document.querySelector('[data-min-stock-input]');
    const preview = document.querySelector('[data-stock-preview]');

    if (!stockInput || !minStockInput || !preview) {
        return;
    }

    let firstRender = true;

    const render = () => {
        const stock = Number(stockInput.value || 0);
        const minimum = Number(minStockInput.value || 0);

        preview.classList.remove('preview-warning', 'preview-success');

        if (stock <= minimum) {
            preview.classList.add('preview-warning');
            preview.textContent = `Atencao: o estoque atual (${stock}) esta no limite ou abaixo do estoque minimo (${minimum}).`;
        } else {
            preview.classList.add('preview-success');
            preview.textContent = `Situacao regular: ${stock} unidades disponiveis para estoque minimo de ${minimum}.`;
        }

        if (!firstRender) {
            pulseElement(preview);
        }

        firstRender = false;
    };

    stockInput.addEventListener('input', render);
    minStockInput.addEventListener('input', render);
    render();
}

function setupTopbarState() {
    const topbar = document.querySelector('.topbar');

    if (!topbar) {
        return;
    }

    const sync = () => {
        topbar.classList.toggle('is-scrolled', window.scrollY > 8);
    };

    sync();
    window.addEventListener('scroll', sync, { passive: true });
}

function setupFieldFocusStates() {
    const fields = document.querySelectorAll('.field');

    fields.forEach((field) => {
        const controls = field.querySelectorAll('input, select, textarea');

        controls.forEach((control) => {
            control.addEventListener('focus', () => {
                field.classList.add('is-focused');
            });

            control.addEventListener('blur', () => {
                field.classList.remove('is-focused');
            });
        });
    });
}

function setupFilterFeedback() {
    const form = document.querySelector('[data-filter-form]');

    if (!form) {
        return;
    }

    const primaryButton = form.querySelector('button[type="submit"]');
    const controls = form.querySelectorAll('input, select');

    controls.forEach((control) => {
        const handler = () => pulseElement(primaryButton);
        control.addEventListener('input', handler);
        control.addEventListener('change', handler);
    });
}

function setupMotionReveal() {
    const targets = Array.from(document.querySelectorAll(
        '.hero-card, .card, .stat-card, .info-card, .metric-pill, .list-row, .table tbody tr, .login-showcase, .login-card, .alert'
    ));

    if (targets.length === 0) {
        return;
    }

    if (!motionAllowed()) {
        targets.forEach((target) => target.classList.add('is-visible'));
        return;
    }

    document.body.classList.add('motion-ui');

    const observer = 'IntersectionObserver' in window
        ? new IntersectionObserver((entries, activeObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                activeObserver.unobserve(entry.target);
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -8% 0px',
        })
        : null;

    targets.forEach((target, index) => {
        target.classList.add('reveal-item');
        target.style.setProperty('--reveal-delay', `${Math.min(index * 34, 260)}ms`);

        if (target.getBoundingClientRect().top < window.innerHeight * 0.9) {
            target.classList.add('is-visible');
        }

        if (observer) {
            observer.observe(target);
        } else {
            target.classList.add('is-visible');
        }
    });
}

function animateCount(element) {
    if (element.dataset.countAnimated === 'true') {
        return;
    }

    const original = element.textContent.trim();

    if (!/^\d+$/.test(original)) {
        return;
    }

    const end = Number(original);

    if (!Number.isFinite(end)) {
        return;
    }

    element.dataset.countAnimated = 'true';

    if (!motionAllowed() || end === 0) {
        element.textContent = String(end);
        return;
    }

    const duration = Math.min(1200, 500 + end * 18);
    const start = performance.now();

    const step = (timestamp) => {
        const progress = Math.min((timestamp - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = String(Math.round(end * eased));

        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };

    window.requestAnimationFrame(step);
}

function setupCountAnimations() {
    const counters = Array.from(document.querySelectorAll(
        '.stat-card strong, .summary-item strong, [data-result-count]'
    )).filter((element) => /^\d+$/.test(element.textContent.trim()));

    if (counters.length === 0) {
        return;
    }

    if (!motionAllowed() || !('IntersectionObserver' in window)) {
        counters.forEach(animateCount);
        return;
    }

    const observer = new IntersectionObserver((entries, activeObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            animateCount(entry.target);
            activeObserver.unobserve(entry.target);
        });
    }, {
        threshold: 0.5,
    });

    counters.forEach((counter) => observer.observe(counter));
}

setupPageLoadingStates();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button.btn').forEach(prepareButtonLabel);
    setupAutoHideAlerts();
    setupPasswordToggle();
    setupResultCount();
    setupStockPreview();
    setupTopbarState();
    setupFieldFocusStates();
    setupFilterFeedback();
    setupMotionReveal();
    setupCountAnimations();
});

document.documentElement.classList.add('js');

const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

/**
 * Convert latin digits inside a string to Persian digits.
 */
const toPersianDigits = (value) =>
    String(value).replace(/[0-9]/g, (digit) => persianDigits[Number(digit)]);

/**
 * Countdown to the last moment of the current month, displayed in red.
 */
const initCountdown = () => {
    const countdown = document.querySelector('[data-countdown]');

    if (!countdown) {
        return;
    }

    const fields = {
        days: countdown.querySelector('[data-unit="days"]'),
        hours: countdown.querySelector('[data-unit="hours"]'),
        minutes: countdown.querySelector('[data-unit="minutes"]'),
        seconds: countdown.querySelector('[data-unit="seconds"]'),
    };

    const now = new Date();
    const campaignDeadline = countdown.dataset.deadline ? new Date(countdown.dataset.deadline) : null;
    const hasCampaignDeadline =
        campaignDeadline instanceof Date && !Number.isNaN(campaignDeadline.getTime()) && campaignDeadline > now;
    const deadline = hasCampaignDeadline
        ? campaignDeadline
        : new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999);

    const render = (field, value) => {
        if (!field) {
            return;
        }

        const text = toPersianDigits(String(value).padStart(2, '0'));

        if (field.textContent === text) {
            return;
        }

        field.textContent = text;
        field.classList.remove('animate-tick');
        void field.offsetWidth;
        field.classList.add('animate-tick');
    };

    const update = () => {
        const remaining = Math.max(0, deadline.getTime() - Date.now());
        const totalSeconds = Math.floor(remaining / 1000);

        render(fields.days, Math.floor(totalSeconds / 86400));
        render(fields.hours, Math.floor((totalSeconds % 86400) / 3600));
        render(fields.minutes, Math.floor((totalSeconds % 3600) / 60));
        render(fields.seconds, totalSeconds % 60);
    };

    update();
    window.setInterval(update, 1000);
};

const initMobileMenu = () => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const panel = document.querySelector('[data-menu-panel]');

    if (!toggle || !panel) {
        return;
    }

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        panel.classList.toggle('hidden', !open);
    };

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });
};

const initHeaderShadow = () => {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const update = () => header.classList.toggle('header-shadow', window.scrollY > 8);

    update();
    window.addEventListener('scroll', update, { passive: true });
};

/**
 * Small sky-blue toast used for cart feedback.
 */
const showToast = (message) => {
    const toast = document.querySelector('[data-toast]');

    if (!toast) {
        return;
    }

    toast.querySelector('[data-toast-message]').textContent = message;
    toast.classList.remove('translate-y-6', 'opacity-0', 'pointer-events-none');
    toast.classList.add('translate-y-0', 'opacity-100');

    window.clearTimeout(showToast.timeout);
    showToast.timeout = window.setTimeout(() => {
        toast.classList.add('translate-y-6', 'opacity-0', 'pointer-events-none');
        toast.classList.remove('translate-y-0', 'opacity-100');
    }, 2600);
};

const initShopActions = () => {
    document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
        button.addEventListener('click', () => {
            const counter = document.querySelector('[data-cart-count]');

            if (counter) {
                counter.textContent = toPersianDigits(Number(counter.dataset.count ?? 0) + 1);
                counter.dataset.count = Number(counter.dataset.count ?? 0) + 1;
            }

            showToast('محصول به سبد خرید اضافه شد.');
        });
    });

    document.querySelectorAll('[data-wishlist]').forEach((button) => {
        button.addEventListener('click', () => {
            const active = button.getAttribute('aria-pressed') === 'true';
            button.setAttribute('aria-pressed', String(!active));
        });
    });
};

const initReveal = () => {
    const items = document.querySelectorAll('[data-reveal]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion || !('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -60px 0px', threshold: 0.08 },
    );

    items.forEach((item) => observer.observe(item));
};

const initAdminSidebar = () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');

    if (!sidebar) {
        return;
    }

    const overlay = document.querySelector('[data-admin-overlay]');
    const toggles = document.querySelectorAll('[data-admin-menu-toggle]');

    const setOpen = (open) => {
        sidebar.dataset.open = String(open);
        overlay?.classList.toggle('hidden', !open);
        toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(open)));
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => setOpen(sidebar.dataset.open !== 'true'));
    });

    overlay?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            setOpen(false);
        }
    });
};

const initProductGalleries = () => {
    document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
        const shots = Array.from(gallery.querySelectorAll('[data-gallery-shot]'));

        if (shots.length === 0) {
            return;
        }

        const defaultIndex = Math.min(
            Math.max(Number.parseInt(gallery.dataset.defaultIndex ?? '0', 10) || 0, 0),
            shots.length - 1,
        );

        const focusShot = (activeIndex) => {
            shots.forEach((shot, index) => {
                shot.classList.toggle('is-focus', index === activeIndex);
            });
        };

        shots.forEach((shot, index) => {
            shot.addEventListener('pointerenter', () => focusShot(index));
            shot.addEventListener('focus', () => focusShot(index));
        });

        gallery.addEventListener('pointerleave', () => focusShot(defaultIndex));

        focusShot(defaultIndex);
    });
};

const initCopyButtons = () => {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.stopPropagation();
            event.preventDefault();

            const value = button.dataset.copy;

            try {
                await navigator.clipboard.writeText(value);
                showToast('کد پیگیری کپی شد.');
            } catch (error) {
                showToast('کپی ناموفق بود.');
            }
        });
    });
};

const initTransactionDrawer = () => {
    const drawer = document.querySelector('[data-trx-drawer]');

    if (!drawer) {
        return;
    }

    const rows = document.querySelectorAll('[data-trx-row]');
    const form = drawer.querySelector('[data-trx-form]');
    const statusSelect = drawer.querySelector('[data-trx-status-select]');
    const descInput = drawer.querySelector('[data-trx-desc-input]');

    const setText = (selector, value) => {
        const el = drawer.querySelector(selector);

        if (el) {
            el.textContent = value ?? '—';
        }
    };

    const open = () => {
        drawer.classList.remove('hidden');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        drawer.classList.add('hidden');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    rows.forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('a, button[data-copy]')) {
                return;
            }

            let data = {};

            try {
                data = JSON.parse(row.dataset.trx || '{}');
            } catch (error) {
                return;
            }

            setText('[data-trx-title]', `#${data.id ?? '—'}`);
            setText('[data-trx-amount]', data.amount ? `${toPersianDigits(Number(data.amount).toLocaleString('en-US'))} تومان` : '—');
            setText('[data-trx-gateway]', data.gateway_label);
            setText('[data-trx-order]', data.order ? `#${data.order}` : '—');
            setText('[data-trx-tracking]', data.tracking_code);
            setText('[data-trx-card]', data.card_number);
            setText('[data-trx-ref]', data.ref_id);
            setText('[data-trx-date]', toPersianDigits(data.date ?? ''));
            setText('[data-trx-customer]', data.phone ? `${data.customer ?? '—'} — ${data.phone}` : data.customer);
            setText('[data-trx-desc]', data.description);

            const statusEl = drawer.querySelector('[data-trx-status]');
            if (statusEl) {
                statusEl.innerHTML = `<span class="status-pill bg-brand-soft text-brand-deep">${data.status_label ?? '—'}</span>`;
            }

            if (statusSelect) {
                statusSelect.value = data.status ?? 'pending';
            }

            if (descInput) {
                descInput.value = data.description ?? '';
            }

            if (form && data.update_url) {
                form.setAttribute('action', data.update_url);
            }

            const receiptWrap = drawer.querySelector('[data-trx-receipt-wrap]');
            const receiptImg = drawer.querySelector('[data-trx-receipt-img]');
            const receiptBtn = receiptWrap?.querySelector('[data-lightbox]');

            if (receiptWrap && receiptImg) {
                if (data.receipt) {
                    receiptImg.src = data.receipt;
                    receiptBtn?.setAttribute('data-lightbox', data.receipt);
                    receiptWrap.classList.remove('hidden');
                } else {
                    receiptWrap.classList.add('hidden');
                }
            }

            open();
        });
    });

    drawer.querySelectorAll('[data-trx-close]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
};

const initAdminSearchToggle = () => {
    const toggle = document.querySelector('[data-admin-search-toggle]');
    const panel = document.querySelector('[data-admin-search-panel]');

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = panel.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', String(isOpen));

        if (isOpen) {
            panel.querySelector('input')?.focus();
        }
    });
};

const initReceiptLightbox = () => {
    const overlay = document.querySelector('[data-lightbox-overlay]');

    if (!overlay) {
        return;
    }

    const img = overlay.querySelector('[data-lightbox-img]');

    document.querySelectorAll('[data-lightbox]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (img) {
                img.src = trigger.dataset.lightbox;
            }
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        });
    });

    const close = () => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    overlay.querySelectorAll('[data-lightbox-close]').forEach((el) => el.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
};

initCountdown();
initMobileMenu();
initHeaderShadow();
initShopActions();
initReveal();
initAdminSidebar();
initProductGalleries();
initCopyButtons();
initTransactionDrawer();
initAdminSearchToggle();
initReceiptLightbox();

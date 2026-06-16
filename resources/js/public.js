/**
 * Jana Prints public website — lightweight interactions.
 * Scroll reveals, sticky header, smooth anchors, counters, subtle parallax.
 */

import { initDocumentPdfDownload } from './document-pdf-download';

document.addEventListener('DOMContentLoaded', () => {
    initPageLoader();
    initScrollProgress();
    initStickyHeader();
    initScrollReveal();
    initStaggerReveal();
    initImageReveal();
    revealAllInViewport();
    initSmoothAnchors();
    initCounters();
    initParallax();
    initTestimonialCarousel();
    initTestimonialRotator();
    initPortfolio();
    initProductionShowcase();
    initQualitySpine();
    initJourney();
    initConversion();
    initQuoteFormAnchor();
    initContactSectionAnchor();
    initStickyFab();
    initScrollNav();
    initMobileNav();
    initRevealOnScroll();
    initClientPasswordToggle();
    initClientPortal();
    initDocumentPdfDownload();

    requestAnimationFrame(() => {
        document.documentElement.classList.add('public-anim-active');
        revealAllInViewport();
    });
});

function initPageLoader() {
    const loader = document.querySelector('[data-page-loader]');

    if (!loader) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const seen = sessionStorage.getItem('jana-loader-seen') === '1';

    if (seen || prefersReducedMotion) {
        loader.classList.add('is-hidden');
        loader.setAttribute('aria-hidden', 'true');

        return;
    }

    document.body.classList.add('is-loading');

    const finish = () => {
        loader.classList.add('is-exiting');
        document.body.classList.remove('is-loading');
        sessionStorage.setItem('jana-loader-seen', '1');
        revealAllInViewport();

        window.setTimeout(() => {
            loader.classList.add('is-hidden');
            loader.setAttribute('aria-hidden', 'true');
        }, 500);
    };

    window.setTimeout(finish, 2600);
}

/**
 * Immediately reveal elements already in the viewport (WEB-FIX-1).
 */
function revealAllInViewport() {
    const selectors = '[data-animate], [data-image-reveal]';
    const margin = 80;

    document.querySelectorAll(selectors).forEach((el) => {
        const rect = el.getBoundingClientRect();

        if (rect.top < window.innerHeight - margin && rect.bottom > -margin) {
            el.classList.add('is-visible');
            el.classList.add('is-revealed');
        }
    });
}

function initScrollProgress() {
    const bar = document.querySelector('[data-scroll-progress-bar]');
    const root = document.querySelector('[data-scroll-progress]');

    if (!bar || !root) {
        return;
    }

    const update = () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = docHeight > 0 ? Math.min((scrollTop / docHeight) * 100, 100) : 0;

        bar.style.width = `${progress}%`;
        root.setAttribute('aria-valuenow', String(Math.round(progress)));
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
}

/**
 * Keep revealed content visible once scrolled past (WEB-FIX-2).
 */
function revealPassedElements() {
    const margin = 40;

    document.querySelectorAll('[data-animate]:not(.is-visible), [data-image-reveal]:not(.is-revealed)').forEach((el) => {
        const rect = el.getBoundingClientRect();

        if (rect.top < window.innerHeight - margin) {
            el.classList.add('is-visible');
            el.classList.add('is-revealed');
        }
    });
}

function initRevealOnScroll() {
    const onScroll = () => revealPassedElements();

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function initScrollNav() {
    const scrollTopBtn = document.querySelector('[data-scroll-to-top]');
    const scrollBottomBtn = document.querySelector('[data-scroll-to-bottom]');

    if (!scrollTopBtn && !scrollBottomBtn) {
        return;
    }

    const docBottom = () => document.documentElement.scrollHeight - window.innerHeight;
    const edgeOffset = 120;

    const scrollTo = (top) => {
        window.scrollTo({ top, behavior: 'smooth' });
    };

    const update = () => {
        const y = window.scrollY;
        const maxY = docBottom();

        if (scrollTopBtn) {
            scrollTopBtn.hidden = y < edgeOffset;
        }

        if (scrollBottomBtn) {
            scrollBottomBtn.hidden = y > maxY - edgeOffset;
        }
    };

    scrollTopBtn?.addEventListener('click', () => scrollTo(0));
    scrollBottomBtn?.addEventListener('click', () => scrollTo(docBottom()));

    update();
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
}

function initStaggerReveal() {
    const groups = document.querySelectorAll('[data-reveal-stagger]');

    if (!groups.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    groups.forEach((group) => {
        const children = [...group.querySelectorAll('[data-animate]')];

        if (!children.length) {
            return;
        }

        if (prefersReducedMotion) {
            children.forEach((child) => child.classList.add('is-visible'));

            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    children.forEach((child, index) => {
                        window.setTimeout(() => {
                            child.classList.add('is-visible');
                        }, index * 80);
                    });

                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -40px 0px' },
        );

        observer.observe(group);
    });

    revealAllInViewport();
}

function initImageReveal() {
    const reveals = document.querySelectorAll('[data-image-reveal]');

    if (!reveals.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        reveals.forEach((el) => el.classList.add('is-revealed'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -20px 0px' },
    );

    reveals.forEach((el) => observer.observe(el));

    revealAllInViewport();
}

function initStickyFab() {
    const fab = document.querySelector('[data-sticky-fab]');

    if (!fab) {
        return;
    }

    const hero = document.querySelector('.public-hero');
    const isMobile = () => window.matchMedia('(max-width: 1023px)').matches;
    const scrollCtaVisible = () => document.querySelector('[data-scroll-cta]')?.classList.contains('is-visible');
    let visible = false;

    const heroThreshold = () => {
        if (!hero) {
            return 400;
        }

        return hero.offsetHeight * 0.6;
    };

    const show = () => {
        if (visible || (isMobile() && scrollCtaVisible())) {
            return;
        }

        visible = true;
        fab.hidden = false;

        requestAnimationFrame(() => {
            fab.classList.add('is-visible');
        });
    };

    const hide = () => {
        if (!visible) {
            return;
        }

        visible = false;
        fab.classList.remove('is-visible');

        window.setTimeout(() => {
            if (!visible) {
                fab.hidden = true;
            }
        }, 300);
    };

    const onScroll = () => {
        if (window.scrollY >= heroThreshold()) {
            if (isMobile() && scrollCtaVisible()) {
                hide();
            } else {
                show();
            }
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

function initMobileNav() {
    const header = document.querySelector('[data-public-header]');
    const nav = document.querySelector('[data-mobile-nav]');
    const toggle = document.querySelector('[data-mobile-nav-toggle]');

    if (!nav || !toggle) {
        return;
    }

    const setToggleState = (isOpen) => {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
        header?.classList.toggle('public-header--menu-open', isOpen);
    };

    const open = () => {
        nav.hidden = false;

        requestAnimationFrame(() => {
            nav.classList.add('is-open');
        });

        setToggleState(true);
    };

    const close = () => {
        nav.classList.remove('is-open');
        setToggleState(false);

        window.setTimeout(() => {
            if (!nav.classList.contains('is-open')) {
                nav.hidden = true;
            }
        }, 200);
    };

    toggle.addEventListener('click', () => {
        if (nav.classList.contains('is-open')) {
            close();
        } else {
            open();
        }
    });

    nav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) {
            close();
        }
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 768px)').matches && nav.classList.contains('is-open')) {
            close();
        }
    });
}

function initTestimonialRotator() {
    const rotators = document.querySelectorAll('[data-testimonial-rotator]');

    if (!rotators.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    rotators.forEach((rotator) => {
        const slides = [...rotator.querySelectorAll('[data-testimonial-slide]')];
        const dots = [...rotator.querySelectorAll('[data-testimonial-dot]')];

        if (slides.length <= 1) {
            slides.forEach((slide) => slide.classList.add('is-active'));

            return;
        }

        let current = slides.findIndex((slide) => slide.classList.contains('is-active'));
        current = current >= 0 ? current : 0;
        let timer = null;

        const setSlide = (index) => {
            current = index;

            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === current);
            });

            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === current);
            });
        };

        const next = () => setSlide((current + 1) % slides.length);

        const stopTimer = () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        const startTimer = () => {
            stopTimer();

            if (prefersReducedMotion) {
                return;
            }

            timer = setInterval(next, 5500);
        };

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                setSlide(i);
                startTimer();
            });
        });

        rotator.addEventListener('mouseenter', stopTimer);
        rotator.addEventListener('mouseleave', startTimer);
        rotator.addEventListener('focusin', stopTimer);
        rotator.addEventListener('focusout', startTimer);

        setSlide(current);
        startTimer();
    });
}

function initStickyHeader() {
    const header = document.querySelector('[data-public-header]');

    if (!header) {
        return;
    }

    const onScroll = () => {
        header.classList.toggle('public-header--scrolled', window.scrollY > 20);
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function initScrollReveal() {
    const elements = [...document.querySelectorAll('[data-animate]')].filter(
        (el) => !el.closest('[data-reveal-stagger]'),
    );

    if (!elements.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        elements.forEach((el) => el.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    elements.forEach((el) => observer.observe(el));

    revealAllInViewport();
}

function initSmoothAnchors() {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const targetId = anchor.getAttribute('href');

            if (!targetId || targetId === '#') {
                return;
            }

            const target = document.querySelector(targetId);

            if (!target) {
                return;
            }

            event.preventDefault();

            const header = document.querySelector('[data-public-header]');
            const headerHeight = header ? header.offsetHeight : 0;
            const top = target.getBoundingClientRect().top + window.scrollY - headerHeight - 8;

            window.scrollTo({ top, behavior: 'smooth' });
        });
    });
}

function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');

    if (!counters.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const animateCounter = (el) => {
        const target = parseInt(el.dataset.counter, 10);
        const suffix = el.dataset.counterSuffix || '';
        const prefix = el.dataset.counterPrefix || '';
        const duration = parseInt(el.dataset.counterDuration, 10) || 1750;
        const start = performance.now();
        const startValue = Math.max(0, Math.floor(target * 0.15));

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(startValue + (target - startValue) * eased);

            el.textContent = `${prefix}${value.toLocaleString()}${suffix}`;

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const el = entry.target;

                if (prefersReducedMotion) {
                    el.textContent = `${el.dataset.counterPrefix || ''}${Number(el.dataset.counter).toLocaleString()}${el.dataset.counterSuffix || ''}`;
                } else {
                    animateCounter(el);
                }

                observer.unobserve(el);
            });
        },
        { threshold: 0.15, rootMargin: '0px' },
    );

    counters.forEach((el) => observer.observe(el));
}

function initParallax() {
    const layers = document.querySelectorAll('[data-parallax]');

    if (!layers.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        return;
    }

    const onScroll = () => {
        const scrollY = window.scrollY;

        layers.forEach((layer) => {
            const speed = parseFloat(layer.dataset.parallax) || 0.3;
            const rect = layer.getBoundingClientRect();
            const inView = rect.top < window.innerHeight && rect.bottom > 0;

            if (inView) {
                layer.style.transform = `translateY(${scrollY * speed * 0.1}px)`;
            }
        });
    };

    window.addEventListener('scroll', onScroll, { passive: true });
}

function initTestimonialCarousel() {
    const carousels = document.querySelectorAll('[data-testimonial-carousel]');

    if (!carousels.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    carousels.forEach((carousel) => {
        const slides = [...carousel.querySelectorAll('[data-testimonial-slide]')];
        const dots = [...carousel.querySelectorAll('[data-testimonial-dot]')];

        if (slides.length <= 1) {
            return;
        }

        let current = 0;
        let timer = null;

        const isDesktop = () => desktopQuery.matches;

        const setSlide = (index) => {
            if (isDesktop()) {
                slides.forEach((slide) => slide.classList.add('is-active'));

                return;
            }

            current = index;

            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === current);
            });

            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === current);
            });
        };

        const next = () => setSlide((current + 1) % slides.length);

        const stopTimer = () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        const startTimer = () => {
            stopTimer();

            if (isDesktop() || prefersReducedMotion) {
                return;
            }

            timer = setInterval(next, 6000);
        };

        const init = () => {
            setSlide(isDesktop() ? 0 : current);
            startTimer();
        };

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                if (isDesktop()) {
                    return;
                }

                setSlide(i);
                startTimer();
            });
        });

        desktopQuery.addEventListener('change', init);

        carousel.addEventListener('mouseenter', stopTimer);
        carousel.addEventListener('mouseleave', startTimer);

        init();
    });
}

function initProductionShowcase() {
    const root = document.querySelector('[data-production-showcase]');

    if (!root) {
        return;
    }

    const line = root.querySelector('[data-production-flow-line]');
    const stages = [...root.querySelectorAll('[data-production-flow-stage]')];
    const steps = [...root.querySelectorAll('[data-production-step]')];

    if (!line || !stages.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        line.style.width = '100%';
        stages.forEach((stage) => stage.classList.add('is-active'));
        steps.forEach((step) => step.classList.add('is-active'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                line.classList.add('is-animating');

                stages.forEach((stage, index) => {
                    window.setTimeout(() => stage.classList.add('is-active'), index * 180);
                });

                steps.forEach((step, index) => {
                    window.setTimeout(() => step.classList.add('is-active'), index * 140);
                });

                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.2 },
    );

    observer.observe(root);
}

function initQualitySpine() {
    const root = document.querySelector('[data-quality-spine]');

    if (!root) {
        return;
    }

    const line = root.querySelector('[data-quality-spine-line]');
    const steps = [...root.querySelectorAll('[data-quality-step]')];

    if (!line) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        line.style.height = '100%';
        steps.forEach((step) => step.classList.add('is-active'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                line.classList.add('is-animating');

                steps.forEach((step, index) => {
                    window.setTimeout(() => step.classList.add('is-active'), index * 160);
                });

                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.15 },
    );

    observer.observe(root);
}

function initPortfolio() {
    initPortfolioFilters();
    initPortfolioModal();
}

function initPortfolioFilters() {
    const filterBar = document.querySelector('[data-portfolio-filters]');
    const grid = document.querySelector('[data-portfolio-grid]');

    if (!filterBar || !grid) {
        return;
    }

    const items = [...grid.querySelectorAll('[data-portfolio-item]')];
    const buttons = [...filterBar.querySelectorAll('[data-filter]')];

    const applyFilter = (slug, allowFallback = true) => {
        let visibleCount = 0;

        buttons.forEach((btn) => {
            const isActive = btn.dataset.filter === slug;

            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        items.forEach((item) => {
            const category = item.dataset.category;
            const show = slug === 'all' || category === slug;

            item.classList.toggle('is-filtered-out', !show);

            if (show) {
                visibleCount += 1;
            }
        });

        if (visibleCount === 0 && slug !== 'all' && allowFallback) {
            applyFilter('all', false);
        }
    };

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => applyFilter(btn.dataset.filter));
    });

    applyFilter('all');

    document.querySelectorAll('[data-portfolio-filter]').forEach((link) => {
        link.addEventListener('click', () => {
            const slug = link.dataset.portfolioFilter;

            if (!slug) {
                return;
            }

            window.setTimeout(() => applyFilter(slug), 600);
        });
    });

    return applyFilter;
}

function initPortfolioModal() {
    const modal = document.querySelector('[data-portfolio-modal]');

    if (!modal) {
        return;
    }

    const image = modal.querySelector('[data-portfolio-modal-image]');
    const category = modal.querySelector('[data-portfolio-modal-category]');
    const title = modal.querySelector('[data-portfolio-modal-title]');
    const location = modal.querySelector('[data-portfolio-modal-location]');

    let lastFocus = null;

    const setDetail = (key, value) => {
        const block = modal.querySelector(`[data-portfolio-modal-detail="${key}"]`);
        const field = modal.querySelector(`[data-portfolio-modal-value="${key}"]`);

        if (!block || !field) {
            return;
        }

        const text = (value || '').trim();
        field.textContent = text;
        block.hidden = text === '';
    };

    const open = (project) => {
        if (!project) {
            return;
        }

        lastFocus = document.activeElement;

        image.src = project.image;
        image.alt = project.alt || project.title;
        category.textContent = project.category_label || '';
        title.textContent = project.title || '';

        if (location) {
            const locationText = (project.location || '').trim();
            location.textContent = locationText;
            location.hidden = locationText === '';
        }

        setDetail('description', project.description);
        setDetail('materials', project.materials_label);
        setDetail('quantity', project.quantity_label);
        setDetail('timeline', project.timeline_label);
        setDetail('outcome', project.outcome);

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            modal.classList.add('is-open');
        });

        modal.querySelector('.gallery-preview-close')?.focus();
    };

    const close = () => {
        modal.classList.remove('is-open');

        window.setTimeout(() => {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            if (lastFocus) {
                lastFocus.focus();
            }
        }, 280);
    };

    document.querySelectorAll('[data-portfolio-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            try {
                const project = JSON.parse(trigger.dataset.project);

                open(project);
            } catch {
                /* ignore malformed data */
            }
        });
    });

    modal.querySelectorAll('[data-portfolio-close]').forEach((el) => {
        el.addEventListener('click', close);
    });

    modal.querySelectorAll('[data-portfolio-close-on-click]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            close();
        }
    });
}

function initJourney() {
    const timeline = document.querySelector('[data-journey-timeline]');
    const progressFill = document.querySelector('[data-journey-progress]');
    const steps = document.querySelectorAll('[data-journey-step]');
    const panel = document.querySelector('[data-journey-panel]');

    if (!timeline) {
        return;
    }

    const syncPanelState = () => {
        if (!panel) {
            return;
        }

        if (window.matchMedia('(min-width: 1024px)').matches) {
            panel.setAttribute('open', '');
        }
    };

    syncPanelState();
    window.addEventListener('resize', syncPanelState, { passive: true });

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (progressFill && !prefersReducedMotion) {
        const progressObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        progressFill.classList.add('is-animated');
                        progressObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15 },
        );

        progressObserver.observe(timeline);
    } else if (progressFill) {
        progressFill.classList.add('is-animated');
    }

    steps.forEach((step) => {
        step.addEventListener('mouseenter', () => {
            steps.forEach((s) => s.classList.remove('is-active'));
            step.classList.add('is-active');
        });

        step.addEventListener('mouseleave', () => {
            step.classList.remove('is-active');
        });
    });
}

function initQuoteFormAnchor() {
    if (window.location.hash !== '#quote-form') {
        return;
    }

    const target = document.getElementById('quote-form');

    if (!target) {
        return;
    }

    window.requestAnimationFrame(() => {
        window.setTimeout(() => {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 150);
    });
}

const CONTACT_INQUIRY_TYPES = {
    consultation: 'Book Consultation',
    quote: 'Request a Quote',
    artwork: 'Send Artwork',
    general: 'General Inquiry',
    'follow-up': 'Follow Up Existing Order',
};

function initContactSectionAnchor() {
    const params = new URLSearchParams(window.location.search);
    const type = params.get('type');
    const hasContactHash = window.location.hash === '#contact';

    if (!hasContactHash && !type) {
        return;
    }

    const section = document.getElementById('contact');
    const select = document.querySelector('[data-contact-inquiry-type]');

    if (select && type && CONTACT_INQUIRY_TYPES[type]) {
        select.value = CONTACT_INQUIRY_TYPES[type];
        syncContactInquiryHint(select);
    }

    if (!section) {
        return;
    }

    window.requestAnimationFrame(() => {
        window.setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });

            if (select && type) {
                select.focus({ preventScroll: true });
            }
        }, 150);
    });
}

function syncContactInquiryHint(select) {
    const hint = document.querySelector('[data-contact-quote-hint]');

    if (!hint) {
        return;
    }

    const isQuote = select.value === 'Request a Quote';
    hint.hidden = !isQuote;
}

function syncFloatingFieldState(field) {
    const control = field.querySelector('input, textarea, select');

    if (!control) {
        return;
    }

    const filled = control.tagName === 'SELECT'
        ? control.value !== ''
        : control.value.trim() !== '';

    field.classList.toggle('is-filled', filled);
}

function initFloatingFormFields(root = document) {
    root.querySelectorAll('.public-field-float').forEach((field) => {
        const control = field.querySelector('input, textarea, select');

        if (!control) {
            return;
        }

        syncFloatingFieldState(field);

        control.addEventListener('input', () => syncFloatingFieldState(field));
        control.addEventListener('change', () => syncFloatingFieldState(field));
        control.addEventListener('blur', () => syncFloatingFieldState(field));
    });
}

function initConversion() {
    initConversionFaq();
    initArtworkUpload();
    initFloatingFormFields();
    initQuoteForm();
    initContactForm();
    initScrollCta();
    initExitIntentStructure();
}

function initConversionFaq() {
    const faqRoot = document.querySelector('[data-conversion-faq]');

    if (!faqRoot) {
        return;
    }

    const items = [...faqRoot.querySelectorAll('[data-faq-item]')];

    items.forEach((item) => {
        const trigger = item.querySelector('[data-faq-trigger]');
        const panel = item.querySelector('[data-faq-panel]');

        if (!trigger || !panel) {
            return;
        }

        trigger.addEventListener('click', () => {
            const isOpen = trigger.getAttribute('aria-expanded') === 'true';

            items.forEach((other) => {
                const otherTrigger = other.querySelector('[data-faq-trigger]');
                const otherPanel = other.querySelector('[data-faq-panel]');

                if (!otherTrigger || !otherPanel) {
                    return;
                }

                otherTrigger.setAttribute('aria-expanded', 'false');
                other.classList.remove('is-open');
                otherPanel.hidden = true;
            });

            if (!isOpen) {
                trigger.setAttribute('aria-expanded', 'true');
                item.classList.add('is-open');
                panel.hidden = false;
            }
        });
    });
}

function initArtworkUpload() {
    document.querySelectorAll('[data-upload-placeholder]').forEach((placeholder) => {
        const input = placeholder.querySelector('[data-artwork-input]');

        if (!input) {
            return;
        }

        placeholder.addEventListener('click', () => input.click());

        input.addEventListener('change', () => {
            const label = placeholder.querySelector('p');

            if (label && input.files?.length) {
                label.textContent = input.files[0].name;
            }
        });
    });
}

async function submitPublicForm(form, successSelector) {
    const success = document.querySelector(successSelector);
    const submitButton = form.querySelector('[type="submit"]');
    const formData = new FormData(form);
    const csrf = form.querySelector('input[name="_token"]')?.value;

    if (submitButton) {
        submitButton.disabled = true;
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                Accept: 'application/json',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(payload.message || 'Submission failed');
        }

        form.reset();
        form.querySelectorAll('.public-field-float').forEach((field) => syncFloatingFieldState(field));
        form.querySelectorAll('input, select, textarea, button').forEach((el) => {
            if (el.type !== 'hidden' && el.name !== '_token') {
                el.disabled = true;
            }
        });

        if (success) {
            const textTarget = success.querySelector('[data-quote-success-text] span, [data-contact-success-text] span')
                || success.querySelector('p span')
                || success.querySelector('p');

            if (textTarget && payload.message) {
                if (textTarget.tagName === 'SPAN') {
                    textTarget.textContent = payload.message;
                } else {
                    textTarget.innerHTML = `<strong>Thank you!</strong> ${payload.message}`;
                }
            }

            success.hidden = false;
            success.removeAttribute('hidden');
            success.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    } catch (error) {
        if (submitButton) {
            submitButton.disabled = false;
        }

        form.submit();
    }
}

function initQuoteForm() {
    const form = document.querySelector('[data-quote-form]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        if (!window.fetch) {
            return;
        }

        event.preventDefault();
        submitPublicForm(form, '[data-quote-success]');
    });
}

function initContactForm() {
    const form = document.querySelector('[data-contact-form]');

    if (!form) {
        return;
    }

    const inquirySelect = form.querySelector('[data-contact-inquiry-type]');

    if (inquirySelect) {
        inquirySelect.addEventListener('change', () => syncContactInquiryHint(inquirySelect));
        syncContactInquiryHint(inquirySelect);
    }

    form.addEventListener('submit', (event) => {
        if (!window.fetch) {
            return;
        }

        event.preventDefault();
        submitPublicForm(form, '[data-contact-success]');
    });
}

function initScrollCta() {
    const banner = document.querySelector('[data-scroll-cta]');
    const closeBtn = document.querySelector('[data-scroll-cta-close]');

    if (!banner) {
        return;
    }

    const dismissed = sessionStorage.getItem('jana-scroll-cta-dismissed') === '1';
    const threshold = 600;
    let visible = false;

    const fab = document.querySelector('[data-sticky-fab]');
    const isMobile = () => window.matchMedia('(max-width: 1023px)').matches;

    const syncBodyState = () => {
        document.body.classList.toggle('has-scroll-cta', visible && isMobile());
    };

    const show = () => {
        if (dismissed || visible) {
            return;
        }

        visible = true;
        banner.hidden = false;

        requestAnimationFrame(() => {
            banner.classList.add('is-visible');
            syncBodyState();

            if (isMobile() && fab) {
                fab.classList.remove('is-visible');
                fab.hidden = true;
            }
        });
    };

    const hide = () => {
        banner.classList.remove('is-visible');
        visible = false;
        syncBodyState();

        window.setTimeout(() => {
            banner.hidden = true;
        }, 300);
    };

    const onScroll = () => {
        const hero = document.querySelector('.public-hero');
        const heroEnd = hero ? hero.offsetHeight * 0.5 : threshold;

        if (window.scrollY >= (isMobile() ? heroEnd : threshold)) {
            show();
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', syncBodyState, { passive: true });

    closeBtn?.addEventListener('click', () => {
        sessionStorage.setItem('jana-scroll-cta-dismissed', '1');
        hide();

        const hero = document.querySelector('.public-hero');
        const heroEnd = hero ? hero.offsetHeight * 0.5 : threshold;

        if (isMobile() && fab && window.scrollY >= heroEnd) {
            fab.hidden = false;

            requestAnimationFrame(() => {
                fab.classList.add('is-visible');
            });
        }
    });

    onScroll();
}

function initExitIntentStructure() {
    const popup = document.querySelector('[data-exit-intent-popup]');

    if (!popup) {
        return;
    }

    const enabled = popup.dataset.exitIntentEnabled === 'true';

    const close = () => {
        popup.hidden = true;
        popup.classList.remove('is-open');
        popup.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    popup.querySelectorAll('[data-exit-intent-close]').forEach((el) => {
        el.addEventListener('click', (event) => {
            const href = el.getAttribute('href');
            const navigates = href && href.startsWith('#') && href !== '#';

            close();

            if (!navigates) {
                event.preventDefault();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !popup.hidden) {
            close();
        }
    });

    if (!enabled) {
        close();

        return;
    }

    let shown = false;

    const open = () => {
        if (shown) {
            return;
        }

        shown = true;
        popup.hidden = false;
        popup.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    document.addEventListener('mouseout', (event) => {
        if (event.clientY <= 0 && !shown) {
            open();
        }
    });
}

function initClientPortal() {
    initClientProfileMenu();
    initClientSidebar();
}

function initClientProfileMenu() {
    document.querySelectorAll('[data-client-profile-menu]').forEach((menu) => {
        const toggle = menu.querySelector('[data-client-profile-toggle]');
        const dropdown = menu.querySelector('[data-client-profile-dropdown]');

        if (!toggle || !dropdown) {
            return;
        }

        const close = () => {
            dropdown.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = dropdown.hidden;
            document.querySelectorAll('[data-client-profile-dropdown]').forEach((panel) => {
                panel.hidden = true;
            });
            document.querySelectorAll('[data-client-profile-toggle]').forEach((btn) => {
                btn.setAttribute('aria-expanded', 'false');
            });

            if (willOpen) {
                dropdown.hidden = false;
                toggle.setAttribute('aria-expanded', 'true');
            }
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
            }
        });
    });
}

function initClientSidebar() {
    const sidebar = document.querySelector('[data-client-sidebar]');
    const backdrop = document.querySelector('[data-client-sidebar-backdrop]');
    const toggles = document.querySelectorAll('[data-client-sidebar-toggle]');
    const links = document.querySelectorAll('[data-client-sidebar-link]');

    if (!sidebar || !backdrop || toggles.length === 0) {
        return;
    }

    const close = () => {
        sidebar.classList.remove('is-open');
        backdrop.hidden = true;
        document.body.classList.remove('client-sidebar-open');
        toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', 'false'));
    };

    const open = () => {
        sidebar.classList.add('is-open');
        backdrop.hidden = false;
        document.body.classList.add('client-sidebar-open');
        toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', 'true'));
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            if (sidebar.classList.contains('is-open')) {
                close();
            } else {
                open();
            }
        });
    });

    backdrop.addEventListener('click', close);

    links.forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 1023px)').matches) {
                close();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}

function initClientPasswordToggle() {
    document.querySelectorAll('.client-password-wrap').forEach((wrap) => {
        const toggle = wrap.querySelector('.client-password-toggle');
        const input = wrap.querySelector('input');

        if (!toggle || !input) {
            return;
        }

        const showIcon = toggle.querySelector('[data-show-icon]');
        const hideIcon = toggle.querySelector('[data-hide-icon]');

        toggle.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(isHidden));
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

            if (showIcon && hideIcon) {
                showIcon.hidden = isHidden;
                hideIcon.hidden = !isHidden;
            }
        });
    });
}

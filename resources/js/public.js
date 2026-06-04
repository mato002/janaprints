/**
 * Jana Prints public website — lightweight interactions.
 * Scroll reveals, sticky header, smooth anchors, counters, subtle parallax.
 */

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
    initJourney();
    initConversion();
    initStickyFab();

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

    const threshold = 400;
    let visible = false;

    const show = () => {
        if (visible) {
            return;
        }

        visible = true;
        fab.hidden = false;

        requestAnimationFrame(() => {
            fab.classList.add('is-visible');
        });
    };

    const onScroll = () => {
        if (window.scrollY >= threshold) {
            show();
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
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

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(target * eased);

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

function initPortfolio() {
    initPortfolioFilters();
    initPortfolioModal();
}

function initPortfolioFilters() {
    const filterBar = document.querySelector('[data-portfolio-filters]');
    const grid = document.querySelector('[data-portfolio-grid]');
    const emptyState = document.querySelector('[data-portfolio-empty]');

    if (!filterBar || !grid) {
        return;
    }

    const items = [...grid.querySelectorAll('[data-portfolio-item]')];
    const buttons = [...filterBar.querySelectorAll('[data-filter]')];

    const applyFilter = (slug) => {
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

        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCount > 0);
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
    const description = modal.querySelector('[data-portfolio-modal-description]');
    const materials = modal.querySelector('[data-portfolio-modal-materials]');
    const quantity = modal.querySelector('[data-portfolio-modal-quantity]');
    const timeline = modal.querySelector('[data-portfolio-modal-timeline]');
    const outcome = modal.querySelector('[data-portfolio-modal-outcome]');

    let lastFocus = null;

    const open = (project) => {
        if (!project) {
            return;
        }

        lastFocus = document.activeElement;

        image.src = project.image;
        image.alt = project.alt || project.title;
        category.textContent = project.category_label;
        title.textContent = project.title;
        location.textContent = project.location;
        description.textContent = project.description;
        materials.textContent = project.materials;
        quantity.textContent = project.quantity;
        timeline.textContent = project.timeline;
        outcome.textContent = project.outcome;

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            modal.classList.add('is-open');
        });

        modal.querySelector('[data-portfolio-close]')?.focus();
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

    if (!timeline) {
        return;
    }

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

function initConversion() {
    initConversionFaq();
    initQuoteForm();
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

function initQuoteForm() {
    const form = document.querySelector('[data-quote-form]');
    const success = document.querySelector('[data-quote-success]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        form.querySelectorAll('input, select, textarea, button').forEach((el) => {
            el.disabled = true;
        });

        if (success) {
            success.hidden = false;
            success.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
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

    const show = () => {
        if (dismissed || visible) {
            return;
        }

        visible = true;
        banner.hidden = false;

        requestAnimationFrame(() => {
            banner.classList.add('is-visible');
        });
    };

    const hide = () => {
        banner.classList.remove('is-visible');
        visible = false;

        window.setTimeout(() => {
            banner.hidden = true;
        }, 300);
    };

    const onScroll = () => {
        if (window.scrollY >= threshold) {
            show();
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });

    closeBtn?.addEventListener('click', () => {
        sessionStorage.setItem('jana-scroll-cta-dismissed', '1');
        hide();
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

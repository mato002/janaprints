document.addEventListener('DOMContentLoaded', () => {
    initBackgroundRotator();
    initParticles();
    initPasswordToggle();
    initCardAnimation();
});

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initCardAnimation() {
    const card = document.querySelector('[data-login-card]');

    if (!card || prefersReducedMotion()) {
        card?.classList.add('login-card--visible');
        return;
    }
}

function initBackgroundRotator() {
    const root = document.querySelector('[data-login-backgrounds]');

    if (!root) {
        return;
    }

    const slides = [...root.querySelectorAll('[data-login-bg-slide]')];

    if (slides.length <= 1) {
        slides.forEach((slide) => slide.classList.add('login-scene__slide--active'));

        return;
    }

    let index = 0;

    const activate = (nextIndex) => {
        slides.forEach((slide, i) => {
            const isActive = i === nextIndex;
            slide.classList.toggle('login-scene__slide--active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
        });
        index = nextIndex;
    };

    activate(0);

    if (prefersReducedMotion()) {
        return;
    }

    window.setInterval(() => {
        activate((index + 1) % slides.length);
    }, 6000);
}

function initPasswordToggle() {
    const toggle = document.querySelector('[data-login-password-toggle]');
    const input = document.querySelector('[data-login-password-input]');

    if (!toggle || !input) {
        return;
    }

    const showLabel = toggle.querySelector('[data-login-password-show]');
    const hideLabel = toggle.querySelector('[data-login-password-hide]');

    toggle.addEventListener('click', () => {
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(isHidden));
        toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

        if (showLabel && hideLabel) {
            showLabel.hidden = isHidden;
            hideLabel.hidden = !isHidden;
        }
    });
}

function initParticles() {
    const canvas = document.querySelector('[data-login-particles]');

    if (!canvas || prefersReducedMotion()) {
        return;
    }

    const ctx = canvas.getContext('2d');

    if (!ctx) {
        return;
    }

    let width = 0;
    let height = 0;
    let particles = [];
    let animationId = 0;

    const palette = ['#FF2D75', '#6C4BFF', '#FF7A18', '#00D4FF', '#FFFFFF'];

    const resize = () => {
        const parent = canvas.parentElement;

        if (!parent) {
            return;
        }

        width = parent.clientWidth;
        height = parent.clientHeight;
        canvas.width = width * window.devicePixelRatio;
        canvas.height = height * window.devicePixelRatio;
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);

        const count = Math.min(56, Math.max(20, Math.floor((width * height) / 18000)));
        particles = Array.from({ length: count }, () => createParticle());
    };

    const createParticle = () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        radius: Math.random() * 1.6 + 0.35,
        speedX: (Math.random() - 0.5) * 0.12,
        speedY: (Math.random() - 0.5) * 0.12 - 0.03,
        opacity: Math.random() * 0.28 + 0.08,
        opacityBase: Math.random() * 0.28 + 0.08,
        opacityPhase: Math.random() * Math.PI * 2,
        colorIndex: Math.floor(Math.random() * palette.length),
        colorDrift: (Math.random() - 0.5) * 0.008,
        driftPhase: Math.random() * Math.PI * 2,
    });

    let frame = 0;

    const draw = () => {
        ctx.clearRect(0, 0, width, height);
        frame += 1;

        particles.forEach((particle) => {
            particle.x += particle.speedX + Math.sin(frame * 0.004 + particle.driftPhase) * 0.04;
            particle.y += particle.speedY + Math.cos(frame * 0.003 + particle.driftPhase) * 0.03;

            if (particle.x < -10) particle.x = width + 10;
            if (particle.x > width + 10) particle.x = -10;
            if (particle.y < -10) particle.y = height + 10;
            if (particle.y > height + 10) particle.y = -10;

            particle.colorIndex = (particle.colorIndex + particle.colorDrift + palette.length) % palette.length;
            const colorMix = Math.sin(frame * 0.002 + particle.opacityPhase) * 0.5 + 0.5;
            const nextIndex = (Math.floor(particle.colorIndex) + 1) % palette.length;
            const currentColor = palette[Math.floor(particle.colorIndex) % palette.length];
            const nextColor = palette[nextIndex];

            particle.opacity = particle.opacityBase + Math.sin(frame * 0.015 + particle.opacityPhase) * 0.06;

            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = colorMix > 0.5 ? nextColor : currentColor;
            ctx.globalAlpha = Math.max(0.05, particle.opacity);
            ctx.fill();
        });

        ctx.globalAlpha = 1;
        animationId = window.requestAnimationFrame(draw);
    };

    resize();
    draw();

    window.addEventListener('resize', resize);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            window.cancelAnimationFrame(animationId);
        } else {
            draw();
        }
    });
}

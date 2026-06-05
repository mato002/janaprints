document.addEventListener('DOMContentLoaded', () => {
    initParticles();
    initPasswordToggle();
});

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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

        const count = Math.min(36, Math.max(14, Math.floor((width * height) / 24000)));
        particles = Array.from({ length: count }, () => createParticle());
    };

    const createParticle = () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        radius: Math.random() * 1.2 + 0.3,
        speedX: (Math.random() - 0.5) * 0.08,
        speedY: (Math.random() - 0.5) * 0.08 - 0.02,
        opacity: Math.random() * 0.2 + 0.06,
        opacityBase: Math.random() * 0.2 + 0.06,
        opacityPhase: Math.random() * Math.PI * 2,
        colorIndex: Math.floor(Math.random() * palette.length),
        driftPhase: Math.random() * Math.PI * 2,
    });

    let frame = 0;

    const draw = () => {
        ctx.clearRect(0, 0, width, height);
        frame += 1;

        particles.forEach((particle) => {
            particle.x += particle.speedX + Math.sin(frame * 0.003 + particle.driftPhase) * 0.03;
            particle.y += particle.speedY + Math.cos(frame * 0.002 + particle.driftPhase) * 0.02;

            if (particle.x < -10) particle.x = width + 10;
            if (particle.x > width + 10) particle.x = -10;
            if (particle.y < -10) particle.y = height + 10;
            if (particle.y > height + 10) particle.y = -10;

            particle.opacity = particle.opacityBase + Math.sin(frame * 0.012 + particle.opacityPhase) * 0.04;

            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = palette[particle.colorIndex % palette.length];
            ctx.globalAlpha = Math.max(0.04, particle.opacity);
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

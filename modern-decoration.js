(function() {
    'use strict';

    const header = document.querySelector('.header');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        if (currentScroll > 50) {
            header?.classList.add('scrolled');
        } else {
            header?.classList.remove('scrolled');
        }
        lastScroll = currentScroll;
    }, { passive: true });

    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
        circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
        circle.classList.add('ripple');

        const ripple = button.getElementsByClassName('ripple')[0];
        if (ripple) ripple.remove();

        button.appendChild(circle);
    }

    const buttons = document.querySelectorAll('.login-btn, .btn-submit, .btn-book, .btn-book-now, .btn-book-movie, .nav-link, .btn-trailer');
    buttons.forEach(button => button.addEventListener('click', createRipple));

    const logo = document.querySelector('.logo');
    
    if (logo && window.innerWidth > 768) {
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX - window.innerWidth / 2) / 100;
            const y = (e.clientY - window.innerHeight / 2) / 100;
            logo.style.transform = `translate(${x}px, ${y}px)`;
        });
    }

    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');

    if (searchInput && searchBtn) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchBtn.click();
        });

        searchBtn.addEventListener('click', () => {
            if (searchInput.value.trim() === '') {
                searchInput.focus();
                searchInput.style.animation = 'shake 0.5s';
                setTimeout(() => searchInput.style.animation = '', 500);
            }
        });
    }

    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const animateElements = document.querySelectorAll('.movie-card, .promotion-card, .intro-card, .ticket-card, .activity-card');
    animateElements.forEach(el => observer.observe(el));

    const vipBadge = document.querySelector('.vip-badge');
    if (vipBadge) {
        vipBadge.addEventListener('click', () => {
            vipBadge.style.animation = 'none';
            setTimeout(() => vipBadge.style.animation = 'vipPulse 2s ease-in-out infinite', 10);
        });
    }

    if (logo) {
        logo.style.cursor = 'pointer';
        logo.addEventListener('click', (e) => {
            if (window.scrollY > 0) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    const formInputs = document.querySelectorAll('.form-group input, .form-group textarea, .form-group select');
    formInputs.forEach(input => {
        input.addEventListener('focus', () => input.parentElement?.classList.add('focused'));
        input.addEventListener('blur', () => {
            if (input.value === '') input.parentElement?.classList.remove('focused');
        });
        if (input.value !== '') input.parentElement?.classList.add('focused');
    });

    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class' && modal.classList.contains('active')) {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.style.animation = 'modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    }
                }
            });
        });
        observer.observe(modal, { attributes: true });
    });



    const movieCards = document.querySelectorAll('.movie-card');
    movieCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-15px) scale(1.02)`;
        });
        card.addEventListener('mouseleave', () => card.style.transform = '');
    });

    const sectionTitles = document.querySelectorAll('.section-title');
    const titleObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'titleFadeIn 1s ease-out';
                titleObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    sectionTitles.forEach(title => titleObserver.observe(title));

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (prefersReducedMotion.matches) {
        document.documentElement.style.setProperty('--animation-duration', '0.01s');
        const reducedMotionStyle = document.createElement('style');
        reducedMotionStyle.textContent = `*, *::before, *::after { animation-duration: 0.01s !important; animation-iteration-count: 1 !important; transition-duration: 0.01s !important; }`;
        document.head.appendChild(reducedMotionStyle);
    }

    window.addEventListener('load', () => {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 0.5s ease';
            document.body.style.opacity = '1';
        }, 100);
    });
})();

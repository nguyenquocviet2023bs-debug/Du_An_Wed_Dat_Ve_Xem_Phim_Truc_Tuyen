document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    initNavigation();
    initBookingButtons();
    initMovieCards();
    initSearchbar();
});
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}
function initBookingButtons() {
    const bookNowBtn = document.querySelector('.btn-book-now');
    const bookBtn = document.querySelector('.btn-book');
    const bookMovieBtns = document.querySelectorAll('.btn-book-movie');
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', handleBooking);
    }
    if (bookBtn) {
        bookBtn.addEventListener('click', handleBooking);
    }
    bookMovieBtns.forEach(btn => {
        btn.addEventListener('click', handleBooking);
    });
}
function handleBooking() {
    const movieSelect = document.querySelector('.selection-input');
    if (movieSelect && movieSelect.value === '') {
        alert('Vui lòng chọn phim trước!');
        movieSelect.focus();
    } else {
        alert('Bạn sẽ được chuyển đến trang đặt vé!');
    }
}
function initMovieCards() {
    const movieCards = document.querySelectorAll('.movie-card');
    const trailerBtns = document.querySelectorAll('.btn-trailer');
    trailerBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            playTrailer();
        });
    });
}
function playTrailer() {
    alert('Trailer sẽ được phát trong một cửa sổ mới!');
}
function initSearchbar() {
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSearchModal();
        });
    }
}
function openSearchModal() {
    alert('Mở cửa sổ tìm kiếm!');
}
const loginBtn = document.querySelector('.login-btn');
if (loginBtn) {
    loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openLoginModal();
    });
}
function openLoginModal() {
    alert('Mở cửa sổ đăng nhập!');
}
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};
const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);
document.querySelectorAll('.movie-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(card);
});
function validateBookingForm() {
    const selects = document.querySelectorAll('.selection-input');
    let isValid = true;
    selects.forEach(select => {
        if (select.value === '') {
            isValid = false;
            select.style.borderColor = '#ff6b35';
        } else {
            select.style.borderColor = '#ddd';
        }
    });
    return isValid;
}
function updateFooterYear() {
    const yearElement = document.querySelector('.footer-copyright');
    if (yearElement) {
        const currentYear = new Date().getFullYear();
        yearElement.innerHTML = `<p>&copy; ${currentYear} Cinema Hub. Bảo lưu mọi quyền.</p>`;
    }
}
updateFooterYear();
function toggleMobileMenu() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.classList.toggle('active');
    }
}
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    document.querySelectorAll('img').forEach(img => {
        imageObserver.observe(img);
    });
}
console.log('Cinema Hub - Home Page Initialized');
console.log('Ready to handle user interactions');

(function() {
    let currentSlide = 0;
    let slides = [];
    let autoPlayInterval = null;
    const AUTO_PLAY_DELAY = 3000;

    async function loadBannerMovies() {
        try {
            const formData = new FormData();
            formData.append('action', 'getMovies');
            
            const response = await fetch('logicDB.php', { method: 'POST', body: formData });
            const data = await response.json();
            
            if (data.success && data.movies && data.movies.length > 0) {
                slides = data.movies.slice(0, 5);
                renderBannerSlides();
                initBannerControls();
                startAutoPlay();
            } else {
                renderDefaultBanner();
            }
        } catch (error) {
            console.error('Lỗi khi tải banner:', error);
            renderDefaultBanner();
        }
    }

    function renderBannerSlides() {
        const slider = document.querySelector('.banner-slider');
        const dotsContainer = document.querySelector('.banner-dots');
        
        if (!slider || !dotsContainer) return;

        slider.innerHTML = slides.map((movie, index) => {
            const releaseDate = movie.ngay_khoi_chieu 
                ? new Date(movie.ngay_khoi_chieu).toLocaleDateString('vi-VN', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric' 
                })
                : 'Đang chiếu';

            const bgUrl = movie.hinh_anh_url ? escapeHtml(movie.hinh_anh_url) : './hq720.jpg';
            return `
                <div class="banner-slide" data-index="${index}" style="--bg-img: url('${bgUrl}')">
                    <div class="banner-image">
                        <img src="${bgUrl}" alt="${escapeHtml(movie.ten_phim)}" onerror="this.src='./hq720.jpg';">
                    </div>
                    <div class="banner-overlay"></div>
                    <div class="banner-text">
                        <h2>${escapeHtml(movie.ten_phim)}</h2>
                        ${movie.mo_ta ? `<p>${escapeHtml(movie.mo_ta.substring(0, 150))}${movie.mo_ta.length > 150 ? '...' : ''}</p>` : ''}
                        <div class="banner-info">
                            ${movie.the_loai ? `<span class="banner-info-item"><i class="fas fa-tag"></i> ${escapeHtml(movie.the_loai)}</span>` : ''}
                            ${movie.thoi_luong ? `<span class="banner-info-item"><i class="fas fa-clock"></i> ${movie.thoi_luong} phút</span>` : ''}
                            ${movie.dao_dien ? `<span class="banner-info-item"><i class="fas fa-user-tie"></i> ${escapeHtml(movie.dao_dien)}</span>` : ''}
                        </div>
                        <p class="banner-date">
                            <i class="fas fa-calendar-alt"></i> ${releaseDate}
                        </p>
                        <button class="btn-book-now" data-movie="${escapeHtml(movie.ten_phim)}">
                            <i class="fas fa-ticket-alt"></i> ĐẶT VÉ NGAY
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        dotsContainer.innerHTML = slides.map((_, index) => 
            `<span class="banner-dot ${index === 0 ? 'active' : ''}" data-index="${index}"></span>`
        ).join('');

        document.querySelectorAll('.btn-book-now').forEach(btn => {
            btn.addEventListener('click', function() {
                const movieName = this.getAttribute('data-movie');
                handleBannerBooking(movieName);
            });
        });
    }

    function renderDefaultBanner() {
        const slider = document.querySelector('.banner-slider');
        if (!slider) return;

        slider.innerHTML = `
            <div class="banner-slide" style="--bg-img: url('./hq720.jpg')">
                <div class="banner-image">
                    <img src="./hq720.jpg" alt="Cinema Banner">
                </div>
                <div class="banner-overlay"></div>
                <div class="banner-text">
                    <h2>CHÀO MỪNG ĐÉN CINEMA GROUP 11</h2>
                    <p>Trải nghiệm điện ảnh đẳng cấp với công nghệ hiện đại</p>
                    <p class="banner-date">Đặt vé ngay hôm nay!</p>
                    <button class="btn-book-now" onclick="alert('Vui lòng chọn phim từ danh sách phía dưới')">
                        <i class="fas fa-ticket-alt"></i> XEM PHIM ĐANG CHIẾU
                    </button>
                </div>
            </div>
        `;
    }

    function initBannerControls() {
        const prevBtn = document.querySelector('.banner-prev');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                stopAutoPlay();
                prevSlide();
                startAutoPlay();
            });
        }

        const nextBtn = document.querySelector('.banner-next');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                stopAutoPlay();
                nextSlide();
                startAutoPlay();
            });
        }

        document.querySelectorAll('.banner-dot').forEach(dot => {
            dot.addEventListener('click', function() {
                stopAutoPlay();
                goToSlide(parseInt(this.getAttribute('data-index')));
                startAutoPlay();
            });
        });

        const banner = document.querySelector('.banner');
        if (banner) {
            banner.addEventListener('mouseenter', stopAutoPlay);
            banner.addEventListener('mouseleave', startAutoPlay);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                stopAutoPlay();
                prevSlide();
                startAutoPlay();
            } else if (e.key === 'ArrowRight') {
                stopAutoPlay();
                nextSlide();
                startAutoPlay();
            }
        });
    }

    function goToSlide(index) {
        if (slides.length === 0) return;

        currentSlide = index;
        if (currentSlide < 0) currentSlide = slides.length - 1;
        if (currentSlide >= slides.length) currentSlide = 0;

        const slider = document.querySelector('.banner-slider');
        if (slider) {
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        document.querySelectorAll('.banner-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    function nextSlide() {
        goToSlide(currentSlide + 1);
    }

    function prevSlide() {
        goToSlide(currentSlide - 1);
    }

    function startAutoPlay() {
        stopAutoPlay();
        if (slides.length > 1) {
            autoPlayInterval = setInterval(nextSlide, AUTO_PLAY_DELAY);
        }
    }

    function stopAutoPlay() {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
            autoPlayInterval = null;
        }
    }

    function handleBannerBooking(movieName) {
        if (typeof bookingState !== 'undefined') {
            bookingState.selectedMovie = movieName;
            document.getElementById('bookingMovieName').textContent = movieName;
            
            const bookingModal = document.getElementById('bookingModal');
            if (bookingModal) {
                bookingModal.classList.add('active');
                
                setTimeout(() => {
                    const today = new Date().toISOString().split('T')[0];
                    bookingState.selectedDate = today;
                    const showDateInput = document.getElementById('showDate');
                    if (showDateInput) showDateInput.value = today;
                    if (typeof populateShowtimes === 'function') populateShowtimes(today);
                    if (typeof initSeatMap === 'function') initSeatMap();
                }, 100);
            }
        } else {
            alert('Vui lòng đăng nhập để đặt vé!');
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadBannerMovies);
    } else {
        loadBannerMovies();
    }

    window.addEventListener('focus', () => {
        setTimeout(loadBannerMovies, 1000);
    });

})();

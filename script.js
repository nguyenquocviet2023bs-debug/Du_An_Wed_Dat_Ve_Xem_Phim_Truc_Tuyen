// Danh sách phim đang chiếu
const moviesData = [
    { id: 1, name: 'Heo 5 Móng', genre: 'Kinh dị', rating: 8.3 },
    { id: 2, name: 'Trùm Sò', genre: 'Hài', rating: 8.6 },
    { id: 3, name: 'Phí Phông: Quỷ Máu Rừng Thiêng', genre: 'Kinh dị', rating: 8.7 },
    { id: 4, name: 'Đại Tiệc Trăng Máu 8', genre: 'Hành động', rating: 8.2 },
    { id: 5, name: 'Anh Hùng', genre: 'Tâm lý', rating: 8.7 },
    { id: 6, name: 'Super Mario Thiên Hà', genre: 'Hoạt hình', rating: 8.1 },
    { id: 7, name: 'Shin Cậu Bé Búp Chì', genre: 'Hoạt hình', rating: 8.0 },
];

// Danh sách giờ chiếu mặc định
const showtimesData = [
    { time: '09:00', type: 'normal' },
    { time: '11:30', type: 'normal' },
    { time: '14:30', type: 'normal' },
    { time: '17:10', type: 'normal' },
    { time: '19:30', type: 'normal' },
    { time: '20:35', type: 'normal' },
    { time: '22:00', type: 'normal' }
];

// Dữ liệu booking
let bookingState = {
    selectedMovie: null,
    selectedDate: null,
    selectedShowtime: null,
    selectedSeats: [],
    pricePerSeat: 50000
};

// Bố cục ghế (10 hàng x 15 cột)
const seatLayout = [
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
    ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
];
const bookedSeats = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    initNavigation();
    initBookingButtons();
    initMovieCards();
    initSearchbar();
    initModals();
    initFormHandlers();
    initBookingModal();
});

// ===== MODAL MANAGEMENT =====
function initModals() {
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    const searchModal = document.getElementById('searchResultsModal');
    const loginBtn = document.getElementById('openLoginBtn');
    const closeButtons = document.querySelectorAll('.close');
    
    // Open login modal
    if (loginBtn) {
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(loginModal);
        });
    }
    
    // Close modals
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('active');
        });
    });
    
    // Close when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });
    
    // Toggle between login and signup
    const openSignupBtn = document.getElementById('openSignupBtn');
    const openLoginBtn2 = document.getElementById('openLoginBtn2');
    
    if (openSignupBtn) {
        openSignupBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(loginModal);
            openModal(signupModal);
        });
    }
    
    if (openLoginBtn2) {
        openLoginBtn2.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(signupModal);
            openModal(loginModal);
        });
    }
}

function openModal(modal) {
    modal.classList.add('active');
}

function closeModal(modal) {
    modal.classList.remove('active');
}

// ===== FORM HANDLERS =====
function initFormHandlers() {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignupSubmit);
    }
}

function handleLoginSubmit(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    if (email && password) {
        alert('Đăng nhập thành công! Email: ' + email);
        document.getElementById('loginForm').reset();
        document.getElementById('loginModal').classList.remove('active');
    } else {
        alert('Vui lòng điền đầy đủ thông tin!');
    }
}

function handleSignupSubmit(e) {
    e.preventDefault();
    const password = document.getElementById('signupPassword').value;
    const confirmPassword = document.getElementById('signupConfirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Mật khẩu không trùng khớp!');
        return;
    }
    
    const name = document.getElementById('signupName').value;
    alert('Đăng ký thành công! Chào mừng ' + name);
    document.getElementById('signupForm').reset();
    document.getElementById('signupModal').classList.remove('active');
}

// ===== BOOKING MODAL HANDLERS =====
function initBookingModal() {
    const showDateInput = document.getElementById('showDate');
    const confirmBtn = document.getElementById('confirmBooking');
    
    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    showDateInput.min = today;
    showDateInput.value = today;
    
    // Load showtimes for today
    populateShowtimes(today);
    
    showDateInput.addEventListener('change', function() {
        bookingState.selectedDate = this.value;
        bookingState.selectedShowtime = null;
        populateShowtimes(this.value);
        resetSeatSelection();
    });
    
    confirmBtn.addEventListener('click', function() {
        if (!bookingState.selectedDate || !bookingState.selectedShowtime) {
            alert('Vui lòng chọn ngày và giờ chiếu!');
            return;
        }
        if (bookingState.selectedSeats.length === 0) {
            alert('Vui lòng chọn ít nhất 1 ghế!');
            return;
        }
        
        const totalPrice = bookingState.selectedSeats.length * bookingState.pricePerSeat;
        alert(`Đặt vé thành công!\n\nPhim: ${bookingState.selectedMovie}\nNgày: ${bookingState.selectedDate}\nGiờ: ${bookingState.selectedShowtime}\nGhế: ${bookingState.selectedSeats.join(', ')}\nTổng tiền: ${totalPrice.toLocaleString()} VND`);
        
        // Reset
        document.getElementById('bookingModal').classList.remove('active');
        resetBookingState();
    });
}

function populateShowtimes(date) {
    const showtimeList = document.getElementById('showtimeList');
    showtimeList.innerHTML = '';
    
    showtimesData.forEach(showtime => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'showtime-btn';
        btn.textContent = showtime.time;
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            bookingState.selectedShowtime = showtime.time;
        });
        
        showtimeList.appendChild(btn);
    });
}

function initSeatMap() {
    const seatMap = document.getElementById('seatMap');
    seatMap.innerHTML = '';
    seatMap.style.gridTemplateColumns = `repeat(15, 1fr)`;
    
    const rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    
    seatLayout.forEach((row, rowIndex) => {
        row.forEach((seatType, colIndex) => {
            const seatName = `${rows[rowIndex]}${colIndex + 1}`;
            const seatDiv = document.createElement('div');
            seatDiv.className = 'seat';
            
            if (seatType === 'E') {
                seatDiv.className = 'seat seat-empty';
                seatDiv.textContent = '';
            } else if (bookedSeats.includes(seatName)) {
                seatDiv.className = 'seat seat-booked';
                seatDiv.textContent = seatName;
            } else {
                seatDiv.className = 'seat seat-available';
                seatDiv.textContent = seatName;
                
                seatDiv.addEventListener('click', function() {
                    if (this.classList.contains('seat-selected')) {
                        this.classList.remove('seat-selected');
                        bookingState.selectedSeats = bookingState.selectedSeats.filter(s => s !== seatName);
                    } else {
                        this.classList.add('seat-selected');
                        bookingState.selectedSeats.push(seatName);
                    }
                    updateBookingInfo();
                });
            }
            
            seatMap.appendChild(seatDiv);
        });
    });
}

function updateBookingInfo() {
    const count = bookingState.selectedSeats.length;
    const total = count * bookingState.pricePerSeat;
    
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('totalPrice').textContent = total.toLocaleString() + ' VND';
}

function resetSeatSelection() {
    bookingState.selectedSeats = [];
    const seatMap = document.getElementById('seatMap');
    seatMap.querySelectorAll('.seat').forEach(seat => {
        if (seat.classList.contains('seat-selected')) {
            seat.classList.remove('seat-selected');
        }
    });
    updateBookingInfo();
}

function resetBookingState() {
    bookingState.selectedSeats = [];
    bookingState.selectedDate = null;
    bookingState.selectedShowtime = null;
    document.getElementById('showDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('selectedCount').textContent = '0';
    document.getElementById('totalPrice').textContent = '0 VND';
    document.querySelectorAll('.showtime-btn').forEach(btn => btn.classList.remove('active'));
    initSeatMap();
}

// ===== SEARCH FUNCTIONALITY =====
function initSearchbar() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
}

function performSearch() {
    const searchInput = document.querySelector('.search-input');
    const query = searchInput.value.toLowerCase().trim();
    
    if (!query) {
        alert('Vui lòng nhập tên phim để tìm kiếm!');
        return;
    }
    
    const results = moviesData.filter(movie =>
        movie.name.toLowerCase().includes(query)
    );
    
    displaySearchResults(results, query);
    openModal(document.getElementById('searchResultsModal'));
}

function displaySearchResults(results, query) {
    const resultsContainer = document.getElementById('searchResults');
    
    if (results.length === 0) {
        resultsContainer.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 30px;">
                <p style="color: #999; font-size: 16px;">Không tìm thấy phim nào với tên "<strong>${query}</strong>"</p>
            </div>
        `;
        return;
    }
    
    resultsContainer.innerHTML = results.map(movie => `
        <div class="search-result-item" onclick="selectMovie('${movie.name}')">
            <div class="search-result-poster">
                <i class="fas fa-film" style="font-size: 60px; color: #ff6b35; display: flex; align-items: center; justify-content: center; height: 100%;"></i>
            </div>
            <div class="search-result-name">${movie.name}</div>
            <div class="search-result-info">${movie.genre} - ${movie.rating}/10</div>
            <button style="background: #ff6b35; color: white; padding: 8px 15px; border-radius: 5px; margin-top: 10px; font-size: 12px; width: 100%;">
                Mua vé
            </button>
        </div>
    `).join('');
}

function selectMovie(movieName) {
    alert('Bạn sẽ mua vé xem: ' + movieName);
}

// ===== NAVIGATION =====
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Set first nav link as active by default
    if (navLinks.length > 0) {
        navLinks[0].classList.add('active');
    }
}

// ===== BOOKING BUTTONS =====
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

function handleBooking(e) {
    e.preventDefault();
    // Lấy tên phim từ card hoặc banner
    let movieName = 'Phí Phông: Quỷ Máu Rừng Thiêng';
    
    if (this.closest('.movie-card')) {
        movieName = this.closest('.movie-card').querySelector('.movie-title').textContent;
    }
    
    bookingState.selectedMovie = movieName;
    document.getElementById('bookingMovieName').textContent = movieName;
    
    openModal(document.getElementById('bookingModal'));
    initSeatMap();
}

// ===== MOVIE CARDS =====
function initMovieCards() {
    const trailerBtns = document.querySelectorAll('.btn-trailer');
    trailerBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            playTrailer();
        });
    });
    
    // Lazy load animation
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
}

function playTrailer() {
    alert('Trailer sẽ được phát trong một cửa sổ mới!');
}

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && !href.includes('Modal')) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    });
});

// ===== UTILITY FUNCTIONS =====
function updateFooterYear() {
    const yearElement = document.querySelector('.footer-copyright');
    if (yearElement) {
        const currentYear = new Date().getFullYear();
        yearElement.innerHTML = `<p>&copy; ${currentYear} Cinema Group 11. Bảo lưu mọi quyền.</p>`;
    }
}

updateFooterYear();

// ===== INITIALIZATION LOG =====
console.log('Cinema Group 11 - Home Page Initialized');
console.log('Ready to handle user interactions');

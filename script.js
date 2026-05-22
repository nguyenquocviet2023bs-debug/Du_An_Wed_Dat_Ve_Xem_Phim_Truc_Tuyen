// ===== SESSION MANAGEMENT =====
let isLoggedIn = false;
let currentUser = null;

// Check if user is logged in from server session
async function checkUserSession() {
    try {
        const formData = new FormData();
        formData.append('action', 'checkSession');
        
        const response = await fetch('logicDB.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.isLoggedIn) {
            isLoggedIn = true;
            currentUser = data.user;
            showMainContent();
        } else {
            isLoggedIn = false;
            currentUser = null;
            showLoginModal();
        }
    } catch (error) {
        console.error('Error checking session:', error);
        showLoginModal();
    }
}

// Show main content
function showMainContent() {
    document.getElementById('mainContent').style.display = 'block';
    document.getElementById('loginModal').classList.remove('active');
    document.getElementById('signupModal').classList.remove('active');
    document.getElementById('logoutBtn').style.display = 'block';
}

// Show login modal
function showLoginModal() {
    document.getElementById('mainContent').style.display = 'none';
    document.getElementById('loginModal').classList.add('active');
    document.getElementById('logoutBtn').style.display = 'none';
}

// Logout
function handleLogout() {
    // Gửi yêu cầu logout tới server
    const formData = new FormData();
    formData.append('action', 'logout');
    
    fetch('logicDB.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Xóa thông tin user từ localStorage
        localStorage.removeItem('currentUser');
        isLoggedIn = false;
        currentUser = null;
        showLoginModal();
        alert(data.message);
    })
    .catch(error => {
        console.error('Lỗi:', error);
        // Nếu có lỗi, vẫn xóa localStorage
        localStorage.removeItem('currentUser');
        isLoggedIn = false;
        currentUser = null;
        showLoginModal();
    });
}

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



// Bố cục ghế (6 hàng x 15 cột)
const seatLayout = [
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
    ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
];

// Lưu trữ ghế đã đặt cho mỗi phòng: "movieName|date|showtime" => []
const bookedSeatsByRoom = {};

// Hàm để lấy key phòng (định danh duy nhất cho mỗi phòng)
function getRoomKey(movieName, date, showtime) {
    return `${movieName}|${date}|${showtime}`;
}

// Hàm để lấy danh sách ghế đã đặt của một phòng
function getBookedSeatsForRoom(movieName, date, showtime) {
    const key = getRoomKey(movieName, date, showtime);
    if (!bookedSeatsByRoom[key]) {
        bookedSeatsByRoom[key] = [];
    }
    return bookedSeatsByRoom[key];
}

document.addEventListener('DOMContentLoaded', function() {
    // Check user session first
    checkUserSession();
    
    // Initialize
    initNavigation();
    initBookingButtons();
    initMovieCards();
    initSearchbar();
    initModals();
    initFormHandlers();
    initBookingModal();
    
    // Setup logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                handleLogout();
            }
        });
    }
});

// ===== MODAL MANAGEMENT =====
function initModals() {
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    const searchModal = document.getElementById('searchResultsModal');
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
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
    const openForgotPasswordBtn = document.getElementById('openForgotPasswordBtn');
    const openLoginBtn3 = document.getElementById('openLoginBtn3');
    
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
    
    // Forgot password modal
    if (openForgotPasswordBtn) {
        openForgotPasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(loginModal);
            openModal(forgotPasswordModal);
        });
    }
    
    if (openLoginBtn3) {
        openLoginBtn3.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(forgotPasswordModal);
            openModal(loginModal);
        });
    }
    
    const openLoginBtn4 = document.getElementById('openLoginBtn4');
    if (openLoginBtn4) {
        openLoginBtn4.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(document.getElementById('resetPasswordModal'));
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
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignupSubmit);
    }
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', handleForgotPasswordSubmit);
    }
    
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', handleResetPasswordSubmit);
    }
}

function handleLoginSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();
    
    if (!username || !password) {
        alert('Vui lòng điền đầy đủ thông tin!');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('username', username);
    formData.append('password', password);
    
    fetch('logicDB.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đăng nhập thành công!');
            document.getElementById('loginForm').reset();
            isLoggedIn = true;
            showMainContent();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

function handleSignupSubmit(e) {
    e.preventDefault();
    const password = document.getElementById('signupPassword').value.trim();
    const confirmPassword = document.getElementById('signupConfirmPassword').value.trim();
    
    if (password !== confirmPassword) {
        alert('Mật khẩu không trùng khớp!');
        return;
    }
    
    const name = document.getElementById('signupName').value.trim();
    const email = document.getElementById('signupEmail').value.trim();
    const phone = document.getElementById('signupPhone')?.value.trim() || '';
    
    if (!name || !email || !phone || !password) {
        alert('Vui lòng điền đầy đủ thông tin!');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'signup');
    formData.append('ho_ten', name);
    formData.append('email', email);
    formData.append('dien_thoai', phone);
    formData.append('password', password);
    
    fetch('logicDB.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đăng ký thành công! Chào mừng bạn');
            document.getElementById('signupForm').reset();
            isLoggedIn = true;
            
            // Close signup modal and show main content
            document.getElementById('signupModal').classList.remove('active');
            showMainContent();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại!');
    });
}

function handleForgotPasswordSubmit(e) {
    e.preventDefault();
    const email = document.getElementById('forgotEmail').value.trim();
    
    if (!email) {
        alert('Vui lòng nhập email hoặc tên đăng nhập!');
        return;
    }
    
    // Chuyển sang modal đặt lại mật khẩu
    document.getElementById('forgotPasswordModal').classList.remove('active');
    // Lưu email vào input hidden để dùng ở form reset
    document.getElementById('resetPasswordForm').dataset.emailOrPhone = email;
    openModal(document.getElementById('resetPasswordModal'));
}

function handleResetPasswordSubmit(e) {
    e.preventDefault();
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmNewPassword = document.getElementById('confirmNewPassword').value.trim();
    const emailOrPhone = document.getElementById('resetPasswordForm').dataset.emailOrPhone;
    
    if (!newPassword || !confirmNewPassword) {
        alert('Vui lòng điền đầy đủ mật khẩu!');
        return;
    }
    
    if (newPassword !== confirmNewPassword) {
        alert('Mật khẩu không trùng khớp!');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Mật khẩu phải có ít nhất 6 ký tự!');
        return;
    }
    
    // Gửi yêu cầu reset password lên server
    const formData = new FormData();
    formData.append('action', 'resetPassword');
    formData.append('email_or_phone', emailOrPhone);
    formData.append('new_password', newPassword);
    
    fetch('logicDB.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đặt lại mật khẩu thành công!\n\nBây giờ bạn có thể đăng nhập với mật khẩu mới.');
            document.getElementById('resetPasswordForm').reset();
            document.getElementById('resetPasswordModal').classList.remove('active');
            
            // Mở lại modal đăng nhập
            setTimeout(() => {
                openModal(document.getElementById('loginModal'));
            }, 500);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại!');
    });
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
        
        // Xác nhận đặt vé
        handleBookingConfirm();
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
            // Refresh seat map khi thay đổi giờ chiếu
            resetSeatSelection();
            initSeatMap();
        });
        
        showtimeList.appendChild(btn);
    });
}



function initSeatMap() {
    const seatMap = document.getElementById('seatMap');
    seatMap.innerHTML = '';
    seatMap.style.gridTemplateColumns = `repeat(15, 1fr)`;
    
    const rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    
    // Lấy danh sách ghế đã đặt của phòng hiện tại
    const bookedSeatsForCurrentRoom = getBookedSeatsForRoom(
        bookingState.selectedMovie,
        bookingState.selectedDate,
        bookingState.selectedShowtime
    );
    
    seatLayout.forEach((row, rowIndex) => {
        row.forEach((seatType, colIndex) => {
            const seatName = `${rows[rowIndex]}${colIndex + 1}`;
            const seatDiv = document.createElement('div');
            seatDiv.className = 'seat';
            
            if (seatType === 'E') {
                seatDiv.className = 'seat seat-empty';
                seatDiv.textContent = '';
            } else if (bookedSeatsForCurrentRoom.includes(seatName)) {
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
        <div class="search-result-item">
            <div class="search-result-poster">
                <i class="fas fa-film" style="font-size: 60px; color: #ff6b35; display: flex; align-items: center; justify-content: center; height: 100%;"></i>
            </div>
            <div class="search-result-name">${movie.name}</div>
            <div class="search-result-info">${movie.genre} - ${movie.rating}/10</div>
            <button class="search-result-book-btn" data-movie-name="${movie.name}" style="background: #ff6b35; color: white; padding: 8px 15px; border-radius: 5px; margin-top: 10px; font-size: 12px; width: 100%;">
                Mua vé
            </button>
        </div>
    `).join('');
    
    // Attach click handlers to all "Mua vé" buttons in search results
    document.querySelectorAll('.search-result-book-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const movieName = this.getAttribute('data-movie-name');
            handleSearchResultBooking(movieName);
        });
    });
}

function handleSearchResultBooking(movieName) {
    // Close search results modal
    document.getElementById('searchResultsModal').classList.remove('active');
    
    // Set selected movie and open booking modal
    bookingState.selectedMovie = movieName;
    document.getElementById('bookingMovieName').textContent = movieName;
    
    openModal(document.getElementById('bookingModal'));
    
    // Initialize seat map and showtimes
    setTimeout(() => {
        initSeatMap();
        const today = new Date().toISOString().split('T')[0];
        populateShowtimes(today);
    }, 100);
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
    
    // Đảm bảo seat map và showtime được load
    setTimeout(() => {
        initSeatMap();
        const today = new Date().toISOString().split('T')[0];
        populateShowtimes(today);
    }, 100);
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

// ===== BOOKING CONFIRMATION =====
function handleBookingConfirm() {
    if (!isLoggedIn) {
        alert('Vui lòng đăng nhập để đặt vé!');
        openModal(document.getElementById('loginModal'));
        return;
    }
    
    const totalPrice = bookingState.selectedSeats.length * bookingState.pricePerSeat;
    const seatsStr = bookingState.selectedSeats.join(', ');
    
    const formData = new FormData();
    formData.append('action', 'booking');
    formData.append('ten_phim', bookingState.selectedMovie);
    formData.append('ngay_chieu', bookingState.selectedDate);
    formData.append('gio_chieu', bookingState.selectedShowtime);
    formData.append('so_ghe', seatsStr);
    formData.append('gia_ve', totalPrice);
    
    fetch('logicDB.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Đặt vé thành công!\n\nPhim: ${bookingState.selectedMovie}\nNgày chiếu: ${bookingState.selectedDate}\nGiờ chiếu: ${bookingState.selectedShowtime}\nGhế: ${seatsStr}\nTổng tiền: ${totalPrice.toLocaleString()} VND`);
            
            // Thêm ghế vào danh sách đã đặt
            const bookedSeatsForCurrentRoom = getBookedSeatsForRoom(
                bookingState.selectedMovie,
                bookingState.selectedDate,
                bookingState.selectedShowtime
            );
            
            bookingState.selectedSeats.forEach(seat => {
                if (!bookedSeatsForCurrentRoom.includes(seat)) {
                    bookedSeatsForCurrentRoom.push(seat);
                }
            });
            
            // Đóng modal và reset
            document.getElementById('bookingModal').classList.remove('active');
            resetBookingState();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại!');
    });
}

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

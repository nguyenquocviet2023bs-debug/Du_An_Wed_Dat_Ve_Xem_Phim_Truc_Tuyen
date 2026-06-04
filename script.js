let isLoggedIn = false;
let currentUser = null;

function updateAdminNavLink(data) {
    const el = document.getElementById('navAdminLink');
    if (!el) return;
    el.style.display = data && data.isLoggedIn && data.user && data.user.is_admin ? '' : 'none';
}

async function checkUserSession() {
    try {
        const formData = new FormData();
        formData.append('action', 'checkSession');
        
        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.isLoggedIn) {
            isLoggedIn = true;
            currentUser = data.user;
            updateAdminNavLink(data);
            showMainContent();
        } else {
            isLoggedIn = false;
            currentUser = null;
            updateAdminNavLink(null);
            showLoginModal();
        }
    } catch (error) {
        updateAdminNavLink(null);
        showLoginModal();
    }
}

function showMainContent() {
    document.getElementById('mainContent').style.display = 'block';
    document.getElementById('loginModal').classList.remove('active');
    document.getElementById('signupModal').classList.remove('active');
    document.getElementById('logoutBtn').style.display = 'block';
    if (typeof loadMoviesFromDatabase === 'function') loadMoviesFromDatabase();
}

function showLoginModal() {
    document.getElementById('mainContent').style.display = 'none';
    document.getElementById('loginModal').classList.add('active');
    document.getElementById('logoutBtn').style.display = 'none';
}

function handleLogout() {
    const formData = new FormData();
    formData.append('action', 'logout');
    
    fetch('logicDB.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        localStorage.removeItem('currentUser');
        isLoggedIn = false;
        currentUser = null;
        updateAdminNavLink(null);
        showLoginModal();
        alert(data.message);
    })
    .catch(error => {
        localStorage.removeItem('currentUser');
        isLoggedIn = false;
        currentUser = null;
        updateAdminNavLink(null);
        showLoginModal();
    });
}

const moviesData = [
    { id: 1, name: 'Heo 5 Móng', genre: 'Kinh dị' },
    { id: 2, name: 'Trùm Sò', genre: 'Hài' },
    { id: 3, name: 'Phí Phông: Quỷ Máu Rừng Thiêng', genre: 'Kinh dị' },
    { id: 4, name: 'Đại Tiệc Trăng Máu 8', genre: 'Hành động' },
    { id: 5, name: 'Anh Hùng', genre: 'Tâm lý' },
    { id: 6, name: 'Super Mario Thiên Hà', genre: 'Hoạt hình' },
    { id: 7, name: 'Shin Cậu Bé Búp Chì', genre: 'Hoạt hình' },
];

const showtimesData = [
    { time: '09:00', type: 'normal' },
    { time: '11:30', type: 'normal' },
    { time: '14:30', type: 'normal' },
    { time: '17:10', type: 'normal' },
    { time: '19:30', type: 'normal' },
    { time: '20:35', type: 'normal' },
    { time: '22:00', type: 'normal' }
];

let bookingState = {
    selectedMovie: null,
    selectedDate: null,
    selectedShowtime: null,
    selectedSeats: [],
    pricePerSeat: 50000
};

const seatLayout = [
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
    ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
    ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
];

const bookedSeatsByRoom = {};

function getRoomKey(movieName, date, showtime) {
    return `${movieName}|${date}|${showtime}`;
}

function getBookedSeatsForRoom(movieName, date, showtime) {
    const key = getRoomKey(movieName, date, showtime);
    if (!bookedSeatsByRoom[key]) bookedSeatsByRoom[key] = [];
    return bookedSeatsByRoom[key];
}

async function fetchBookedSeatsFromServer(movieName, date, showtime) {
    if (!movieName || !date || !showtime) return [];
    try {
        const formData = new FormData();
        formData.append('action', 'getBookedSeats');
        formData.append('ten_phim', movieName);
        formData.append('ngay_chieu', date);
        formData.append('gio_chieu', showtime);

        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success && Array.isArray(data.seats)) {
            const key = getRoomKey(movieName, date, showtime);
            bookedSeatsByRoom[key] = data.seats;
            return data.seats;
        }
    } catch (error) {}
    return getBookedSeatsForRoom(movieName, date, showtime);
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.AuthLogin) {
        window.AuthLogin.onSuccess = function () {
            checkUserSession();
        };
    }

    initNavigation();
    initBookingButtons();
    initMovieCards();
    initSearchbar();
    initModals();
    initFormHandlers();
    initBookingModal();
    
    document.addEventListener('click', function(e) {
        var card = e.target.closest('.movie-card');
        if (card && !e.target.closest('.btn-trailer')) {
            var btn = card.querySelector('.btn-book-movie');
            if (btn) {
                e.preventDefault();
                handleBooking.call(btn, e);
            }
        }
    });
    
    checkUserSession();
    
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Bạn có chắc chắn muốn đăng xuất?')) handleLogout();
        });
    }
});

function initModals() {
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    const searchModal = document.getElementById('searchResultsModal');
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const loginBtn = document.getElementById('openLoginBtn');
    const closeButtons = document.querySelectorAll('.close');
    
    if (loginBtn) {
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(loginModal);
        });
    }
    
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('active');
        });
    });
    
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });
    
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

function initFormHandlers() {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    
    if (signupForm) signupForm.addEventListener('submit', handleSignupSubmit);
    if (forgotPasswordForm) forgotPasswordForm.addEventListener('submit', handleForgotPasswordSubmit);
    if (resetPasswordForm) resetPasswordForm.addEventListener('submit', handleResetPasswordSubmit);
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
    const birthday = document.getElementById('signupBirthday')?.value.trim() || '';
    
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
    if (birthday) formData.append('ngay_sinh', birthday);
    
    fetch('logicDB.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đăng ký thành công! Chào mừng bạn');
            document.getElementById('signupForm').reset();
            isLoggedIn = true;
            document.getElementById('signupModal').classList.remove('active');
            showMainContent();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
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
    
    document.getElementById('forgotPasswordModal').classList.remove('active');
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
    
    const formData = new FormData();
    formData.append('action', 'resetPassword');
    formData.append('email_or_phone', emailOrPhone);
    formData.append('new_password', newPassword);
    
    fetch('logicDB.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đặt lại mật khẩu thành công!\n\nBây giờ bạn có thể đăng nhập với mật khẩu mới.');
            document.getElementById('resetPasswordForm').reset();
            document.getElementById('resetPasswordModal').classList.remove('active');
            setTimeout(() => openModal(document.getElementById('loginModal')), 500);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Có lỗi xảy ra, vui lòng thử lại!');
    });
}

function initBookingModal() {
    const showDateInput = document.getElementById('showDate');
    const confirmBtn = document.getElementById('confirmBooking');
    
    const today = new Date().toISOString().split('T')[0];
    showDateInput.min = today;
    showDateInput.value = today;
    
    populateShowtimes(today);
    bookingState.selectedDate = today;

    showDateInput.addEventListener('change', function() {
        bookingState.selectedDate = this.value;
        bookingState.selectedShowtime = null;
        populateShowtimes(this.value);
        resetSeatSelection();
        initSeatMap();
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
            resetSeatSelection();
            fetchBookedSeatsFromServer(
                bookingState.selectedMovie,
                bookingState.selectedDate,
                bookingState.selectedShowtime
            ).then(() => initSeatMap());
        });
        
        showtimeList.appendChild(btn);
    });
}

function initSeatMap() {
    const seatMap = document.getElementById('seatMap');
    if (!seatMap) return;

    if (!bookingState.selectedMovie || !bookingState.selectedDate || !bookingState.selectedShowtime) {
        seatMap.innerHTML = '<p style="color:#999;padding:20px;text-align:center;">Vui lòng chọn ngày và giờ chiếu</p>';
        return;
    }

    seatMap.innerHTML = '<p style="color:#999;padding:20px;text-align:center;">Đang tải sơ đồ ghế...</p>';
    seatMap.style.gridTemplateColumns = `repeat(15, 1fr)`;
    
    const rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    
    const bookedSeatsForCurrentRoom = getBookedSeatsForRoom(
        bookingState.selectedMovie,
        bookingState.selectedDate,
        bookingState.selectedShowtime
    );

    seatMap.innerHTML = '';
    
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

function initSearchbar() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performSearch();
        });
    }
    
    if (searchBtn) searchBtn.addEventListener('click', performSearch);
}

function performSearch() {
    const searchInput = document.querySelector('.search-input');
    const query = searchInput.value.toLowerCase().trim();
    
    if (!query) {
        alert('Vui lòng nhập tên phim để tìm kiếm!');
        return;
    }
    
    const results = moviesData.filter(movie => movie.name.toLowerCase().includes(query));
    displaySearchResults(results, query);
    openModal(document.getElementById('searchResultsModal'));
}

function displaySearchResults(results, query) {
    const resultsContainer = document.getElementById('searchResults');
    
    if (results.length === 0) {
        resultsContainer.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 30px;"><p style="color: #999; font-size: 16px;">Không tìm thấy phim nào với tên "<strong>${query}</strong>"</p></div>`;
        return;
    }
    
    resultsContainer.innerHTML = results.map(movie => `
        <div class="search-result-item">
            <div class="search-result-poster">
                ${movie.image 
                    ? `<img src="${movie.image}" alt="${movie.name}" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><i class="fas fa-film" style="display:none; font-size: 60px; color: #ff6b35; height: 100%; align-items: center; justify-content: center;"></i>`
                    : `<i class="fas fa-film" style="font-size: 60px; color: #ff6b35; display: flex; align-items: center; justify-content: center; height: 100%;"></i>`
                }
            </div>
            <div class="search-result-name">${movie.name}</div>
            <div class="search-result-info">${movie.genre}</div>
            <button class="search-result-book-btn" data-movie-name="${movie.name}" style="background: #ff6b35; color: white; padding: 8px 15px; border-radius: 5px; margin-top: 10px; font-size: 12px; width: 100%;">Mua vé</button>
        </div>
    `).join('');
    
    document.querySelectorAll('.search-result-book-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const movieName = this.getAttribute('data-movie-name');
            handleSearchResultBooking(movieName);
        });
    });
}

function handleSearchResultBooking(movieName) {
    document.getElementById('searchResultsModal').classList.remove('active');
    bookingState.selectedMovie = movieName;
    document.getElementById('bookingMovieName').textContent = movieName;
    openModal(document.getElementById('bookingModal'));
    setTimeout(() => {
        const today = new Date().toISOString().split('T')[0];
        bookingState.selectedDate = today;
        const showDateInput = document.getElementById('showDate');
        if (showDateInput) showDateInput.value = today;
        populateShowtimes(today);
        initSeatMap();
    }, 100);
}

function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPage = window.location.pathname.split('/').pop() || 'TrangChu.php';
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '#') {
            if (href === currentPage || (currentPage === '' && href === 'TrangChu.php')) {
                link.classList.add('active');
            }
        } else {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        }
    });
}

function initBookingButtons() {
    // Event delegation handled globally via document click
}

function openBookingForMovie(movieName) {
    bookingState.selectedMovie = movieName;
    document.getElementById('bookingMovieName').textContent = movieName;
    openModal(document.getElementById('bookingModal'));
    setTimeout(() => {
        const today = new Date().toISOString().split('T')[0];
        bookingState.selectedDate = today;
        const showDateInput = document.getElementById('showDate');
        if (showDateInput) showDateInput.value = today;
        populateShowtimes(today);
        initSeatMap();
    }, 100);
}

function handleBooking(e) {
    e.preventDefault();
    let movieName = 'Phí Phông: Quỷ Máu Rừng Thiêng';
    
    if (this.closest('.movie-card')) {
        movieName = this.closest('.movie-card').querySelector('.movie-title').textContent;
    }
    
    openBookingForMovie(movieName);
}

function initMovieCards() {
    const trailerBtns = document.querySelectorAll('.btn-trailer');
    trailerBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Trailer sẽ được phát trong một cửa sổ mới!');
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
}

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && !href.includes('Modal')) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});

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
    
    fetch('logicDB.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Đặt vé thành công!\n\nPhim: ${bookingState.selectedMovie}\nNgày chiếu: ${bookingState.selectedDate}\nGiờ chiếu: ${bookingState.selectedShowtime}\nGhế: ${seatsStr}\nTổng tiền: ${totalPrice.toLocaleString()} VND`);
            
            const bookedSeatsForCurrentRoom = getBookedSeatsForRoom(
                bookingState.selectedMovie,
                bookingState.selectedDate,
                bookingState.selectedShowtime
            );
            
            bookingState.selectedSeats.forEach(seat => {
                if (!bookedSeatsForCurrentRoom.includes(seat)) bookedSeatsForCurrentRoom.push(seat);
            });
            
            document.getElementById('bookingModal').classList.remove('active');
            resetBookingState();
        } else {
            alert(data.message || 'Không thể đặt vé!');
            fetchBookedSeatsFromServer(
                bookingState.selectedMovie,
                bookingState.selectedDate,
                bookingState.selectedShowtime
            ).then(() => initSeatMap());
        }
    })
    .catch(error => {
        alert('Có lỗi xảy ra, vui lòng thử lại!');
    });
}

function updateFooterYear() {
    const yearElement = document.querySelector('.footer-copyright');
    if (yearElement) {
        const currentYear = new Date().getFullYear();
        yearElement.innerHTML = `<p>&copy; ${currentYear} Cinema Group 11. Bảo lưu mọi quyền.</p>`;
    }
}

updateFooterYear();

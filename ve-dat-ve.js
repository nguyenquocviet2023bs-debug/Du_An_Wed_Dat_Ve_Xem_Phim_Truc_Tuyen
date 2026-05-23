let isLoggedIn = false;

async function checkUserSession() {
    try {
        const formData = new FormData();
        formData.append('action', 'checkSession');

        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.isLoggedIn) {
            isLoggedIn = true;
            showMainContent();
            loadBookings();
        } else {
            showLoginModal();
        }
    } catch (error) {
        console.error('Error checking session:', error);
        showLoginModal();
    }
}

function showMainContent() {
    document.getElementById('mainContent').style.display = 'block';
    document.getElementById('loginModal').classList.remove('active');
}

function showLoginModal() {
    document.getElementById('mainContent').style.display = 'none';
    document.getElementById('loginModal').classList.add('active');
}

function handleLogout() {
    const formData = new FormData();
    formData.append('action', 'logout');

    fetch('logicDB.php', { method: 'POST', body: formData })
        .then(() => {
            isLoggedIn = false;
            showLoginModal();
        });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return dateStr.split(' ')[0].split('T')[0];
}

function formatTime(timeStr) {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
}

function formatPrice(price) {
    const num = parseInt(price, 10) || 0;
    return num.toLocaleString('vi-VN') + ' VND';
}

async function loadBookings() {
    const container = document.getElementById('ticketsList');
    container.innerHTML = '<p class="tickets-loading">Đang tải danh sách vé...</p>';

    const formData = new FormData();
    formData.append('action', 'getBookings');

    try {
        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (!data.success) {
            container.innerHTML = `<p class="tickets-empty">${data.message || 'Không thể tải vé.'}</p>`;
            return;
        }

        if (!data.bookings || data.bookings.length === 0) {
            container.innerHTML = `
                <div class="tickets-empty">
                    <i class="fas fa-ticket-alt"></i>
                    <p>Bạn chưa đặt vé nào.</p>
                    <a href="TrangChu.php" class="btn-view-all">Đặt vé ngay</a>
                </div>`;
            return;
        }

        container.innerHTML = data.bookings.map(renderTicketCard).join('');
        bindTicketEvents();
    } catch (error) {
        container.innerHTML = '<p class="tickets-empty">Lỗi kết nối. Vui lòng thử lại.</p>';
    }
}

function renderTicketCard(booking) {
    const canEditDatetime = booking.can_edit_datetime;
    const hoursLeft = booking.hours_remaining_edit;
    const statusClass = canEditDatetime ? 'status-editable' : 'status-locked';
    const statusText = canEditDatetime
        ? `Còn ${formatHoursRemaining(hoursLeft)} để sửa ngày/giờ`
        : 'Đã quá 5 giờ — không sửa được ngày/giờ';

    return `
        <article class="ticket-card" data-id="${booking.id}">
            <div class="ticket-card-header">
                <h3 class="ticket-movie">${escapeHtml(booking.ten_phim_dat)}</h3>
                <span class="ticket-status ${statusClass}">${statusText}</span>
            </div>
            <div class="ticket-meta">
                <span><i class="fas fa-clock"></i> Đặt lúc: ${formatDateTime(booking.ngay_dat_ve)}</span>
                <span><i class="fas fa-money-bill"></i> ${formatPrice(booking.gia_ve)}</span>
            </div>
            <form class="ticket-edit-form" data-booking-id="${booking.id}">
                <div class="ticket-fields">
                    <div class="ticket-field">
                        <label>Ngày chiếu</label>
                        <input type="date" name="ngay_chieu" value="${formatDate(booking.ngay_chieu)}"
                            ${canEditDatetime ? '' : 'disabled readonly'} class="ticket-input">
                    </div>
                    <div class="ticket-field">
                        <label>Giờ chiếu</label>
                        <input type="time" name="gio_chieu" value="${formatTime(booking.gio_chieu)}"
                            ${canEditDatetime ? '' : 'disabled readonly'} class="ticket-input">
                    </div>
                    <div class="ticket-field ticket-field-wide">
                        <label>Ghế đã chọn <span class="ticket-fixed-label">(cố định)</span></label>
                        <input type="text" name="so_ghe" value="${escapeHtml(booking.so_ghe || '')}"
                            class="ticket-input ticket-input-fixed" disabled readonly>
                    </div>
                </div>
                ${!canEditDatetime ? '<p class="ticket-lock-note"><i class="fas fa-lock"></i> Ngày và giờ chiếu đã bị khóa sau 5 giờ kể từ lúc đặt vé.</p>' : ''}
                ${canEditDatetime ? `<button type="submit" class="btn-submit btn-save-ticket">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>` : ''}
            </form>
        </article>`;
}

function formatHoursRemaining(hours) {
    if (hours < 1) {
        const mins = Math.ceil(hours * 60);
        return mins <= 1 ? '1 phút' : `${mins} phút`;
    }
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    if (m === 0) return `${h} giờ`;
    return `${h} giờ ${m} phút`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function bindTicketEvents() {
    document.querySelectorAll('.ticket-edit-form').forEach(form => {
        form.addEventListener('submit', handleUpdateBooking);
    });
}

async function handleUpdateBooking(e) {
    e.preventDefault();
    const form = e.target;
    const bookingId = form.closest('.ticket-card').dataset.id;
    const dateInput = form.querySelector('[name="ngay_chieu"]');
    const timeInput = form.querySelector('[name="gio_chieu"]');

    if (dateInput.disabled || timeInput.disabled) {
        alert('Đã quá 5 giờ. Không thể sửa ngày và giờ chiếu!');
        return;
    }

    const ngayChieu = dateInput.value;
    const gioChieu = timeInput.value;

    if (!ngayChieu || !gioChieu) {
        alert('Vui lòng chọn ngày và giờ chiếu!');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'updateBooking');
    formData.append('booking_id', bookingId);
    formData.append('ngay_chieu', ngayChieu);
    formData.append('gio_chieu', gioChieu);

    const btn = form.querySelector('.btn-save-ticket');
    btn.disabled = true;

    try {
        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            alert(data.message);
            loadBookings();
        } else {
            alert(data.message || 'Cập nhật thất bại!');
        }
    } catch (error) {
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.AuthLogin) {
        window.AuthLogin.onSuccess = function () {
            isLoggedIn = true;
            showMainContent();
            loadBookings();
        };
    }

    checkUserSession();

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                handleLogout();
            }
        });
    }

    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.modal').classList.remove('active');
        });
    });
});

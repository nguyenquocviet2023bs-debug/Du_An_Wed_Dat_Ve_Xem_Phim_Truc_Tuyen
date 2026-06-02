let isLoggedIn = false;

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
            updateAdminNavLink(data);
            showMainContent();
            loadBookings();
        } else {
            updateAdminNavLink(null);
            showLoginModal();
        }
    } catch (error) {
        console.error('Error checking session:', error);
        updateAdminNavLink(null);
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
            updateAdminNavLink(null);
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

function getFormMovieName(form) {
    const card = form.closest('.ticket-card');
    return card?.querySelector('.ticket-movie')?.textContent?.trim() || '';
}

function updateSeatLabels(form, seats, price) {
    const label = form.querySelector('.selected-seats-label');
    const hidden = form.querySelector('[name="so_ghe"]');
    const priceEl = form.querySelector('.ticket-price-preview');
    const str = SeatMapCommon.seatsToString(seats);

    if (label) {
        label.textContent = str || '(chưa chọn ghế)';
        label.classList.toggle('text-warning', seats.length === 0);
    }
    if (hidden) hidden.value = str;
    if (priceEl) priceEl.textContent = formatPrice(price);
}

function hideSeatPicker(form) {
    const picker = form.querySelector('.ticket-seat-picker');
    const btn = form.querySelector('.btn-change-seat');
    if (picker) picker.style.display = 'none';
    if (btn) btn.style.display = '';
    form.dataset.seatPickerOpen = '0';
}

function showSeatPicker(form) {
    const picker = form.querySelector('.ticket-seat-picker');
    const btn = form.querySelector('.btn-change-seat');
    if (picker) picker.style.display = 'block';
    if (btn) btn.style.display = 'none';
    form.dataset.seatPickerOpen = '1';
}

function isDatetimeChanged(form) {
    const date = form.querySelector('[name="ngay_chieu"]')?.value;
    const time = form.querySelector('[name="gio_chieu"]')?.value;
    return date !== (form.dataset.originalDate || '') ||
        time !== (form.dataset.originalTime || '');
}

async function refreshEditSeatMap(form, keepSelection) {
    const mapBox = form.querySelector('.edit-seat-map-box');
    if (!mapBox || !window.SeatMapCommon) return;

    const date = form.querySelector('[name="ngay_chieu"]')?.value;
    const time = form.querySelector('[name="gio_chieu"]')?.value;
    const movie = getFormMovieName(form);
    const hidden = form.querySelector('[name="so_ghe"]');

    let selected = keepSelection
        ? SeatMapCommon.parseSeatString(hidden?.value)
        : SeatMapCommon.parseSeatString(form.dataset.originalSeats || hidden?.value);

    await SeatMapCommon.renderEditMap(mapBox, {
        movie,
        date,
        time,
        selectedSeats: selected,
        onChange(seats, price) {
            updateSeatLabels(form, seats, price);
            const warn = form.querySelector('.ticket-seat-warning');
            if (warn && seats.length > 0) warn.style.display = 'none';
        }
    });
}

/** Kiểm tra suất mới: chỉ mở sơ đồ ghế khi trùng ghế hoặc user bấm đổi ghế */
async function onDatetimeChange(form) {
    const date = form.querySelector('[name="ngay_chieu"]')?.value;
    const time = form.querySelector('[name="gio_chieu"]')?.value;
    const hidden = form.querySelector('[name="so_ghe"]');
    const originalSeats = SeatMapCommon.parseSeatString(form.dataset.originalSeats);

    if (!isDatetimeChanged(form)) {
        hideSeatPicker(form);
        if (hidden) hidden.value = SeatMapCommon.seatsToString(originalSeats);
        updateSeatLabels(form, originalSeats, originalSeats.length * SeatMapCommon.pricePerSeat);
        const warn = form.querySelector('.ticket-seat-warning');
        if (warn) warn.style.display = 'none';
        return;
    }

    if (!date || !time) return;

    const movie = getFormMovieName(form);
    const booked = await SeatMapCommon.fetchBookedSeats(movie, date, time);
    const conflict = originalSeats.filter(s => booked.includes(s));

    if (conflict.length > 0) {
        const warn = form.querySelector('.ticket-seat-warning');
        if (warn) {
            warn.style.display = 'block';
            warn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Ghế ' +
                escapeHtml(conflict.join(', ')) +
                ' đã có người đặt ở suất này. Vui lòng chọn ghế trống bên dưới.';
        }
        showSeatPicker(form);
        if (hidden) hidden.value = '';
        updateSeatLabels(form, [], 0);
        await refreshEditSeatMap(form, false);
    } else {
        hideSeatPicker(form);
        if (hidden) hidden.value = SeatMapCommon.seatsToString(originalSeats);
        updateSeatLabels(form, originalSeats, originalSeats.length * SeatMapCommon.pricePerSeat);
        const warn = form.querySelector('.ticket-seat-warning');
        if (warn) warn.style.display = 'none';
    }
}

function renderTicketCard(booking) {
    const canEditDatetime = booking.can_edit_datetime;
    const hoursLeft = booking.hours_remaining_edit;
    const statusClass = canEditDatetime ? 'status-editable' : 'status-locked';
    const statusText = canEditDatetime
        ? `Còn ${formatHoursRemaining(hoursLeft)} để sửa ngày/giờ`
        : 'Đã quá 1 giờ — không sửa được ngày/giờ';

    const seatSection = canEditDatetime ? `
                <div class="ticket-seat-edit">
                    <div class="ticket-seat-display">
                        <p class="ticket-seat-selected">
                            <i class="fas fa-chair"></i> Ghế: <strong class="selected-seats-label">${escapeHtml(booking.so_ghe || '')}</strong>
                            — <span class="ticket-price-preview">${formatPrice(booking.gia_ve)}</span>
                        </p>
                        <button type="button" class="btn-change-seat">
                            <i class="fas fa-exchange-alt"></i> Thay đổi ghế
                        </button>
                    </div>
                    <div class="ticket-seat-picker" style="display:none;">
                        <p class="ticket-seat-warning" style="display:none;"></p>
                        <div class="edit-seat-map-box"></div>
                    </div>
                    <input type="hidden" name="so_ghe" value="${escapeHtml(booking.so_ghe || '')}">
                </div>` : `
                <div class="ticket-field ticket-field-wide">
                    <label>Ghế đã chọn</label>
                    <input type="text" value="${escapeHtml(booking.so_ghe || '')}"
                        class="ticket-input ticket-input-fixed" disabled readonly>
                </div>`;

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
            <form class="ticket-edit-form" data-booking-id="${booking.id}"
                data-original-seats="${escapeHtml(booking.so_ghe || '')}"
                data-original-date="${formatDate(booking.ngay_chieu)}"
                data-original-time="${formatTime(booking.gio_chieu)}">
                <div class="ticket-fields">
                    <div class="ticket-field">
                        <label>Ngày chiếu</label>
                        <input type="date" name="ngay_chieu" value="${formatDate(booking.ngay_chieu)}"
                            ${canEditDatetime ? '' : 'disabled readonly'} class="ticket-input ticket-date-input">
                    </div>
                    <div class="ticket-field">
                        <label>Giờ chiếu</label>
                        <input type="time" name="gio_chieu" value="${formatTime(booking.gio_chieu)}"
                            ${canEditDatetime ? '' : 'disabled readonly'} class="ticket-input ticket-time-input">
                    </div>
                </div>
                ${seatSection}
                ${!canEditDatetime ? '<p class="ticket-lock-note"><i class="fas fa-lock"></i> Ngày và giờ chiếu đã bị khóa sau 1 giờ kể từ lúc đặt vé.</p>' : ''}
                ${canEditDatetime ? `<button type="submit" class="btn-submit btn-save-ticket">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>` : ''}
            </form>
        </article>`;
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

function bindTicketEvents() {
    document.querySelectorAll('.ticket-edit-form').forEach(form => {
        form.addEventListener('submit', handleUpdateBooking);

        const dateInput = form.querySelector('.ticket-date-input');
        const timeInput = form.querySelector('.ticket-time-input');

        if (dateInput) {
            dateInput.addEventListener('change', () => onDatetimeChange(form));
        }
        if (timeInput) {
            timeInput.addEventListener('change', () => onDatetimeChange(form));
        }

        const changeSeatBtn = form.querySelector('.btn-change-seat');
        if (changeSeatBtn) {
            changeSeatBtn.addEventListener('click', async () => {
                showSeatPicker(form);
                await refreshEditSeatMap(form, true);
                form.querySelector('.ticket-seat-picker')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        hideSeatPicker(form);
    });
}

async function handleUpdateBooking(e) {
    e.preventDefault();
    const form = e.target;
    const bookingId = form.closest('.ticket-card').dataset.id;
    const dateInput = form.querySelector('[name="ngay_chieu"]');
    const timeInput = form.querySelector('[name="gio_chieu"]');
    const soGheInput = form.querySelector('[name="so_ghe"]');

    if (dateInput.disabled || timeInput.disabled) {
        alert('Đã quá 1 giờ. Không thể sửa ngày và giờ chiếu!');
        return;
    }

    const ngayChieu = dateInput.value;
    const gioChieu = timeInput.value;
    const soGhe = soGheInput ? soGheInput.value.trim() : '';

    if (!ngayChieu || !gioChieu) {
        alert('Vui lòng chọn ngày và giờ chiếu!');
        return;
    }

    if (!soGhe) {
        alert('Vui lòng chọn ghế (bấm "Thay đổi ghế" hoặc chọn ghế trống trên sơ đồ).');
        showSeatPicker(form);
        await refreshEditSeatMap(form, false);
        form.querySelector('.ticket-seat-picker')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const formData = new FormData();
    formData.append('action', 'updateBooking');
    formData.append('booking_id', bookingId);
    formData.append('ngay_chieu', ngayChieu);
    formData.append('gio_chieu', gioChieu);
    formData.append('so_ghe', soGhe);

    const btn = form.querySelector('.btn-save-ticket');
    if (btn) btn.disabled = true;

    try {
        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            alert(data.message);
            loadBookings();
        } else {
            if (data.need_seat_pick) {
                const warn = form.querySelector('.ticket-seat-warning');
                if (warn) {
                    warn.style.display = 'block';
                    warn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' +
                        escapeHtml(data.message) + ' Chọn ghế trống bên dưới.';
                }
                showSeatPicker(form);
                await refreshEditSeatMap(form, false);
                form.querySelector('.ticket-seat-picker')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert(data.message || 'Cập nhật thất bại!');
            }
        }
    } catch (error) {
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.AuthLogin) {
        window.AuthLogin.onSuccess = function () {
            checkUserSession();
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

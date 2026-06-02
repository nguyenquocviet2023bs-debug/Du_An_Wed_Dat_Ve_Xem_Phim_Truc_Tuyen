/**
 * Sơ đồ ghế dùng chung (đặt vé & sửa vé)
 */
window.SeatMapCommon = {
    rows: ['A', 'B', 'C', 'D', 'E', 'F'],
    pricePerSeat: 50000,
    layout: [
        ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
        ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
        ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
        ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'E', 'A', 'A', 'A', 'A', 'A', 'A', 'A'],
        ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
        ['A', 'A', 'B', 'B', 'B', 'B', 'B', 'E', 'B', 'B', 'B', 'B', 'B', 'A', 'A'],
    ],

    parseSeatString(str) {
        if (!str) return [];
        return str.split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
    },

    seatsToString(seats) {
        return [...seats].sort().join(', ');
    },

    async fetchBookedSeats(tenPhim, ngayChieu, gioChieu) {
        if (!tenPhim || !ngayChieu || !gioChieu) return [];
        try {
            const formData = new FormData();
            formData.append('action', 'getBookedSeats');
            formData.append('ten_phim', tenPhim);
            formData.append('ngay_chieu', ngayChieu);
            formData.append('gio_chieu', gioChieu);
            const res = await fetch('logicDB.php', { method: 'POST', body: formData });
            const data = await res.json();
            return data.success && Array.isArray(data.seats) ? data.seats.map(s => s.toUpperCase()) : [];
        } catch (e) {
            console.error(e);
            return [];
        }
    },

    /**
     * @param {HTMLElement} container
     * @param {Object} opts - movie, date, time, selectedSeats[], onChange(seats, price)
     */
    async renderEditMap(container, opts) {
        const { movie, date, time, selectedSeats = [], onChange } = opts;
        container.innerHTML = '<p class="edit-seat-loading">Đang tải ghế...</p>';

        if (!date || !time) {
            container.innerHTML = '<p class="edit-seat-hint">Chọn ngày và giờ để xem ghế trống.</p>';
            return;
        }

        const booked = await this.fetchBookedSeats(movie, date, time);
        let selected = selectedSeats.map(s => s.toUpperCase());

        selected = selected.filter(s => !booked.includes(s));

        const mapEl = document.createElement('div');
        mapEl.className = 'edit-seat-map-grid';
        mapEl.style.gridTemplateColumns = 'repeat(15, 1fr)';

        const self = this;

        this.layout.forEach((row, rowIndex) => {
            row.forEach((seatType, colIndex) => {
                const seatName = `${this.rows[rowIndex]}${colIndex + 1}`;
                const div = document.createElement('div');
                div.className = 'seat';
                div.dataset.seat = seatName;

                if (seatType === 'E') {
                    div.className = 'seat seat-empty';
                } else if (booked.includes(seatName)) {
                    div.className = 'seat seat-booked';
                    div.textContent = seatName;
                    div.title = 'Đã có người đặt';
                } else {
                    div.className = 'seat seat-available' + (selected.includes(seatName) ? ' seat-selected' : '');
                    div.textContent = seatName;
                    div.addEventListener('click', function () {
                        if (this.classList.contains('seat-selected')) {
                            this.classList.remove('seat-selected');
                            selected = selected.filter(s => s !== seatName);
                        } else {
                            this.classList.add('seat-selected');
                            selected.push(seatName);
                        }
                        selected.sort();
                        if (typeof onChange === 'function') {
                            onChange(selected, selected.length * self.pricePerSeat);
                        }
                    });
                }
                mapEl.appendChild(div);
            });
        });

        container.innerHTML = '';
        const legend = document.createElement('div');
        legend.className = 'edit-seat-legend';
        legend.innerHTML = `
            <span><i class="seat seat-available"></i> Trống</span>
            <span><i class="seat seat-selected"></i> Đang chọn</span>
            <span><i class="seat seat-booked"></i> Đã bán</span>`;
        container.appendChild(legend);
        container.appendChild(mapEl);

        if (typeof onChange === 'function') {
            onChange(selected, selected.length * this.pricePerSeat);
        }
    }
};

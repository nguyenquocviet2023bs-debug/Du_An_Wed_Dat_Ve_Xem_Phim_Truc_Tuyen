(function () {
    let revenueChart = null;

    function postAction(action, extra) {
        const fd = new FormData();
        fd.append('action', action);
        if (extra) {
            Object.keys(extra).forEach(function (k) {
                fd.append(k, extra[k]);
            });
        }
        return fetch('logicDB.php', { method: 'POST', body: fd }).then(function (r) {
            return r.json();
        });
    }

    function formatMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + ' đ';
    }

    function escapeHtml(s) {
        if (s == null) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function formatDt(s) {
        if (!s) return '—';
        const d = new Date(s);
        return isNaN(d.getTime()) ? escapeHtml(s) : d.toLocaleString('vi-VN');
    }

    function loadMovies() {
        const el = document.getElementById('moviesContent');
        el.innerHTML = '<p class="admin-loading">Đang tải…</p>';
        postAction('adminGetMovies')
            .then(function (data) {
                if (!data.success) {
                    el.innerHTML = '<p class="admin-error">' + escapeHtml(data.message || 'Lỗi') + '</p>';
                    return;
                }
                el.innerHTML = renderMovies(data.movies);
                bindMovieEvents();
            })
            .catch(function () {
                el.innerHTML = '<p class="admin-error">Lỗi kết nối.</p>';
            });
    }

    function renderMovies(list) {
        if (!list || !list.length) {
            return '<p class="admin-loading">Chưa có phim nào trong hệ thống.</p>';
        }
        let html = '<div class="movies-grid">';
        list.forEach(function (m) {
            const img = m.hinh_anh_url
                ? '<img src="' + escapeHtml(m.hinh_anh_url) + '" alt="' + escapeHtml(m.ten_phim) + '">'
                : '<div class="movie-no-image"><i class="fas fa-film"></i></div>';
            html += `
                <div class="movie-card" data-id="${m.id}">
                    <div class="movie-image">${img}</div>
                    <div class="movie-info">
                        <h4 class="movie-title">${escapeHtml(m.ten_phim)}</h4>
                        <p class="movie-meta">
                            ${m.the_loai ? '<span><i class="fas fa-tag"></i> ' + escapeHtml(m.the_loai) + '</span>' : ''}
                            ${m.thoi_luong ? '<span><i class="fas fa-clock"></i> ' + m.thoi_luong + ' phút</span>' : ''}
                        </p>
                        ${m.dao_dien ? '<p class="movie-director"><i class="fas fa-user-tie"></i> ' + escapeHtml(m.dao_dien) + '</p>' : ''}
                        <div class="movie-actions">
                            <button type="button" class="btn-admin-action btn-edit-movie" data-id="${m.id}">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button type="button" class="btn-admin-action btn-delete-movie" data-id="${m.id}">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>`;
        });
        html += '</div>';
        return html;
    }

    function bindMovieEvents() {
        document.querySelectorAll('.btn-edit-movie').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.getAttribute('data-id');
                openEditMovieModal(id);
            });
        });
        document.querySelectorAll('.btn-delete-movie').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.getAttribute('data-id');
                if (!confirm('Xác nhận xóa phim này?')) return;
                postAction('adminDeleteMovie', { movie_id: id }).then(function (res) {
                    alert(res.message || (res.success ? 'Xóa thành công' : 'Lỗi'));
                    if (res.success) loadMovies();
                });
            });
        });
    }

    function openAddMovieModal() {
        document.getElementById('movieModalTitle').textContent = 'Thêm phim mới';
        document.getElementById('movieForm').reset();
        document.getElementById('movieId').value = '';
        document.getElementById('movieImagePreview').style.display = 'none';
        document.getElementById('movieModal').classList.add('active');
    }

    function openEditMovieModal(movieId) {
        postAction('adminGetMovies').then(function (data) {
            if (!data.success) return;
            const movie = data.movies.find(function (m) {
                return m.id == movieId;
            });
            if (!movie) return;

            document.getElementById('movieModalTitle').textContent = 'Sửa phim';
            document.getElementById('movieId').value = movie.id;
            document.getElementById('movieName').value = movie.ten_phim || '';
            document.getElementById('movieDesc').value = movie.mo_ta || '';
            document.getElementById('movieGenre').value = movie.the_loai || '';
            document.getElementById('movieDirector').value = movie.dao_dien || '';
            document.getElementById('movieDuration').value = movie.thoi_luong || '';
            document.getElementById('movieReleaseDate').value = movie.ngay_khoi_chieu || '';
            document.getElementById('movieImage').value = movie.hinh_anh_url || '';

            if (movie.hinh_anh_url) {
                document.getElementById('movieImagePreviewImg').src = movie.hinh_anh_url;
                document.getElementById('movieImagePreview').style.display = 'block';
            } else {
                document.getElementById('movieImagePreview').style.display = 'none';
            }

            document.getElementById('movieModal').classList.add('active');
        });
    }

    function populateYearDropdown() {
        const yearSelect = document.getElementById('revenueYear');
        const currentYear = new Date().getFullYear();
        yearSelect.innerHTML = '';
        for (let y = currentYear; y >= currentYear - 5; y--) {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = 'Năm ' + y;
            if (y === currentYear) opt.selected = true;
            yearSelect.appendChild(opt);
        }
        const currentMonth = new Date().getMonth() + 1;
        document.getElementById('revenueMonth').value = currentMonth;
    }

    function loadRevenue() {
        const month = document.getElementById('revenueMonth').value;
        const year = document.getElementById('revenueYear').value;
        const el = document.getElementById('revenueContent');
        el.innerHTML = '<p class="admin-loading">Đang tải…</p>';
        postAction('adminGetRevenue', { month: month, year: year })
            .then(function (data) {
                if (!data.success) {
                    el.innerHTML = '<p class="admin-error">' + escapeHtml(data.message || 'Lỗi') + '</p>';
                    return;
                }
                el.innerHTML = renderRevenue(data, month, year);
            })
            .catch(function () {
                el.innerHTML = '<p class="admin-error">Lỗi kết nối.</p>';
            });
    }

    function renderRevenue(data, month, year) {
        if (!data.success) {
            return '<p class="admin-error">' + escapeHtml(data.message || 'Lỗi') + '</p>';
        }
        const byMovie = data.by_movie || [];
        if (byMovie.length === 0) {
            return '<p class="admin-loading">Chưa có dữ liệu doanh thu.</p>';
        }

        let html = '<div class="revenue-chart-container"><canvas id="revenueChart"></canvas></div>';
        html += '<div class="admin-stats-row">';
        html += '<div class="admin-stat-card"><span>Tổng vé</span><strong>' + (data.total_tickets || 0) + '</strong></div>';
        html += '<div class="admin-stat-card"><span>Tổng doanh thu</span><strong>' + formatMoney(data.total_revenue) + '</strong></div>';
        html += '</div>';

        html += '<h4 style="margin:16px 0 8px">Chi tiết theo phim</h4>';
        html += '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>';
        html += '<th>Phim</th><th>Số vé</th><th>Doanh thu</th><th>Tỷ lệ</th></tr></thead><tbody>';
        
        byMovie.forEach(function (r) {
            const percent = data.total_revenue > 0 ? ((r.tong_tien / data.total_revenue) * 100).toFixed(1) : 0;
            html += '<tr><td>' + escapeHtml(r.ten_phim_dat || '') + '</td>';
            html += '<td>' + (r.so_ve || 0) + '</td>';
            html += '<td>' + formatMoney(r.tong_tien) + '</td>';
            html += '<td>' + percent + '%</td></tr>';
        });
        html += '</tbody></table></div>';

        setTimeout(function () {
            renderRevenueChart(byMovie);
        }, 100);

        return html;
    }

    function renderRevenueChart(byMovie) {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;

        const labels = byMovie.map(function (m) {
            return m.ten_phim_dat;
        });
        const dataValues = byMovie.map(function (m) {
            return m.tong_tien;
        });
        const colors = generateColors(byMovie.length);

        if (revenueChart) {
            revenueChart.destroy();
        }

        revenueChart = new Chart(canvas, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.parsed || 0;
                                return context.label + ': ' + formatMoney(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function generateColors(count) {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF9F40'
        ];
        const result = [];
        for (let i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        return result;
    }

    function loadActivities() {
        const el = document.getElementById('activitiesContent');
        el.innerHTML = '<p class="admin-loading">Đang tải…</p>';
        postAction('adminGetActivities', { limit: '200' })
            .then(function (data) {
                if (!data.success) {
                    el.innerHTML = '<p class="admin-error">' + escapeHtml(data.message || '') + '</p>';
                    return;
                }
                el.innerHTML = renderActivities(data.activities);
            })
            .catch(function () {
                el.innerHTML = '<p class="admin-error">Lỗi kết nối.</p>';
            });
    }

    function renderActivities(list) {
        if (!list || !list.length) {
            return '<p class="admin-loading">Chưa có hoạt động.</p>';
        }
        const rows = list
            .map(function (a) {
                return (
                    '<tr><td>' +
                    escapeHtml(a.user_phone) +
                    '</td><td>' +
                    escapeHtml(a.loai_hoat_dong) +
                    '</td><td>' +
                    escapeHtml(a.noi_dung) +
                    '</td><td>' +
                    formatDt(a.created_at) +
                    '</td></tr>'
                );
            })
            .join('');
        return (
            '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>' +
            '<th>SĐT</th><th>Loại</th><th>Nội dung</th><th>Thời gian</th></tr></thead><tbody>' +
            rows +
            '</tbody></table></div>'
        );
    }

    function loadUsers() {
        const el = document.getElementById('usersContent');
        el.innerHTML = '<p class="admin-loading">Đang tải…</p>';
        postAction('adminGetUsers')
            .then(function (data) {
                if (!data.success) {
                    el.innerHTML = '<p class="admin-error">' + escapeHtml(data.message || '') + '</p>';
                    return;
                }
                el.innerHTML = renderUsers(data.users);
                el.querySelectorAll('.btn-lock').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const phone = btn.getAttribute('data-phone');
                        if (!phone || btn.disabled) return;
                        if (!confirm('Khóa tài khoản ' + phone + '?')) return;
                        postAction('adminSetUserLock', { dien_thoai: phone, locked: '1' }).then(function (res) {
                            alert(res.message || (res.success ? 'OK' : 'Lỗi'));
                            if (res.success) loadUsers();
                        });
                    });
                });
                el.querySelectorAll('.btn-unlock').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const phone = btn.getAttribute('data-phone');
                        if (!phone) return;
                        if (!confirm('Mở khóa tài khoản ' + phone + '?')) return;
                        postAction('adminSetUserLock', { dien_thoai: phone, locked: '0' }).then(function (res) {
                            alert(res.message || (res.success ? 'OK' : 'Lỗi'));
                            if (res.success) loadUsers();
                        });
                    });
                });
            })
            .catch(function () {
                el.innerHTML = '<p class="admin-error">Lỗi kết nối.</p>';
            });
    }

    function renderUsers(list) {
        if (!list || !list.length) {
            return '<p class="admin-loading">Chưa có người dùng.</p>';
        }
        const rows = list
            .map(function (u) {
                const isAdmin = (u.vai_tro || '') === 'admin';
                const locked = Number(u.tai_khoan_bi_khoa) === 1;
                const lockBtn = locked
                    ? '<button type="button" class="btn-admin-action secondary btn-unlock" data-phone="' +
                      escapeHtml(u.dien_thoai) +
                      '">Mở khóa</button>'
                    : '<button type="button" class="btn-admin-action btn-lock" data-phone="' +
                      escapeHtml(u.dien_thoai) +
                      '"' +
                      (isAdmin ? ' disabled title="Không khóa admin"' : '') +
                      '>Khóa</button>';
                return (
                    '<tr data-phone="' +
                    escapeHtml(u.dien_thoai) +
                    '"><td>' +
                    escapeHtml(u.dien_thoai) +
                    '</td><td>' +
                    escapeHtml(u.ho_ten) +
                    '</td><td>' +
                    escapeHtml(u.email) +
                    '</td><td><span class="admin-badge ' +
                    (isAdmin ? 'role-admin' : 'role-user') +
                    '">' +
                    escapeHtml(isAdmin ? 'Admin' : 'Khách') +
                    '</span></td><td>' +
                    (locked
                        ? '<span class="admin-badge locked">Đã khóa</span>'
                        : '<span class="admin-badge active-u">Hoạt động</span>') +
                    '</td><td>' +
                    lockBtn +
                    '</td></tr>'
                );
            })
            .join('');
        return (
            '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>' +
            '<th>SĐT</th><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>' +
            rows +
            '</tbody></table></div>'
        );
    }

    function switchTab(name) {
        document.querySelectorAll('.admin-tab').forEach(function (t) {
            t.classList.toggle('active', t.getAttribute('data-tab') === name);
        });
        document.querySelectorAll('.admin-panel').forEach(function (p) {
            p.classList.remove('active');
        });
        const panel = document.getElementById('panel-' + name);
        if (panel) panel.classList.add('active');

        if (name === 'movies') loadMovies();
        if (name === 'revenue') loadRevenue();
        if (name === 'activities') loadActivities();
        if (name === 'users') loadUsers();
    }

    document.addEventListener('DOMContentLoaded', function () {
        populateYearDropdown();
        loadMovies();

        document.querySelectorAll('.admin-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                switchTab(tab.getAttribute('data-tab'));
            });
        });

        document.getElementById('logoutBtn').addEventListener('click', function () {
            if (!confirm('Đăng xuất khỏi quản trị?')) return;
            postAction('logout').then(function () {
                window.location.href = 'TrangChu.php';
            });
        });

        document.getElementById('btnAddMovie').addEventListener('click', openAddMovieModal);

        document.getElementById('btnFilterRevenue').addEventListener('click', loadRevenue);

        document.getElementById('movieForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            const movieId = fd.get('movie_id');
            const action = movieId ? 'adminUpdateMovie' : 'adminAddMovie';
            
            const data = {};
            fd.forEach((value, key) => data[key] = value);

            postAction(action, data).then(function (res) {
                alert(res.message || (res.success ? 'Thành công' : 'Lỗi'));
                if (res.success) {
                    document.getElementById('movieModal').classList.remove('active');
                    loadMovies();
                }
            });
        });

        document.getElementById('movieImage').addEventListener('input', function () {
            const url = this.value.trim();
            if (url) {
                document.getElementById('movieImagePreviewImg').src = url;
                document.getElementById('movieImagePreview').style.display = 'block';
            } else {
                document.getElementById('movieImagePreview').style.display = 'none';
            }
        });

        document.querySelectorAll('.modal .close').forEach(function (btn) {
            btn.addEventListener('click', function () {
                this.closest('.modal').classList.remove('active');
            });
        });
    });
})();

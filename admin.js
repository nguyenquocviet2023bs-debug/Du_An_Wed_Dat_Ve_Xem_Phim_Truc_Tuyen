(function () {
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

    function renderRevenue(data) {
        if (!data.success) {
            return '<p class="admin-error">' + escapeHtml(data.message || 'Lỗi tải dữ liệu') + '</p>';
        }
        const byMovie = data.by_movie || [];
        let rows = byMovie
            .map(function (r) {
                return (
                    '<tr><td>' +
                    escapeHtml(r.ten_phim_dat || '') +
                    '</td><td>' +
                    (r.so_ve || 0) +
                    '</td><td>' +
                    formatMoney(r.tong_tien) +
                    '</td></tr>'
                );
            })
            .join('');
        if (!rows) {
            rows = '<tr><td colspan="3">Chưa có vé trong hệ thống.</td></tr>';
        }
        return (
            '<div class="admin-stats-row">' +
            '<div class="admin-stat-card"><span>Tổng vé</span><strong>' +
            (data.total_tickets || 0) +
            '</strong></div>' +
            '<div class="admin-stat-card"><span>Tổng doanh thu</span><strong>' +
            formatMoney(data.total_revenue) +
            '</strong></div></div>' +
            '<h4 style="margin:16px 0 8px">Theo phim</h4>' +
            '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>' +
            '<th>Phim</th><th>Số vé</th><th>Doanh thu</th></tr></thead><tbody>' +
            rows +
            '</tbody></table></div>'
        );
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

    function loadRevenue() {
        const el = document.getElementById('revenueContent');
        el.innerHTML = '<p class="admin-loading">Đang tải…</p>';
        postAction('adminGetRevenue')
            .then(function (data) {
                el.innerHTML = renderRevenue(data);
            })
            .catch(function () {
                el.innerHTML = '<p class="admin-error">Lỗi kết nối.</p>';
            });
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

    function switchTab(name) {
        document.querySelectorAll('.admin-tab').forEach(function (t) {
            t.classList.toggle('active', t.getAttribute('data-tab') === name);
        });
        document.querySelectorAll('.admin-panel').forEach(function (p) {
            p.classList.remove('active');
        });
        const panel = document.getElementById('panel-' + name);
        if (panel) panel.classList.add('active');

        if (name === 'revenue') loadRevenue();
        if (name === 'activities') loadActivities();
        if (name === 'users') loadUsers();
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadRevenue();

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
    });
})();

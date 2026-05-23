let isLoggedIn = false;

const ACTIVITY_META = {
    dang_nhap: { icon: 'fa-right-to-bracket', label: 'Đăng nhập', class: 'type-login' },
    dang_xuat: { icon: 'fa-right-from-bracket', label: 'Đăng xuất', class: 'type-logout' },
    dang_ky: { icon: 'fa-user-plus', label: 'Đăng ký', class: 'type-signup' },
    dat_ve: { icon: 'fa-ticket', label: 'Đặt vé', class: 'type-booking' },
    sua_ve: { icon: 'fa-pen-to-square', label: 'Sửa vé', class: 'type-edit' },
    het_han: { icon: 'fa-clock', label: 'Vé hết hạn', class: 'type-expired' }
};

async function checkUserSession() {
    try {
        const formData = new FormData();
        formData.append('action', 'checkSession');

        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.isLoggedIn) {
            isLoggedIn = true;
            showMainContent();
            loadActivities();
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function getActivityMeta(loai) {
    return ACTIVITY_META[loai] || { icon: 'fa-circle-info', label: 'Hoạt động', class: 'type-default' };
}

async function loadActivities() {
    const container = document.getElementById('activityList');
    container.innerHTML = '<p class="activity-loading">Đang tải hoạt động...</p>';

    const formData = new FormData();
    formData.append('action', 'getActivities');

    try {
        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (!data.success) {
            container.innerHTML = `<p class="activity-empty">${escapeHtml(data.message || 'Không thể tải hoạt động.')}</p>`;
            return;
        }

        if (!data.activities || data.activities.length === 0) {
            container.innerHTML = `
                <div class="activity-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>Chưa có hoạt động nào.</p>
                </div>`;
            return;
        }

        container.innerHTML = data.activities.map(renderActivityItem).join('');
    } catch (error) {
        container.innerHTML = '<p class="activity-empty">Lỗi kết nối. Vui lòng thử lại.</p>';
    }
}

function renderActivityItem(activity) {
    const meta = getActivityMeta(activity.loai_hoat_dong);
    return `
        <article class="activity-item ${meta.class}">
            <div class="activity-icon">
                <i class="fas ${meta.icon}"></i>
            </div>
            <div class="activity-body">
                <div class="activity-header">
                    <span class="activity-type">${meta.label}</span>
                    <time class="activity-time">${formatDateTime(activity.created_at)}</time>
                </div>
                <p class="activity-content">${escapeHtml(activity.noi_dung)}</p>
            </div>
        </article>`;
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.AuthLogin) {
        window.AuthLogin.onSuccess = function () {
            isLoggedIn = true;
            showMainContent();
            loadActivities();
        };
    }

    checkUserSession();

    document.getElementById('logoutBtn').addEventListener('click', (e) => {
        e.preventDefault();
        if (confirm('Bạn có chắc muốn đăng xuất?')) {
            handleLogout();
        }
    });

    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.modal').classList.remove('active');
        });
    });
});

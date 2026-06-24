(function () {
    let resendCooldown = 0;
    let otpCountdownInterval = null;

    function openModal(modal) {
        if (modal) modal.classList.add('active');
    }

    function closeModal(modal) {
        if (modal) modal.classList.remove('active');
    }

    function onLoginSuccess() {
        stopOtpCountdown();
        if (typeof window.AuthLogin !== 'undefined' && typeof window.AuthLogin.onSuccess === 'function') {
            window.AuthLogin.onSuccess();
            return;
        }
        if (typeof showMainContent === 'function') showMainContent();
        if (typeof window.isLoggedIn !== 'undefined') window.isLoggedIn = true;
    }

    function stopOtpCountdown() {
        if (otpCountdownInterval) {
            clearInterval(otpCountdownInterval);
            otpCountdownInterval = null;
        }
    }

    function showExpiredNotice(show) {
        const notice = document.getElementById('otpExpiredNotice');
        const resendBtn = document.getElementById('resendOtpBtn');
        if (notice) notice.style.display = show ? 'block' : 'none';
        if (resendBtn) resendBtn.classList.toggle('otp-resend-highlight', show);
    }

    function startOtpCountdown(seconds) {
        stopOtpCountdown();
        showExpiredNotice(false);

        const hint = document.getElementById('otpHint');
        let left = seconds;

        function updateHint() {
            if (!hint) return;
            if (left > 0) {
                hint.textContent = 'Mã có hiệu lực còn ' + left + ' giây.';
            } else {
                hint.textContent = 'Mã đã hết hạn.';
            }
        }

        updateHint();
        otpCountdownInterval = setInterval(function () {
            left--;
            if (left <= 0) {
                stopOtpCountdown();
                showExpiredNotice(true);
                updateHint();
                return;
            }
            updateHint();
        }, 1000);
    }

    function showOtpModal(maskedEmail, devMode, expiresIn, devOtp) {
        const loginModal = document.getElementById('loginModal');
        const otpModal = document.getElementById('otpModal');
        const msg = document.getElementById('otpSentMessage');

        closeModal(loginModal);
        if (msg) {
            msg.innerHTML = '<span style="color:#999;">Đang gửi mã xác thực...</span>';
        }
        const otpInput = document.getElementById('otpCode');
        if (otpInput) {
            otpInput.value = '';
            otpInput.disabled = false;
            otpInput.focus();
        }
        startOtpCountdown(expiresIn || 60);
        openModal(otpModal);
    }

    function handleOtpExpiredResponse(message) {
        showExpiredNotice(true);
        const hint = document.getElementById('otpHint');
        if (hint) hint.textContent = 'Mã đã hết hạn.';
        const otpInput = document.getElementById('otpCode');
        if (otpInput) otpInput.value = '';
        stopOtpCountdown();
        alert(message || 'Mã đã hết hạn. Vui lòng bấm "Gửi lại mã" để nhận OTP mới.');
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

        const btn = e.target.querySelector('.btn-submit');
        if (btn) btn.disabled = true;

        fetch('logicDB.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.require_otp) {
                    showOtpModal(data.masked_email, false, data.expires_in);
                    sendOtpAfterLogin();
                } else if (data.success) {
                    document.getElementById('loginForm').reset();
                    onLoginSuccess();
                    if (data.is_admin) {
                        window.location.href = 'QuanTri.php';
                    } else {
                        alert(data.message || 'Đăng nhập thành công!');
                    }
                } else {
                    alert(data.message || 'Đăng nhập thất bại!');
                }
            })
            .catch(() => alert('Có lỗi xảy ra. Vui lòng thử lại.'))
            .finally(() => {
                if (btn) btn.disabled = false;
            });
    }

    function handleOtpSubmit(e) {
        e.preventDefault();
        const otp = document.getElementById('otpCode').value.trim();

        if (!/^\d{6}$/.test(otp)) {
            alert('Vui lòng nhập đúng mã 6 chữ số!');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'verifyLoginOtp');
        formData.append('otp', otp);

        const btn = e.target.querySelector('.btn-submit');
        if (btn) btn.disabled = true;

        fetch('logicDB.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('loginForm')?.reset();
                    document.getElementById('otpForm')?.reset();
                    closeModal(document.getElementById('otpModal'));
                    onLoginSuccess();
                    alert(data.message || 'Xác thực thành công!');
                } else if (data.otp_expired) {
                    handleOtpExpiredResponse(data.message);
                } else {
                    alert(data.message || 'Mã xác thực không đúng!');
                }
            })
            .catch(() => alert('Có lỗi xảy ra. Vui lòng thử lại.'))
            .finally(() => {
                if (btn) btn.disabled = false;
            });
    }

    function sendOtpAfterLogin() {
        const formData = new FormData();
        formData.append('action', 'resendLoginOtp');

        const msg = document.getElementById('otpSentMessage');

        fetch('logicDB.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showExpiredNotice(false);
                    const otpInput = document.getElementById('otpCode');
                    if (otpInput) {
                        otpInput.value = '';
                        otpInput.focus();
                    }
                    startOtpCountdown(data.expires_in || 60);
                    if (data.dev_mode) {
                        if (msg) msg.innerHTML = '⚠️ CHẾ ĐỘ DEV — Mã OTP của bạn là: <strong>' + (data.dev_otp || '??????') + '</strong>';
                    } else {
                        if (msg) msg.textContent = 'Mã xác thực 6 số đã được gửi đến ' + (data.masked_email || 'email của bạn') + '.';
                    }
                } else {
                    if (msg) msg.textContent = data.message || 'Không gửi được mã xác thực!';
                    alert(data.message || 'Không gửi được mã xác thực!');
                }
            })
            .catch(() => {
                if (msg) msg.textContent = 'Lỗi kết nối. Vui lòng bấm "Gửi lại mã".';
                alert('Có lỗi xảy ra khi gửi mã xác thực.');
            });
    }

    function resendOtp(e) {
        e.preventDefault();
        if (resendCooldown > 0) {
            alert('Vui lòng đợi ' + resendCooldown + ' giây trước khi gửi lại.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'resendLoginOtp');

        const resendBtn = document.getElementById('resendOtpBtn');
        if (resendBtn) resendBtn.style.pointerEvents = 'none';

        fetch('logicDB.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showExpiredNotice(false);
                    const otpInput = document.getElementById('otpCode');
                    if (otpInput) {
                        otpInput.value = '';
                        otpInput.focus();
                    }
                    startOtpCountdown(data.expires_in || 60);
                    alert(data.message || 'Đã gửi mã OTP mới!');
                } else {
                    alert(data.message || 'Không gửi được mã!');
                }
            })
            .catch(() => alert('Có lỗi xảy ra. Vui lòng thử lại.'))
            .finally(() => {
                if (resendBtn) resendBtn.style.pointerEvents = '';
            });
    }

    function initAuthLogin() {
        const loginForm = document.getElementById('loginForm');
        const otpForm = document.getElementById('otpForm');
        const resendBtn = document.getElementById('resendOtpBtn');
        const backBtn = document.getElementById('backToLoginBtn');

        if (loginForm) loginForm.addEventListener('submit', handleLoginSubmit);
        if (otpForm) otpForm.addEventListener('submit', handleOtpSubmit);
        if (resendBtn) resendBtn.addEventListener('click', resendOtp);
        if (backBtn) {
            backBtn.addEventListener('click', function (e) {
                e.preventDefault();
                stopOtpCountdown();
                showExpiredNotice(false);
                closeModal(document.getElementById('otpModal'));
                openModal(document.getElementById('loginModal'));
            });
        }

        document.getElementById('otpCode')?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    window.AuthLogin = { onSuccess: null, init: initAuthLogin };
    document.addEventListener('DOMContentLoaded', initAuthLogin);
})();

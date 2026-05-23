    <div id="otpModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2><i class="fas fa-shield-halved"></i> Xác Thực Đăng Nhập</h2>
                <p id="otpSentMessage">Mã xác thực 6 số đã được gửi đến email của bạn.</p>
            </div>
            <form class="otp-form" id="otpForm">
                <div class="form-group">
                    <label for="otpCode">Mã xác thực (6 chữ số)</label>
                    <input type="text" id="otpCode" class="otp-input" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
                </div>
                <button type="submit" class="btn-submit">Xác Nhận</button>
                <p class="otp-hint" id="otpHint">Mã có hiệu lực trong 60 giây.</p>
                <p class="otp-expired-notice" id="otpExpiredNotice" style="display: none;">
                    <i class="fas fa-clock"></i> Mã đã hết hạn. Bấm <strong>Gửi lại mã</strong> bên dưới để nhận OTP mới.
                </p>
                <p class="otp-resend">Chưa nhận được mã hoặc hết hạn? <a href="#" id="resendOtpBtn">Gửi lại mã</a></p>
                <p class="login-link"><a href="#" id="backToLoginBtn">Quay lại đăng nhập</a></p>
            </form>
        </div>
    </div>

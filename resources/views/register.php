<main class="welcome">
    <section class="welcome-story" aria-label="Giới thiệu Relay">
        <a class="brand brand-light" href="/"><img src="/assets/favicon.svg" alt="" width="36" height="36">relay<span class="brand-period">.</span></a>
        <div class="story-copy"><span class="eyebrow light-eyebrow">TÀI KHOẢN CÁ NHÂN</span><h1>Bắt đầu trò chuyện.</h1><p>Tạo tài khoản để tham gia các phòng và kết nối với bạn bè.</p></div>
        <div class="story-footer"><span>Relay / Lập trình Web và Ứng dụng</span><span>503073</span></div>
    </section>
    <section class="welcome-form">
        <div class="login-topline"><span>THAM GIA KHÔNG GIAN CHUNG</span><span class="tiny-mark">↗</span></div>
        <div class="login-card">
            <div class="small-logo"><img src="/assets/favicon.svg" alt="" width="40" height="40"></div>
            <h2>Tạo tài khoản Relay</h2>
            <p class="muted">Điền thông tin để bắt đầu trò chuyện.</p>
            <form id="register-form">
                <label for="register-username">Tên đăng nhập</label>
                <input id="register-username" name="username" autocomplete="username" placeholder="Ví dụ: minh.nguyen" required minlength="3" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9._-]{2,31}">
                <label for="register-display-name">Tên hiển thị</label>
                <input id="register-display-name" name="displayName" autocomplete="name" placeholder="Tên mọi người sẽ nhìn thấy" required minlength="2" maxlength="60">
                <label for="register-password">Mật khẩu</label>
                <input id="register-password" name="password" type="password" autocomplete="new-password" placeholder="Tối thiểu 12 ký tự" required minlength="12" maxlength="256">
                <p id="register-error" class="form-error" role="alert" hidden></p>
                <button class="button button-primary login-submit" type="submit" id="register-submit" disabled><span>Tạo tài khoản</span><span aria-hidden="true">→</span></button>
            </form>
            <p class="register-link">Đã có tài khoản? <a href="/login">Đăng nhập</a></p>
        </div>
        <footer class="login-footer"><span>Relay / Lập trình Web và Ứng dụng</span><span>503073</span></footer>
    </section>
</main>

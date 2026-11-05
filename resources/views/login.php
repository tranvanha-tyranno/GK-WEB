<main class="welcome">
    <section class="welcome-story" aria-label="Giới thiệu Relay">
        <a class="brand brand-light" href="/"><img src="/assets/favicon.svg" alt="" width="36" height="36">relay<span class="brand-period">.</span></a>
        <div class="story-copy">
            <span class="eyebrow light-eyebrow"><span class="live-dot"></span> KHÔNG GIAN CHO NHỮNG Ý TƯỞNG CHUNG</span>
            <h1>Gần nhau hơn.<br>Ngay lúc này.</h1>
            <p>Mang theo ý tưởng, câu hỏi và những người bạn.<br>Cùng trò chuyện, cùng hoàn thành công việc.</p>
            <div class="conversation-preview" aria-hidden="true">
                <div class="preview-person"><span class="avatar avatar-lilac">AN</span><div><strong>Alice Nguyen</strong><span>Cùng bắt tay làm điều gì đó nhé?</span></div><small>vừa xong</small></div>
                <div class="preview-person preview-reply"><span class="avatar avatar-mint">BT</span><div><strong>Bob Tran</strong><span>Có mặt rồi. Bắt đầu thôi ✨</span></div><small>vừa xong</small></div>
                <div class="preview-typing"><span></span><span></span><span></span><small>Cuộc trò chuyện đang bắt đầu</small></div>
            </div>
        </div>
        <div class="story-footer"><span>Kết nối những người cùng chung ý tưởng.</span><span>01 / TRÒ CHUYỆN TRỰC TIẾP</span></div>
    </section>
    <section class="welcome-form">
        <div class="login-topline"><span>BẮT ĐẦU CUỘC TRÒ CHUYỆN CỦA BẠN</span><span class="tiny-mark">↗</span></div>
        <div class="login-card">
            <div class="small-logo"><img src="/assets/favicon.svg" alt="" width="40" height="40"></div>
            <h2>Chào mừng đến với Relay</h2>
            <p class="muted">Đăng nhập để trao đổi và làm việc cùng mọi người.</p>
            <form id="login-form">
                <label for="username">Tên đăng nhập</label>
                <input id="username" name="username" autocomplete="username" placeholder="Ví dụ: alice" required maxlength="64" autofocus>
                <label for="password">Mật khẩu</label>
                <div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Nhập mật khẩu của bạn" required maxlength="256"><button id="toggle-password" type="button" aria-label="Hiện mật khẩu" disabled>Hiện</button></div>
                <p id="login-error" class="form-error" role="alert" hidden></p>
                <button class="button button-primary login-submit" type="submit" id="login-submit" disabled><span>Đăng nhập vào không gian chung</span><span aria-hidden="true">→</span></button>
            </form>
            <p class="register-link">Chưa có tài khoản? <a href="/register">Tạo tài khoản mới</a></p>
            <div class="demo-accounts">
                <span class="eyebrow">BẠN MUỐN DÙNG THỬ?</span>
                <p>Chọn tài khoản mẫu để điền nhanh thông tin.</p>
                <div class="account-buttons"><button type="button" data-demo="alice" disabled><span class="avatar avatar-lilac">AN</span>Alice</button><button type="button" data-demo="bob" disabled><span class="avatar avatar-mint">BT</span>Bob</button><button type="button" data-demo="charlie" disabled><span class="avatar avatar-peach">CL</span>Charlie</button></div>
                <p class="demo-note">Mật khẩu dùng thử: <code>ChatDemo!2026</code></p>
            </div>
            <p class="login-footnote">Đồ án môn học về giao tiếp thời gian thực.<br>Chỉ dùng dữ liệu mẫu. Nội dung được chia sẻ trong phòng.</p>
        </div>
        <footer class="login-footer"><span>Relay / Lập trình Web và Ứng dụng</span><span>503073</span></footer>
    </section>
</main>

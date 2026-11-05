<div class="workspace">
    <aside class="sidebar">
        <a class="brand" href="/chat"><img src="/assets/favicon.svg" alt="" width="32" height="32">relay<span class="brand-period">.</span></a>
        <div class="social-nav"><button id="open-friends" type="button" disabled>♧ <span>Bạn bè</span><span id="friend-badge" hidden></span></button><button id="open-create-group" type="button" disabled>＋ <span>Tạo nhóm</span></button></div>
        <div class="sidebar-heading"><span>PHÒNG TRÒ CHUYỆN</span><span id="room-count">0</span></div>
        <nav id="room-list" aria-label="Phòng trò chuyện"><p class="sidebar-loading">Đang tải danh sách phòng…</p></nav>
        <p class="sidebar-caption">Một nơi để trao đổi<br>và cùng hoàn thành.</p>
        <div class="sidebar-bottom"><div class="user-card"><span id="self-avatar" class="avatar avatar-lilac">?</span><div><strong id="self-name">Đang tải…</strong><small><span class="live-dot"></span> Sẵn sàng trò chuyện</small></div><button id="logout" type="button" title="Đăng xuất" aria-label="Đăng xuất">↪</button></div></div>
    </aside>
    <main class="chat-main">
        <header class="chat-header">
            <div class="room-title"><span class="room-hash" aria-hidden="true">#</span><div><h1 id="room-name">Trò chuyện chung</h1><p id="room-description">Đang chuẩn bị phòng trò chuyện…</p></div></div>
            <div class="header-actions">
                <div class="connection-pill" id="connection-state" data-state="connecting" role="status"><span></span><span id="connection-label">Đang kết nối…</span></div>
                <button type="button" id="toggle-search" class="header-icon" aria-label="Tìm tin nhắn" title="Tìm tin nhắn (Ctrl K)" aria-expanded="false" aria-controls="search-panel"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.5"/><path d="m16 16 4.5 4.5"/></svg></button>
                <div class="tools-menu">
                    <button type="button" id="toggle-tools" class="header-icon" aria-label="Mở công cụ phòng" title="Công cụ phòng" aria-expanded="false" aria-controls="tools-panel"><svg viewBox="0 0 24 24" width="21" height="21" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg></button>
                    <div id="tools-panel" class="tools-panel" role="group" aria-label="Công cụ phòng" hidden>
                        <span class="menu-heading">PHÒNG TRÒ CHUYỆN</span>
                        <button type="button" id="show-members" aria-label="Xem thành viên"><span>Thành viên</span><span id="member-count" class="menu-count">0</span></button>
                        <button type="button" id="manage-group" hidden><span>Quản lý nhóm</span><span aria-hidden="true">⚙</span></button>
                        <button type="button" id="export-messages" aria-label="Xuất hội thoại"><span>Xuất hội thoại</span><span aria-hidden="true">↓</span></button>
                        <div class="menu-divider"></div>
                        <span class="menu-heading">GIAO DIỆN</span>
                        <button type="button" id="toggle-theme" aria-label="Bật giao diện tối"><span id="theme-label">Giao diện tối</span><span aria-hidden="true">◐</span></button>
                        <button type="button" id="toggle-compact" aria-label="Thu gọn" aria-pressed="false"><span>Thu gọn tin nhắn</span><span class="menu-check" aria-hidden="true">✓</span></button>
                        <div class="menu-divider"></div>
                        <button type="button" id="show-help"><span>Hướng dẫn và phím tắt</span><span aria-hidden="true">?</span></button>
                    </div>
                </div>
            </div>
        </header>
        <div id="search-panel" class="search-panel" hidden><label class="search-field" for="message-search"><input id="message-search" type="search" placeholder="Tìm toàn bộ lịch sử…" autocomplete="off" aria-label="Tìm tin nhắn"><kbd>Ctrl K</kbd></label><button type="button" id="close-search" class="header-icon" aria-label="Đóng tìm kiếm" title="Đóng tìm kiếm">×</button></div>
        <div id="history-summary" class="search-summary" hidden><span id="history-label" role="status"></span></div>
        <div class="history-load"><button id="load-older" type="button" hidden>Tải tin nhắn cũ hơn</button></div>
        <section id="messages" class="messages" role="log" aria-live="polite" aria-label="Tin nhắn" aria-relevant="additions"><div class="empty-state">Đang tải cuộc trò chuyện…</div></section>
        <button id="jump-latest" class="jump-latest" type="button" hidden>↓ Về tin nhắn mới nhất</button>
        <div id="typing-indicator" class="typing-indicator" aria-live="polite"></div>
        <div class="composer-area"><div id="chat-notice" class="chat-notice" role="alert" hidden></div><div id="reply-preview" class="reply-preview" hidden><span id="reply-text"></span><button id="cancel-reply" type="button" aria-label="Hủy trả lời">×</button></div>
            <form id="message-form" class="composer"><label class="sr-only" for="message-input">Tin nhắn của bạn</label><textarea id="message-input" rows="1" placeholder="Viết tin nhắn…" disabled></textarea><div class="composer-toolbar"><div class="composer-extras"><button type="button" id="toggle-emoji" class="composer-emoji" aria-label="Chọn biểu tượng cảm xúc" title="Biểu tượng cảm xúc" aria-expanded="false" aria-controls="emoji-picker">☺</button></div><div class="send-controls"><span id="character-count" hidden>0 / 500</span><button id="send-message" class="send-button" type="submit" disabled aria-label="Gửi tin nhắn">Gửi <span aria-hidden="true">↗</span></button></div></div>
                <div id="emoji-picker" class="emoji-picker" role="group" aria-label="Biểu tượng cảm xúc" hidden>
                    <?php foreach (['😀' => 'Vui vẻ', '😊' => 'Mỉm cười', '❤️' => 'Yêu thích', '👍' => 'Đồng ý', '🎉' => 'Chúc mừng', '✨' => 'Lấp lánh', '🙏' => 'Cảm ơn', '💡' => 'Ý tưởng'] as $emoji => $label): ?>
                    <button type="button" data-emoji="<?= $emoji ?>" aria-label="<?= $label ?>"><?= $emoji ?></button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </main>
</div>
<dialog id="members-dialog" class="app-dialog"><div class="dialog-heading"><h2>Thành viên trong phòng</h2><button type="button" data-close-dialog aria-label="Đóng danh sách thành viên">×</button></div><ul id="mobile-member-list" class="member-list"></ul></dialog>
<dialog id="help-dialog" class="app-dialog"><div class="dialog-heading"><h2>Sử dụng Relay</h2><button type="button" data-close-dialog aria-label="Đóng hướng dẫn">×</button></div><dl class="shortcut-list"><dt>Enter</dt><dd>Gửi tin nhắn</dd><dt>Shift + Enter</dt><dd>Xuống dòng</dd><dt>Ctrl / ⌘ + K</dt><dd>Mở tìm kiếm trong phòng</dd><dt>Esc</dt><dd>Đóng công cụ, tìm kiếm hoặc cửa sổ</dd></dl><p>Rê chuột lên tin nhắn để hiện Trả lời và Sao chép. Trên điện thoại, chạm vào tin nhắn. Khi dùng bàn phím, chuyển tiêu điểm tới tin nhắn để hiện thao tác.</p><p>Bản nháp được lưu riêng theo tài khoản và phòng trên trình duyệt. Trả lời chèn trích dẫn văn bản; cả trích dẫn tính vào giới hạn 500 ký tự. Tìm kiếm và xuất áp dụng cho toàn bộ lịch sử.</p><p>Khi nối lại, ứng dụng khôi phục tin gần nhất và cho phép tải các trang cũ hơn. Tin gửi chưa rõ kết quả cần kiểm tra trước khi gửi lại.</p></dialog>

<dialog id="friends-dialog" class="app-dialog social-dialog" aria-labelledby="friends-title">
  <div class="dialog-heading"><div><span class="social-eyebrow">KẾT NỐI TRÊN RELAY</span><h2 id="friends-title">Bạn bè & khám phá</h2></div><button type="button" data-close-dialog aria-label="Đóng bạn bè">×</button></div>
  <label class="social-search" for="people-search"><span aria-hidden="true">⌕</span><input id="people-search" aria-label="Tìm bạn bè" type="search" maxlength="100" placeholder="Tìm tên hoặc tên đăng nhập…" autocomplete="off"></label>
  <div class="social-tabs" role="group" aria-label="Lọc bạn bè">
    <button type="button" data-people-mode="all" aria-pressed="true">Tất cả</button><button type="button" data-people-mode="friends" aria-pressed="false">Bạn bè</button><button type="button" data-people-mode="requests" aria-pressed="false">Lời mời</button><button type="button" data-people-mode="suggestions" aria-pressed="false">Gợi ý</button>
  </div>
  <div class="social-list-heading"><p id="people-description">Tìm một người bạn và bắt đầu kết nối.</p><button id="refresh-people" type="button" class="social-button" aria-label="Làm mới danh sách bạn bè">↻</button></div>
  <p id="social-notice" class="social-notice" role="status" hidden></p>
  <ul id="people-list" class="people-list" aria-label="Danh sách người dùng" aria-live="polite"></ul>
</dialog>
<dialog id="group-dialog" class="app-dialog social-dialog" aria-labelledby="group-title">
  <div class="dialog-heading"><div><span class="social-eyebrow">CÙNG NHAU TRÒ CHUYỆN</span><h2 id="group-title">Tạo nhóm mới</h2></div><button type="button" data-close-dialog aria-label="Đóng tạo nhóm">×</button></div>
  <form id="create-group-form"><label class="social-field" for="group-name">Tên nhóm<input id="group-name" required minlength="2" maxlength="60" placeholder="Ví dụ: Nhóm đồ án Web" autocomplete="off"></label><div class="social-list-heading"><p>Thêm bạn bè <span id="group-selected">0 / 30</span></p></div><p class="social-hint">Chỉ thành viên được chọn mới xem được nhóm. Bạn có thể tạo nhóm riêng cho mình trước.</p><div id="group-friends" class="group-friends"></div><p id="group-error" class="social-notice" role="alert" hidden></p><div class="social-footer"><span id="group-total">1 thành viên, gồm bạn</span><button id="create-group-submit" type="submit" class="social-button primary">Tạo nhóm ↗</button></div></form>
</dialog>
<dialog id="manage-group-dialog" class="app-dialog social-dialog" aria-labelledby="manage-group-title">
  <div class="dialog-heading"><div><span class="social-eyebrow">CÀI ĐẶT PHÒNG</span><h2 id="manage-group-title">Quản lý nhóm</h2></div><button type="button" data-close-dialog aria-label="Đóng quản lý nhóm">×</button></div>
  <p id="manage-group-loading" class="social-hint" role="status" aria-live="polite" hidden>Đang tải thông tin nhóm…</p>
  <button id="retry-manage-group" type="button" class="social-button" hidden>Tải lại thông tin nhóm</button>
  <form id="rename-group-form"><label class="social-field" for="manage-group-name">Tên nhóm<input id="manage-group-name" required minlength="2" maxlength="60"></label><button id="rename-group-submit" type="submit" class="social-button primary">Lưu tên nhóm</button></form>
  <div class="social-list-heading"><p>Thành viên</p><span id="manage-group-count"></span></div><ul id="manage-group-members" class="people-list"></ul>
  <form id="add-group-member-form" class="group-add-member"><label class="social-field" for="add-group-member">Thêm bạn bè<select id="add-group-member"></select></label><button id="add-group-member-submit" type="submit" class="social-button primary">Thêm thành viên</button></form>
  <p id="manage-group-error" class="social-notice" role="alert" hidden></p>
  <div class="social-footer"><span id="manage-group-hint"></span><button id="leave-group" type="button" class="social-button">Rời nhóm</button><button id="delete-group" type="button" class="social-button danger">Xóa nhóm</button></div>
</dialog>

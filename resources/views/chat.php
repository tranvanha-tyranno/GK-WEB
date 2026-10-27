<div class="workspace">
    <aside class="sidebar">
        <a class="brand" href="/chat"><img src="/assets/favicon.svg" alt="" width="32" height="32">relay<span class="brand-period">.</span></a>
        <div class="workspace-label"><span class="workspace-icon">R</span><div><strong>The common room</strong><small>Your shared workspace</small></div><span class="workspace-chevron">⌄</span></div>
        <div class="sidebar-heading"><span>CONVERSATIONS</span><span id="room-count">3</span></div>
        <nav id="room-list" aria-label="Chat rooms"><p class="sidebar-loading">Finding your rooms…</p></nav>
        <div class="sidebar-note"><span class="note-symbol">✧</span><strong>Good ideas start here.</strong><p>Say hello, ask a question,<br>or share what you're working on.</p></div>
        <div class="sidebar-bottom"><div class="user-card"><span id="self-avatar" class="avatar avatar-lilac">?</span><div><strong id="self-name">Loading…</strong><small><span class="live-dot"></span> Here to connect</small></div><button id="logout" type="button" title="Sign out" aria-label="Sign out">↪</button></div></div>
    </aside>
    <main class="chat-main">
        <header class="chat-header"><div class="room-title"><span class="room-hash">#</span><div><h1 id="room-name">General</h1><p id="room-description">The place for everyday conversations.</p></div></div><div class="connection-pill" id="connection-state" data-state="connecting"><span></span><span id="connection-label">Connecting</span></div></header>
        <div class="room-welcome"><div class="room-welcome-icon">#</div><div><h2 id="room-welcome-title">A conversation starts with hello.</h2><p>You're in good company. Make yourself at home.</p></div><span class="room-welcome-spark" aria-hidden="true">✳</span></div>
        <div class="chat-day-divider"><span id="history-label">RECENT CONVERSATION</span></div>
        <section id="messages" class="messages" role="log" aria-live="polite" aria-label="Messages" aria-relevant="additions"><div class="empty-state">Getting the conversation ready…</div></section>
        <div id="typing-indicator" class="typing-indicator" aria-live="polite"></div>
        <div class="composer-area"><div id="chat-notice" class="chat-notice" role="alert" hidden></div><form id="message-form" class="composer"><label class="sr-only" for="message-input">Your message</label><textarea id="message-input" rows="1" placeholder="Message #General" disabled></textarea><div class="composer-toolbar"><span class="composer-hint">Enter to send <span>·</span> Shift + Enter for a new line</span><div class="send-controls"><span id="character-count">0 / 500</span><button id="send-message" class="send-button" type="submit" disabled aria-label="Send message">Send <span aria-hidden="true">↗</span></button></div></div></form><p class="history-note">Your latest 50 messages are kept here. A fresh connection brings them back.</p></div>
    </main>
    <aside class="members-panel" aria-label="Room information"><div class="members-heading"><h2>In this room</h2><span id="member-count">0</span></div><p class="muted members-subtitle">A few familiar faces. A new idea away.</p><ul id="member-list" class="member-list"></ul><div class="room-info"><span class="eyebrow">ROOM NOTES</span><h3>A place to connect.</h3><p>Keep it kind, stay curious, and give everyone a little room to share.</p><div class="room-info-line"><span>✧</span><span>Live conversations</span></div><div class="room-info-line"><span>◷</span><span>History saved for later</span></div><div class="room-info-line"><span>♧</span><span>Better with your people</span></div></div><div class="members-footer"><span class="live-dot"></span> Small moments, shared live.</div></aside>
</div>

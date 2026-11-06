<main class="welcome">
    <section class="welcome-story" aria-label="About Relay">
        <a class="brand brand-light" href="/"><img src="/assets/favicon.svg" alt="" width="36" height="36">relay<span class="brand-period">.</span></a>
        <div class="story-copy">
            <span class="eyebrow light-eyebrow"><span class="live-dot"></span> A SPACE FOR GOOD CONVERSATIONS</span>
            <h1>A little closer.<br>In real time.</h1>
            <p>Bring your ideas, your questions, and your people.<br>One room. A conversation that keeps moving.</p>
            <div class="conversation-preview" aria-hidden="true">
                <div class="preview-person"><span class="avatar avatar-lilac">AN</span><div><strong>Alice Nguyen</strong><span>Ready to build something together?</span></div><small>now</small></div>
                <div class="preview-person preview-reply"><span class="avatar avatar-mint">BT</span><div><strong>Bob Tran</strong><span>Already here. Let's go ✨</span></div><small>now</small></div>
                <div class="preview-typing"><span></span><span></span><span></span><small>A conversation is starting</small></div>
            </div>
        </div>
        <div class="story-footer"><span>Built for the moments between ideas.</span><span>01 / LIVE CHAT</span></div>
    </section>
    <section class="welcome-form">
        <div class="login-topline"><span>YOUR CONVERSATION STARTS HERE</span><span class="tiny-mark">↗</span></div>
        <div class="login-card">
            <div class="small-logo"><img src="/assets/favicon.svg" alt="" width="40" height="40"></div>
            <h2>Welcome to Relay</h2>
            <p class="muted">Sign in and make yourself part of the conversation.</p>
            <form id="login-form">
                <label for="username">Username</label>
                <input id="username" name="username" autocomplete="username" placeholder="e.g. alice" required maxlength="64" autofocus>
                <label for="password">Password</label>
                <div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required maxlength="256"><button id="toggle-password" type="button" aria-label="Show password" disabled>Show</button></div>
                <p id="login-error" class="form-error" role="alert" hidden></p>
                <button class="button button-primary login-submit" type="submit" id="login-submit" disabled><span>Sign in to your workspace</span><span aria-hidden="true">→</span></button>
            </form>
            <div class="demo-accounts">
                <span class="eyebrow">JUST EXPLORING?</span>
                <p>Choose a demo account to try it out.</p>
                <div class="account-buttons"><button type="button" data-demo="alice" disabled><span class="avatar avatar-lilac">AN</span>Alice</button><button type="button" data-demo="bob" disabled><span class="avatar avatar-mint">BT</span>Bob</button><button type="button" data-demo="charlie" disabled><span class="avatar avatar-peach">CL</span>Charlie</button></div>
                <p class="demo-note">Demo password: <code>ChatDemo!2026</code></p>
            </div>
            <p class="login-footnote">A course project about real-time communication.<br>Use demo data only. Conversations are shared.</p>
        </div>
        <footer class="login-footer"><span>Relay / Web Programming &amp; Applications</span><span>503073</span></footer>
    </section>
</main>

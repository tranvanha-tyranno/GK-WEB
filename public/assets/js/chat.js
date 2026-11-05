import { startSocial } from './social.js';
import { ChatSocket } from './socket.js';
import { readPreference, savePreference, applyTheme, normalizeSearch, roomName } from './preferences.js';

const $ = selector => document.querySelector(selector);
const initials = name => name.split(/\s+/).map(word => Array.from(word)[0]).slice(0, 2).join('').toUpperCase();
const color = id => ['avatar-lilac', 'avatar-mint', 'avatar-peach'][(id - 1) % 3];
const descriptions = { General: 'Chia sẻ công việc, ý tưởng và những câu chuyện mỗi ngày.', Engineering: 'Cùng xây dựng, sửa lỗi và trao đổi giải pháp kỹ thuật.', 'Study group': 'Cùng ôn bài, đặt câu hỏi và chuẩn bị thuyết trình.' };
const element = (tag, className, text) => { const node = document.createElement(tag); if (className) node.className = className; if (text !== undefined) node.textContent = text; return node; };

export async function startChat(api, user) {
  let rooms = [], roomId = null, joined = false, generation = 0, request = null, typingTimer = null, typingAt = 0, pending = null, sessionCheck = null, state = 'connecting', reply = null, toastTimer = null, activeMessageId = null;
  const messages = new Map(), drafts = new Map(), uncertain = new Set();
  let sessionRequest = null, checkingSession = false, identityChanged = false;
  const input = $('#message-input');
  const draftKey = id => `relay:draft:${user.id}:${id}`;
  function loadDraft(id) {
    if (drafts.has(id)) return drafts.get(id);
    try { return sessionStorage.getItem(draftKey(id)) ?? ''; } catch { return ''; }
  }
  function saveDraft(id = roomId, value = input.value) {
    if (id === null) return;
    drafts.set(id, value);
    try { if (value) sessionStorage.setItem(draftKey(id), value); else sessionStorage.removeItem(draftKey(id)); } catch { /* Lại dùng bản nháp trong bộ nhớ. */ }
  }
  function toast(text) { clearTimeout(toastTimer); $('#toast').textContent = text; $('#toast').hidden = false; toastTimer = setTimeout(() => { $('#toast').hidden = true; }, 3500); }
  function notice(text = '') { $('#chat-notice').textContent = text; $('#chat-notice').hidden = !text; }
  const uncertainNotice = 'Tin gửi trước đó có thể đã được lưu. Hãy kiểm tra hội thoại trước khi gửi lại; ứng dụng giữ nội dung để bạn kiểm tra.';
  function quote() { return reply ? `> ${reply.user.displayName}: ${Array.from(reply.content.replace(/\s+/g, ' ')).slice(0, 120).join('')}\n\n` : ''; }
  function content() { return quote() + input.value.trim(); }
  function resize() { input.style.height = 'auto'; input.style.height = `${Math.min(120, input.scrollHeight)}px`; }
  function controls() {
    const count = Array.from(content()).length;
    $('#character-count').textContent = `${count} / 500`;
    $('#character-count').classList.toggle('over', count > 500);
    $('#character-count').hidden = count < 400;
    $('#character-count').setAttribute('aria-label', `${count} trên tối đa 500 ký tự`);
    // Có thể soạn và giữ bản nháp trong lúc chờ kết nối.
    input.disabled = roomId === null;
    $('#send-message').disabled = identityChanged || checkingSession || !joined || state !== 'connected' || pending !== null || !input.value.trim() || count > 500;
  }
  function cancelReply() { reply = null; $('#reply-preview').hidden = true; controls(); }
  function preservePending() {
    if (!pending) return;
    const existing = pending.roomId === roomId ? input.value : loadDraft(pending.roomId);
    const value = existing ? `${pending.content}\n${existing}` : pending.content;
    saveDraft(pending.roomId, value); uncertain.add(pending.roomId);
    if (pending.roomId === roomId) { input.value = value; cancelReply(); resize(); notice(uncertainNotice); }
    pending = null;
  }
  $('#self-name').textContent = user.displayName;
  $('#self-avatar').textContent = initials(user.displayName);
  $('#self-avatar').className = `avatar ${color(user.id)}`;
  function checkSession() {
    if (identityChanged || socket.stopped) return Promise.resolve(false);
    if (sessionRequest) return sessionRequest;
    checkingSession = true; controls();
    sessionRequest = (async () => {
      try {
        const me = await api.request('/api/auth/me');
        if (!me.user) { joined = false; socket.stop(); location.replace('/login'); return false; }
        if (me.user.id !== user.id) {
          identityChanged = true; joined = false; socket.stop();
          toast('Tài khoản đã thay đổi ở thẻ khác. Đang tải lại để đồng bộ danh tính.');
          setTimeout(() => location.reload(), 100);
          return false;
        }
        return true;
      } catch { return false; /* Không gửi khi chưa xác minh được danh tính. */ }
      finally { checkingSession = false; sessionRequest = null; controls(); }
    })();
    return sessionRequest;
  }
  const socket = new ChatSocket(onEvent, newState => {
    state = newState;
    $('#connection-state').dataset.state = newState;
    $('#connection-label').textContent = { connected: 'Đã kết nối', connecting: 'Đang kết nối…', disconnected: 'Đang kết nối lại…' }[newState];
    $('#connection-state').setAttribute('aria-label', $('#connection-label').textContent);
    $('#connection-state').title = $('#connection-label').textContent;
    if (newState !== 'connected') {
      joined = false; $('#typing-indicator').textContent = ''; preservePending();
      clearTimeout(sessionCheck); sessionCheck = setTimeout(checkSession, 500);
    }
    controls();
  }, async () => {
    if (await checkSession()) { if (roomId !== null) socket.send('room:join', { roomId }); }
    else if (!socket.stopped) socket.networkLost();
  });

  function updateJump() {
    const list = $('#messages');
    $('#jump-latest').hidden = list.scrollHeight - list.scrollTop - list.clientHeight < 100 || Boolean($('#message-search').value);
  }
  function renderMessages() {
    const list = $('#messages'), oldTop = list.scrollTop;
    const nearBottom = list.scrollHeight - oldTop - list.clientHeight < 100;
    const fragment = document.createDocumentFragment();
    const ordered = [...messages.values()].sort((a, b) => a.id - b.id);
    const query = normalizeSearch($('#message-search').value);
    const visible = ordered.filter(message => !query || normalizeSearch(`${message.user.displayName} ${message.content}`).includes(query));
    $('#history-label').textContent = query ? `${visible.length} KẾT QUẢ / ${ordered.length} TIN ĐÃ TẢI` : `${ordered.length} TIN NHẮN ĐÃ TẢI`;
    $('#history-summary').hidden = !query;
    $('#export-messages').disabled = ordered.length === 0;
    if (!visible.length) fragment.append(element('div', 'empty-state', query ? 'Không tìm thấy tin nhắn phù hợp. Thử từ khóa khác hoặc xóa tìm kiếm.' : 'Phòng đang yên tĩnh. Hãy là người đầu tiên gửi lời chào!'));
    for (const message of visible) {
      const row = element('article', `message${message.user.id === user.id ? ' own' : ''}`); row.dataset.messageId = String(message.id);
      row.tabIndex = 0;
      row.classList.toggle('actions-open', message.id === activeMessageId);
      row.addEventListener('click', event => {
        if (event.target.closest('button') || window.getSelection()?.toString()) return;
        activeMessageId = activeMessageId === message.id ? null : message.id;
        for (const item of list.children) item.classList.toggle('actions-open', Number(item.dataset.messageId) === activeMessageId);
      });
      row.append(element('span', `avatar message-avatar ${color(message.user.id)}`, initials(message.user.displayName)));
      const body = element('div', 'message-body'), meta = element('div', 'message-meta');
      meta.append(element('span', 'message-name', message.user.displayName));
      if (message.user.id === user.id) meta.append(element('span', 'you-label', '(bạn)'));
      const date = new Date(message.createdAt);
      const time = element('time', 'message-time', date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })); time.dateTime = message.createdAt; time.title = date.toLocaleString('vi-VN');
      meta.append(time);
      const actions = element('div', 'message-actions');
      for (const [label, action] of [['Trả lời', () => {
        reply = message; $('#reply-preview').hidden = false; $('#reply-text').textContent = `Đang trả lời ${message.user.displayName}: ${message.content}`; controls(); input.focus();
      }], ['Sao chép', async () => {
        try { await navigator.clipboard.writeText(message.content); toast('Đã sao chép tin nhắn.'); }
        catch { toast('Không thể sao chép tự động. Bạn có thể chọn văn bản để sao chép.'); }
      }]]) { const button = element('button', '', label); button.type = 'button'; button.setAttribute('aria-label', `${label} tin nhắn của ${message.user.displayName}`); button.addEventListener('click', action); actions.append(button); }
      body.append(meta, element('p', 'message-text', message.content)); row.append(body, actions); fragment.append(row);
    }
    list.replaceChildren(fragment);
    list.scrollTop = nearBottom ? list.scrollHeight : oldTop;
    updateJump();
  }
  function renderMembers(users) {
    $('#member-count').textContent = String(users.length);
    const fragment = document.createDocumentFragment();
    if (!users.length) fragment.append(element('li', 'muted', 'Đang cập nhật thành viên…'));
    for (const person of users) {
      const row = element('li', 'member'); row.append(element('span', `avatar ${color(person.id)}`, initials(person.displayName)));
      const info = element('div'), status = element('span', 'member-status'); status.append(element('span', 'live-dot'), document.createTextNode('Đang trực tuyến'));
      info.append(element('span', 'member-name', person.displayName), status); row.append(info);
      if (person.id === user.id) row.append(element('span', 'member-you', 'bạn'));
      fragment.append(row);
    }
    $('#mobile-member-list').replaceChildren(fragment);
  }
  async function history(id) {
    request?.abort(); request = new AbortController();
    const current = ++generation;
    try {
      const result = await api.request(`/api/rooms/${id}/messages?limit=50`, { signal: request.signal });
      if (current !== generation || id !== roomId) return;
      for (const message of result) if (message.roomId === roomId) messages.set(message.id, message);
      renderMessages(); $('#messages').scrollTop = $('#messages').scrollHeight; updateJump();
      if (!uncertain.has(id)) notice();
    } catch (error) {
      if (error.name === 'AbortError') return;
      if (error.status === 401) { socket.stop(); location.replace('/login'); return; }
      if (current === generation) notice('Không tải được lịch sử. Chọn lại phòng để thử lại.');
    }
  }
  function onEvent(event) {
    const payload = event.payload;
    if (event.type === 'system:error') { preservePending(); notice(payload.message); controls(); return; }
    if (payload.roomId !== roomId) return;
    if (event.type === 'room:joined') { joined = true; controls(); history(roomId); }
    if (event.type === 'chat:new') {
      messages.set(payload.id, payload); renderMessages();
      if (payload.user.id === user.id && payload.content === pending?.content && payload.roomId === pending.roomId) { pending = null; saveDraft(); uncertain.delete(roomId); notice(); controls(); }
    }
    if (event.type === 'room:presence') renderMembers(payload.users);
    if (event.type === 'room:typing') {
      const names = payload.users.filter(person => person.id !== user.id).map(person => person.displayName);
      $('#typing-indicator').textContent = names.length ? `${names.join(', ')} đang nhập…` : '';
    }
  }
  function stopTyping() { clearTimeout(typingTimer); if (joined) socket.send('typing:set', { roomId, isTyping: false }); typingAt = 0; }
  function chooseRoom(id) {
    stopTyping(); preservePending(); saveDraft(); request?.abort(); generation++; joined = false; roomId = id;
    messages.clear(); activeMessageId = null; closeSearch(); renderMessages(); renderMembers([]); $('#typing-indicator').textContent = ''; notice(uncertain.has(id) ? uncertainNotice : ''); cancelReply();
    const room = rooms.find(item => item.id === id), name = roomName(room);
    $('#room-name').textContent = name; $('#room-description').textContent = room.private ? `Nhóm riêng · ${room.memberCount} thành viên` : (descriptions[room.name] ?? 'Không gian dành cho cuộc trò chuyện của bạn.');
    input.placeholder = 'Viết tin nhắn…'; input.value = loadDraft(id); resize();
    document.title = `${name} · Relay`;
    for (const button of $('#room-list').children) button.setAttribute('aria-current', String(Number(button.dataset.roomId) === id));
    savePreference(`room:${user.id}`, String(id)); socket.send('room:join', { roomId }); controls();
  }
  input.addEventListener('input', () => {
    saveDraft(); controls(); resize(); clearTimeout(typingTimer);
    if (!joined) return;
    if (!input.value.trim()) { stopTyping(); return; }
    if (Date.now() - typingAt > 1000) { socket.send('typing:set', { roomId, isTyping: true }); typingAt = Date.now(); }
    typingTimer = setTimeout(stopTyping, 2800);
  });
  input.addEventListener('keydown', event => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); if (!$('#send-message').disabled) $('#message-form').requestSubmit(); }
  });
  $('#message-form').addEventListener('submit', async event => {
    event.preventDefault(); controls();
    if ($('#send-message').disabled) return;
    const text = content(), sendingRoom = roomId;
    if (!await checkSession()) return;
    // Người dùng có thể đổi phòng hoặc sửa bản nháp trong lúc kiểm tra HTTP.
    if (roomId !== sendingRoom || content() !== text || !joined || state !== 'connected' || pending !== null) return;
    if (!socket.send('chat:send', { roomId, content: text })) { notice('Bạn đang mất kết nối. Bản nháp được giữ lại, hãy đợi kết nối trở lại.'); return; }
    pending = { roomId, content: text };
    // Giữ bản gửi trong bộ nhớ phiên cho đến khi nhận được phản hồi, kể cả khi tải lại trang.
    saveDraft(roomId, text); input.value = ''; cancelReply(); resize(); stopTyping(); controls();
  });
  $('#logout').addEventListener('click', async () => {
    $('#logout').disabled = true;
    try {
      await api.request('/api/auth/logout', { method: 'POST', body: {} });
      for (const room of rooms) { try { sessionStorage.removeItem(draftKey(room.id)); } catch { /* Bộ nhớ bị chặn. */ } }
      stopTyping(); socket.stop(); location.replace('/login');
    } catch (error) { if (error.status === 401 || error.status === 403) await checkSession(); notice(error.message); $('#logout').disabled = false; }
  });
  $('#message-search').addEventListener('input', renderMessages);
  $('#messages').addEventListener('scroll', updateJump);
  $('#jump-latest').addEventListener('click', () => { $('#messages').scrollTop = $('#messages').scrollHeight; updateJump(); });
  $('#cancel-reply').addEventListener('click', () => { cancelReply(); input.focus(); });
  $('#toggle-emoji').addEventListener('click', () => { const open = $('#emoji-picker').hidden; $('#emoji-picker').hidden = !open; $('#toggle-emoji').setAttribute('aria-expanded', String(open)); });
  for (const button of document.querySelectorAll('[data-emoji]')) button.addEventListener('click', () => {
    input.setRangeText(button.dataset.emoji, input.selectionStart, input.selectionEnd, 'end'); input.dispatchEvent(new Event('input')); input.focus();
  });
  function themeButton() {
    const dark = document.documentElement.dataset.theme === 'dark';
    $('#toggle-theme').setAttribute('aria-label', dark ? 'Bật giao diện sáng' : 'Bật giao diện tối');
    $('#theme-label').textContent = dark ? 'Giao diện sáng' : 'Giao diện tối';
  }
  themeButton(); $('#toggle-theme').addEventListener('click', () => { const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark'; applyTheme(theme); savePreference('theme', theme); themeButton(); });
  function compact(enabled) { document.body.classList.toggle('compact', enabled); $('#toggle-compact').setAttribute('aria-pressed', String(enabled)); }
  compact(readPreference('compact') === 'true');
  $('#toggle-compact').addEventListener('click', () => { const enabled = !document.body.classList.contains('compact'); compact(enabled); savePreference('compact', String(enabled)); });
  $('#show-members').addEventListener('click', () => $('#members-dialog').showModal());
  $('#show-help').addEventListener('click', () => $('#help-dialog').showModal());
  function closeTools(restoreFocus = false) {
    const wasOpen = !$('#tools-panel').hidden;
    $('#tools-panel').hidden = true;
    $('#toggle-tools').setAttribute('aria-expanded', 'false');
    if (wasOpen && restoreFocus) $('#toggle-tools').focus();
  }
  function openSearch() {
    closeTools();
    $('#search-panel').hidden = false;
    $('#toggle-search').setAttribute('aria-expanded', 'true');
    $('#message-search').focus();
  }
  function closeSearch(restoreFocus = false) {
    const hadQuery = Boolean($('#message-search').value);
    $('#message-search').value = '';
    $('#search-panel').hidden = true;
    $('#toggle-search').setAttribute('aria-expanded', 'false');
    if (hadQuery) renderMessages();
    if (restoreFocus) $('#toggle-search').focus();
  }
  $('#toggle-tools').addEventListener('click', () => {
    const open = $('#tools-panel').hidden;
    $('#tools-panel').hidden = !open;
    $('#toggle-tools').setAttribute('aria-expanded', String(open));
    if (open) { $('#emoji-picker').hidden = true; $('#toggle-emoji').setAttribute('aria-expanded', 'false'); }
  });
  $('#tools-panel').addEventListener('click', event => {
    if (event.target.closest('button')) closeTools(!document.querySelector('dialog[open]'));
  });
  $('#toggle-search').addEventListener('click', () => $('#search-panel').hidden ? openSearch() : closeSearch(true));
  $('#close-search').addEventListener('click', () => closeSearch(true));
  document.addEventListener('click', event => {
    if (!event.target.closest('.tools-menu')) closeTools();
    if (!event.target.closest('.message')) {
      activeMessageId = null;
      for (const item of $('#messages').children) item.classList.remove('actions-open');
    }
    if (!event.target.closest('#toggle-emoji, #emoji-picker')) { $('#emoji-picker').hidden = true; $('#toggle-emoji').setAttribute('aria-expanded', 'false'); }
  });
  for (const button of document.querySelectorAll('[data-close-dialog]')) button.addEventListener('click', () => button.closest('dialog').close());
  for (const dialog of document.querySelectorAll('#members-dialog, #help-dialog')) dialog.addEventListener('close', () => $('#toggle-tools').focus());
  document.addEventListener('keydown', event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && !document.querySelector('dialog[open]')) { event.preventDefault(); openSearch(); }
    if (event.key === 'Escape') {
      if (!$('#tools-panel').hidden) { closeTools(true); return; }
      if (!$('#search-panel').hidden && !document.querySelector('dialog[open]')) { closeSearch(true); return; }
      $('#emoji-picker').hidden = true; $('#toggle-emoji').setAttribute('aria-expanded', 'false'); cancelReply();
    }
  });
  $('#export-messages').addEventListener('click', () => {
    const room = rooms.find(item => item.id === roomId);
    const lines = [`Relay — ${roomName(room)}`, `Xuất lúc ${new Date().toLocaleString('vi-VN')}`, 'Chỉ bao gồm tin nhắn đã tải trong phiên hiện tại.', '', ...[...messages.values()].sort((a, b) => a.id - b.id).map(message => `[${new Date(message.createdAt).toLocaleString('vi-VN')}] ${message.user.displayName}: ${message.content}`)];
    const url = URL.createObjectURL(new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/plain;charset=utf-8' }));
    const link = element('a'); link.href = url; link.download = `relay-phong-${roomId}-${new Date().toISOString().slice(0, 10)}.txt`; document.body.append(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000); toast('Đã xuất tin nhắn đã tải của phòng này.');
  });
  window.addEventListener('pagehide', () => { preservePending(); saveDraft(); stopTyping(); socket.stop(); clearTimeout(sessionCheck); clearTimeout(toastTimer); });
  window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
  window.addEventListener('online', () => { if (!socket.ready && !socket.stopped) socket.connect(); });
  window.addEventListener('offline', () => socket.networkLost());
  window.addEventListener('resize', resize);
  async function refreshRooms(selectId = null) {
    rooms = await api.request('/api/rooms');
    const fragment = document.createDocumentFragment();
    for (const room of rooms) {
      const button = element('button', 'room-button'); button.type = 'button'; button.dataset.roomId = String(room.id);
      button.setAttribute('aria-current', String(room.id === roomId));
      button.title = room.private ? `Nhóm riêng · ${room.memberCount} thành viên` : 'Phòng chung';
      button.append(element('span', 'hash', room.private ? '♧' : '#'), element('span', '', roomName(room)));
      button.addEventListener('click', () => chooseRoom(room.id)); fragment.append(button);
    }
    $('#room-list').replaceChildren(fragment); $('#room-count').textContent = String(rooms.length);
    if (selectId !== null) chooseRoom(selectId);
  }
  await refreshRooms();
  startSocial(api, user, refreshRooms);
  const roomRefresh = setInterval(() => { if (!document.hidden) refreshRooms().catch(() => {}); }, 10000);
  window.addEventListener('pagehide', () => clearInterval(roomRefresh));
  if (rooms.length) chooseRoom(rooms.find(room => room.id === Number(readPreference(`room:${user.id}`)))?.id ?? rooms[0].id);
  else notice('Chưa có phòng trò chuyện. Hãy tạo nhóm mới.');
  socket.connect();
}

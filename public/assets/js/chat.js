import { startSocial } from './social.js';
import { ChatSocket } from './socket.js';
import { readPreference, savePreference, applyTheme, normalizeSearch, roomName } from './preferences.js';

const $ = selector => document.querySelector(selector);
const initials = name => name.split(/\s+/).map(word => Array.from(word)[0]).slice(0, 2).join('').toUpperCase();
const color = id => ['avatar-lilac', 'avatar-mint', 'avatar-peach'][(id - 1) % 3];
const descriptions = { General: 'Chia sẻ công việc, ý tưởng và những câu chuyện mỗi ngày.', Engineering: 'Cùng xây dựng, sửa lỗi và trao đổi giải pháp kỹ thuật.', 'Study group': 'Cùng ôn bài, đặt câu hỏi và chuẩn bị thuyết trình.' };
const element = (tag, className, text) => { const node = document.createElement(tag); if (className) node.className = className; if (text !== undefined) node.textContent = text; return node; };

export async function startChat(api, user) {
  let rooms = [], roomId = null, joined = false, generation = 0, request = null, typingTimer = null, typingAt = 0, pending = null, sessionCheck = null, state = 'connecting', reply = null, toastTimer = null, activeMessageId = null, historyHasOlder = false, historyBusy = false, searchTimer = null, searchHasMore = false, searchLoaded = false, searchResults = [], exportBusy = false;
  const messages = new Map(), drafts = new Map(), uncertain = new Set();
  let sessionRequest = null, checkingSession = false, identityChanged = false;
  const input = $('#message-input');
  const draftKey = id => `relay:draft:${user.id}:${id}`;
  function loadDraft(id) {
    if (drafts.has(id)) return drafts.get(id);
    try {
      const key = draftKey(id);
      let value = localStorage.getItem(key);
      if (value === null) {
        value = sessionStorage.getItem(key);
        if (value !== null) { localStorage.setItem(key, value); sessionStorage.removeItem(key); }
      }
      return value ?? '';
    } catch { return ''; }
  }
  function saveDraft(id = roomId, value = input.value) {
    if (id === null) return;
    drafts.set(id, value);
    try { if (value) localStorage.setItem(draftKey(id), value); else localStorage.removeItem(draftKey(id)); sessionStorage.removeItem(draftKey(id)); } catch { /* Vẫn dùng bản nháp trong bộ nhớ. */ }
  }
  function clearDrafts() {
    const prefix = `relay:draft:${user.id}:`;
    try { for (const key of Object.keys(localStorage)) if (key.startsWith(prefix)) localStorage.removeItem(key); } catch { /* Bộ nhớ có thể bị chặn. */ }
    for (const room of rooms) { try { sessionStorage.removeItem(draftKey(room.id)); } catch { /* Bộ nhớ có thể bị chặn. */ } }
    drafts.clear();
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
    const query = normalizeSearch($('#message-search').value);
    const ordered = (query && searchLoaded ? searchResults : [...messages.values()]).slice().sort((a, b) => a.id - b.id);
    const visible = ordered.filter(message => !query || normalizeSearch(`${message.user.displayName} ${message.content}`).includes(query));
    $('#history-label').textContent = query ? `${visible.length} kết quả${searchHasMore ? ' trở lên' : ''} · tìm trong toàn bộ lịch sử` : `${ordered.length} tin nhắn đã tải`;
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
        catch {
          const fallback = document.createElement('textarea'); fallback.value = message.content; fallback.setAttribute('readonly', '');
          fallback.style.position = 'fixed'; fallback.style.opacity = '0'; fallback.style.left = '-10000px'; document.body.append(fallback);
          fallback.select();
          let copied = false;
          try { copied = document.execCommand('copy'); } catch { copied = false; }
          fallback.remove();
          toast(copied ? 'Đã sao chép tin nhắn.' : 'Không thể sao chép tự động. Hãy chọn văn bản để sao chép.');
        }
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
      historyHasOlder = result.length === 50;
      $('#load-older').hidden = !historyHasOlder || Boolean($('#message-search').value.trim());
      renderMessages(); $('#messages').scrollTop = $('#messages').scrollHeight; updateJump();
      if (!uncertain.has(id)) notice();
    } catch (error) {
      if (error.name === 'AbortError') return;
      if (error.status === 401) { socket.stop(); location.replace('/login'); return; }
      if (current === generation) notice('Không tải được lịch sử. Chọn lại phòng để thử lại.');
    }
  }
  async function loadOlder() {
    const term = $('#message-search').value.trim();
    if (historyBusy || roomId === null) return;
    if (term) {
      if (!searchHasMore || !searchResults.length) return;
      const id = roomId, current = generation; let oldestResult = Infinity;
      for (const message of searchResults) oldestResult = Math.min(oldestResult, message.id);
      historyBusy = true; $('#load-older').disabled = true; $('#load-older').textContent = 'Đang tải kết quả…';
      try {
        const result = await api.request(`/api/rooms/${id}/messages/search?q=${encodeURIComponent(term)}&limit=50&beforeId=${oldestResult}`);
        if (current !== generation || id !== roomId || $('#message-search').value.trim() !== term) return;
        searchResults = [...result.messages, ...searchResults]; searchHasMore = result.hasMore; renderMessages(); $('#load-older').hidden = !searchHasMore;
      } catch (error) { if (current === generation) notice(error.message); }
      finally { historyBusy = false; $('#load-older').disabled = false; $('#load-older').textContent = $('#message-search').value.trim() ? 'Tải kết quả cũ hơn' : 'Tải tin nhắn cũ hơn'; }
      return;
    }
    if (!historyHasOlder) return;
    const id = roomId, current = generation, list = $('#messages');
    let oldest = Infinity; for (const id of messages.keys()) oldest = Math.min(oldest, id);
    if (!Number.isFinite(oldest)) return;
    const scrollTop = list.scrollTop, scrollHeight = list.scrollHeight;
    historyBusy = true; $('#load-older').disabled = true; $('#load-older').textContent = 'Đang tải tin cũ…';
    try {
      const result = await api.request(`/api/rooms/${id}/messages?limit=50&beforeId=${oldest}`);
      if (current !== generation || id !== roomId) return;
      for (const message of result) if (message.roomId === roomId) messages.set(message.id, message);
      historyHasOlder = result.length === 50;
      renderMessages(); list.scrollTop = scrollTop + list.scrollHeight - scrollHeight;
      $('#load-older').hidden = !historyHasOlder;
    } catch (error) { if (current === generation) notice(error.message); }
    finally { historyBusy = false; $('#load-older').disabled = false; $('#load-older').textContent = 'Tải tin nhắn cũ hơn'; }
  }
  async function searchHistory(term) {
    const id = roomId, current = generation;
    if (!term || id === null) { searchHasMore = false; searchLoaded = false; searchResults = []; renderMessages(); return; }
    try {
      const result = await api.request(`/api/rooms/${id}/messages/search?q=${encodeURIComponent(term)}&limit=50`);
      if (current !== generation || id !== roomId || $('#message-search').value.trim() !== term) return;
      searchResults = result.messages; searchHasMore = result.hasMore; searchLoaded = true; $('#load-older').hidden = !searchHasMore; $('#load-older').textContent = 'Tải kết quả cũ hơn'; renderMessages();
    } catch (error) { if (current === generation && id === roomId) notice(error.message); }
  }
  function onEvent(event) {
    const payload = event.payload;
    if (event.type === 'system:error') { preservePending(); notice(payload.message); controls(); if (payload.code === 'ROOM_NOT_FOUND') refreshRooms().catch(() => {}); return; }
    if (payload.roomId !== roomId) return;
    if (event.type === 'room:joined') { joined = true; controls(); history(roomId); }
    if (event.type === 'chat:new') {
      messages.set(payload.id, payload);
      const term = $('#message-search').value.trim();
      if (term) { clearTimeout(searchTimer); searchTimer = setTimeout(() => searchHistory(term), 100); }
      else renderMessages();
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
    messages.clear(); activeMessageId = null; historyHasOlder = false; searchLoaded = false; searchResults = []; $('#load-older').hidden = true; closeSearch(); renderMessages(); renderMembers([]); $('#typing-indicator').textContent = ''; notice(uncertain.has(id) ? uncertainNotice : ''); cancelReply();
    const room = rooms.find(item => item.id === id), name = roomName(room);
    $('#manage-group').hidden = !room.private || room.direct;
    $('#room-name').textContent = name; $('#room-description').textContent = room.direct ? 'Trò chuyện riêng · chỉ hai người tham gia' : room.private ? `Nhóm riêng · ${room.memberCount} thành viên` : (descriptions[room.name] ?? 'Không gian dành cho cuộc trò chuyện của bạn.');
    input.placeholder = 'Viết tin nhắn…'; input.value = loadDraft(id); resize();
    document.title = `${name} · Relay`;
    for (const button of $('#room-list').children) button.setAttribute('aria-current', String(Number(button.dataset.roomId) === id));
    savePreference(`room:${user.id}`, String(id)); socket.send('room:join', { roomId }); controls();
  }
  async function loadGroupManager(roomId) {
    const data = await api.request(`/api/groups/${roomId}`);
    $('#manage-group-name').value = data.name; $('#manage-group-count').textContent = `${data.members.length} thành viên`;
    $('#manage-group-name').disabled = !data.isOwner; $('#rename-group-submit').hidden = !data.isOwner;
    $('#add-group-member-form').hidden = !data.isOwner; $('#delete-group').hidden = !data.isOwner; $('#leave-group').hidden = data.isOwner;
    $('#manage-group-hint').textContent = data.isOwner ? 'Bạn là chủ nhóm.' : 'Chủ nhóm: ' + (data.members.find(member => member.id === data.ownerId)?.displayName ?? '');
    const list = document.createDocumentFragment();
    for (const member of data.members) {
      const row = element('li', 'person-row'), info = element('div', 'person-info');
      info.append(element('strong', '', member.displayName), element('small', '', `@${member.username}${member.id === data.ownerId ? ' · Chủ nhóm' : ''}`));
      row.append(info);
      if (data.isOwner && member.id !== data.ownerId) {
        const remove = element('button', 'social-button', 'Xóa khỏi nhóm'); remove.type = 'button';
        remove.addEventListener('click', () => manageGroup('remove', { userId: member.id })); row.append(remove);
      }
      list.append(row);
    }
    $('#manage-group-members').replaceChildren(list);
    const select = $('#add-group-member'); select.replaceChildren();
    for (const friend of data.friends) { const option = element('option', '', friend.displayName); option.value = String(friend.id); select.append(option); }
    $('#add-group-member-submit').disabled = data.friends.length === 0;
    if (!data.friends.length) { const option = element('option', '', 'Không còn bạn bè có thể thêm'); option.value = ''; select.append(option); }
  }
  async function manageGroup(action, extra = {}) {
    const id = roomId; $('#manage-group-error').hidden = true;
    try {
      const result = await api.request('/api/groups/manage', { method: 'POST', body: { roomId: id, action, ...extra } });
      if (result.deleted || result.left) {
        $('#manage-group-dialog').close(); const fallback = rooms.find(room => room.id !== id)?.id ?? null; await refreshRooms(fallback); toast(result.deleted ? 'Đã xóa nhóm.' : 'Đã rời nhóm.'); return;
      }
      await refreshRooms(id); await loadGroupManager(id);
    } catch (error) { $('#manage-group-error').textContent = error.message; $('#manage-group-error').hidden = false; }
  }
  function groupManagerLoading(loading, retryable = false) {
    const dialog = $('#manage-group-dialog');
    dialog.setAttribute('aria-busy', String(loading));
    $('#manage-group-loading').textContent = loading ? 'Đang tải thông tin nhóm…' : 'Không tải được thông tin nhóm.';
    $('#manage-group-loading').hidden = !loading && !retryable;
    $('#retry-manage-group').hidden = loading || !retryable;
    for (const control of dialog.querySelectorAll('input, select, button:not([data-close-dialog])')) control.disabled = loading || (retryable && control.id !== 'retry-manage-group');
  }
  async function openGroupManager() {
    if (!roomId) return;
    if (!$('#manage-group-dialog').open) $('#manage-group-dialog').showModal();
    $('#manage-group-error').hidden = true;
    groupManagerLoading(true);
    try { await loadGroupManager(roomId); groupManagerLoading(false); }
    catch (error) { $('#manage-group-error').textContent = error.message; $('#manage-group-error').hidden = false; groupManagerLoading(false, true); }
  }
  $('#manage-group').addEventListener('click', openGroupManager);
  $('#retry-manage-group').addEventListener('click', openGroupManager);
  $('#rename-group-form').addEventListener('submit', event => { event.preventDefault(); manageGroup('rename', { name: $('#manage-group-name').value }); });
  $('#add-group-member-form').addEventListener('submit', event => { event.preventDefault(); const id = Number($('#add-group-member').value); if (id > 0) manageGroup('add', { userId: id }); });
  $('#leave-group').addEventListener('click', async () => {
    if (!window.confirm('Rời nhóm này? Bạn sẽ không còn xem được lịch sử hoặc nhận tin nhắn mới.')) return;
    await manageGroup('leave');
  });
  $('#delete-group').addEventListener('click', async () => {
    if (window.confirm('Xóa nhóm và toàn bộ lịch sử tin nhắn? Thao tác này không thể hoàn tác.')) await manageGroup('delete');
  });
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
      clearDrafts();
      stopTyping(); socket.stop(); location.replace('/login');
    } catch (error) { if (error.status === 401 || error.status === 403) await checkSession(); notice(error.message); $('#logout').disabled = false; }
  });
  $('#message-search').addEventListener('input', () => {
    clearTimeout(searchTimer); searchHasMore = false; searchLoaded = false; searchResults = []; renderMessages();
    const term = $('#message-search').value.trim();
    $('#load-older').hidden = Boolean(term) || !historyHasOlder;
    if (term) searchTimer = setTimeout(() => searchHistory(term), 250);
  });
  $('#load-older').addEventListener('click', loadOlder);
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
    clearTimeout(searchTimer); searchHasMore = false; searchLoaded = false; searchResults = [];
    const hadQuery = Boolean($('#message-search').value);
    $('#message-search').value = '';
    $('#search-panel').hidden = true;
    $('#toggle-search').setAttribute('aria-expanded', 'false');
    if (hadQuery) renderMessages();
    $('#load-older').hidden = !historyHasOlder;
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
  $('#export-messages').addEventListener('click', async () => {
    if (exportBusy || roomId === null) return;
    exportBusy = true; $('#export-messages').disabled = true;
    const id = roomId, all = new Map();
    notice('Đang tải toàn bộ lịch sử để xuất…');
    try {
      let page = await api.request(`/api/rooms/${id}/messages?limit=50`);
      while (page.length) {
        for (const message of page) all.set(message.id, message);
        if (page.length < 50) break;
        const before = Math.min(...page.map(message => message.id));
        page = await api.request(`/api/rooms/${id}/messages?limit=50&beforeId=${before}`);
      }
      if (id !== roomId) return;
    const room = rooms.find(item => item.id === roomId);
    const lines = [`Relay — ${roomName(room)}`, `Xuất lúc ${new Date().toLocaleString('vi-VN')}`, `Toàn bộ ${all.size} tin nhắn trong phòng.`, '', ...[...all.values()].sort((a, b) => a.id - b.id).map(message => `[${new Date(message.createdAt).toLocaleString('vi-VN')}] ${message.user.displayName}: ${message.content}`)];
    const url = URL.createObjectURL(new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/plain;charset=utf-8' }));
    const link = element('a'); link.href = url; link.download = `relay-phong-${roomId}-${new Date().toISOString().slice(0, 10)}.txt`; document.body.append(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000); notice(); toast(`Đã xuất toàn bộ ${all.size} tin nhắn.`);
    } catch (error) { notice(error.message); }
    finally { exportBusy = false; $('#export-messages').disabled = messages.size === 0; }
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
      button.title = room.direct ? `${room.name} · tin nhắn riêng` : room.private ? `${room.name} · nhóm riêng · ${room.memberCount} thành viên` : `${room.name} · phòng chung`;
      button.append(element('span', 'hash', room.direct ? '↔' : room.private ? '♧' : '#'), element('span', '', roomName(room)));
      button.addEventListener('click', () => chooseRoom(room.id)); fragment.append(button);
    }
    $('#room-list').replaceChildren(fragment); $('#room-count').textContent = String(rooms.length);
    if (selectId !== null) chooseRoom(selectId);
    else if (roomId !== null && !rooms.some(room => room.id === roomId)) {
      const fallback = rooms.find(room => room.id === Number(readPreference(`room:${user.id}`))) ?? rooms[0];
      if (fallback) chooseRoom(fallback.id);
      else { roomId = null; messages.clear(); renderMessages(); input.disabled = true; $('#send-message').disabled = true; notice('Bạn không còn quyền truy cập phòng này.'); }
    }
  }
  await refreshRooms();
  startSocial(api, user, refreshRooms);
  const roomRefresh = setInterval(() => { if (!document.hidden) refreshRooms().catch(() => {}); }, 3000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshRooms().catch(() => {}); });
  window.addEventListener('focus', () => refreshRooms().catch(() => {}));
  window.addEventListener('pagehide', () => clearInterval(roomRefresh));
  if (rooms.length) chooseRoom(rooms.find(room => room.id === Number(readPreference(`room:${user.id}`)))?.id ?? rooms[0].id);
  else notice('Chưa có phòng trò chuyện. Hãy tạo nhóm mới.');
  socket.connect();
}

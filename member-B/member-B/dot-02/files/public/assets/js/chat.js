import { ChatSocket } from './socket.js';

const $ = selector => document.querySelector(selector);
const initials = name => name.split(/\s+/).map(word => Array.from(word)[0]).slice(0, 2).join('').toUpperCase();
const color = id => ['avatar-lilac', 'avatar-mint', 'avatar-peach'][(id - 1) % 3];
const descriptions = { General: 'The place for everyday conversations.', Engineering: 'Build, break, learn. Share what you are making.', 'Study group': 'A little focus, a little help from your people.' };
const element = (tag, className, text) => { const node = document.createElement(tag); if (className) node.className = className; if (text !== undefined) node.textContent = text; return node; };

export async function startChat(api, user) {
  let rooms = [], roomId = null, joined = false, generation = 0, request = null, typingTimer = null, typingAt = 0, draftSent = '', sessionCheck = null, state = 'connecting';
  const messages = new Map();
  $('#self-name').textContent = user.displayName;
  $('#self-avatar').textContent = initials(user.displayName);
  $('#self-avatar').className = `avatar ${color(user.id)}`;
  function notice(text = '') { $('#chat-notice').textContent = text; $('#chat-notice').hidden = !text; }
  function controls() {
    const count = Array.from($('#message-input').value).length;
    $('#character-count').textContent = `${count} / 500`;
    $('#character-count').classList.toggle('over', count > 500);
    $('#message-input').disabled = !joined || state !== 'connected';
    $('#send-message').disabled = !joined || state !== 'connected' || Boolean(draftSent) || !$('#message-input').value.trim() || count > 500;
  }
  async function checkSession() {
    try { const me = await api.request('/api/auth/me'); if (!me.user) { socket.stop(); location.replace('/login'); } }
    catch { /* Retry after a connection attempt or after the next protected request. */ }
  }
  const socket = new ChatSocket(onEvent, newState => {
    state = newState;
    $('#connection-state').dataset.state = newState;
    $('#connection-label').textContent = { connected: 'Connected', connecting: 'Connecting…', disconnected: 'Reconnecting…' }[newState];
    if (newState !== 'connected') {
      joined = false; $('#typing-indicator').textContent = '';
      if (draftSent) { notice('Connection lost. The last message may have been saved. Check the conversation before sending it again.'); draftSent = ''; }
      clearTimeout(sessionCheck); sessionCheck = setTimeout(checkSession, 500);
    }
    controls();
  }, () => { if (roomId !== null) socket.send('room:join', { roomId }); });

  function renderMessages() {
    const list = $('#messages');
    const nearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 100;
    const fragment = document.createDocumentFragment();
    const ordered = [...messages.values()].sort((a, b) => a.id - b.id);
    if (!ordered.length) fragment.append(element('div', 'empty-state', 'It is quiet in here. Be the first to say hello.'));
    for (const message of ordered) {
      const row = element('article', `message${message.user.id === user.id ? ' own' : ''}`); row.dataset.messageId = String(message.id);
      row.append(element('span', `avatar message-avatar ${color(message.user.id)}`, initials(message.user.displayName)));
      const body = element('div', 'message-body'), meta = element('div', 'message-meta');
      meta.append(element('span', 'message-name', message.user.displayName));
      if (message.user.id === user.id) meta.append(element('span', 'you-label', '(you)'));
      const time = element('time', 'message-time', new Date(message.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })); time.dateTime = message.createdAt;
      meta.append(time); body.append(meta, element('p', 'message-text', message.content)); row.append(body); fragment.append(row);
    }
    list.replaceChildren(fragment);
    if (nearBottom) list.scrollTop = list.scrollHeight;
  }
  function renderMembers(users) {
    $('#member-count').textContent = String(users.length);
    const fragment = document.createDocumentFragment();
    for (const person of users) {
      const row = element('li', 'member'); row.append(element('span', `avatar ${color(person.id)}`, initials(person.displayName)));
      const info = element('div'), status = element('span', 'member-status'); status.append(element('span', 'live-dot'), document.createTextNode('Online'));
      info.append(element('span', 'member-name', person.displayName), status); row.append(info);
      if (person.id === user.id) row.append(element('span', 'member-you', 'you'));
      fragment.append(row);
    }
    $('#member-list').replaceChildren(fragment);
  }
  async function history(id) {
    request?.abort(); request = new AbortController();
    const current = ++generation;
    try {
      const result = await api.request(`/api/rooms/${id}/messages?limit=50`, { signal: request.signal });
      if (current !== generation || id !== roomId) return;
      for (const message of result) if (message.roomId === roomId) messages.set(message.id, message);
      renderMessages(); $('#messages').scrollTop = $('#messages').scrollHeight;
    } catch (error) {
      if (error.name === 'AbortError') return;
      if (error.status === 401) { socket.stop(); location.replace('/login'); return; }
      if (current === generation) notice('History could not be loaded. Reconnect or choose the room again to retry.');
    }
  }
  function onEvent(event) {
    const payload = event.payload;
    if (event.type === 'system:error') {
      notice(payload.message);
      if (draftSent && !$('#message-input').value) $('#message-input').value = draftSent;
      draftSent = ''; controls(); return;
    }
    if (payload.roomId !== roomId) return;
    if (event.type === 'room:joined') { joined = true; controls(); history(roomId); }
    if (event.type === 'chat:new') {
      messages.set(payload.id, payload); renderMessages();
      if (payload.user.id === user.id && payload.content === draftSent.trim()) { draftSent = ''; notice(); controls(); }
    }
    if (event.type === 'room:presence') renderMembers(payload.users);
    if (event.type === 'room:typing') {
      const names = payload.users.filter(person => person.id !== user.id).map(person => person.displayName.split(' ')[0]);
      $('#typing-indicator').textContent = names.length ? `${names.join(', ')} ${names.length === 1 ? 'is' : 'are'} typing…` : '';
    }
  }
  function stopTyping() { clearTimeout(typingTimer); if (joined) socket.send('typing:set', { roomId, isTyping: false }); typingAt = 0; }
  function chooseRoom(id) {
    stopTyping(); request?.abort(); generation++; joined = false; roomId = id;
    messages.clear(); renderMessages(); renderMembers([]); $('#typing-indicator').textContent = ''; notice();
    const room = rooms.find(item => item.id === id);
    $('#room-name').textContent = room.name; $('#room-description').textContent = descriptions[room.name] ?? 'A place for your conversation.';
    $('#message-input').placeholder = `Message #${room.name}`;
    document.title = `${room.name} · Relay`;
    for (const button of $('#room-list').children) button.setAttribute('aria-current', String(Number(button.dataset.roomId) === id));
    socket.send('room:join', { roomId }); controls();
  }
  $('#message-input').addEventListener('input', () => {
    controls(); clearTimeout(typingTimer);
    if (!joined) return;
    if (!$('#message-input').value.trim()) { stopTyping(); return; }
    if (Date.now() - typingAt > 1000) { socket.send('typing:set', { roomId, isTyping: true }); typingAt = Date.now(); }
    typingTimer = setTimeout(stopTyping, 2800);
    $('#message-input').style.height = 'auto'; $('#message-input').style.height = `${Math.min(120, $('#message-input').scrollHeight)}px`;
  });
  $('#message-input').addEventListener('keydown', event => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); if (!$('#send-message').disabled) $('#message-form').requestSubmit(); }
  });
  $('#message-form').addEventListener('submit', event => {
    event.preventDefault(); controls();
    if ($('#send-message').disabled) return;
    const content = $('#message-input').value.trim();
    if (!socket.send('chat:send', { roomId, content })) { notice('You are offline. Wait for the connection to return.'); return; }
    draftSent = content; $('#message-input').value = ''; $('#message-input').style.height = 'auto'; stopTyping(); controls();
  });
  $('#logout').addEventListener('click', async () => {
    $('#logout').disabled = true;
    try { await api.request('/api/auth/logout', { method: 'POST', body: {} }); stopTyping(); socket.stop(); location.replace('/login'); }
    catch (error) { if (error.status === 401 || error.status === 403) { await checkSession(); } notice(error.message); $('#logout').disabled = false; }
  });
  window.addEventListener('pagehide', () => { stopTyping(); socket.stop(); clearTimeout(sessionCheck); });
  window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
  window.addEventListener('online', () => { if (!socket.ready && !socket.stopped) socket.connect(); });
  window.addEventListener('offline', () => socket.networkLost());
  rooms = await api.request('/api/rooms');
  const fragment = document.createDocumentFragment();
  for (const room of rooms) { const button = element('button', 'room-button'); button.type = 'button'; button.dataset.roomId = String(room.id); button.append(element('span', 'hash', '#'), element('span', '', room.name)); button.addEventListener('click', () => chooseRoom(room.id)); fragment.append(button); }
  $('#room-list').replaceChildren(fragment); $('#room-count').textContent = String(rooms.length);
  if (rooms.length) chooseRoom(rooms[0].id);
  socket.connect();
}

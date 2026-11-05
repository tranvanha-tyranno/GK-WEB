const $ = selector => document.querySelector(selector);
const node = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text !== undefined) n.textContent = text; return n; };
const descriptions = {
  all: 'Tìm theo tên hoặc tên đăng nhập, hỗ trợ tìm không dấu.', friends: 'Những người bạn đã kết nối trên Relay.',
  requests: 'Chấp nhận lời mời đến hoặc hủy lời mời đã gửi.', suggestions: 'Ưu tiên người có bạn chung và nhóm riêng chung với bạn.',
  nearby: 'Người dùng đang chia sẻ vị trí trong bán kính 10 km.', random: 'Một cơ hội làm quen mới. Nhấn ↻ để đổi thứ tự gợi ý.'
};
export function startSocial(api, user, refreshRooms) {
  let mode = 'all', version = 0, timer, busy = false, groupBusy = false, locating = false;
  const dialog = $('#friends-dialog'), groupDialog = $('#group-dialog');
  function notice(message = '') { $('#social-notice').textContent = message; $('#social-notice').hidden = !message; }
  function button(label, action, primary = false) {
    const b = node('button', `social-button${primary ? ' primary' : ''}`, label); b.type = 'button'; b.addEventListener('click', action); return b;
  }
  async function action(person, kind) {
    if (busy) return; busy = true;
    for (const b of $('#people-list').querySelectorAll('button')) b.disabled = true;
    try { await api.request('/api/friends', { method: 'POST', body: { userId: person.id, action: kind } }); await load(); await badge(); }
    catch (error) { notice(error.message); }
    finally { busy = false; for (const b of $('#people-list').querySelectorAll('button')) b.disabled = false; }
  }
  async function load() {
    const current = ++version;
    $('#people-description').textContent = descriptions[mode]; $('#location-panel').hidden = mode !== 'nearby';
    $('#people-list').setAttribute('aria-busy', 'true');
    try {
      const result = await api.request(`/api/people?mode=${mode}&q=${encodeURIComponent($('#people-search').value)}`);
      if (current !== version || !dialog.open) return;
      $('#share-location').textContent = result.locationEnabled ? 'Cập nhật vị trí' : 'Chia sẻ vị trí'; $('#stop-location').hidden = !result.locationEnabled;
      const fragment = document.createDocumentFragment();
      if (!result.people.length) fragment.append(node('li', 'social-empty', mode === 'nearby' ? (result.locationEnabled ? 'Chưa có ai đang chia sẻ vị trí trong 10 km. Hãy quay lại sau.' : 'Bật chia sẻ vị trí để tìm bạn lân cận.') : mode === 'friends' ? 'Chưa có bạn bè. Tìm người và gửi lời mời đầu tiên nhé.' : mode === 'requests' ? 'Bạn chưa có lời mời kết bạn.' : 'Chưa có người phù hợp. Thử tìm tên khác hoặc quay lại sau.'));
      for (const person of result.people) {
        const row = node('li', 'person-row');
        row.append(node('span', `avatar ${['avatar-lilac', 'avatar-mint', 'avatar-peach'][(person.id - 1) % 3]}`, person.displayName.split(/\s+/).map(n => Array.from(n)[0]).slice(0, 2).join('')));
        const info = node('div', 'person-info'); info.append(node('strong', '', person.displayName), node('small', '', `@${person.username}`));
        const reasons = [];
        if (person.mutualFriends) reasons.push(`${person.mutualFriends} bạn chung`);
        if (person.sharedGroups) reasons.push(`${person.sharedGroups} nhóm chung`);
        if (mode === 'nearby' && person.distanceKm !== null) reasons.push(`Cách khoảng ${person.distanceKm.toLocaleString('vi-VN')} km`);
        if (!reasons.length && mode === 'suggestions') reasons.push('Có thể bạn muốn làm quen');
        if (!reasons.length && mode === 'random') reasons.push('Một kết nối mới dành cho bạn');
        if (reasons.length) info.append(node('small', 'person-reason', reasons.join(' · ')));
        const actions = node('div', 'person-actions');
        if (person.relation === 'incoming') actions.append(button('Chấp nhận', () => action(person, 'accept'), true), button('Từ chối', () => action(person, 'remove')));
        else if (person.relation === 'outgoing') actions.append(node('small', 'relation-label', 'Đã gửi lời mời'), button('Hủy lời mời', () => action(person, 'remove')));
        else if (person.relation === 'friends') actions.append(node('small', 'relation-label', '✓ Bạn bè'), button('Hủy kết bạn', () => {
          actions.replaceChildren(node('small', '', 'Hủy kết bạn?'), button('Đồng ý hủy', () => action(person, 'remove')), button('Giữ lại', () => load()));
        }));
        else actions.append(button('Kết bạn +', () => action(person, 'send'), true));
        row.append(info, actions); fragment.append(row);
      }
      $('#people-list').replaceChildren(fragment);
    } catch (error) { if (current === version) notice(error.message); }
    finally { if (current === version) $('#people-list').setAttribute('aria-busy', 'false'); }
  }
  async function badge() {
    try { const { people } = await api.request('/api/people?mode=requests'); const count = people.filter(p => p.relation === 'incoming').length; $('#friend-badge').textContent = String(count); $('#friend-badge').hidden = count === 0; }
    catch { /* Retry on next refresh. */ }
  }
  $('#open-friends').addEventListener('click', () => { dialog.showModal(); notice(); load(); });
  dialog.addEventListener('close', () => { ++version; $('#open-friends').focus(); });
  groupDialog.addEventListener('close', () => { if (!groupBusy) $('#open-create-group').focus(); });
  for (const tab of document.querySelectorAll('[data-people-mode]')) tab.addEventListener('click', () => {
    clearTimeout(timer); mode = tab.dataset.peopleMode;
    for (const t of document.querySelectorAll('[data-people-mode]')) t.setAttribute('aria-pressed', String(t === tab));
    notice(); load();
  });
  $('#people-search').addEventListener('input', () => { clearTimeout(timer); ++version; timer = setTimeout(() => { notice(); load(); }, 250); });
  $('#refresh-people').addEventListener('click', () => { notice(); load(); badge(); });
  $('#share-location').addEventListener('click', async () => {
    if (locating) return;
    if (!navigator.geolocation) { notice('Trình duyệt không hỗ trợ vị trí. Bạn vẫn có thể dùng gợi ý và ngẫu nhiên.'); return; }
    locating = true; $('#share-location').disabled = true; $('#stop-location').disabled = true; notice('Đang lấy vị trí. Hãy cho phép truy cập vị trí khi trình duyệt hỏi.');
    try {
      const position = await new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 }));
      await api.request('/api/location', { method: 'POST', body: { latitude: position.coords.latitude, longitude: position.coords.longitude } });
      notice('Đã bật chia sẻ trong 24 giờ. Bạn có thể tắt bất cứ lúc nào.'); await load();
    } catch (error) { notice(error.code === 1 ? 'Bạn chưa cho phép truy cập vị trí. Có thể bật lại trong quyền của trang web.' : 'Không lấy được vị trí. Hãy kiểm tra quyền vị trí và thử lại.'); }
    finally { locating = false; $('#share-location').disabled = false; $('#stop-location').disabled = false; }
  });
  $('#stop-location').addEventListener('click', async () => {
    $('#stop-location').disabled = true; $('#share-location').disabled = true;
    try { await api.request('/api/location', { method: 'POST', body: { enabled: false } }); notice('Đã xóa vị trí đã chia sẻ.'); await load(); }
    catch (error) { notice(error.message); } finally { $('#stop-location').disabled = false; $('#share-location').disabled = false; }
  });
  $('#open-create-group').addEventListener('click', async () => {
    $('#create-group-form').reset(); $('#group-friends').replaceChildren(); $('#group-error').hidden = true; $('#group-friends').textContent = 'Đang tải bạn bè…'; groupDialog.showModal(); updateSelected();
    try {
      const { people } = await api.request('/api/people?mode=friends');
      $('#group-friends').replaceChildren();
      if (!people.length) $('#group-friends').append(node('p', 'social-empty', 'Kết bạn trước để thêm người vào nhóm, hoặc tạo nhóm cho riêng bạn.'));
      for (const p of people) {
        const label = node('label', 'group-choice'), checkbox = node('input'); checkbox.type = 'checkbox'; checkbox.value = String(p.id); checkbox.name = 'member'; checkbox.addEventListener('change', updateSelected);
        label.append(checkbox, node('span', '', p.displayName), node('small', '', `@${p.username}`)); $('#group-friends').append(label);
      }
    } catch (error) { $('#group-friends').textContent = error.message; }
  });
  function updateSelected() { const count = $('#group-friends').querySelectorAll('input:checked').length; $('#group-selected').textContent = `${count} / 30`; $('#group-total').textContent = `${count + 1} thành viên, gồm bạn`; for (const c of $('#group-friends').querySelectorAll('input:not(:checked)')) c.disabled = count >= 30; }
  $('#create-group-form').addEventListener('submit', async event => {
    event.preventDefault(); if (groupBusy) return; groupBusy = true; $('#create-group-submit').disabled = true; $('#group-error').hidden = true;
    try {
      const room = await api.request('/api/groups', { method: 'POST', body: { name: $('#group-name').value.trim(), memberIds: [...$('#group-friends').querySelectorAll('input:checked')].map(c => Number(c.value)) } });
      groupDialog.close();
      await refreshRooms(room.id);
    } catch (error) { if (!groupDialog.open) groupDialog.showModal(); $('#group-error').textContent = error.message; $('#group-error').hidden = false; }
    finally { groupBusy = false; $('#create-group-submit').disabled = false; }
  });
  $('#open-friends').disabled = false; $('#open-create-group').disabled = false;
  badge();
  const interval = setInterval(() => { if (document.hidden) return; badge(); if (dialog.open && !busy && mode !== 'random' && !$('#people-list').contains(document.activeElement)) load(); }, 10000);
  window.addEventListener('pagehide', () => { clearInterval(interval); clearTimeout(timer); });
}

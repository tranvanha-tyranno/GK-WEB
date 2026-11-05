async (page) => {
  const base = 'http://localhost:8088', checks = [], errors = [];
  const assert = (ok, message) => { if (!ok) throw new Error(message); checks.push(message); };
  if (new URL(page.url()).origin !== base) throw new Error('Run only on isolated test stack localhost:8088');
  const browser = page.context().browser();
  const bobContext = await browser.newContext({ geolocation: { latitude: 10.777, longitude: 106.7 }, permissions: ['geolocation'] });
  const charlieContext = await browser.newContext();
  const bob = await bobContext.newPage(), charlie = await charlieContext.newPage();
  for (const p of [page, bob, charlie]) p.on('pageerror', e => errors.push(e.message));
  const login = async (p, username) => {
    await p.goto(base + '/login');
    await p.getByRole('textbox', { name: 'Tên đăng nhập' }).fill(username);
    await p.getByRole('textbox', { name: 'Mật khẩu', exact: true }).fill('ChatDemo!2026');
    await p.getByRole('button', { name: 'Đăng nhập vào không gian chung' }).click();
    await p.waitForURL('**/chat'); await p.getByRole('status', { name: 'Đã kết nối', exact: true }).waitFor();
  };
  try {
    await page.reload(); await page.getByRole('status', { name: 'Đã kết nối', exact: true }).waitFor(); await page.getByRole('button', { name: '♧ Bạn bè' }).click();
    await page.getByRole('searchbox', { name: 'Tìm bạn bè' }).fill('bob');
    const row = page.getByRole('listitem').filter({ hasText: '@bob' });
    await row.waitFor();
    await page.waitForFunction(() => document.querySelector('#people-list').getAttribute('aria-busy') === 'false');
    if (await row.getByRole('button', { name: 'Hủy kết bạn', exact: true }).count()) {
      await row.getByRole('button', { name: 'Hủy kết bạn', exact: true }).click();
      await row.getByRole('button', { name: 'Đồng ý hủy' }).click();
    } else if (await row.getByRole('button', { name: 'Hủy lời mời' }).count()) {
      await row.getByRole('button', { name: 'Hủy lời mời' }).click();
    } else if (await row.getByRole('button', { name: 'Từ chối' }).count()) {
      await row.getByRole('button', { name: 'Từ chối' }).click();
    }
    await row.getByRole('button', { name: 'Kết bạn +' }).click();
    await row.getByText('Đã gửi lời mời', { exact: true }).waitFor();
    assert(true, 'Alice sends friend request through UI');
    await login(bob, 'bob');
    await bob.getByRole('button', { name: /♧ Bạn bè/ }).click();
    await bob.getByRole('button', { name: 'Lời mời', exact: true }).click();
    await bob.getByRole('listitem').filter({ hasText: '@alice' }).getByRole('button', { name: 'Chấp nhận' }).click();
    await bob.getByRole('button', { name: 'Bạn bè', exact: true }).click();
    await bob.getByRole('listitem').filter({ hasText: '@alice' }).getByText('✓ Bạn bè', { exact: true }).waitFor();
    assert(true, 'Bob accepts; friendship is displayed');
    await page.getByRole('button', { name: 'Đóng bạn bè' }).click();
    await page.getByRole('button', { name: '＋ Tạo nhóm' }).click();
    const groupName = 'Nhóm kiểm tra ' + Date.now();
    await page.getByLabel('Tên nhóm', { exact: true }).fill(groupName);
    await page.getByRole('checkbox', { name: /Bob Tran/ }).check();
    await page.getByRole('button', { name: 'Tạo nhóm ↗', exact: true }).click();
    await page.getByRole('heading', { name: groupName, exact: true }).waitFor();
    await page.getByRole('textbox', { name: 'Tin nhắn của bạn' }).fill('Xin chào nhóm mới!');
    await page.getByRole('button', { name: 'Gửi tin nhắn' }).click();
    await page.getByRole('log').getByText('Xin chào nhóm mới!', { exact: true }).waitFor();
    const rooms = await (await page.request.get(base + '/api/rooms')).json();
    const room = rooms.data.find(r => r.name === groupName);
    await bob.getByRole('button', { name: 'Đóng bạn bè' }).click();
    await bob.getByRole('button', { name: '♧ ' + groupName, exact: true }).click({ timeout: 15000 });
    await bob.getByRole('log').getByText('Xin chào nhóm mới!', { exact: true }).waitFor();
    await bob.getByRole('textbox', { name: 'Tin nhắn của bạn' }).fill('Bob đã vào nhóm.');
    await bob.getByRole('button', { name: 'Gửi tin nhắn' }).click();
    await page.getByRole('log').getByText('Bob đã vào nhóm.', { exact: true }).waitFor();
    assert(true, 'Selected member discovers group and chat works in both directions');
    await login(charlie, 'charlie');
    const forbidden = await charlie.request.get(`${base}/api/rooms/${room.id}/messages`);
    assert(forbidden.status() === 404, 'Outsider cannot read private group history');
    const outsiderRooms = (await (await charlie.request.get(base + '/api/rooms')).json()).data;
    assert(!outsiderRooms.some(r => r.id === room.id), 'Outsider cannot discover private group');
    const csrf = (await (await page.request.get(base + '/api/auth/me')).json()).data.csrfToken;
    const denied = await page.request.post(base + '/api/friends', { headers: { Origin: base, 'X-CSRF-Token': 'wrong' }, data: { userId: 3, action: 'send' } });
    assert(denied.status() === 403, 'Social mutation rejects invalid CSRF token');
    await page.context().setGeolocation({ latitude: 10.776, longitude: 106.7 });
    await page.context().grantPermissions(['geolocation'], { origin: base });
    for (const p of [page, bob]) {
      await p.getByRole('button', { name: /♧ Bạn bè/ }).click();
      await p.getByRole('searchbox', { name: 'Tìm bạn bè' }).fill('');
      await p.getByRole('button', { name: 'Lân cận', exact: true }).click();
      await p.waitForFunction(() => document.querySelector('#people-list').getAttribute('aria-busy') === 'false');
      await p.getByRole('button', { name: /^(Chia sẻ vị trí|Cập nhật vị trí)$/ }).click();
      await p.getByText('Đã bật chia sẻ trong 24 giờ.', { exact: false }).waitFor();
    }
    await page.getByRole('button', { name: 'Làm mới danh sách bạn bè' }).click();
    await page.getByRole('listitem').filter({ hasText: '@bob' }).getByText(/Cách khoảng/).waitFor();
    assert(true, 'Nearby finds consenting user using browser geolocation');
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.screenshot({ path: 'output/playwright/social-desktop.png' });
    for (const width of [390, 320]) {
      await page.setViewportSize({ width, height: 844 });
      assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth && document.querySelector('#friends-dialog').scrollWidth <= document.querySelector('#friends-dialog').clientWidth), `Friend dialog fits mobile width ${width}`);
    }
    await page.screenshot({ path: 'output/playwright/social-mobile.png' });
    await page.getByRole('button', { name: 'Tắt chia sẻ', exact: true }).click();
    await page.getByText('Đã xóa vị trí đã chia sẻ.', { exact: true }).waitFor();
    await page.getByText('Bật chia sẻ vị trí để tìm bạn lân cận.', { exact: true }).waitFor();
    assert(true, 'Disabling sharing removes saved location');
    await page.getByRole('button', { name: 'Ngẫu nhiên', exact: true }).click();
    await page.getByRole('listitem').filter({ hasText: '@charlie' }).waitFor();
    assert(await page.getByRole('listitem').count() === 1, 'Random suggests a new user and excludes existing friend');
    assert(errors.length === 0, 'No browser JavaScript errors');
    return { checks, groupId: room.id, csrfAvailable: !!csrf };
  } finally { await bobContext.close(); await charlieContext.close(); }
}

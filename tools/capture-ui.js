async (page) => {
  await page.context().setOffline(false);
  await page.goto('http://localhost:8080/login');
  await page.waitForFunction(() => document.body.dataset.page === 'chat' || document.querySelector('#login-submit')?.disabled === false);
  if (await page.getByRole('button', { name: 'BT Bob', exact: true }).count()) {
    await page.getByRole('button', { name: 'BT Bob', exact: true }).click();
    await page.getByRole('button', { name: 'Đăng nhập vào không gian chung', exact: true }).click();
  }
  await page.getByText('Đã kết nối', { exact: true }).waitFor();
  await page.getByRole('log').getByText('Chào mừng đến với Relay. Hãy chọn phòng và gửi lời chào!', { exact: true }).waitFor();
  const box = page.getByRole('textbox', { name: 'Tin nhắn của bạn' });
  const text = 'Mình thấy tin mới ngay rồi. Cùng trao đổi tiếp nhé!';
  await box.fill(text); await box.press('Enter');
  await page.getByRole('log').getByText(text, { exact: true }).last().waitFor();
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.screenshot({ path: 'output/playwright/EV-06-chat-desktop.png' });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.screenshot({ path: 'output/playwright/EV-07-chat-mobile.png' });
  await page.setViewportSize({ width: 1440, height: 900 });
  return { status: 'PASS', actualUi: true, historyLoadedBeforeScreenshot: true, noDomFabrication: true, screenshots: ['EV-06-chat-desktop.png','EV-07-chat-mobile.png'] };
}

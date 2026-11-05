async (page) => {
  await page.setViewportSize({width:1440,height:900});
  await page.goto('http://localhost:8080/login');
  await page.waitForFunction(() => document.body.dataset.page === 'chat' || document.querySelector('#login-submit')?.disabled === false);
  if (await page.locator('#login-form').count()) {
    await page.getByRole('button',{name:'AN Alice',exact:true}).click();
    await page.getByRole('button',{name:'Đăng nhập vào không gian chung',exact:true}).click();
  }
  await page.getByText('Đã kết nối',{exact:true}).waitFor();
  const tool = async name => {
    await page.getByRole('button',{name:'Mở công cụ phòng',exact:true}).click();
    await page.getByRole('button',{name,exact:true}).click();
  };
  if (await page.evaluate(() => document.documentElement.dataset.theme === 'dark')) await tool('Bật giao diện sáng');
  if (await page.locator('#toggle-compact').getAttribute('aria-pressed') === 'true') await tool('Thu gọn');
  await page.getByRole('button',{name:'# Trò chuyện chung',exact:true}).click();
  await page.waitForTimeout(500);
  await page.locator('#room-name').click();
  await page.locator('#room-name').click();
  await page.screenshot({path:'output/playwright/clean-chat-desktop.png'});
  await page.setViewportSize({width:390,height:844});
  await page.locator('#room-name').click();
  await page.screenshot({path:'output/playwright/clean-chat-mobile.png'});
  await page.setViewportSize({width:1440,height:900});
  return {status:'PASS',screenshots:['clean-chat-desktop.png','clean-chat-mobile.png'],realBrowser:true};
}

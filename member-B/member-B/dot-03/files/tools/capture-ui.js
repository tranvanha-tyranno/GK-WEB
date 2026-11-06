async (page) => {
  await page.context().setOffline(false);
  await page.goto('http://localhost:8080/login');
  await page.waitForFunction(() => document.body.dataset.page === 'chat' || document.querySelector('#login-submit')?.disabled === false);
  if (await page.getByRole('button', { name: 'BT Bob', exact: true }).count()) {
    await page.getByRole('button', { name: 'BT Bob', exact: true }).click();
    await page.getByRole('button', { name: 'Sign in to your workspace', exact: true }).click();
  }
  await page.getByText('Connected', { exact: true }).waitFor();
  await page.getByRole('log').getByText('Welcome to Relay. Choose a room and say hello!', { exact: true }).waitFor();
  const box = page.getByRole('textbox', { name: 'Your message' });
  const text = 'I can see your update right away. The conversation is live!';
  await box.fill(text); await box.press('Enter');
  await page.getByRole('log').getByText(text, { exact: true }).last().waitFor();
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.screenshot({ path: 'output/playwright/EV-06-chat-desktop.png' });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.screenshot({ path: 'output/playwright/EV-07-chat-mobile.png' });
  await page.setViewportSize({ width: 1440, height: 900 });
  return { status: 'PASS', actualUi: true, historyLoadedBeforeScreenshot: true, noDomFabrication: true, screenshots: ['EV-06-chat-desktop.png','EV-07-chat-mobile.png'] };
}

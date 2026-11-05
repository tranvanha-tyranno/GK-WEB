async (page) => {
  await page.getByRole('button', { name: 'Đóng bạn bè' }).click();
  await page.getByRole('button', { name: '＋ Tạo nhóm' }).click();
  await page.getByRole('checkbox', { name: /Bob Tran/ }).waitFor();
  const checks = [];
  for (const width of [320, 390, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    if (!await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth && document.querySelector('#group-dialog').scrollWidth <= document.querySelector('#group-dialog').clientWidth)) throw new Error('Group dialog overflow ' + width);
    checks.push('Group dialog fits ' + width);
  }
  await page.screenshot({ path: 'output/playwright/social-create-group.png' });
  await page.getByRole('button', { name: 'Đóng tạo nhóm' }).click();
  await page.getByRole('button', { name: 'Mở công cụ phòng' }).click();
  await page.getByRole('button', { name: 'Bật giao diện tối' }).click();
  await page.getByRole('button', { name: /♧ Bạn bè/ }).click();
  await page.getByRole('button', { name: 'Gợi ý', exact: true }).click();
  await page.getByRole('listitem').filter({ hasText: '@charlie' }).waitFor();
  await page.screenshot({ path: 'output/playwright/social-dark.png' });
  checks.push('Dark theme uses existing theme toggle');
  return checks;
}

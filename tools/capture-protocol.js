async (page) => {
  const session = await page.context().newCDPSession(page);
  const handshakes = [], events = [];
  await session.send('Network.enable');
  session.on('Network.webSocketHandshakeResponseReceived', ({ response }) => {
    // Never serialize request headers or cookie values into evidence.
    handshakes.push({ status: response.status, statusText: response.statusText, headers: Object.fromEntries(Object.entries(response.headers).filter(([key]) => ['upgrade','connection'].includes(key.toLowerCase()))) });
  });
  function frame(direction, response) {
    if (response.opcode !== 1) return;
    try {
      const data = JSON.parse(response.payloadData);
      events.push({ direction, type: data.type, roomId: data.payload?.roomId, messageId: data.type === 'chat:new' ? data.payload.id : undefined, errorCode: data.type === 'system:error' ? data.payload.code : undefined });
    } catch {}
  }
  session.on('Network.webSocketFrameSent', ({ response }) => frame('client-to-server', response));
  session.on('Network.webSocketFrameReceived', ({ response }) => frame('server-to-client', response));
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.reload();
  await page.getByText('Connected', { exact: true }).waitFor();
  await page.getByRole('textbox', { name: 'Your message' }).fill('Protocol evidence: this message is stored before it is pushed.');
  await page.getByRole('textbox', { name: 'Your message' }).press('Enter');
  await page.getByRole('log').getByText('Protocol evidence: this message is stored before it is pushed.', { exact: true }).last().waitFor();
  await page.screenshot({ path: 'output/playwright/EV-02-protocol-chat.png' });
  await session.detach();
  if (!handshakes.some(h => h.status === 101)) throw new Error('No 101 handshake captured');
  if (!events.some(e => e.type === 'chat:send') || !events.some(e => e.type === 'chat:new')) throw new Error('Missing expected application events');
  return { status: 'PASS', source: 'Chromium DevTools Protocol network events from the running app', handshakes, events };
}

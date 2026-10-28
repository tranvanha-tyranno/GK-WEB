import { Api } from './api.js';
import { startChat } from './chat.js';

const api = new Api();
const $ = selector => document.querySelector(selector);
async function init() {
  const { user } = await api.request('/api/auth/me');
  if (document.body.dataset.page === 'chat') {
    if (!user) { location.replace('/login'); return; }
    await startChat(api, user); return;
  }
  if (user) { location.replace('/chat'); return; }
  $('#toggle-password').addEventListener('click', () => {
    const show = $('#password').type === 'password'; $('#password').type = show ? 'text' : 'password';
    $('#toggle-password').textContent = show ? 'Hide' : 'Show'; $('#toggle-password').setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });
  for (const button of document.querySelectorAll('[data-demo]')) button.addEventListener('click', () => { $('#username').value = button.dataset.demo; $('#password').value = 'ChatDemo!2026'; $('#login-submit').focus(); });
  $('#login-form').addEventListener('submit', async event => {
    event.preventDefault(); $('#login-error').hidden = true; $('#login-submit').disabled = true;
    try {
      await api.request('/api/auth/login', { method: 'POST', body: { username: $('#username').value, password: $('#password').value } }); location.assign('/chat');
    } catch (error) {
      $('#login-error').textContent = error.message; $('#login-error').hidden = false; $('#login-submit').disabled = false;
      if (error.status === 403) await api.request('/api/auth/me');
    }
  });
  for (const button of document.querySelectorAll('#login-submit, #toggle-password, [data-demo]')) button.disabled = false;
}
init().catch(error => {
  if ($('#login-error')) { $('#login-error').textContent = 'Cannot connect to the service. Check the server and reload the page.'; $('#login-error').hidden = false; }
  else { $('#toast').textContent = error.message || 'Cannot connect to the service. Reload to try again.'; $('#toast').hidden = false; }
});

import { Api } from './api.js';
import { startChat } from './chat.js';
import { applyTheme, readPreference } from './preferences.js';

applyTheme(readPreference('theme', matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
const api = new Api();
const $ = selector => document.querySelector(selector);
async function init() {
  const { user } = await api.request('/api/auth/me');
  if (document.body.dataset.page === 'chat') {
    if (!user) { location.replace('/login'); return; }
    await startChat(api, user); return;
  }
  if (user) { location.replace('/chat'); return; }
  if (document.body.dataset.page === 'register') {
    const form = $('#register-form');
    form.addEventListener('submit', async event => {
      event.preventDefault(); $('#register-error').hidden = true; $('#register-submit').disabled = true;
      try {
        await api.request('/api/auth/register', { method: 'POST', body: { username: $('#register-username').value, displayName: $('#register-display-name').value, password: $('#register-password').value } });
        location.assign('/chat');
      } catch (error) { $('#register-error').textContent = error.message; $('#register-error').hidden = false; $('#register-submit').disabled = false; }
    });
    $('#register-submit').disabled = false;
    return;
  }
  $('#toggle-password').addEventListener('click', () => {
    const show = $('#password').type === 'password'; $('#password').type = show ? 'text' : 'password';
    $('#toggle-password').textContent = show ? 'Ẩn' : 'Hiện'; $('#toggle-password').setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
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
  if ($('#login-error')) { $('#login-error').textContent = 'Không kết nối được máy chủ. Hãy kiểm tra kết nối rồi tải lại trang.'; $('#login-error').hidden = false; }
  else if ($('#register-error')) { $('#register-error').textContent = 'Không kết nối được máy chủ. Hãy kiểm tra kết nối rồi tải lại trang.'; $('#register-error').hidden = false; }
  else { $('#toast').textContent = error.message || 'Không kết nối được máy chủ. Hãy tải lại trang để thử lại.'; $('#toast').hidden = false; }
});

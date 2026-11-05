export function readPreference(key, fallback = '') {
  try { return localStorage.getItem(`relay:${key}`) ?? fallback; } catch { return fallback; }
}
export function savePreference(key, value) {
  try { localStorage.setItem(`relay:${key}`, value); } catch { /* Vẫn hoạt động khi bộ nhớ bị chặn. */ }
}
export function applyTheme(theme) {
  document.documentElement.dataset.theme = theme === 'dark' ? 'dark' : 'light';
}
export const normalizeSearch = text => text.normalize('NFD').replace(/\p{M}/gu, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLocaleLowerCase('vi').trim();
export const roomName = room => ({ General: 'Trò chuyện chung', Engineering: 'Kỹ thuật', 'Study group': 'Nhóm học tập' })[room.name] ?? room.name;

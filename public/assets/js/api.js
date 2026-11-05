export class ApiError extends Error {
  constructor(status, code, message) { super(message); this.status = status; this.code = code; }
}
export class Api {
  csrf = '';
  async request(path, { method = 'GET', body, signal } = {}) {
    const headers = { Accept: 'application/json' };
    if (method !== 'GET') { headers['X-CSRF-Token'] = this.csrf; headers['Content-Type'] = 'application/json'; }
    let response;
    try { response = await fetch(path, { method, headers, credentials: 'same-origin', cache: 'no-store', signal, ...(body === undefined ? {} : { body: JSON.stringify(body) }) }); }
    catch (error) {
      if (error.name === 'AbortError') throw error;
      throw new ApiError(0, 'NETWORK_ERROR', 'Không kết nối được máy chủ. Hãy kiểm tra mạng rồi thử lại.');
    }
    let json;
    try { json = await response.json(); }
    catch { throw new ApiError(response.status, 'BAD_RESPONSE', 'Máy chủ trả về phản hồi không hợp lệ.'); }
    if (!response.ok) throw new ApiError(response.status, json.error?.code ?? 'REQUEST_FAILED', json.error?.message ?? 'Không thể thực hiện yêu cầu.');
    if (json.data?.csrfToken) this.csrf = json.data.csrfToken;
    return json.data;
  }
}

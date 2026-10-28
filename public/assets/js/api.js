export class ApiError extends Error {
  constructor(status, code, message) { super(message); this.status = status; this.code = code; }
}
export class Api {
  csrf = '';
  async request(path, { method = 'GET', body, signal } = {}) {
    const headers = { Accept: 'application/json' };
    if (method !== 'GET') { headers['X-CSRF-Token'] = this.csrf; headers['Content-Type'] = 'application/json'; }
    const response = await fetch(path, { method, headers, credentials: 'same-origin', cache: 'no-store', signal, ...(body === undefined ? {} : { body: JSON.stringify(body) }) });
    let json;
    try { json = await response.json(); }
    catch { throw new ApiError(response.status, 'BAD_RESPONSE', 'The service returned an unexpected response.'); }
    if (!response.ok) throw new ApiError(response.status, json.error?.code ?? 'REQUEST_FAILED', json.error?.message ?? 'The request failed.');
    if (json.data?.csrfToken) this.csrf = json.data.csrfToken;
    return json.data;
  }
}

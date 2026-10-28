export class ChatSocket {
  socket = null; stopped = false; attempts = 0; timer = null;
  constructor(onEvent, onState, onReconnect) { this.onEvent = onEvent; this.onState = onState; this.onReconnect = onReconnect; }
  connect() {
    if (this.stopped) return;
    clearTimeout(this.timer);
    if (!navigator.onLine) { this.onState('disconnected'); this.timer = setTimeout(() => this.connect(), 10000); return; }
    this.onState('connecting');
    const url = new URL('/ws', window.location.href); url.protocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
    const socket = this.socket = new WebSocket(url);
    socket.addEventListener('open', () => { if (this.socket !== socket || this.stopped) { socket.close(); return; } this.attempts = 0; this.onState('connected'); this.onReconnect(); });
    socket.addEventListener('message', event => {
      if (this.socket !== socket || this.stopped) return;
      try { const data = JSON.parse(event.data); if (data && typeof data.type === 'string' && data.payload) this.onEvent(data); }
      catch { this.onState('disconnected'); socket.close(); }
    });
    socket.addEventListener('close', () => {
      if (this.socket !== socket || this.stopped) return;
      this.onState('disconnected');
      const delay = [1000, 2000, 4000, 8000, 10000][Math.min(this.attempts++, 4)];
      this.timer = setTimeout(() => this.connect(), delay);
    });
    socket.addEventListener('error', () => { /* The close handler performs retry. */ });
  }
  get ready() { return !this.stopped && this.socket?.readyState === WebSocket.OPEN; }
  networkLost() { this.onState('disconnected'); this.socket?.close(); }
  send(type, payload) { if (!this.ready) return false; this.socket.send(JSON.stringify({ type, payload })); return true; }
  stop() { this.stopped = true; clearTimeout(this.timer); this.socket?.close(); this.socket = null; }
}

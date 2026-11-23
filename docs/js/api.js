/**
 * Cliente HTTP + autenticação JWT (localStorage).
 */
const Auth = {
  key: 'cc_token',
  userKey: 'cc_user',

  getToken() {
    return localStorage.getItem(this.key);
  },

  getUser() {
    const raw = localStorage.getItem(this.userKey);
    return raw ? JSON.parse(raw) : null;
  },

  setSession(auth) {
    localStorage.setItem(this.key, auth.token);
    localStorage.setItem(this.userKey, JSON.stringify({
      idUsuario: auth.idUsuario,
      nome: auth.nome,
      email: auth.email
    }));
  },

  clear() {
    localStorage.removeItem(this.key);
    localStorage.removeItem(this.userKey);
  },

  requireLogin() {
    if (!this.getToken()) {
      window.location.href = this.href('login.html');
      return false;
    }
    return true;
  },

  href(page) {
    // Funciona tanto na raiz do Pages quanto em /repo/
    const base = document.querySelector('base')?.href
      || window.location.href.replace(/\/pages\/.*$/, '/').replace(/\/[^/]*\.html.*$/, '/');
    if (page.startsWith('pages/')) return new URL(page, base).href;
    if (window.location.pathname.includes('/pages/')) {
      return new URL('../' + page, window.location.href).href;
    }
    return new URL(page, base).href;
  }
};

async function api(path, options = {}) {
  const headers = Object.assign(
    { 'Content-Type': 'application/json', Accept: 'application/json' },
    options.headers || {}
  );

  const token = Auth.getToken();
  if (token) headers.Authorization = 'Bearer ' + token;

  const res = await fetch(window.APP_CONFIG.API_BASE.replace(/\/$/, '') + path, {
    ...options,
    headers
  });

  let data = null;
  const text = await res.text();
  if (text) {
    try { data = JSON.parse(text); } catch { data = { mensagem: text }; }
  }

  if (res.status === 401) {
    Auth.clear();
    if (!path.includes('/auth/login')) {
      window.location.href = Auth.href('login.html');
    }
  }

  if (!res.ok) {
    const msg = (data && (data.mensagem || data.title || data.detail)) || 'Erro na requisição.';
    const err = new Error(msg);
    err.status = res.status;
    err.data = data;
    throw err;
  }

  return data;
}

function showAlert(el, message, type = 'success') {
  if (!el) return;
  el.className = 'alert alert-' + (type === 'erro' || type === 'danger' ? 'danger' : 'success');
  el.textContent = message;
  el.classList.remove('d-none');
}

function formatDateBr(iso) {
  if (!iso) return '';
  const [y, m, d] = iso.substring(0, 10).split('-');
  return `${d}/${m}/${y}`;
}

function resumir(texto, limite = 120) {
  if (!texto) return '';
  return texto.length <= limite ? texto : texto.slice(0, limite - 1).trim() + '…';
}

function statusClass(status) {
  const mapa = {
    'Planejada': 'status-planejada',
    'Em andamento': 'status-em-andamento',
    'Concluída': 'status-concluida',
    'Cancelada': 'status-cancelada'
  };
  return mapa[status] || 'status-planejada';
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

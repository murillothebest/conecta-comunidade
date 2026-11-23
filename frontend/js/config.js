/**
 * Configuração da API.
 * Em produção (GitHub Pages), aponta para a API no Render.
 */
window.APP_CONFIG = {
  API_BASE: localStorage.getItem('API_BASE') || 'https://conecta-comunidade.onrender.com'
};

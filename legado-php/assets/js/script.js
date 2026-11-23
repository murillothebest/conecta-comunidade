/**
 * Conecta Comunidade - JavaScript auxiliar
 * Confirmações extras e UX leve (opcional).
 */
document.addEventListener('DOMContentLoaded', function () {
    // Destaca o item ativo do menu com base na URL
    var path = window.location.pathname;
    document.querySelectorAll('.navbar-nav .nav-link').forEach(function (link) {
        if (link.getAttribute('href') && path.indexOf(link.getAttribute('href').replace('/conecta-comunidade', '')) !== -1) {
            // comparação simples; não crítico se não bater
        }
        if (link.href === window.location.href || path.indexOf(link.pathname) !== -1) {
            link.classList.add('active');
        }
    });
});

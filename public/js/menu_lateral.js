// menu_lateral.js - Controle do menu lateral
document.addEventListener('DOMContentLoaded', function () {
    // Funções para controle do menu lateral
    function toggleSubmenu(id) {
        // Verificar se estamos na página administrativa
        const isAdministrativoPage = window.location.href.includes('wf-administrativo');
        
        // Se estamos na página administrativa e o submenu é de apreensão, não fazer nada
        if (isAdministrativoPage && id === 'apreensao-submenu') {
            return false;
        }
        
        const submenu = document.getElementById(id);
        const menuToggle = submenu.previousElementSibling;

        submenu.classList.toggle('expanded');
        menuToggle.classList.toggle('active');
    }

    // Controle do menu em dispositivos móveis
    const menuCollapseBtn = document.getElementById('menuCollapseBtn');
    if (menuCollapseBtn) {
        menuCollapseBtn.addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');

            // Alterar ícone do botão
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('show')) {
                icon.classList.remove('bi-list');
                icon.classList.add('bi-x');
            } else {
                icon.classList.remove('bi-x');
                icon.classList.add('bi-list');
            }
        });
    }

    // Fechar menu ao clicar em um item (apenas em mobile)
    if (window.innerWidth < 992) {
        document.querySelectorAll('.sidebar-menu a').forEach(item => {
            item.addEventListener('click', function () {
                document.getElementById('sidebar').classList.remove('show');
                const menuBtn = document.getElementById('menuCollapseBtn');
                if (menuBtn) {
                    const icon = menuBtn.querySelector('i');
                    icon.classList.remove('bi-x');
                    icon.classList.add('bi-list');
                }
            });
        });
    }

    // Expor a função globalmente para uso no HTML
    window.toggleSubmenu = toggleSubmenu;

    function openSubTab(id, title, url) {
        const tabs = document.getElementById('abasPrincipais');
        const content = document.querySelector('.tab-content');
        if (!tabs || !content) return;

        let link = tabs.querySelector(`a[href="#${id}"]`);
        let pane = document.getElementById(id);

        if (!link) {
            const li = document.createElement('li');
            li.className = 'nav-item';
            link = document.createElement('a');
            link.className = 'nav-link';
            link.setAttribute('data-bs-toggle', 'tab');
            link.setAttribute('href', `#${id}`);
            link.setAttribute('role', 'tab');
            link.textContent = title;
            li.appendChild(link);
            tabs.appendChild(li);

            pane = document.createElement('div');
            pane.className = 'tab-pane fade';
            pane.id = id;
            pane.setAttribute('role', 'tabpanel');
            pane.innerHTML = `<div class="p-3 text-center text-muted">Carregando...</div>`;
            content.appendChild(pane);

            fetch(url, { credentials: 'same-origin' })
                .then(r => r.text())
                .then(html => {
                    pane.innerHTML = html;
                    try {
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const scripts = temp.querySelectorAll('script');
                        scripts.forEach(sc => {
                            const s = document.createElement('script');
                            if (sc.src) { s.src = sc.src; } else { s.textContent = sc.textContent; }
                            document.body.appendChild(s);
                        });
                        if (window.initIntimacaoIfPresent) { window.initIntimacaoIfPresent(); }
                        setTimeout(function () {
                            if (window.initVeiculoIfPresent) { window.initVeiculoIfPresent(); }
                        }, 100);
                    } catch (e) { }
                })
                .catch(() => {
                    pane.innerHTML = `<div class="p-3 text-center text-danger">Erro ao carregar conteúdo</div>`;
                });
        }

        if (window.bootstrap && link) { try { new bootstrap.Tab(link).show(); } catch (e) { link.click(); } } else { link.click(); }
    }

    function closeSubTab(id) {
        const tabs = document.getElementById('abasPrincipais');
        const linkLi = tabs.querySelector(`a[href="#${id}"]`)?.parentElement;
        const pane = document.getElementById(id);
        if (linkLi) linkLi.remove();
        if (pane) pane.remove();
        const firstLink = tabs.querySelector('a.nav-link');
        if (firstLink) {
            if (window.bootstrap) { try { new bootstrap.Tab(firstLink).show(); } catch (e) { firstLink.click(); } } else { firstLink.click(); }
        }
    }

    document.addEventListener('click', function (e) {
        const a = e.target.closest('[data-subtab-id]');
        if (a) {
            e.preventDefault();
            const id = a.getAttribute('data-subtab-id');
            const title = a.getAttribute('data-subtab-title') || 'Subaba';
            const url = a.getAttribute('data-subtab-url');
            openSubTab(id, title, url);
        }
        const closeBtn = e.target.closest('[data-close-tab]');
        if (closeBtn) {
            const id = closeBtn.getAttribute('data-close-tab');
            closeSubTab(id);
        }

        if (e.target.closest('#btnFecharCelular')) {
            closeSubTab('aba-celulares');
        }
        if (e.target.closest('#btnFecharVeiculo')) {
            closeSubTab('aba-veiculos');
        }
        if (e.target.closest('#btnFecharIntimacao')) {
            closeSubTab('aba-intimacao-din');
        }
    });

    window.openSubTab = openSubTab;
    window.closeSubTab = closeSubTab;

    window.addEventListener('message', function (ev) {
        if (ev.data && ev.data.type === 'close-subtab') {
            const id = ev.data.id;
            const tabs = document.getElementById('abasPrincipais');
            const linkLi = tabs.querySelector(`a[href="#${id}"]`)?.parentElement;
            const pane = document.getElementById(id);
            if (linkLi) linkLi.remove();
            if (pane) pane.remove();
            const firstLink = tabs.querySelector('a.nav-link');
            if (firstLink) {
                if (window.bootstrap) { try { new bootstrap.Tab(firstLink).show(); } catch (e) { firstLink.click(); } } else { firstLink.click(); }
            }
        }
    });
});


// Atualizar data e hora em tempo real
function updateDateTime() {
    const now = new Date();
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    const el = document.getElementById('currentDateTime');
    if (el) {
        el.textContent = now.toLocaleDateString('pt-BR', options);
    }
}

// Atualizar a cada segundo
setInterval(updateDateTime, 1000);
updateDateTime(); // Inicializar imediatamente


// Controle de acesso ao menu lateral
document.addEventListener('DOMContentLoaded', function () {
    // Verifica se o menu está desabilitado
    const menuLateral = document.getElementById('sidebar');

    if (menuLateral && menuLateral.classList.contains('menu-disabled')) {
        // Desabilita todos os links do menu lateral (exceto logout)
        const menuLinks = menuLateral.querySelectorAll('a:not(.btn-logout-sidebar)');
        menuLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                return false;
            });
        });

        // Desabilita todos os botões do menu lateral (exceto logout)
        const menuButtons = menuLateral.querySelectorAll('button:not(.btn-logout-sidebar)');
        menuButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                return false;
            });
        });

        // Remove a funcionalidade de toggle do submenu
        const menuToggles = menuLateral.querySelectorAll('.menu-toggle');
        menuToggles.forEach(toggle => {
            toggle.setAttribute('onclick', 'return false;');
        });
    }
});

/**
 * core.js - Centralização de Utilitários Globais do SisDP
 * Este arquivo deve ser carregado em TODAS as páginas após o jQuery, Bootstrap e SweetAlert2.
 */

// === CONFIGURAÇÃO GLOBAL DO AJAX ===
// Garante que o Token CSRF do Laravel seja enviado em todas as requisições AJAX automaticamente
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// === GLOBAL MODAL HELPERS (Centralizado) ===
window.sucessoTimer = window.sucessoTimer || null;
window.mostrarSucesso = function (mensagem) {
    if (window.sucessoTimer) clearTimeout(window.sucessoTimer);

    // Remove qualquer modal antigo para evitar conflitos
    $('#modalSucessoDynamic').remove();
    $('#modalSucesso').remove(); 
    $('.modal-backdrop').remove(); 

    const dynamicModalId = 'modalSucessoDynamic';

    // HTML do Modal Profissional de Sucesso
    const modalHtml = `
        <div class="modal fade" id="${dynamicModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-sm"> <!-- Modal Pequeno e Centralizado -->
                <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                    <div class="modal-header bg-success text-white border-0 justify-content-center py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Sucesso</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem; display: block; animation: bounceIn 0.5s;"></i>
                        </div>
                        <h6 id="${dynamicModalId}Msg" class="fw-bold text-secondary mb-3 fs-6">${mensagem || 'Operação realizada com sucesso!'}</h6>
                        <button type="button" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes bounceIn {
                0% { opacity: 0; transform: scale(0.3); }
                50% { opacity: 1; transform: scale(1.05); }
                70% { transform: scale(0.9); }
                100% { transform: scale(1); }
            }
        </style>
    `;

    $('body').append(modalHtml);

    const modalEl = document.getElementById(dynamicModalId);
    const modal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false,
        focus: true
    });

    modal.show();

    // Auto-fechar após 3 segundos para fluidez
    window.sucessoTimer = setTimeout(() => {
        modal.hide();
    }, 3000);
};

window.erroTimer = window.erroTimer || null;
window.mostrarErro = function (mensagem) {
    if (window.erroTimer) clearTimeout(window.erroTimer);

    // Remove qualquer modal antigo
    $('#modalErroDynamic').remove();
    $('#modalErro').remove(); 
    $('.modal-backdrop').remove();

    const errorModalId = 'modalErroDynamic';

    // HTML do Modal Profissional de Erro
    const modalHtml = `
        <div class="modal fade" id="${errorModalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                    <div class="modal-header bg-danger text-white border-0 justify-content-center py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Erro</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-x-circle text-danger" style="font-size: 4rem; display: block; animation: shake 0.5s;"></i>
                        </div>
                        <h6 id="${errorModalId}Msg" class="fw-bold text-secondary mb-3 fs-6">${mensagem || 'Ocorreu um erro inesperado.'}</h6>
                        <button type="button" class="btn btn-danger w-100 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes shake {
                0% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                50% { transform: translateX(5px); }
                75% { transform: translateX(-5px); }
                100% { transform: translateX(0); }
            }
        </style>
    `;

    $('body').append(modalHtml);

    const modalEl = document.getElementById(errorModalId);
    const modal = new bootstrap.Modal(modalEl, {
        focus: true
    });

    modal.show();
};

window.alertaTimer = window.alertaTimer || null;
window.mostrarAlerta = function (mensagem, titulo = 'Atenção') {
    if (window.alertaTimer) clearTimeout(window.alertaTimer);

    // Remove qualquer modal antigo
    $('#modalAlertaDynamic').remove();
    $('#modalAlertaGenerico').remove();
    $('.modal-backdrop').remove();

    const alertModalId = 'modalAlertaDynamic';

    // HTML do Modal Profissional de Alerta/Atenção
    const modalHtml = `
        <div class="modal fade" id="${alertModalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                    <div class="modal-header bg-warning text-dark border-0 justify-content-center py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>${titulo}</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem; display: block; animation: pulse 1s infinite;"></i>
                        </div>
                        <h6 id="${alertModalId}Msg" class="fw-bold text-secondary mb-3 fs-6">${mensagem || 'Atenção necessária.'}</h6>
                        <button type="button" class="btn btn-warning w-100 rounded-pill fw-bold shadow-sm text-dark" data-bs-dismiss="modal">Entendi</button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        </style>
    `;

    $('body').append(modalHtml);

    const modalEl = document.getElementById(alertModalId);
    const modal = new bootstrap.Modal(modalEl, {
        focus: true
    });

    modal.show();
};

// Alias para compatibilidade
window.mostrarAtencao = window.mostrarAlerta;

window.confirmarExclusaoGenerica = function (mensagem, callback) {
    // Remove qualquer modal antigo
    $('#modalConfirmacaoGenerico').remove();
    $('.modal-backdrop').remove();

    const modalId = 'modalConfirmacaoGenerico';
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                    <div class="modal-header bg-danger text-white border-0 justify-content-center py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-trash3-fill me-2"></i>Excluir</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-question-circle text-danger" style="font-size: 4rem; display: block; animation: pulse 1s infinite;"></i>
                        </div>
                        <h6 class="fw-bold text-secondary mb-3 fs-6">${mensagem}</h6>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" id="btnConfirmarExclusaoGenerico" class="btn btn-danger rounded-pill fw-bold shadow-sm">Excluir</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        </style>
    `;

    $('body').append(modalHtml);
    const modalEl = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalEl, { focus: true });
    modal.show();

    $('#btnConfirmarExclusaoGenerico').off('click').on('click', function () {
        if (typeof callback === 'function') {
            callback();
        }
        modal.hide();
    });
};

// === CONFIRMAÇÃO DE BOE DUPLICADO (múltiplos IPs no mesmo BOE) ===
window.confirmarBoeDuplicado = function (mensagem, procedimentos, callback) {
    // Remove qualquer modal antigo
    $('#modalConfirmacaoBoeDuplicado').remove();
    $('.modal-backdrop').remove();

    const modalId = 'modalConfirmacaoBoeDuplicado';

    // Monta a lista de procedimentos existentes
    let listaHtml = '';
    if (Array.isArray(procedimentos) && procedimentos.length) {
        listaHtml = '<div class="text-start mx-auto mb-3" style="max-width: 320px;">' +
            '<div class="fw-bold text-secondary small mb-2"><i class="bi bi-folder2-open me-1"></i>Procedimentos já vinculados a este BOE:</div>' +
            '<ul class="list-unstyled mb-0">';
        procedimentos.forEach(function (p) {
            const ip = p.ip || '—';
            const data = p.data ? String(p.data).substring(0, 10) : '—';
            listaHtml += `<li class="d-flex align-items-center gap-2 py-1 border-bottom border-light">` +
                `<i class="bi bi-file-earmark-text text-warning"></i>` +
                `<span class="fw-bold small">IP ${ip}</span>` +
                `<span class="text-muted small ms-auto">${data}</span></li>`;
        });
        listaHtml += '</ul></div>';
    }

    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                    <div class="modal-header bg-warning text-dark border-0 justify-content-center py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>BOE já cadastrado</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-files text-warning" style="font-size: 4rem; display: block; animation: pulse 1s infinite;"></i>
                        </div>
                        <h6 class="fw-bold text-secondary mb-3 fs-6">${mensagem}</h6>
                        ${listaHtml}
                        <p class="small text-muted mb-3">Um mesmo BOE pode ter mais de um IP (desmembramento de inquérito).</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" id="btnConfirmarBoeDuplicado" class="btn btn-warning rounded-pill fw-bold shadow-sm text-dark">Cadastrar mesmo assim</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        </style>
    `;

    $('body').append(modalHtml);
    const modalEl = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalEl, { focus: true });
    modal.show();

    $('#btnConfirmarBoeDuplicado').off('click').on('click', function () {
        if (typeof callback === 'function') {
            callback();
        }
        modal.hide();
    });
};

// === MODO FOCO DE LEITURA (DARK MODE) GLOBAL ===
$(document).ready(function() {
    // 1. Criar o botão flutuante e injetar no body
    const darkModeBtnHtml = `
        <button id="btnDarkModeToggle" class="btn btn-dark shadow-lg" title="Alternar Modo Noturno" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
            <i class="bi bi-moon-stars-fill fs-5"></i>
        </button>
    `;
    $('body').append(darkModeBtnHtml);

    const $html = $('html');
    const $btn = $('#btnDarkModeToggle');
    const $icon = $btn.find('i');

    // 2. Função para aplicar o tema visualmente
    function aplicarTema(isDark) {
        if (isDark) {
            $html.attr('data-bs-theme', 'dark');
            $icon.removeClass('bi-moon-stars-fill').addClass('bi-sun-fill text-warning');
            $btn.removeClass('btn-dark').addClass('btn-light');
        } else {
            $html.removeAttr('data-bs-theme');
            $icon.removeClass('bi-sun-fill text-warning').addClass('bi-moon-stars-fill');
            $btn.removeClass('btn-light').addClass('btn-dark');
        }
    }

    // 3. Verificar estado salvo no localStorage
    const savedTheme = localStorage.getItem('sisdp_theme');
    if (savedTheme === 'dark') {
        aplicarTema(true);
    }

    // 4. Ação do clique no botão
    $btn.on('click', function() {
        const isDark = $html.attr('data-bs-theme') === 'dark';
        if (isDark) {
            aplicarTema(false);
            localStorage.setItem('sisdp_theme', 'light');
        } else {
            aplicarTema(true);
            localStorage.setItem('sisdp_theme', 'dark');
        }
    });
});

// === FORMATADOR GLOBAL DE OBJETOS APREENDIDOS ===
// Permite que qualquer página do sistema formate a string bagunçada do banco/API
window.formatarObjetosApreendidos = function(texto) {
    if (!texto) return '';

    // Se o texto vier separado por barra " / " em vez de quebra de linha
    if (texto.indexOf('\n') === -1 && texto.indexOf(' / ') !== -1) {
        texto = texto.split(' / ').join('\n');
    }

    // Categorias conhecidas para forçar quebra de linha antes, caso o texto venha 100% colado sem Enter
    const categorias = [
        'CELULAR', 'VEICULO', 'VEÍCULO', 'OUTRO', 'OUTROS', 'ARMA', 'ARMA DE FOGO', 'ARMA BRANCA',
        'DROGA', 'ENTORPECENTE', 'DOCUMENTO', 'VALOR', 'DINHEIRO', 'TELEFONE', 'MUNICAO', 'MUNIÇÃO',
        'OBJETO', 'OBJETOS', 'ELETROELETRONICO', 'VESTUARIO', 'ACESSORIO', 'EQUIPAMENTO', 'FACA', 'REVOLVER', 'PISTOLA'
    ];
    categorias.forEach(cat => {
        const regex = new RegExp('([^\\n])\\s*\\b(' + cat + ')\\s*-\\s*', 'gi');
        texto = texto.replace(regex, '$1\n$2 - ');
    });

    const linhas = texto.split('\n');
    const linhasFormatadas = [];

    linhas.forEach(linha => {
        let l = linha.trim();
        if (!l) return;
        
        // Remove marcadores antigos do início
        l = l.replace(/^[-*•\s]+/, '');

        // Tenta extrair o cabeçalho e os atributos
        // Exemplo: "CELULAR - Marca: MOTOROLA, Mod: MOTO G 6 PLAY, Cor: PRETO..."
        let parts = l.match(/^([^-]+)\s*-\s*(.+)$/);
        
        if (parts) {
            let titulo = parts[1].trim();
            let resto = parts[2].trim();
            let attrs = {};
            
            // Lista de chaves geradas pelo boe_extractor.py
            const keys = ['Marca/Modelo', 'Marca', 'Mod', 'Cor', 'Qtd', 'Quantidade', 'Desc', 'Descrição', 'Série', 'IMEI1', 'IMEI2', 'Placa', 'Chassi'];
            const regexChaves = new RegExp('\\b(' + keys.join('|') + '):\\s*', 'gi');
            
            let tokens = resto.split(regexChaves);
            
            if (tokens.length > 1) {
                if (tokens[0].trim()) attrs['Detalhes'] = tokens[0].trim().replace(/,\s*$/, '');
                
                for (let i = 1; i < tokens.length; i += 2) {
                    let k = tokens[i].toUpperCase();
                    let v = (tokens[i+1] || '').trim();
                    // Remove a vírgula solta que separava a próxima chave
                    v = v.replace(/,\s*$/, '').trim();
                    attrs[k] = v;
                }
            } else {
                attrs['Detalhes'] = resto;
            }

            // Monta uma frase corrida natural!
            let desc = (attrs['DESC'] || attrs['DESCRIÇÃO'] || attrs['DETALHES'] || '').trim();
            if (desc.match(/^NÃO INFORMADO$|^NAO INFORMADO$|^N\/A$/i)) desc = '';

            let sentence = "";
            
            // Se a descrição for longa (provavelmente já foi digitada pelo policial no formato completo)
            // ex: "01 (UM) APARELHO CELULAR MOTOROLA..."
            if (desc.length > 15) {
                sentence = desc;
                
                // Anexa chaves importantes se elas foram extraídas separadamente mas faltaram no texto principal
                if (attrs['IMEI1'] && !sentence.includes(attrs['IMEI1'])) sentence += `, IMEI ${attrs['IMEI1']}`;
                if (attrs['IMEI2'] && !sentence.includes(attrs['IMEI2'])) sentence += `, IMEI ${attrs['IMEI2']}`;
                if (attrs['PLACA'] && !sentence.includes(attrs['PLACA'])) sentence += `, PLACA ${attrs['PLACA']}`;
                if (attrs['CHASSI'] && !sentence.includes(attrs['CHASSI'])) sentence += `, CHASSI ${attrs['CHASSI']}`;
                if (attrs['SÉRIE'] && !sentence.includes(attrs['SÉRIE'])) sentence += `, SÉRIE ${attrs['SÉRIE']}`;
                
            } else {
                // Se a descrição for curta ou não existir, o sistema constrói a frase em texto corrido!
                let qtd = attrs['QTD'] || attrs['QUANTIDADE'] || '1';
                if (qtd === '1' || qtd === '1,0') qtd = '01 (UM)';
                else if (qtd === '2' || qtd === '2,0') qtd = '02 (DOIS)';
                
                let obj = titulo.toUpperCase();
                let marca = attrs['MARCA'] || '';
                let mod = attrs['MOD'] || attrs['MARCA/MODELO'] || '';
                let cor = attrs['COR'] || '';
                
                let partes = [];
                partes.push(`${qtd} ${obj}`);
                if (marca && !marca.match(/NÃO|NAO/i)) partes.push(`MARCA ${marca}`);
                if (mod && !mod.match(/NÃO|NAO/i)) partes.push(`MODELO ${mod}`);
                if (cor && !cor.match(/NÃO|NAO/i)) partes.push(`COR ${cor}`);
                if (attrs['PLACA']) partes.push(`PLACA ${attrs['PLACA']}`);
                if (attrs['CHASSI']) partes.push(`CHASSI ${attrs['CHASSI']}`);
                if (attrs['SÉRIE']) partes.push(`SÉRIE ${attrs['SÉRIE']}`);
                if (attrs['IMEI1']) partes.push(`IMEI ${attrs['IMEI1']}`);
                if (attrs['IMEI2']) partes.push(`IMEI ${attrs['IMEI2']}`);
                
                sentence = partes.join(", ");
                if (desc) {
                    sentence += ". " + desc;
                }
            }
            
            // Garante que comece com letra maiúscula e acabe bem
            sentence = sentence.charAt(0).toUpperCase() + sentence.slice(1);
            
            linhasFormatadas.push("- " + sentence);
        } else {
            // Se não bater no padrão "Objeto - Detalhes", apenas limpa as redundâncias antigas e mostra
            l = l.replace(/Mod:\s*NÃO\s*INFORMADO,?\s*/gi, '')
                 .replace(/Cor:\s*NÃO\s*INFORMADO,?\s*/gi, '')
                 .replace(/Marca:\s*NÃO\s*INFORMADO,?\s*/gi, '');
            if (l.trim()) linhasFormatadas.push(`- ${l.trim()}`);
        }
    });

    return linhasFormatadas.join('\n\n');
};

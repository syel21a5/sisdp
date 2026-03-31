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

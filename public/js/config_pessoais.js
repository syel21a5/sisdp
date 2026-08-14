/**
 * Configurações Pessoais Automáticas (Sincronizado na Nuvem)
 * Salva e carrega dados de preenchimento padrão do usuário logado via banco de dados.
 */

$(document).ready(function() {
    let configuracoesGlobais = null;

    // 1. Carregar valores salvos do banco de dados
    function carregarConfiguracoes() {
        $.ajax({
            url: '/configuracoes-pessoais/carregar',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    configuracoesGlobais = response.data;
                    
                    // Preenche o modal se ele existir
                    if ($('#modalConfigPessoais').length > 0) {
                        $('#configDelegado').val(configuracoesGlobais.default_delegado || '');
                        $('#configEscrivao').val(configuracoesGlobais.default_escrivao || '');
                        $('#configDelegacia').val(configuracoesGlobais.default_delegacia || '');
                        $('#configCidade').val(configuracoesGlobais.default_cidade || '');
                        $('#configPolicial1').val(configuracoesGlobais.default_policial1 || '');
                        $('#configPolicial2').val(configuracoesGlobais.default_policial2 || '');
                    }

                    // Preenche o formulário APFD se estivermos na tela de APFD
                    autoPreencherFormularioAPFD();
                }
            },
            error: function() {
                console.warn('Não foi possível carregar as configurações pessoais do servidor.');
            }
        });
    }

    // 2. Preencher formulário principal do APFD (se a página atual for o APFD)
    function autoPreencherFormularioAPFD() {
        if (!configuracoesGlobais) return;

        // Verifica se os inputs do formulário principal existem nesta página
        if ($('#inputDelegado').length > 0) {
            // Só preenche se estiver vazio (para não sobrescrever dados importados de um BOE)
            if (!$('#inputDelegado').val()) $('#inputDelegado').val(configuracoesGlobais.default_delegado || '');
            if (!$('#inputEscrivao').val()) $('#inputEscrivao').val(configuracoesGlobais.default_escrivao || '');
            if (!$('#inputDelegacia').val()) $('#inputDelegacia').val(configuracoesGlobais.default_delegacia || '');
            if (!$('#inputCidade').val()) $('#inputCidade').val(configuracoesGlobais.default_cidade || '');
            
            // Tratamento especial para Policial 1 e 2
            if ($('#inputPolicial1').length > 0 && !$('#inputPolicial1').val()) {
                $('#inputPolicial1').val(configuracoesGlobais.default_policial1 || '');
            }
            if ($('#inputPolicial2').length > 0 && !$('#inputPolicial2').val()) {
                $('#inputPolicial2').val(configuracoesGlobais.default_policial2 || '');
            }
        }
    }

    // Inicialização ao carregar a página
    carregarConfiguracoes();

    // ✅ Expor função global para que outros scripts (ex: botão "Novo" do APFD) possam
    // reaplicar as configurações pessoais após limpar o formulário.
    window.aplicarConfigPessoais = function() {
        autoPreencherFormularioAPFD();
    };

    // 3. Salvar configurações quando clicar no botão Salvar do modal
    $('#btnSalvarConfigPessoais').on('click', function() {
        const btn = $(this);
        const originalHtml = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');
        btn.prop('disabled', true);

        const dados = {
            default_delegado: $('#configDelegado').val() || '',
            default_escrivao: $('#configEscrivao').val() || '',
            default_delegacia: $('#configDelegacia').val() || '',
            default_cidade: $('#configCidade').val() || '',
            default_policial1: $('#configPolicial1').val() || '',
            default_policial2: $('#configPolicial2').val() || '',
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: '/configuracoes-pessoais/salvar',
            method: 'POST',
            data: dados,
            success: function(response) {
                if (response.success) {
                    // Atualiza cache local
                    configuracoesGlobais = dados;

                    // Mostrar um aviso de sucesso usando o alert nativo
                    let alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <i class="bi bi-cloud-check-fill me-2"></i> Configurações padrão salvas na sua conta!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#modalConfigPessoais .modal-body').prepend(alertHtml);

                    // Fecha o modal após 1.5 segundos
                    setTimeout(() => {
                        $('#modalConfigPessoais').modal('hide');
                        $('#modalConfigPessoais .alert').remove();
                        autoPreencherFormularioAPFD();
                        btn.html(originalHtml);
                        btn.prop('disabled', false);
                    }, 1500);
                }
            },
            error: function() {
                alert('Erro ao salvar as configurações. Tente novamente.');
                btn.html(originalHtml);
                btn.prop('disabled', false);
            }
        });
    });
});

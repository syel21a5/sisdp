@extends('layouts.app')

@push('styles')
<style>
    /* ========== Estilos Globais da Página ========== */
    body {
        background-color: #121212 !important;
        color: #f1f1f1;
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* Container Principal Integrado ao Fundo Escuro */
    .consulta-container {
        padding: 30px 15px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Hero Search Bar (Estilo Google) */
    .search-hero {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 3rem 2rem;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    
    .search-title {
        color: #fff;
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .search-subtitle {
        color: #94a3b8;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .search-input-wrapper {
        position: relative;
        max-width: 800px;
        margin: 0 auto;
    }

    #busca_pessoa {
        height: 64px;
        border-radius: 32px;
        padding: 0 30px 0 65px;
        font-size: 1.25rem;
        border: 2px solid rgba(255,255,255,0.1);
        background-color: rgba(255,255,255,0.05);
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    #busca_pessoa:focus {
        background-color: rgba(255,255,255,0.1);
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        outline: none;
    }

    #busca_pessoa::placeholder {
        color: #64748b;
        font-style: normal;
        font-weight: 400;
    }

    /* Ocultar spinner nativo do JQuery UI e utilizar apenas o Bootstrap */
    .ui-autocomplete-loading {
        background-image: none !important;
    }

    .search-icon {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        color: #3b82f6;
    }

    .spinner-search {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        color: #3b82f6;
    }

    /* Autocomplete Dropdown Dark Mode */
    .ui-autocomplete {
        z-index: 2000 !important;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        border: 1px solid #334155;
        background: #1e293b;
        padding: 8px;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .ui-menu-item {
        padding: 0;
        margin-bottom: 4px;
        border: none !important;
    }
    
    .ui-menu-item .ui-menu-item-wrapper {
        padding: 12px 16px;
        color: #cbd5e1;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 1.05rem;
    }
    
    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background-color: #3b82f6;
        color: white;
        border: none;
        margin: 0;
    }

    /* Painel de Perfil do Indivíduo */
    .profile-hero {
        background: #1e293b;
        border-radius: 16px 16px 0 0;
        padding: 2rem;
        border: 1px solid rgba(255,255,255,0.05);
        border-bottom: none;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }

    .profile-info h3 {
        color: white;
        font-weight: 700;
        margin-bottom: 5px;
        font-size: 1.8rem;
    }

    .id-badge {
        background: rgba(255,255,255,0.1);
        color: #cbd5e1;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-left: 10px;
        vertical-align: middle;
    }

    /* Cards de Dados Pessoais (Mini Dashboard) */
    .data-cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        background: #0f172a;
        padding: 20px;
        border-radius: 0 0 16px 16px;
        border: 1px solid rgba(255,255,255,0.05);
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 2rem;
    }

    .data-card {
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        border: 1px solid rgba(255,255,255,0.02);
        transition: transform 0.2s;
    }

    .data-card:hover {
        background: rgba(255,255,255,0.05);
        transform: translateY(-2px);
    }

    .data-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        background: rgba(255,255,255,0.1);
        color: #94a3b8;
    }

    .data-icon.icon-cpf { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
    .data-icon.icon-rg { color: #10b981; background: rgba(16, 185, 129, 0.1); }
    .data-icon.icon-bday { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
    .data-icon.icon-mom { color: #ec4899; background: rgba(236, 72, 153, 0.1); }
    .data-icon.icon-alias { color: #06b6d4; background: rgba(6, 182, 212, 0.1); }

    .data-content label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin-bottom: 2px;
        font-weight: 600;
    }

    .data-content span {
        color: #f1f5f9;
        font-weight: 600;
        font-size: 1rem;
    }

    /* Tabela de Procedimentos (Estilo Relatório) */
    .table-container {
        background: #1e293b;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(255,255,255,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .table-header h4 {
        color: white;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .total-badge {
        background: #3b82f6;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .custom-table {
        width: 100%;
        color: #cbd5e1;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .custom-table th {
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: none;
    }

    .custom-table td {
        background: rgba(255,255,255,0.03);
        padding: 16px;
        vertical-align: middle;
        border-top: 1px solid transparent;
        border-bottom: 1px solid transparent;
    }

    .custom-table tr td:first-child {
        border-radius: 10px 0 0 10px;
        border-left: 4px solid transparent; /* Para os borders colors do modulo */
    }

    .custom-table tr td:last-child {
        border-radius: 0 10px 10px 0;
    }

    .custom-table tbody tr {
        transition: transform 0.2s, background-color 0.2s;
    }

    .custom-table tbody tr:hover td {
        background: rgba(255,255,255,0.06);
    }

    .custom-table tbody tr:hover {
        transform: scale(1.01);
    }

    /* Cores Módulos */
    .row-apfd td:first-child { border-left-color: #3b82f6; }
    .row-administrativo td:first-child { border-left-color: #f59e0b; }
    
    .badge-mod {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .badge-apfd { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
    .badge-admin { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }

    .proc-papel {
        font-weight: 700;
        color: #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .proc-papel i { color: #94a3b8; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
    }
    .empty-state i {
        font-size: 5rem;
        color: #334155;
        margin-bottom: 1rem;
    }
    .empty-state h3 {
        color: #64748b;
        font-weight: 600;
    }

</style>
@endpush

@section('content')
<div class="consulta-container">
    
    <!-- Hero Search -->
    <div class="search-hero">
        <h1 class="search-title">Consulta de Antecedentes</h1>
        <p class="search-subtitle">Busca unificada em todo o banco de dados policial</p>
        
        <div class="search-input-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="busca_pessoa" class="form-control" autocomplete="off" placeholder="Digite o nome completo, apelido, CPF ou RG...">
            <div id="loading" class="spinner-search d-none">
                <div class="spinner-border spinner-border-sm" role="status"></div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div id="resultado_container" class="d-none">
        
        <!-- Perfil Header -->
        <div class="profile-hero">
            <div class="profile-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="profile-info">
                <h3 id="pessoa_nome" class="mb-1">---</h3>
                <div class="text-muted d-flex align-items-center">
                    <i class="bi bi-card-heading me-2"></i> Ficha Cadastral Unificada
                </div>
            </div>
        </div>

        <!-- Dashboard de Dados (Cards) -->
        <div class="data-cards-container">
            <div class="data-card">
                <div class="data-icon icon-cpf"><i class="bi bi-person-vcard"></i></div>
                <div class="data-content">
                    <label>CPF</label>
                    <span id="pessoa_cpf">---</span>
                </div>
            </div>
            <div class="data-card">
                <div class="data-icon icon-rg"><i class="bi bi-upc-scan"></i></div>
                <div class="data-content">
                    <label>RG</label>
                    <span id="pessoa_rg">---</span>
                </div>
            </div>
            <div class="data-card">
                <div class="data-icon icon-bday"><i class="bi bi-calendar-event"></i></div>
                <div class="data-content">
                    <label>Nascimento</label>
                    <span id="pessoa_nascimento">---</span>
                </div>
            </div>
            <div class="data-card">
                <div class="data-icon icon-mom"><i class="bi bi-people"></i></div>
                <div class="data-content">
                    <label>Nome da Mãe</label>
                    <span id="pessoa_mae">---</span>
                </div>
            </div>
            <div class="data-card">
                <div class="data-icon icon-alias"><i class="bi bi-tag"></i></div>
                <div class="data-content">
                    <label>Alcunha / Apelido</label>
                    <span id="pessoa_alcunha">---</span>
                </div>
            </div>
        </div>

        <!-- Tabela Data Table Moderna -->
        <div class="table-container">
            <div class="table-header">
                <h4><i class="bi bi-journal-text text-primary"></i> Histórico de Envolvimento</h4>
                <span class="total-badge"><span id="total_count">0</span> Registros</span>
            </div>
            
            <div class="table-responsive">
                <table class="custom-table" id="tabelaProcedimentos">
                    <thead>
                        <tr>
                            <th>Data/Módulo</th>
                            <th>Nº Reg. BOE / IP</th>
                            <th>Natureza (Crime/Fato)</th>
                            <th>Papel do Envolvido</th>
                            <th style="width:80px; text-align:center;">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="lista_procedimentos">
                        <!-- Renderizado via JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Empty State Inicial -->
    <div id="empty_state" class="empty-state">
        <i class="bi bi-shield-check"></i>
        <h3>Sistema de Inteligência Policial</h3>
        <p class="text-muted">Utilize o campo de busca acima para carregar o histórico completo de um indivíduo.</p>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // Configura o Autocomplete customizado para aceitar destaque HTML
    $.widget("custom.searchAutocomplete", $.ui.autocomplete, {
        _renderItem: function(ul, item) {
            let html = `
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-white mb-1"><i class="bi bi-person me-2 text-primary"></i> ${item.dados.nome}</div>
                        <div class="small text-muted" style="margin-left: 24px;">
                            ${item.dados.cpf ? `<span class="me-3"><i class="bi bi-credit-card me-1"></i> ${item.dados.cpf}</span>` : ''}
                            ${item.dados.mae ? `<span><i class="bi bi-people me-1"></i> Mãe: ${item.dados.mae}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
            return $("<li>")
                .append($("<div>").html(html))
                .appendTo(ul);
        }
    });

    $("#busca_pessoa").searchAutocomplete({
        source: function(request, response) {
            $("#loading").removeClass('d-none');
            $.ajax({
                url: "{{ route('pessoa.search') }}",
                data: { term: request.term },
                success: function(data) {
                    $("#loading").addClass('d-none');
                    // Valida se há dados, caso contrário envia array vazio pra fechar o loading do plugin
                    if (!data || data.error || !Array.isArray(data)) {
                        return response([]);
                    }
                    response($.map(data, function(item) {
                        return { label: item.nome, value: item.nome, id: item.id, dados: item };
                    }));
                },
                error: function() {
                    $("#loading").addClass('d-none');
                    response([]); // Força fechamento da roleta
                }
            });
        },
        minLength: 3,
        select: function(event, ui) {
            buscarDetalhes(ui.item.id);
            setTimeout(() => $("#busca_pessoa").blur(), 100); 
        }
    });

    // Máscara automática de CPF no campo de busca
    $("#busca_pessoa").on('input', function() {
        let val = $(this).val().replace(/\D/g, '');
        // Só aplica máscara se o usuário estiver digitando números (provavel CPF)
        if (val.length >= 4 && /^\d+$/.test($(this).val().replace(/[.\-]/g, ''))) {
            if (val.length <= 11) {
                val = val
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
                // Atualiza o campo preservando o cursor
                const cur = this.selectionStart + (val.length - $(this).val().length);
                $(this).val(val);
            }
        }
    });

    function buscarDetalhes(id) {
        $("#loading").removeClass('d-none');
        $("#empty_state").addClass('d-none');
        $("#resultado_container").addClass('d-none'); // Oculta enquanto carrega
        
        $.ajax({
            url: "/consulta-pessoa/detalhes/" + id,
            success: function(res) {
                $("#loading").addClass('d-none');
                if (res.success) {
                    renderPessoa(res.pessoa);
                    renderProcedimentos(res.procedimentos);
                    $("#resultado_container").removeClass('d-none').hide().fadeIn(400); // Exibe c/ efeito p ficar premium
                }
            }
        });
    }

    function formatarCPF(cpf) {
        const n = (cpf || '').replace(/\D/g, '');
        if (n.length === 11 && n !== '00000000000') {
            return n.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        return cpf || 'Não informado';
    }

    function renderPessoa(p) {
        $("#pessoa_nome").text(p.Nome);
        $("#pessoa_cpf").text(formatarCPF(p.CPF));
        $("#pessoa_rg").text(p.RG || 'Não informado');
        $("#pessoa_nascimento").text(p.Nascimento ? new Date(p.Nascimento).toLocaleDateString('pt-BR') : 'Não informado');
        $("#pessoa_mae").text(p.Mae || 'Não informada');
        $("#pessoa_alcunha").text(p.Alcunha || 'Não constam apelidos');
    }

    function renderProcedimentos(procs) {
        const tbody = $("#lista_procedimentos");
        tbody.empty();
        $("#total_count").text(procs.length);

        if (procs.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center py-5 text-muted">Nenhum procedimento encontrado na base de dados.</td></tr>');
            return;
        }

        procs.forEach(p => {
            const dataFormatada = p.data ? new Date(p.data).toLocaleDateString('pt-BR') : 'S/ Data';
            
            // Lógica p definir classe de cor
            const isApfd = p.modulo.includes('APFD') || p.modulo.includes('IP');
            const rowClass = isApfd ? 'row-apfd' : 'row-administrativo';
            const badgeClass = isApfd ? 'badge-apfd' : 'badge-admin';
            
            // Construção dos numerais
            let docsHtml = '';
            if (p.boe) docsHtml += `<div class="fw-bold mb-1"><span class="text-muted small">BOE:</span> ${p.boe}</div>`;
            if (p.ip) docsHtml += `<div class="fw-bold"><span class="text-muted small">IP:</span> ${p.ip}</div>`;
            if (!p.boe && !p.ip) docsHtml = '<span class="text-muted italic">Sem numeração</span>';

            // Construir URL de acao para abrir o registro no módulo correto
            let urlAcao = '';
            let labelAcao = '';
            let iconAcao = '';
            if (isApfd) {
                urlAcao = `/ip-apfd?abrir_id=${p.id}`;
                labelAcao = 'Ver no APFD';
                iconAcao = 'bi-box-arrow-up-right';
            } else if (p.modulo.includes('BOE')) {
                urlAcao = `/wf-geral?abrir_id=${p.id}`;
                labelAcao = 'Ver no BOE';
                iconAcao = 'bi-box-arrow-up-right';
            } else {
                urlAcao = `/wf-administrativo?abrir_id=${p.id}`;
                labelAcao = 'Ver no ADM';
                iconAcao = 'bi-box-arrow-up-right';
            }

            const btnAcao = urlAcao ? `
                <a href="${urlAcao}" target="_blank" 
                   class="btn btn-sm" 
                   style="background:rgba(59,130,246,0.15);color:#60a5fa;border:1px solid rgba(59,130,246,0.3);border-radius:8px;font-size:0.75rem;white-space:nowrap;" 
                   title="${labelAcao}">
                    <i class="bi ${iconAcao}"></i>
                </a>` : '<span class="text-muted small">-</span>';

            const html = `
                <tr class="${rowClass}">
                    <td>
                        <div class="fw-bold mb-2 text-white">${dataFormatada}</div>
                        <span class="badge-mod ${badgeClass}">${p.modulo}</span>
                    </td>
                    <td>${docsHtml}</td>
                    <td>
                        <div class="text-white">${p.crime || 'Não especificado'}</div>
                    </td>
                    <td>
                        <div class="proc-papel">
                            <i class="bi bi-person-badge"></i> ${p.papel}
                        </div>
                    </td>
                    <td style="text-align:center;">${btnAcao}</td>
                </tr>
            `;
            tbody.append(html);
        });
    }
});
</script>
@endpush

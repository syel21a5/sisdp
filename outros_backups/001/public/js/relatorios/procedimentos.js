$(document).ready(function () {
    console.log('📊 Módulo de Relatórios: Procedimentos iniciado');

    // Inicialização
    const anoAtual = new Date().getFullYear();
    preencherFiltroAnos();
    $('#filtroAno').val(anoAtual);

    // Carregar dados iniciais
    carregarDados();

    // Event Listeners dos Filtros
    $('#filtroAno, #filtroMes, #filtroStatus').change(function () {
        carregarDados();
    });

    // Função para preencher select de anos (do ano atual para trás até 2020)
    function preencherFiltroAnos() {
        const select = $('#filtroAno');
        const anoInicio = 2020;
        select.empty();
        select.append('<option value="">Todos os Anos</option>');

        for (let i = anoAtual; i >= anoInicio; i--) {
            select.append(`<option value="${i}">${i}</option>`);
        }
    }

    // --- FUNÇÕES ---

    // Função global para filtro por status (chamada pelos cards)
    window.filtrarPorStatus = function (status) {
        $('#filtroStatus').val(status);
        carregarDados(1); // Reseta para primeira página

        // Feedback visual (opcional: scroll até a tabela)
        $('html, body').animate({
            scrollTop: $("#tabelaResultados").offset().top - 150
        }, 500);
    };

    function carregarDados(page = 1) {
        // Mostra estado de carregamento
        if (page === 1) {
            $('#rankingContainer').html('<div class="col-12 text-center text-muted py-3">Carregando dados...</div>');
        }
        $('#tabelaResultados tbody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><br>Carregando registros...</td></tr>');

        const filtros = {
            ano: $('#filtroAno').val(),
            mes: $('#filtroMes').val(),
            status: $('#filtroStatus').val(),
            page: page // Envia o número da página
        };

        $.ajax({
            url: '/relatorios/procedimentos/dados',
            method: 'GET',
            data: filtros,
            success: function (response) {
                if (response.success) {
                    atualizarCards(response.contadores);
                    atualizarRanking(response.ranking);
                    atualizarTabela(response.registros);
                    atualizarPaginacao(response.paginacao);
                } else {
                    mostrarErro('Erro ao carregar dados do relatório.');
                }
            },
            error: function (xhr) {
                console.error('Erro na requisição:', xhr);
                $('#tabelaResultados tbody').html('<tr><td colspan="6" class="text-center text-danger">Erro ao carregar dados. Tente novamente.</td></tr>');
            }
        });
    }

    function atualizarPaginacao(paginacao) {
        const container = $('#paginacaoContainer');
        container.empty();

        if (!paginacao || paginacao.last_page <= 1) return;

        let html = '<nav aria-label="Navegação da Tabela"><ul class="pagination pagination-sm justify-content-center mb-0">';

        // Botão Anterior
        const prevDisabled = paginacao.current_page === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" onclick="window.RelatorioApp.mudarPagina(${paginacao.current_page - 1}); return false;">Anterior</a>
                 </li>`;

        // Informação de Página
        html += `<li class="page-item disabled"><span class="page-link text-muted border-0">Página ${paginacao.current_page} de ${paginacao.last_page}</span></li>`;

        // Botão Próximo
        const nextDisabled = paginacao.current_page === paginacao.last_page ? 'disabled' : '';
        html += `<li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" onclick="window.RelatorioApp.mudarPagina(${paginacao.current_page + 1}); return false;">Próximo</a>
                 </li>`;

        html += '</ul></nav>';
        container.html(html);
    }

    // Expor função de mudar página
    window.RelatorioApp = {
        mudarPagina: function (page) {
            carregarDados(page);
        }
    };

    function atualizarCards(contadores) {
        // Animação simples dos números
        animateValue("countTotal", contadores.total);
        animateValue("countAndamento", contadores.em_andamento);
        animateValue("countConcluidos", contadores.concluidos);
        animateValue("countParados", contadores.parados);
        animateValue("countRemetidos", contadores.remetidos);
        animateValue("countArquivados", contadores.arquivados);
    }

    function atualizarRanking(ranking) {
        const container = $('#rankingContainer');
        container.empty();

        if (!ranking || ranking.length === 0) {
            container.html('<div class="col-12 text-center text-muted py-3">Sem dados suficientes para gerar ranking neste período.</div>');
            return;
        }

        // Pega o maior valor para calcular a porcentagem da barra (barra cheia = 100%)
        const maiorValor = ranking[0].total;

        ranking.forEach((item, index) => {
            const porcentagem = (item.total / maiorValor) * 100;
            // Cores alternadas para o top 3
            let corBarra = 'bg-primary';
            if (index === 0) corBarra = 'bg-danger';
            else if (index === 1) corBarra = 'bg-warning';
            else if (index === 2) corBarra = 'bg-info';

            // HTML simplificado para o ranking
            const html = `
                <div class="col-12">
                     <div class="d-flex justify-content-between mb-1 small">
                        <span class="fw-bold text-dark">${index + 1}º ${item.nome}</span>
                        <span class="fw-bold">${item.total}</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar ${corBarra}" role="progressbar" style="width: ${porcentagem}%" 
                             aria-valuenow="${item.total}" aria-valuemin="0" aria-valuemax="${maiorValor}"></div>
                    </div>
                </div>
            `;
            container.append(html);
        });
    }

    function atualizarTabela(registros) {
        const tbody = $('#tabelaResultados tbody');
        tbody.empty();

        if (registros.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted py-4">Nenhum registro encontrado com os filtros selecionados.</td></tr>');
            return;
        }

        registros.forEach(item => {
            let statusBadge = 'bg-secondary';
            let statusLabel = item.status; // Label padrão = valor do banco

            if (item.status === 'Em andamento') {
                statusBadge = 'bg-primary';
                statusLabel = 'Em Andamento';
            }
            else if (item.status === 'Concluído') {
                statusBadge = 'bg-success';
                statusLabel = 'Concluído - Ag. Relatório'; // Abreviação para caber bem
            }
            else if (item.status === 'Parado') {
                statusBadge = 'bg-warning text-dark';
                statusLabel = 'Aguardando Diligência';
            }
            else if (item.status === 'Arquivado') {
                statusBadge = 'bg-dark';
            }
            else if (item.status === 'Remetido a Justiça') {
                statusLabel = 'Remetido à Justiça';
            }

            // Lógica de cores para Prioridade
            let prioridadeBadge = 'bg-secondary';
            let prioridadeTexto = item.prioridade || '-';
            let prioridadeUpper = prioridadeTexto.toUpperCase();

            if (prioridadeUpper.includes('ALTA')) {
                prioridadeBadge = 'bg-danger';
            } else if (prioridadeUpper.includes('MÉDIA') || prioridadeUpper.includes('MEDIA')) {
                prioridadeBadge = 'bg-warning text-dark';
            } else if (prioridadeUpper.includes('BAIXA')) {
                prioridadeBadge = 'bg-success';
            }

            const row = `
                <tr>
                    <td>${item.data}</td>
                    <td class="fw-bold text-primary">${item.boe}</td>
                    <td>${item.ip}</td>
                    <td><span class="badge ${statusBadge}">${statusLabel}</span></td>
                    <td class="text-truncate" style="max-width: 200px;" title="${item.natureza}">${item.natureza}</td>
                    <td><span class="badge ${prioridadeBadge}">${prioridadeTexto}</span></td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Função utilitária para animação de números
    function animateValue(id, end) {
        const obj = document.getElementById(id);
        if (!obj) return;

        const start = parseInt(obj.innerHTML) || 0;
        if (start === end) return;

        const duration = 500;
        const range = end - start;
        let current = start;
        const increment = end > start ? 1 : -1;
        const stepTime = Math.abs(Math.floor(duration / range));

        const timer = setInterval(function () {
            current += increment;
            obj.innerHTML = current;
            if (current == end) {
                clearInterval(timer);
            }
        }, Math.max(stepTime, 10)); // Mínimo de 10ms

        // Fallback para atualização instantânea se o passo for muito pequeno
        if (stepTime < 10) {
            clearInterval(timer);
            obj.innerHTML = end;
        }
    }

    // --- FUNÇÕES DE EXPORTAÇÃO ---
    window.abrirModalExportacao = function () {
        const ano = $('#filtroAno').val() || 'Todos';
        const mestexto = $('#filtroMes option:selected').text();
        const statustexto = $('#filtroStatus option:selected').text();

        $('#resumoAno').text(ano);
        $('#resumoMes').text(mestexto);
        $('#resumoStatus').text(statustexto);

        const modalEl = document.getElementById('modalExportacao');
        if (modalEl) new bootstrap.Modal(modalEl).show();
        else console.error('Modal de exportação não encontrado!');
    };

    window.confirmarExportacao = function () {
        const ano = $('#filtroAno').val();
        const mes = $('#filtroMes').val();
        const status = $('#filtroStatus').val();
        const formato = $('#formatoExportacao').val();

        let url = `/relatorios/procedimentos/exportar?formato=${formato}`;
        if (ano) url += `&ano=${ano}`;
        if (mes) url += `&mes=${mes}`;
        if (status) url += `&status=${encodeURIComponent(status)}`;

        // Fechar modal
        const modalEl = document.getElementById('modalExportacao');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Iniciar download
        // Iniciar download (mesma janela para evitar popup)
        window.location.href = url;
    };

    function mostrarErro(msg) {
        // Usa o sistema de alerta padrão se existir, ou alert
        if (typeof window.mostrarAlerta === 'function') {
            window.mostrarAlerta(msg);
        } else {
            alert(msg);
        }
    }
});

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Apreensão de Veículos - SYS-DP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 e Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">



    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <!-- Menu Lateral -->
    <!-- (Copie o mesmo menu lateral do wf_geral.blade.php aqui) -->

    <!-- Botão para recolher/expandir menu em dispositivos móveis -->
    <button class="menu-collapse-btn" id="menuCollapseBtn">
        <i class="bi bi-list"></i>
    </button>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="container">

            <!-- Cabeçalho Profissional -->
            <div class="header-container">
                <div class="page-header">
                    <h1 class="page-title">
                        <img src="{{ asset('images/police_avatar.ico') }}" alt="Logo" class="me-2">
                        SisDP - Apreensão de Veículos
                    </h1>
                </div>
                <div class="system-info">
                    <div class="system-date" id="currentDateTime">{{ date('d/m/Y H:i:s') }}</div>
                    <div class="system-user">Usuário: {{ Auth::user()->nome ?? 'Administrador' }}</div>
                </div>
            </div>

            <!-- Formulário Principal -->
            <form id="formVeiculo">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-2 pe-1">
                        <input type="text" class="form-control" placeholder="DD/MM/AAAA" name="data" id="inputData">
                    </div>
                    <div class="col-md-4"><input type="text" class="form-control" placeholder="IP" name="ip" id="inputIP"></div>
                    <div class="col-md-6"><input type="text" class="form-control" placeholder="BOE" name="boe" id="inputBOE"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6"><input type="text" class="form-control" placeholder="Proprietário/Envolvido" name="pessoa" id="inputPessoa"></div>
                    <div class="col-md-6"><input type="text" class="form-control" placeholder="Veículo" name="veiculo" id="inputVeiculo"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4"><input type="text" class="form-control" placeholder="Placa" name="placa" id="inputPlaca"></div>
                    <div class="col-md-4"><input type="text" class="form-control" placeholder="Chassi" name="chassi" id="inputChassi"></div>
                    <div class="col-md-4"><input type="text" class="form-control" placeholder="SEI" name="sei" id="inputSEI"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <select class="form-select" name="status" id="inputStatus">
                            <option value="">Status...</option>
                            <option value="Apreendido">Apreendido</option>
                            <option value="Devolvido">Devolvido</option>
                            <option value="Destruído">Destruído</option>
                            <option value="Em custódia">Em custódia</option>
                        </select>
                    </div>
                </div>

                <!-- Botões de ação -->
                <div class="btn-group-custom">
                    <button type="button" class="btn btn-primary btn-action-lg" id="btnNovo">
                        <i class="bi bi-file-earmark-plus"></i> Novo
                    </button>
                    <button type="button" class="btn btn-success btn-action-lg" id="btnSalvar">
                        <i class="bi bi-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-warning btn-action-lg" id="btnEditar" disabled>
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
                    <button type="button" class="btn btn-danger btn-action-lg" id="btnExcluir" disabled>
                        <i class="bi bi-trash"></i> Excluir
                    </button>
                    <button type="button" class="btn btn-secondary btn-action-lg" id="btnLimpar">
                        <i class="bi bi-x-circle"></i> Limpar
                    </button>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="ddlFiltro">
                            <option value="BOE" selected>BOE</option>
                            <option value="IP">IP</option>
                            <option value="placa">Placa</option>
                            <option value="chassi">Chassi</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="txtPesquisa" placeholder="Digite o termo para pesquisa">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" type="button" id="btnPesquisar">
                            <i class="bi bi-search"></i> Pesquisar
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="gridResultados">
                        <thead>
                            <tr>
                                <th>BOE</th>
                                <th>IP</th>
                                <th>DATA</th>
                                <th>PROPRIETÁRIO</th>
                                <th>PLACA</th>
                                <th>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">Nenhum registro encontrado. Realize uma pesquisa.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>

    <script src="{{ asset('js/core.js') }}"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>


    <!-- Rotas para Veículo -->
    <script>
        const rotas = {
            veiculo: {
                pesquisar: "{{ route('veiculo.pesquisar') }}",
                salvar: "{{ route('veiculo.salvar') }}",
                buscar: "{{ url('/veiculo/buscar') }}",
                atualizar: "{{ url('/veiculo/atualizar') }}",
                excluir: "{{ url('/veiculo/excluir') }}"
            }
        };
    </script>

    <!-- Script específico -->
    <script src="{{ asset('js/veiculo.js') }}"></script>
</body>
</html>


@extends('layouts.app')

@php
    $title = "Sincronização SDS (INFOPOL)";
@endphp

@push('styles')
<style>
    /* ========== Estilos Globais ========== */
    [v-cloak] {
        display: none !important;
    }
    
    html, body {
        background-color: #0f172a !important;
        color: #f1f1f1;
        font-family: 'Outfit', 'Inter', system-ui, sans-serif;
        height: 100%;
        overflow: hidden;
    }

    main.container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        height: calc(100vh - 56px);
        overflow-y: auto;
    }

    .container-sinc {
        padding: 10px 15px 40px;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Hero Header */
    .sync-hero {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 1.2rem;
        animation: fadeIn 0.8s ease-out;
    }

    .sync-icon {
        font-size: 2.2rem;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .sync-title {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 0;
        letter-spacing: -0.5px;
    }

    .sync-subtitle {
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Cards */
    .sync-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .sync-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sync-card-body {
        padding: 1.5rem;
    }

    /* Form Elements */
    .form-label {
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
        border-radius: 10px;
        padding: 0.5rem 0.8rem;
        font-size: 0.85rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control::placeholder {
        color: #64748b !important;
        font-weight: 500;
        opacity: 1 !important;
    }
    
    /* Suporte para outros navegadores */
    :-ms-input-placeholder { color: #64748b !important; font-weight: 500; opacity: 1 !important; }
    ::-ms-input-placeholder { color: #64748b !important; font-weight: 500; opacity: 1 !important; }

    .form-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
    }

    /* Buttons */
    .btn-action {
        border-radius: 10px;
        padding: 0.6rem 1.2rem;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-connect {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .btn-connect.connected {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .btn-primary-sync {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .btn-primary-sync:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
    }

    /* Grid de BOEs e Checkboxes */
    .boe-card {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .boe-card:hover {
        background: rgba(255,255,255,0.05);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .boe-card.selected {
        background: rgba(59, 130, 246, 0.1);
        border-color: #3b82f6;
    }

    .checkbox-custom {
        width: 20px;
        height: 20px;
        background: rgba(0,0,0,0.3);
        border: 2px solid rgba(255,255,255,0.2);
        border-radius: 6px;
        cursor: pointer;
        position: relative;
        appearance: none;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .checkbox-custom:checked {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .checkbox-custom:checked::after {
        content: '\F633';
        font-family: 'bootstrap-icons';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.8rem;
    }

    .boe-number {
        font-weight: 700;
        font-size: 0.95rem;
        color: #f8fafc;
        margin: 0;
        letter-spacing: 0.5px;
    }

    /* Paginator */
    .pagination-custom .page-link {
        background-color: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: #94a3b8;
        border-radius: 8px;
        margin: 0 4px;
        transition: all 0.2s;
    }

    .pagination-custom .page-item.active .page-link {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .pagination-custom .page-link:hover:not(.active) {
        background: rgba(255,255,255,0.1);
        color: white;
    }

    .pagination-custom .page-item.disabled .page-link {
        opacity: 0.5;
        background-color: rgba(0,0,0,0.2);
    }

    /* Progress Bar */
    .progress-custom {
        height: 8px;
        background: rgba(0,0,0,0.3);
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar-custom {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #10b981);
        transition: width 0.4s ease;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-online { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-offline { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@endpush

@section('content')
<div class="container-sinc" id="app" v-cloak>
    
    <!-- Header -->
    <div class="sync-hero">
        <i class="bi bi-shield-check sync-icon"></i>
        <div>
            <h1 class="sync-title">Sincronizador INFOPOL</h1>
            <span class="sync-subtitle">Extração Seletiva de Boletins de Ocorrência</span>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- ==========================================
             MENU LATERAL (CONEXÃO)
             ========================================== -->
        <div class="col-lg-3">
            <div class="sync-card">
                <div class="sync-card-header">
                    <span class="fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>Conexão</span>
                    <div :class="['status-badge', isConnected ? 'status-online' : 'status-offline']">
                        <i :class="['bi', isConnected ? 'bi-circle-fill' : 'bi-x-circle-fill']" style="font-size: 0.5rem;"></i>
                        @{{ isConnected ? 'Online' : 'Offline' }}
                    </div>
                </div>
                <div class="sync-card-body p-3">
                    <div class="mb-3">
                        <label class="form-label">Usuário SDS</label>
                        <input type="text" v-model="auth.usuario" class="form-control" placeholder="000.000.000-00">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Senha INFOPOL</label>
                        <input type="password" v-model="auth.senha" class="form-control" placeholder="••••••••">
                    </div>
                    <button type="button" @click="handleConnect" :disabled="isConnecting" 
                            :class="['btn-action', 'w-100', isConnected ? 'btn-connect connected' : 'btn-connect']">
                        <i v-if="isConnecting" class="spinner-border spinner-border-sm"></i>
                        <i v-else :class="['bi', isConnected ? 'bi-check-all' : 'bi-lightning-fill']"></i>
                        @{{ isConnected ? 'Sessão Ativa' : 'Conectar' }}
                    </button>
                    <p class="text-muted small mt-3 mb-0 text-center" style="font-size: 0.65rem;">
                        A sessão permanece ativa neste navegador.
                    </p>
                </div>
            </div>
        </div>

        <!-- ==========================================
             ÁREA PRINCIPAL (BUSCA E RESULTADOS)
             ========================================== -->
        <div class="col-lg-9 d-flex flex-column gap-3">
            
            <!-- Box de Pesquisa e Ferramentas Em Linha -->
            <div class="sync-card mb-0 flex-shrink-0">
                <div class="sync-card-header pb-2">
                    <span class="fw-bold"><i class="bi bi-search me-2 text-primary"></i>Critérios de Busca</span>
                </div>
                <div class="sync-card-body pt-3 pb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-lg-5">
                            <label class="form-label">Nome do Envolvido</label>
                            <input type="text" v-model="filters.nome" class="form-control text-uppercase" placeholder="MÍNIMO 5 CARACTERES">
                        </div>
                        <div class="col-xl-3 col-lg-4">
                            <label class="form-label">Unidade (Opç.)</label>
                            <input type="text" v-model="filters.delegacia" class="form-control text-uppercase" placeholder="DP OU NÚMERO">
                        </div>
                        <div class="col-xl-3 col-lg-3 row g-2 m-0">
                            <div class="col-6">
                                <label class="form-label">Início</label>
                                <input type="text" v-model="filters.inicio" class="form-control date-mask px-2" placeholder="dd/mm/aaaa">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Fim</label>
                                <input type="text" v-model="filters.fim" class="form-control date-mask px-2" placeholder="dd/mm/aaaa">
                            </div>
                        </div>

                        <!-- Botões Em Linha -->
                        <div class="col-xl-2 col-lg-12 d-flex gap-2">
                            <button type="button" @click="handleSearch" :disabled="!canSearch || isSearching || isDownloading" class="btn-action btn-primary-sync w-100 justify-content-center p-2">
                                <i v-if="isSearching" class="spinner-border spinner-border-sm"></i>
                                <i v-else class="bi bi-search"></i>
                                <span class="d-none d-xl-inline ms-1">@{{ isSearching ? 'Buscando...' : 'Buscar' }}</span>
                                <span class="d-inline d-xl-none ms-1">@{{ isSearching ? 'Buscando...' : 'Pesquisar Boletins' }}</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <button type="button" @click="handleDownload" :disabled="selectedIndices.length === 0 || isDownloading" class="btn-action w-auto justify-content-center px-4" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                            <i v-if="isDownloading" class="spinner-border spinner-border-sm"></i>
                            <i v-else class="bi bi-cloud-download"></i>
                            <span class="ms-1">@{{ isDownloading ? 'Baixando...' : 'Baixar Selecionados (' + selectedIndices.length + ')' }}</span>
                        </button>
                        
                        <div v-if="isDownloading" class="flex-grow-1 ms-4 me-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold" style="color: #94a3b8; font-size:0.7rem;">@{{ downloadStatus }}</span>
                                <span class="text-success fw-bold" style="font-size:0.7rem;">@{{ downloadPercent }}%</span>
                            </div>
                            <div class="progress-custom" style="height: 6px;">
                                <div class="progress-bar-custom" :style="{ width: downloadPercent + '%' }"></div>
                            </div>
                        </div>

                        <span v-if="results.length > 0 && !isDownloading" class="small text-muted">
                            <i class="bi bi-info-circle me-1"></i> A pesquisa encontrou @{{ results.length }} BOEs.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Box de Resultados (Grid layout) -->
            <div class="sync-card flex-grow-1 d-flex flex-column mb-0">
                <div class="sync-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Boletins Encontrados</span>
                        <span v-if="results.length > 0" class="badge rounded-pill bg-primary ms-2" style="font-size: 0.7rem;">@{{ results.length }}</span>
                    </div>
                    
                    <!-- Botões de Seleção em Lote -->
                    <div v-if="results.length > 0 && !isSearching" class="d-flex gap-2">
                        <button @click="selectAllPage" class="btn btn-sm" style="font-size: 0.75rem; padding: 3px 10px; border-radius: 6px; background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); font-weight: 500;">Selecionar Página</button>
                        <button @click="clearSelection" class="btn btn-sm" style="font-size: 0.75rem; padding: 3px 10px; border-radius: 6px; background: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.2); font-weight: 500;">Limpar Tudo</button>
                    </div>
                </div>
                
                <div class="sync-card-body d-flex flex-column flex-grow-1 p-3">
                    
                    <!-- Placeholder Inicial -->
                    <div v-if="results.length === 0 && !isSearching" class="text-center my-auto py-5 opacity-50">
                        <i class="bi bi-search display-1 mb-3 d-block text-secondary"></i>
                        <h5>Nenhum resultado para exibir</h5>
                        <p class="small text-muted">Realize uma pesquisa e os BOEs aparecerão aqui.</p>
                    </div>

                    <!-- Loader de Inicialização Rápida (quando inicia e ainda n tem array) -->
                    <div v-if="isSearching && results.length === 0" class="text-center my-auto py-5">
                        <div class="spinner-grow text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                        <h5>Consultando INFOPOL...</h5>
                        <p class="text-muted">Isso pode levar de 15 a 45 segundos. @{{ currentStatus }}</p>
                    </div>

                    <!-- Loader Flutuante na pesquisa (se já tem itens) -->
                    <div v-if="isSearching && results.length > 0" class="d-flex align-items-center justify-content-center p-2 mb-3 bg-primary rounded bg-opacity-10 border border-primary border-opacity-25">
                        <i class="spinner-border spinner-border-sm text-primary me-2"></i>
                        <span class="text-primary fw-bold" style="font-size: 0.8rem;">@{{ currentStatus || 'Buscando...' }}</span>
                    </div>

                    <!-- Grid de BOEs -->
                    <div v-if="results.length > 0" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-4 g-3 mb-4">
                        <div class="col" v-for="item in paginatedResults" :key="item.index">
                            <!-- Card do BOE: se o usuário clicar no fundo inteiro, ativa o checkbox -->
                            <div :class="['boe-card', isSelected(item.index) ? 'selected' : '']" @click="toggleSelection(item.index)">
                                <input type="checkbox" class="checkbox-custom" :value="item.index" v-model="selectedIndices" @click.stop>
                                <div>
                                    <p class="boe-number">@{{ item.numero }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paginação -->
                    <div v-if="totalPages > 1 && !isSearching" class="mt-auto d-flex justify-content-center">
                        <nav>
                            <ul class="pagination pagination-custom mb-0 pagination-sm">
                                <li :class="['page-item', currentPage === 1 ? 'disabled' : '']">
                                    <button class="page-link" @click="changePage(currentPage - 1)">Anterior</button>
                                </li>
                                
                                <li v-for="page in visiblePages" :key="page" :class="['page-item', currentPage === page ? 'active' : '']">
                                    <button class="page-link" @click="changePage(page)">@{{ page }}</button>
                                </li>
                                
                                <li :class="['page-item', currentPage === totalPages ? 'disabled' : '']">
                                    <button class="page-link" @click="changePage(currentPage + 1)">Próxima</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const { createApp, ref, computed, onMounted, watch } = Vue;

    createApp({
        setup() {
            const jobId = ref('job_' + Math.random().toString(36).substr(2, 9));
            const auth = ref({ usuario: '', senha: '' });
            const filters = ref({ nome: '', delegacia: '', inicio: '', fim: '' });
            
            const isConnected = ref(false);
            const isConnecting = ref(false);
            const isSearching = ref(false);
            const isDownloading = ref(false);
            
            const currentStatus = ref('');
            const results = ref([]);
            const selectedIndices = ref([]);
            
            const downloadStatus = ref('');
            const downloadPercent = ref(0);

            // PAGINAÇÃO (Múltiplo de 4 para ficar legal com o grid atualizado)
            const currentPage = ref(1);
            const itemsPerPage = 24; 

            onMounted(() => {
                document.querySelectorAll('.date-mask').forEach(input => {
                    input.addEventListener('input', (e) => {
                        let v = e.target.value.replace(/\D/g, '').substring(0, 8);
                        if (v.length >= 5) v = v.substring(0,2) + '/' + v.substring(2,4) + '/' + v.substring(4);
                        else if (v.length >= 3) v = v.substring(0,2) + '/' + v.substring(2);
                        e.target.value = v;
                    });
                });
            });

            const canSearch = computed(() => isConnected.value && filters.value.nome.length >= 5);

            // Helpers de Paginação
            const paginatedResults = computed(() => {
                const start = (currentPage.value - 1) * itemsPerPage;
                return results.value.slice(start, start + itemsPerPage);
            });

            const totalPages = computed(() => Math.ceil(results.value.length / itemsPerPage));

            const visiblePages = computed(() => {
                let pages = [];
                let start = Math.max(1, currentPage.value - 2);
                let end = Math.min(totalPages.value, start + 4);
                if (end - start < 4) {
                    start = Math.max(1, end - 4);
                }
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                return pages;
            });

            const changePage = (page) => {
                if(page >= 1 && page <= totalPages.value) {
                    currentPage.value = page;
                }
            };

            // Helpers de Seleção
            const isSelected = (index) => selectedIndices.value.includes(index);
            
            const toggleSelection = (index) => {
                const pos = selectedIndices.value.indexOf(index);
                if (pos > -1) {
                    selectedIndices.value.splice(pos, 1);
                } else {
                    selectedIndices.value.push(index);
                }
            };

            const selectAllPage = () => {
                paginatedResults.value.forEach(item => {
                    if (!isSelected(item.index)) {
                        selectedIndices.value.push(item.index);
                    }
                });
            };

            const clearSelection = () => {
                selectedIndices.value = [];
            };

            // Actions Backend
            const handleConnect = async () => {
                if (!auth.value.usuario || !auth.value.senha) return alert('Informe as credenciais.');
                isConnecting.value = true;
                isConnected.value = false;

                try {
                    const response = await fetch("{{ route('infopol.conectar') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ ...auth.value, jobId: jobId.value })
                    });
                    await processStream(response, (data) => {
                        if (data.status === 'connected') isConnected.value = true;
                        if (data.status === 'error') alert(data.message);
                    });
                } finally {
                    isConnecting.value = false;
                }
            };

            const handleSearch = async () => {
                isSearching.value = true;
                results.value = [];
                selectedIndices.value = [];
                currentPage.value = 1;
                currentStatus.value = '';

                try {
                    const response = await fetch("{{ route('infopol.buscar') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ ...filters.value, jobId: jobId.value })
                    });

                    if (response.status === 401) {
                        isConnected.value = false;
                        alert('Sessão expirada. Conecte-se novamente.');
                        return;
                    }

                    await processStream(response, (data) => {
                        if (data.message) currentStatus.value = data.message;
                        
                        // Atualiza a grid imediatamente
                        if (data.status === 'partial_result' && data.data.item) {
                            results.value.push(data.data.item);
                        }
                        
                        // Fallback pra método de resposta total antigo, se existir
                        if (data.status === 'results' && data.data.results) {
                            results.value = data.data.results;
                        }

                        if (data.status === 'no_results') alert('Nenhum BO encontrado.');
                    });
                } finally {
                    isSearching.value = false;
                }
            };

            const handleDownload = async () => {
                isDownloading.value = true;
                downloadStatus.value = 'Iniciando downloads...';
                downloadPercent.value = 0;

                try {
                    const indicesStr = selectedIndices.value.join(',');
                    const response = await fetch("{{ route('infopol.baixarSelecionados') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ ...filters.value, jobId: jobId.value, indices: indicesStr })
                    });

                    await processStream(response, (data) => {
                        if (data.message) downloadStatus.value = data.message;
                        if (data.status === 'downloading' && data.data) {
                            downloadPercent.value = Math.floor((data.data.current / data.data.total) * 100);
                        }
                        if (data.status === 'finished' && data.download_url) {
                            window.location.href = data.download_url;
                            downloadStatus.value = 'Concluído!';
                        }
                    });
                } finally {
                    isDownloading.value = false;
                }
            };

            const processStream = async (response, onData) => {
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    for (const line of lines) {
                        if (!line.trim()) continue;
                        try {
                            const data = JSON.parse(line);
                            onData(data);
                        } catch (e) { }
                    }
                }
            };

            return {
                auth, filters, isConnected, isConnecting, isSearching, isDownloading,
                currentStatus, results, selectedIndices,
                downloadStatus, downloadPercent, canSearch,
                currentPage, totalPages, paginatedResults, visiblePages, itemsPerPage,
                changePage, isSelected, toggleSelection, selectAllPage, clearSelection,
                handleConnect, handleSearch, handleDownload
            };
        }
    }).mount('#app');
</script>
@endpush


@extends('layouts.app')

@php
    $title = "Verificação SEI";
@endphp

@push('styles')
<style>
    html, body {
        background-color: #0f172a !important;
        color: #f1f1f1;
    }

    main.container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        height: calc(100vh - 56px);
        overflow-y: auto;
    }

    .container-sei {
        padding: 12px 16px 40px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .hero {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .hero i {
        font-size: 2.1rem;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .card-sei {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
    }

    .form-control, .form-select {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(148, 163, 184, 0.18) !important;
        color: #e2e8f0 !important;
    }

    .form-control::placeholder {
        color: rgba(226, 232, 240, 0.45);
    }

    .btn-action {
        border-radius: 10px;
        font-weight: 700;
        letter-spacing: .2px;
        padding: 10px 12px;
    }

    .btn-connect {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        border: none;
        color: #0b1220;
    }

    .btn-verify-sei {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none;
        color: #fff;
    }

    .badge-state {
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(15, 23, 42, 0.6);
        color: #cbd5e1;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .75rem;
    }

    .table {
        color: #e2e8f0;
    }

    .table thead th {
        color: #cbd5e1;
        border-color: rgba(148, 163, 184, 0.18);
    }

    .table td, .table th {
        border-color: rgba(148, 163, 184, 0.10);
    }

    .muted {
        color: #94a3b8;
    }

    [v-cloak] {
        display: none;
    }

    /* Estilo Premium para a Tabela */
    .table-custom {
        background: rgba(15, 23, 42, 0.4);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .table-custom thead {
        background: rgba(30, 41, 59, 0.8);
    }

    .table-custom th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        color: #94a3b8 !important;
        border: none !important;
        padding: 12px 15px !important;
    }

    .table-custom td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
        padding: 10px 15px !important;
        color: #e2e8f0;
        vertical-align: middle;
        transition: background 0.2s;
    }

    .table-custom tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    .text-nowrap-sei {
        white-space: nowrap !important;
        font-family: 'Roboto Mono', 'Courier New', monospace;
        letter-spacing: -0.5px;
        font-size: 0.95rem; /* Aumentado */
        color: #3b82f6;
    }

    .text-identificador {
        font-size: 0.9rem;
        font-weight: 600;
        color: #e2e8f0;
    }

    .text-pessoa {
        font-size: 0.85rem;
        color: #94a3b8;
    }

    /* Badges Customizados Premium */
    .badge-premium {
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .bg-status-success { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .bg-status-warning { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .bg-status-danger  { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .bg-status-muted   { background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }

    /* Paginação Customizada */
    .pagination-custom {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 15px;
    }

    .page-link-custom {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #94a3b8;
        padding: 5px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.8rem;
    }

    .page-link-custom:hover {
        background: rgba(59, 130, 246, 0.2);
        border-color: #3b82f6;
        color: #fff;
    }

    .page-link-custom.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #fff;
        font-weight: bold;
    }

    .btn-xs {
        padding: 2px 8px;
        font-size: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-sei" id="app" v-cloak>
    <div class="hero">
        <i class="bi bi-shield-check"></i>
        <div>
            <h1 class="h4 mb-0 fw-bold text-white">@{{ appTitle }}</h1>
            <div class="muted small">Conecta no SEI e confere se existe perícia (ou palavras-chave) para os @{{ appSubtitle }} cadastrados.</div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="badge-state">
                <i :class="['bi', isConnected ? 'bi-circle-fill' : 'bi-x-circle-fill']" style="font-size: .55rem;"></i>
                @{{ isConnected ? 'Conectado' : 'Desconectado' }}
            </span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card-sei p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold text-white small">Sessão</div>
                </div>

                <div class="mb-2">
                    <label class="form-label small">URL do SEI</label>
                    <input class="form-control" v-model="auth.base_url" placeholder="https://sei.pe.gov.br/sip/login.php?...">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Usuário</label>
                    <input class="form-control" v-model="auth.usuario" placeholder="Seu usuário do SEI">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Senha</label>
                    <input class="form-control" type="password" v-model="auth.senha" placeholder="••••••••">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Órgão</label>
                    <input class="form-control" v-model="auth.orgao" placeholder="Ex: PCPE">
                </div>
                <div v-if="isConnected" class="d-flex align-items-center gap-2 mt-1">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span class="small text-success fw-semibold">Sessão ativa</span>
                </div>

                <div class="mt-3">
                    <label class="form-label small text-info">Palavras-chave (opcional)</label>
                    <input class="form-control" v-model="keywords" placeholder="PERÍCIA, PERICIA, LAUDO">
                </div>
                <div v-if="lastLoginScreenshotUrl" class="mt-2 small">
                    <a :href="lastLoginScreenshotUrl" target="_blank" class="link-light text-decoration-underline">Ver screenshot do login</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card-sei p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="fw-bold text-white">SEIs para verificar</div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-light" @click="loadFromCadastro" :disabled="isLoadingSeis">
                            <span v-if="isLoadingSeis" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-database me-2"></i>
                            Carregar Pendentes (@{{ tipoLabel }})
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" @click="clearAll" :disabled="isChecking">
                            <i class="bi bi-eraser me-2"></i>Limpar
                        </button>
                    </div>
                </div>

                <textarea class="form-control" rows="5" v-model="seisText" placeholder="Um SEI por linha"></textarea>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                    <div class="muted small">
                        Total: <span class="text-white fw-bold">@{{ seisList.length }}</span>
                        <span v-if="progress.total" class="ms-2">Progresso: <span class="text-white fw-bold">@{{ progress.current }}</span>/<span class="text-white fw-bold">@{{ progress.total }}</span></span>
                    </div>
                    <div class="d-flex gap-2">
                        <button v-if="isChecking" type="button" class="btn btn-action btn-outline-danger" @click="handleStop">
                            <i class="bi bi-stop-circle me-1"></i>
                            Parar
                        </button>
                        <button type="button" class="btn btn-action btn-verify-sei" @click="handleCheck" :disabled="!canCheck">
                            <span v-if="isChecking" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-search me-2"></i>
                            Verificar no SEI
                        </button>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="muted small">@{{ statusText }}</div>
                </div>
            </div>

            <div class="card-sei p-3 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold text-white">Resultados</div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="muted small">Encontrados: @{{ resultsFound }} | Com laudo/perícia: @{{ resultsPericia }}</div>
                        <button type="button" class="btn btn-sm btn-outline-light" @click="showSummary" :disabled="results.length === 0">
                            <i class="bi bi-clipboard-data me-2"></i>Resumo
                        </button>
                    </div>
                </div>

                <div class="table-responsive table-custom">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 260px;">SEI</th>
                                <th style="width: 120px;">BOE</th>
                                <th style="width: 130px;">@{{ colIdentificador }}</th>
                                <th>Nome / Pessoa</th>
                                <th style="width: 160px; text-align: center;">Status Final</th>
                                <th style="width: 100px; text-align: right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="results.length === 0">
                                <td colspan="6" class="muted text-center py-4">Nenhum resultado ainda.</td>
                            </tr>
                            <tr v-for="r in paginatedResults" :key="r.sei">
                                <td class="fw-bold text-nowrap-sei">
                                    <i class="bi bi-file-text-fill me-1 opacity-50"></i>
                                    @{{ r.sei }}
                                </td>
                                <td class="small opacity-75">@{{ r.boe || '-' }}</td>
                                <td class="text-identificador">@{{ r.identificador || '-' }}</td>
                                <td class="text-pessoa">@{{ r.pessoa || '-' }}</td>
                                <td class="text-center">
                                    <div v-if="r.status === 'nao_encontrado'" class="badge-premium bg-status-muted">
                                        <i class="bi bi-dash-circle"></i> Não achou
                                    </div>
                                    <div v-else-if="r.status === 'pericia_encontrada'" class="badge-premium bg-status-success">
                                        <i class="bi bi-check-circle-fill"></i> Perícia OK
                                    </div>
                                    <div v-else-if="r.status === 'sem_pericia'" class="badge-premium bg-status-warning">
                                        <i class="bi bi-exclamation-circle"></i> Sem Perícia
                                    </div>
                                    <div v-else-if="r.status && r.status.startsWith('erro')" class="badge-premium bg-status-danger">
                                        <i class="bi bi-x-circle"></i> Erro
                                    </div>
                                    <div v-else class="badge-premium bg-status-muted">@{{ r.status || '-' }}</div>
                                </td>
                                <td class="text-end">
                                    <a v-if="r.url" :href="r.url" target="_blank" class="btn btn-outline-info btn-xs fw-bold">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div v-if="results.length > pageSize" class="pagination-custom">
                    <div class="page-link-custom" :class="{ disabled: currentPage === 1 }" @click="prevPage">
                        <i class="bi bi-chevron-left"></i>
                    </div>
                    <div v-for="p in totalPages" :key="p" class="page-link-custom" 
                         :class="{ active: currentPage === p }" @click="currentPage = p">
                        @{{ p }}
                    </div>
                    <div class="page-link-custom" :class="{ disabled: currentPage === totalPages }" @click="nextPage">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast de Notificação -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060">
        <div id="toastStatus" class="toast align-items-center text-white bg-dark border-secondary shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i :class="['bi', toastIcon, toastColor, 'me-2']" style="font-size: 1.1rem"></i>
                    <span class="fw-semibold">@{{ toastMessage }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Modal moved inside #app -->
    <div class="modal fade" id="modalResumoSei" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-data me-2"></i>Resumo da verificação</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><div class="badge text-bg-secondary w-100 p-2 d-block">Total: @{{ summary.total ?? results.length }}</div></div>
                        <div class="col-md-4"><div class="badge text-bg-success w-100 p-2 d-block">Encontrados: @{{ summary.encontrados ?? resultsFound }}</div></div>
                        <div class="col-md-4"><div class="badge text-bg-warning w-100 p-2 d-block text-dark">Com laudo: @{{ summary.com_laudo ?? resultsPericia }}</div></div>
                        <div class="col-md-4"><div class="badge text-bg-info w-100 p-2 d-block text-dark">Sem laudo: @{{ summary.sem_laudo ?? 0 }}</div></div>
                        <div class="col-md-4"><div class="badge text-bg-dark border w-100 p-2 d-block">Não achou: @{{ summary.nao_encontrados ?? 0 }}</div></div>
                        <div class="col-md-4"><div class="badge text-bg-danger w-100 p-2 d-block">Erros: @{{ summary.erros ?? 0 }}</div></div>
                    </div>

                    <div class="mb-2 fw-semibold">SEIs com laudo/perícia detectado:</div>
                    <textarea class="form-control bg-black text-success border-secondary font-monospace" rows="8" readonly>@{{ laudoSeis.join('\n') }}</textarea>
                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-outline-success btn-sm" @click="copyLaudos" :disabled="laudoSeis.length === 0">
                            <i class="bi bi-clipboard me-2"></i>Copiar lista
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const { createApp, ref, computed, watch } = Vue;

    createApp({
        setup() {
            const jobId = ref(localStorage.getItem('seiJobId') || ('sess_' + Math.random().toString(16).slice(2)));
            localStorage.setItem('seiJobId', jobId.value);

            const userId = '{{ auth()->id() ?? "guest" }}';
            const authKey = 'seiAuth_' + userId;

            // Carrega credenciais salvas (exceto senha por segurança)
            const savedRaw = localStorage.getItem(authKey);
            const savedAuth = savedRaw ? JSON.parse(savedRaw) : null;
            if (savedAuth) {
                // Força a senha a vir vazia mesmo que estivesse salva anteriormente
                savedAuth.senha = ''; 
            }
            const auth = ref(savedAuth || {
                base_url: '',
                usuario: '',
                senha: '',
                orgao: 'PCPE'
            });

            const keywords = ref('');

            const isConnected = ref(false);
            const isConnecting = ref(false);
            const isChecking = ref(false);
            const isLoadingSeis = ref(false);

            const seisText = ref('');
            const statusText = ref('');
            const progress = ref({ current: 0, total: 0 });
            const lastLoginScreenshotUrl = ref('');

            // Notificações Toast
            const toastMessage = ref('');
            const toastIcon = ref('bi-info-circle');
            const toastColor = ref('text-info');

            const showToast = (msg, icon = 'bi-info-circle', color = 'text-info') => {
                toastMessage.value = msg;
                toastIcon.value = icon;
                toastColor.value = color;
                const toastEl = document.getElementById('toastStatus');
                if (toastEl) {
                    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
                    toast.show();
                }
            };

            const params = new URLSearchParams(window.location.search);
            const tipo = ref(params.get('tipo') || 'veiculo');
            const appTitle = computed(() => tipo.value === 'celular' ? 'Verificação SEI (Celulares)' : 'Verificação SEI (Veículos)');
            const appSubtitle = computed(() => tipo.value === 'celular' ? 'celulares' : 'veículos');
            const tipoLabel = computed(() => tipo.value === 'celular' ? 'Celulares' : 'Veículos');
            const colIdentificador = computed(() => tipo.value === 'celular' ? 'Pessoa/Dono' : 'Placa');

            const seiParam = (params.get('sei') || '').trim();
            const baseUrlParam = (params.get('base_url') || '').trim();
            if (seiParam) {
                seisText.value = seiParam;
            }
            if (baseUrlParam) {
                auth.value.base_url = baseUrlParam;
            } else {
                auth.value.base_url = auth.value.base_url || 'https://sei.pe.gov.br/sip/login.php?sigla_orgao_sistema=GOVPE&sigla_sistema=SEI';
            }

            const results = ref([]);
            const resultsBySei = ref({});
            const dataMap = ref({}); // Novo mapa completo para BOE, Pessoa, etc.
            const summary = ref({});

            // Paginação
            const currentPage = ref(1);
            const pageSize = ref(5);
            const totalPages = computed(() => Math.ceil(results.value.length / pageSize.value) || 1);
            const paginatedResults = computed(() => {
                const start = (currentPage.value - 1) * pageSize.value;
                return results.value.slice(start, start + pageSize.value);
            });

            const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
            const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

            const seisList = computed(() => {
                const lines = (seisText.value || '')
                    .split(/\r?\n/)
                    .map(s => s.trim())
                    .filter(Boolean);
                const unique = [];
                const seen = new Set();
                for (const l of lines) {
                    if (seen.has(l)) continue;
                    seen.add(l);
                    unique.push(l);
                }
                return unique;
            });

            const resultsFound = computed(() => results.value.filter(r => r.encontrado === true).length);
            const resultsPericia = computed(() => results.value.filter(r => r.pericia === true).length);
            const laudoSeis = computed(() => results.value.filter(r => r.pericia === true).map(r => r.sei));

            const canCheck = computed(() => !isChecking.value && !!auth.value.base_url && !!auth.value.usuario && !!auth.value.senha && seisList.value.length > 0);

            const upsertResult = (item) => {
                if (!item || !item.sei) return;
                const prev = resultsBySei.value[item.sei] || {};
                
                // Mescla dados do cadastro (BOE, Pessoa, Identificador original)
                const cadData = dataMap.value[item.sei] || {};
                const merged = { ...prev, ...cadData, ...item };
                
                // Se o item for novo na lista de resultados, podemos avançar a página se necessário
                const isNew = !resultsBySei.value[item.sei];
                
                resultsBySei.value[item.sei] = merged;
                results.value = Object.values(resultsBySei.value);

                // Auto-advance logic: if new item, check if it pushes us to a new page
                if (isNew && isChecking.value) {
                    const newTotal = results.value.length;
                    const calculatedPage = Math.ceil(newTotal / pageSize.value);
                    if (calculatedPage > currentPage.value) {
                        currentPage.value = calculatedPage;
                    }
                }
            };

            let abortController = null;

            const processStream = async (response, onData) => {
                if (!response.ok) {
                    let body = '';
                    try { body = await response.text(); } catch (e) { }
                    throw new Error(`HTTP ${response.status} ${response.statusText}${body ? ' - ' + body.slice(0, 180) : ''}`);
                }
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

            const handleConnect = async () => {
                if (!auth.value.base_url || !auth.value.usuario || !auth.value.senha) {
                    alert('Preencha URL, usuário e senha.');
                    return;
                }
                isConnecting.value = true;
                isConnected.value = false;
                statusText.value = 'Iniciando login...';
                lastLoginScreenshotUrl.value = '';

                try {
                    const response = await fetch("{{ route('sei.conectar') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ ...auth.value, jobId: jobId.value })
                    });

                    await processStream(response, (data) => {
                        if (data.message) statusText.value = data.message;
                        if (data.status === 'connected') isConnected.value = true;
                        if (data.status === 'error') alert(data.message || 'Falha ao conectar.');
                        if (data.status === 'screenshot' && data.data && data.data.url) {
                            lastLoginScreenshotUrl.value = data.data.url;
                        }
                    });
                } catch (e) {
                    alert(`Falha ao conectar: ${e.message || e}`);
                } finally {
                    isConnecting.value = false;
                }
            };

            const loadFromCadastro = async () => {
                isLoadingSeis.value = true;
                
                try {
                    const url = new URL("{{ route('sei.listarSeis') }}", window.location.origin);
                    url.searchParams.set('limit', '400');
                    url.searchParams.set('tipo', tipo.value);
                    
                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!data.success) {
                        alert('Falha ao carregar lista.');
                        return;
                    }
                    
                    // Guarda dados completos do cadastro
                    const lines = (data.data || [])
                        .map(i => {
                            if (i.sei) {
                                dataMap.value[i.sei.trim()] = {
                                    identificador: i.identificador,
                                    boe: i.boe,
                                    pessoa: i.pessoa
                                };
                            }
                            return (i.sei || '').trim();
                        })
                        .filter(Boolean);
                        
                    seisText.value = lines.join('\n');
                } catch (e) {
                    alert('Erro ao carregar dados do cadastro.');
                } finally {
                    isLoadingSeis.value = false;
                }
            };

            const clearAll = () => {
                seisText.value = '';
                statusText.value = '';
                progress.value = { current: 0, total: 0 };
                results.value = [];
                resultsBySei.value = {};
                summary.value = {};
                lastLoginScreenshotUrl.value = '';
                currentPage.value = 1;
            };

            const handleStop = async () => {
                if (abortController) {
                    abortController.abort();
                }
                isChecking.value = false;
                statusText.value = 'Interrompido pelo usuário.';
                
                try {
                    await fetch("{{ route('sei.parar') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ jobId: jobId.value })
                    });
                } catch (e) {}
            };

            const handleCheck = async () => {
                isChecking.value = true;
                statusText.value = 'Iniciando verificação...';
                progress.value = { current: 0, total: seisList.value.length };
                results.value = [];
                resultsBySei.value = {};
                summary.value = {};
                currentPage.value = 1;
                abortController = new AbortController();

                try {
                    const response = await fetch("{{ route('sei.verificar') }}", {
                        method: 'POST',
                        signal: abortController.signal,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            base_url: auth.value.base_url,
                            jobId: jobId.value,
                            seis: seisList.value,
                            keywords: keywords.value,
                            usuario: auth.value.usuario,
                            senha: auth.value.senha,
                            orgao: auth.value.orgao
                        })
                    });

                    if (response.status === 401) {
                        isConnected.value = false;
                        alert('Sessão expirada. Conecte-se novamente.');
                        return;
                    }

                    await processStream(response, (data) => {
                        if (data.message) statusText.value = data.message;
                        if (data.status === 'progress' && data.data) {
                            progress.value = { current: data.data.current || 0, total: data.data.total || progress.value.total };
                        }
                        if (data.status === 'partial_result' && data.data && data.data.item) {
                            upsertResult({
                                ...data.data.item,
                                screenshot_url: data.data.item.screenshot_url
                            });
                        }
                        if (data.status === 'screenshot' && data.data && data.data.url && data.data.sei) {
                            upsertResult({ sei: data.data.sei, screenshot_url: data.data.url });
                        }
                        if (data.status === 'connected') {
                            isConnected.value = true;
                        }
                        if (data.status === 'finished') {
                            isConnected.value = true;
                            summary.value = data.data || {};
                            showSummary();
                        }
                        if (data.status === 'expired') {
                            isConnected.value = false;
                        }
                    });
                } catch (e) {
                    if (e.name === 'AbortError') {
                        showToast('Operação interrompida pelo usuário', 'bi-stop-circle', 'text-warning');
                    } else {
                        alert(`Falha ao verificar: ${e.message || e}`);
                    }
                } finally {
                    isChecking.value = false;
                }
            };

            const openSei = () => {
                if (!auth.value.base_url) return;
                window.open(auth.value.base_url, '_blank');
            };

            const showSummary = () => {
                const el = document.getElementById('modalResumoSei');
                if (!el) return;
                const modal = new bootstrap.Modal(el);
                modal.show();
            };

            const copyLaudos = async () => {
                try {
                    await navigator.clipboard.writeText(laudoSeis.value.join('\n'));
                } catch (e) {
                    alert('Não foi possível copiar automaticamente.');
                }
            };

            // Salva credenciais no navegador sempre que mudar (mas filtrando a senha por segurança)
            watch(auth, (val) => {
                const toSave = { ...val };
                delete toSave.senha; // Nunca salva a senha no localStorage
                localStorage.setItem(authKey, JSON.stringify(toSave));
                if (!val.base_url) isConnected.value = false;
            }, { deep: true });

            return {
                jobId,
                auth,
                keywords,
                isConnected,
                isConnecting,
                isChecking,
                isLoadingSeis,
                seisText,
                seisList,
                statusText,
                progress,
                results,
                summary,
                lastLoginScreenshotUrl,
                resultsFound,
                resultsPericia,
                laudoSeis,
                canCheck,
                handleConnect,
                canCheck,
                handleConnect,
                loadFromCadastro,
                clearAll,
                handleCheck,
                openSei,
                showSummary,
                copyLaudos,
                tipo,
                appTitle,
                appSubtitle,
                tipoLabel,
                colIdentificador,
                currentPage,
                pageSize,
                totalPages,
                paginatedResults,
                prevPage,
                nextPage,
                handleStop,
                toastMessage,
                toastIcon,
                toastColor
            };
        }
    }).mount('#app');
</script>
@endpush

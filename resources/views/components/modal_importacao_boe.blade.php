<!-- Modal para Importação Híbrida de BOE (Texto ou PDF) -->
<div class="modal fade" id="{{ $modalId ?? 'modalImportarBoe' }}" tabindex="-1" aria-labelledby="{{ $modalId ?? 'modalImportarBoe' }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="{{ $modalId ?? 'modalImportarBoe' }}Label">
                    <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>{{ $modalTitle ?? 'Importar Histórico do BOE pelo Sistema' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body pt-3">
                <p class="text-muted small mb-4">O sistema detecta automaticamente se o Boletim é da Polícia Civil ou da Polícia Militar. Basta escolher a forma de envio abaixo.</p>

                <!-- Abas de Navegação Simplificadas -->
                <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3" id="boeImportTabs{{ $suffix ?? '' }}" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-3 fw-bold" id="tab-texto{{ $suffix ?? '' }}" data-bs-toggle="tab" data-bs-target="#content-texto{{ $suffix ?? '' }}" type="button" role="tab">
                            <i class="bi bi-fonts me-2"></i> Colar Texto do BOE
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 fw-bold" id="tab-pdf{{ $suffix ?? '' }}" data-bs-toggle="tab" data-bs-target="#content-pdf{{ $suffix ?? '' }}" type="button" role="tab">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Enviar Arquivo PDF
                        </button>
                    </li>
                    @if(strtolower($suffix ?? '') === 'intimacao')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 fw-bold" id="tab-buscar{{ $suffix ?? '' }}" data-bs-toggle="tab" data-bs-target="#content-buscar{{ $suffix ?? '' }}" type="button" role="tab">
                            <i class="bi bi-search me-2"></i> Buscar BOE Já Extraído
                        </button>
                    </li>
                    @endif
                </ul>
                
                <div class="tab-content" id="boeImportTabsContent{{ $suffix ?? '' }}">
                    <!-- Aba de Texto -->
                    <div class="tab-pane fade show active" id="content-texto{{ $suffix ?? '' }}" role="tabpanel">
                        <div class="form-floating mb-3 shadow-sm">
                            <textarea class="form-control" id="textoBoe{{ $suffix ?? '' }}" placeholder="Copie todo o texto do Boletim e cole aqui..." style="height: 250px; resize: none; border-radius: 0.75rem;"></textarea>
                            <label for="textoBoe{{ $suffix ?? '' }}" class="text-muted"><i class="bi bi-clipboard me-1"></i> Cole o conteúdo do BOE aqui...</label>
                        </div>
                    </div>
                    
                    <!-- Aba de PDF -->
                    <div class="tab-pane fade" id="content-pdf{{ $suffix ?? '' }}" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-4 text-center" style="border-radius: 0.75rem;">
                            <i class="bi bi-file-earmark-pdf text-danger mb-3" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold">Envio de Arquivo PDF</h6>
                            <p class="text-muted small mb-4">Selecione o arquivo PDF original do Boletim de Ocorrência salvo no seu computador.</p>
                            <div>
                                <input class="form-control" type="file" id="pdfBoe{{ $suffix ?? '' }}" accept=".pdf" style="border-radius: 0.5rem;">
                            </div>
                        </div>
                    </div>

                    @if(strtolower($suffix ?? '') === 'intimacao')
                    <!-- Aba Buscar BOE Já Extraído -->
                    <div class="tab-pane fade" id="content-buscar{{ $suffix ?? '' }}" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-4 text-center" style="border-radius: 0.75rem;">
                            <i class="bi bi-database-check text-success mb-3" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold">Reutilizar BOE Já Processado</h6>
                            <p class="text-muted small mb-3">Digite o número do BOE que <strong>você já extraiu</strong> em outra tela (ex: IP/APFD). Os dados serão carregados automaticamente, <strong>sem precisar colar ou extrair de novo</strong>.</p>
                            <div class="input-group input-group-lg mx-auto" style="max-width: 460px;">
                                <input type="text" class="form-control" id="inputBoeBuscar{{ $suffix ?? '' }}" placeholder="Ex: 26E0257002301" style="border-radius: 0.5rem 0 0 0.5rem;">
                                <button type="button" class="btn btn-success px-4 fw-bold" id="btnBuscarBoeExtraido{{ $suffix ?? '' }}" style="border-radius: 0 0.5rem 0.5rem 0;" title="Carregar dados deste BOE">
                                    <i class="bi bi-search me-1"></i> Buscar
                                </button>
                            </div>
                            <div id="buscarBoeFeedback{{ $suffix ?? '' }}" class="mt-3 small"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Barra de Progresso da IA -->
            <div id="{{ strtolower($suffix ?? '') === 'intimacao' ? 'boeProgressWrapperIntimacao' : (strtolower($suffix ?? '') === 'veiculo' ? 'veiculoProgressWrapper' : (strtolower($suffix ?? '') === 'celular' ? 'celularProgressWrapper' : 'boeProgressWrapper')) }}" class="px-4 pb-3" style="display:none;">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-primary fw-bold" id="{{ strtolower($suffix ?? '') === 'intimacao' ? 'boeProgressLabelIntimacao' : (strtolower($suffix ?? '') === 'veiculo' ? 'veiculoProgressLabel' : (strtolower($suffix ?? '') === 'celular' ? 'celularProgressLabel' : 'boeProgressLabel')) }}">🤖 Processando documento...</small>
                    <small class="text-primary fw-bold" id="{{ strtolower($suffix ?? '') === 'intimacao' ? 'boeProgressPercentIntimacao' : (strtolower($suffix ?? '') === 'veiculo' ? 'veiculoProgressPercent' : (strtolower($suffix ?? '') === 'celular' ? 'celularProgressPercent' : 'boeProgressPercent')) }}">0%</small>
                </div>
                <div class="progress" style="height: 8px; border-radius: 10px;">
                    <div id="{{ strtolower($suffix ?? '') === 'intimacao' ? 'boeProgressBarIntimacao' : (strtolower($suffix ?? '') === 'veiculo' ? 'veiculoProgressBar' : (strtolower($suffix ?? '') === 'celular' ? 'celularProgressBar' : 'boeProgressBar')) }}" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 10px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary text-white px-4 fw-bold" style="border-radius: 0.5rem;" data-bs-dismiss="modal">Cancelar</button>
                <div class="d-flex gap-2">
                    @php
                        $userAuth = auth()->user();
                        $canUseIa = !($userAuth && isset($userAuth->permissions['extracao_boe_ia']) && !$userAuth->permissions['extracao_boe_ia']);
                    @endphp
                    
                    @if($canUseIa)
                        <button type="button" class="btn btn-dark px-4 fw-bold shadow-sm" id="btnProcessarIA{{ $suffix ?? '' }}" style="border-radius: 0.5rem;" title="Extrair usando a Inteligência Artificial (DeepSeek)">
                            <i class="bi bi-robot text-info me-1"></i> Inteligência Artificial
                        </button>
                    @endif
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnProcessarBoe{{ $suffix ?? '' }}" style="border-radius: 0.5rem;" title="Extrair usando as regras nativas (Regex/Python)">
                        <i class="bi bi-cpu me-1"></i> Motor Nativo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

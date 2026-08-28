<!-- MODAL GLOBAL DE RECONHECIMENTO FOTOGRÁFICO -->
<div class="modal fade" id="modalGlobalReconhecimento" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-camera"></i> Preparar Auto de Reconhecimento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs" id="reconhecimentoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="busca-tab" data-bs-toggle="tab" data-bs-target="#busca-album" type="button" role="tab">
                            <i class="bi bi-search"></i> Buscar no Álbum
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-novo" type="button" role="tab">
                            <i class="bi bi-upload"></i> Subir Foto na Hora
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-3" id="reconhecimentoTabsContent">
                    <!-- Aba 1: Busca -->
                    <div class="tab-pane fade show active" id="busca-album" role="tabpanel">
                        <form id="formFiltroGlobalAlbum" class="mb-3">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" id="global_filtro_termo" placeholder="Nome/Alcunha/Marcas...">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="global_filtro_sexo">
                                        <option value="">Gênero...</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="FEMININO">FEMININO</option>
                                        <option value="OUTRO">OUTRO/LGBTQIA+</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="global_filtro_cor_pele">
                                        <option value="">Cor da Pele...</option>
                                        <option value="BRANCO">BRANCO</option>
                                        <option value="PARDO">PARDO</option>
                                        <option value="NEGRO">NEGRO</option>
                                        <option value="AMARELO">AMARELO</option>
                                        <option value="INDIGENA">INDÍGENA</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="global_filtro_cabelo">
                                        <option value="">Cabelo...</option>
                                        <option value="LISO">LISO</option>
                                        <option value="ONDULADO">ONDULADO</option>
                                        <option value="CACHEADO">CACHEADO</option>
                                        <option value="CRESPO">CRESPO</option>
                                        <option value="CARECA/CALVO">CARECA/CALVO</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="globalBuscarImagensAlbum()">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="globalResultadoBuscaImagens" class="row" style="max-height: 400px; overflow-y: auto;">
                            <!-- Imagens -->
                        </div>
                    </div>
                    
                    <!-- Aba 2: Upload na hora -->
                    <div class="tab-pane fade" id="upload-novo" role="tabpanel">
                        <div class="text-center py-4">
                            <p class="text-muted">A foto selecionada aqui será usada como IMAGEM 1 no Auto de Reconhecimento.</p>
                            <input type="file" id="globalUploadFotoReal" accept="image/*" class="form-control w-50 mx-auto mb-3">
                            <img id="globalPreviewFotoReal" src="" style="max-height: 200px; display: none;" class="img-thumbnail">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-primary fs-6"><span id="globalCountSelecionadas">0</span>/4 Selecionadas</span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGlobalGerarAuto" onclick="globalGerarAutoReconhecimento()">
                        <i class="bi bi-file-earmark-pdf"></i> Gerar Auto de Reconhecimento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

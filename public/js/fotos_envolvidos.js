/**
 * ✅ MÓDULO CENTRALIZADO DE FOTOS DE ENVOLVIDOS
 * 
 * Gerencia upload, galeria e exibição de fotos para TODOS os tipos de envolvido:
 * Autor, Vítima, Testemunha, Condutor, Outros.
 * 
 * A foto pertence à PESSOA (IdCad na cadpessoa), não ao papel.
 * Uma foto cadastrada para um "Autor" aparece automaticamente se a mesma pessoa
 * for "Vítima" em outro procedimento.
 */
$(document).ready(function () {

    // Configuração de cada tipo de envolvido
    const tiposEnvolvido = [
        {
            tipo: 'Autor',
            previewId: '#previewFotoAutor1',
            btnUploadId: '#btnUploadFotoAutor1',
            inputFileId: '#inputFotoAutor1',
            btnGaleriaId: '#btnGaleriaAutor1',
            hiddenIdField: '#autor1_id'
        },
        {
            tipo: 'Vitima',
            previewId: '#previewFotoVitima1',
            btnUploadId: '#btnUploadFotoVitima1',
            inputFileId: '#inputFotoVitima1',
            btnGaleriaId: '#btnGaleriaVitima1',
            hiddenIdField: '#vitima1_id'
        },
        {
            tipo: 'Testemunha',
            previewId: '#previewFotoTestemunha1',
            btnUploadId: '#btnUploadFotoTestemunha1',
            inputFileId: '#inputFotoTestemunha1',
            btnGaleriaId: '#btnGaleriaTestemunha1',
            hiddenIdField: '#testemunha1_id'
        },
        {
            tipo: 'Condutor',
            previewId: '#previewFotoCondutor',
            btnUploadId: '#btnUploadFotoCondutor',
            inputFileId: '#inputFotoCondutor',
            btnGaleriaId: '#btnGaleriaCondutor',
            hiddenIdField: '#condutor_id'
        },
        {
            tipo: 'Outro',
            previewId: '#previewFotoOutro',
            btnUploadId: '#btnUploadFotoOutro',
            inputFileId: '#inputFotoOutro',
            btnGaleriaId: '#btnGaleriaOutro',
            hiddenIdField: '#outro_id'
        }
    ];

    // Inicializar cada tipo
    tiposEnvolvido.forEach(cfg => {
        const $preview = $(cfg.previewId);
        const $btnUpload = $(cfg.btnUploadId);
        const $inputFile = $(cfg.inputFileId);
        const $btnGaleria = $(cfg.btnGaleriaId);

        if (!$preview.length) return; // Elemento não existe na página

        // Botão câmera → abre file input
        $btnUpload.on('click', function () {
            const pessoaId = $(cfg.hiddenIdField).val();
            if (!pessoaId) {
                window.mostrarErro ? window.mostrarErro('Selecione ou salve o envolvido antes de anexar foto.') : alert('Selecione ou salve o envolvido antes.');
                return;
            }
            $inputFile.trigger('click');
        });

        // Selecionou arquivo → upload com crop via canvas (3:4 retrato)
        $inputFile.on('change', function () {
            const file = this.files[0];
            if (!file) return;

            const pessoaId = $(cfg.hiddenIdField).val();
            if (!pessoaId) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    // Crop 3:4 (retrato) centralizado
                    const targetRatio = 3 / 4;
                    let sw, sh, sx, sy;
                    const imgRatio = img.width / img.height;

                    if (imgRatio > targetRatio) {
                        sh = img.height;
                        sw = sh * targetRatio;
                        sx = (img.width - sw) / 2;
                        sy = 0;
                    } else {
                        sw = img.width;
                        sh = sw / targetRatio;
                        sx = 0;
                        sy = (img.height - sh) / 2;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = Math.min(sw, 600);
                    canvas.height = canvas.width / targetRatio;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob(function (blob) {
                        const formData = new FormData();
                        formData.append('foto', blob, 'foto_envolvido.jpg');
                        formData.append('tipo_envolvido', cfg.tipo);
                        formData.append('envolvido_id', pessoaId);

                        $.ajax({
                            url: '/envolvidos-fotos',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (resp) {
                                if (resp.success && resp.data && resp.data.url) {
                                    $preview.attr('src', resp.data.url);
                                    if (window.mostrarSucesso) window.mostrarSucesso('Foto salva com sucesso!');
                                }
                            },
                            error: function (xhr) {
                                console.error('Erro ao salvar foto:', xhr);
                                if (window.mostrarErro) window.mostrarErro('Erro ao salvar foto.');
                            }
                        });
                    }, 'image/jpeg', 0.85);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            $inputFile.val(''); // Reset para permitir re-upload do mesmo arquivo
        });

        // Botão Galeria → abre modal com todas as fotos da pessoa
        $btnGaleria.on('click', function () {
            const pessoaId = $(cfg.hiddenIdField).val();
            if (!pessoaId) {
                window.mostrarErro ? window.mostrarErro('Selecione ou salve o envolvido para ver a galeria.') : alert('Selecione ou salve o envolvido.');
                return;
            }
            abrirGaleria(pessoaId, cfg.tipo, $preview);
        });
    });

    // ========================
    // GALERIA (Modal compartilhado)
    // ========================
    function abrirGaleria(pessoaId, tipoLabel, $previewTarget) {
        // Cria/reutiliza modal
        let $modal = $('#modalGaleriaEnvolvido');
        if (!$modal.length) {
            $('body').append(`
                <div class="modal fade" id="modalGaleriaEnvolvido" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #f8f9fa;">
                            <div class="modal-header border-0 pb-0 pt-4 px-4">
                                <h5 class="modal-title d-flex align-items-center gap-2 text-dark" style="font-weight: 700; font-size: 1.25rem;">
                                    <div class="bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px;">
                                        <i class="bi bi-images fs-5"></i>
                                    </div>
                                    Galeria de Fotos
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: #e9ecef; border-radius: 50%; padding: 0.6rem; margin-right: 0.5rem;"></button>
                            </div>
                            <div class="modal-body p-4 pt-3" style="min-height: 300px;">
                                <div id="galeriaFotosContainer" class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-4 justify-content-center">
                                    <div class="d-flex justify-content-center align-items-center w-100 py-5">
                                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            $modal = $('#modalGaleriaEnvolvido');
        }

        const $container = $('#galeriaFotosContainer');
        $container.html(`
            <div class="d-flex justify-content-center align-items-center w-100 py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            </div>
        `);
        
        let modalInstance = bootstrap.Modal.getInstance($modal[0]);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal($modal[0]);
        }
        modalInstance.show();

        $.ajax({
            url: '/envolvidos-fotos',
            method: 'GET',
            data: { envolvido_id: pessoaId },
            success: function (resp) {
                $container.empty();
                if (!resp.success || !resp.data || resp.data.length === 0) {
                    $container.html(`
                        <div class="text-center w-100 py-5">
                            <div class="bg-white shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-camera text-muted" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="text-secondary fw-bold">Nenhuma foto encontrada</h5>
                            <p class="text-muted">Adicione fotos a este perfil para visualizá-las aqui.</p>
                        </div>
                    `);
                    return;
                }

                resp.data.forEach(function (foto) {
                    const isPrincipal = foto.is_principal == 1;
                    const card = $(`
                        <div class="col">
                            <div class="card h-100 border-0 position-relative overflow-hidden group" style="border-radius: 16px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);">
                                <div class="position-relative overflow-hidden" style="padding-top: 133.33%; background: #f1f3f5;">
                                    <img src="${foto.url}" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); cursor: pointer;" title="${isPrincipal ? 'Foto Principal' : 'Clique para definir como principal'}">
                                    
                                    ${isPrincipal ? `
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge rounded-pill bg-primary shadow-sm" style="font-weight: 600; padding: 0.4rem 0.6rem; border: 1px solid rgba(255,255,255,0.5);">
                                                <i class="bi bi-check2-circle me-1"></i> Principal
                                            </span>
                                        </div>
                                    ` : `
                                        <div class="foto-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.4); opacity: 0; transition: opacity 0.3s ease; cursor: pointer;">
                                            <button class="btn btn-light rounded-pill fw-bold text-primary btn-set-principal shadow" style="padding: 0.4rem 1rem;">
                                                <i class="bi bi-star me-1"></i> Definir
                                            </button>
                                        </div>
                                    `}
                                </div>
                                <div class="card-body p-2 bg-white d-flex align-items-center justify-content-center" style="border-top: 1px solid rgba(0,0,0,0.03);">
                                    <button class="btn btn-sm text-danger btn-excluir-foto hover-bg-danger rounded-pill w-100 d-flex align-items-center justify-content-center gap-1" data-id="${foto.id}" style="transition: all 0.2s; font-weight: 500;">
                                        <i class="bi bi-trash3"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);

                    // Adicionar efeitos visuais de hover
                    const $cardInner = card.find('.card');
                    const $img = card.find('img');
                    const $overlay = card.find('.foto-overlay');
                    const $btnDelete = card.find('.btn-excluir-foto');
                    
                    $cardInner.hover(
                        function() {
                            $(this).css('transform', 'translateY(-5px)');
                            $(this).css('box-shadow', '0 12px 25px rgba(0,0,0,0.1)');
                            $img.css('transform', 'scale(1.08)');
                            if ($overlay.length) $overlay.css('opacity', '1');
                        },
                        function() {
                            $(this).css('transform', 'translateY(0)');
                            $(this).css('box-shadow', '0 4px 15px rgba(0,0,0,0.05)');
                            $img.css('transform', 'scale(1)');
                            if ($overlay.length) $overlay.css('opacity', '0');
                        }
                    );

                    $btnDelete.hover(
                        function() {
                            $(this).removeClass('text-danger').addClass('bg-danger text-white');
                        },
                        function() {
                            $(this).removeClass('bg-danger text-white').addClass('text-danger');
                        }
                    );

                    // Ações
                    $img.on('click', function () {
                        if (!isPrincipal) definirPrincipal(foto.id, pessoaId, $previewTarget);
                    });

                    card.find('.btn-set-principal').on('click', function (e) {
                        e.stopPropagation();
                        definirPrincipal(foto.id, pessoaId, $previewTarget);
                    });

                    $btnDelete.on('click', function () {
                        if (!confirm('Deseja realmente excluir esta foto da galeria?')) return;
                        $.ajax({
                            url: '/envolvidos-fotos/' + foto.id,
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function () {
                                abrirGaleria(pessoaId, tipoLabel, $previewTarget);
                                if (isPrincipal) {
                                    $previewTarget.attr('src', '/images/b_PCPE.png');
                                }
                            }
                        });
                    });

                    $container.append(card);
                });
            },
            error: function () {
                $container.html('<p class="text-danger text-center w-100">Erro ao carregar galeria.</p>');
            }
        });
    }

    function definirPrincipal(fotoId, pessoaId, $previewTarget) {
        $.ajax({
            url: '/envolvidos-fotos/' + fotoId + '/principal',
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (resp) {
                if (resp.success) {
                    // Recarrega foto principal no preview
                    $.get('/envolvidos-fotos', { envolvido_id: pessoaId }, function (r) {
                        if (r.success && r.data && r.data.length > 0) {
                            const principal = r.data.find(f => f.is_principal == 1) || r.data[0];
                            $previewTarget.attr('src', principal.url);
                        }
                    });
                    if (window.mostrarSucesso) window.mostrarSucesso('Foto principal atualizada!');
                    // Refresh galeria
                    abrirGaleria(pessoaId, '', $previewTarget);
                }
            }
        });
    }

    // ========================
    // RESET DA FOTO ao clicar Novo/Limpar
    // ========================
    const resetButtons = [
        { btns: '#btnNovoAutor1, #btnLimparAutor1', preview: '#previewFotoAutor1' },
        { btns: '#btnNovaVitima1, #btnLimparVitima1', preview: '#previewFotoVitima1' },
        { btns: '#btnNovaTestemunha1, #btnLimparTestemunha1', preview: '#previewFotoTestemunha1' },
        { btns: '#btnNovoCondutor, #btnLimparCondutor', preview: '#previewFotoCondutor' },
        { btns: '#btnNovoOutro, #btnLimparOutro', preview: '#previewFotoOutro' }
    ];

    resetButtons.forEach(cfg => {
        $(cfg.btns).on('click', function () {
            $(cfg.preview).attr('src', '/images/b_PCPE.png');
        });
    });

    console.log('✅ MÓDULO FOTOS ENVOLVIDOS CARREGADO - Todos os tipos suportados');
});

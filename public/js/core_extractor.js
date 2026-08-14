/**
 * Core Extractor
 * Lida com as interações do modal de importação (Texto/PDF) e IA, 
 * disparando eventos para as páginas específicas manipularem os dados retornados.
 */

const CoreExtractor = (function() {
    // Instância única para gerenciar a extração
    class Extractor {
        constructor(options) {
            this.suffix = options.suffix || '';
            this.extractUrl = options.extractUrl || '';
            this.extractUrlIA = options.extractUrlIA || '/prompt/extrair-ia';
            this.onSuccess = options.onSuccess || function() {};
            
            this.selectors = {
                btnSistema: '#btnProcessarBoe' + this.suffix,
                btnIA: '#btnProcessarIA' + this.suffix,
                tabTexto: '#tab-texto' + this.suffix,
                tabPdf: '#tab-pdf' + this.suffix,
                tabTextoPM: '#tab-texto-pm' + this.suffix,
                tabPdfPM: '#tab-pdf-pm' + this.suffix,
                txtBoe: '#textoBoe' + this.suffix,
                pdfBoe: '#pdfBoe' + this.suffix,
                txtBoePM: '#textoBoePM' + this.suffix,
                pdfBoePM: '#pdfBoePM' + this.suffix,
                
                // O nome dos wrappers no modal original varia um pouco:
                progressWrapper: this.suffix.toLowerCase() === 'intimacao' ? '#boeProgressWrapperIntimacao' : (this.suffix.toLowerCase() === 'veiculo' ? '#veiculoProgressWrapper' : (this.suffix.toLowerCase() === 'celular' ? '#celularProgressWrapper' : '#boeProgressWrapper')),
                progressBar: this.suffix.toLowerCase() === 'intimacao' ? '#boeProgressBarIntimacao' : (this.suffix.toLowerCase() === 'veiculo' ? '#veiculoProgressBar' : (this.suffix.toLowerCase() === 'celular' ? '#celularProgressBar' : '#boeProgressBar')),
                progressPercent: this.suffix.toLowerCase() === 'intimacao' ? '#boeProgressPercentIntimacao' : (this.suffix.toLowerCase() === 'veiculo' ? '#veiculoProgressPercent' : (this.suffix.toLowerCase() === 'celular' ? '#celularProgressPercent' : '#boeProgressPercent')),
                progressLabel: this.suffix.toLowerCase() === 'intimacao' ? '#boeProgressLabelIntimacao' : (this.suffix.toLowerCase() === 'veiculo' ? '#veiculoProgressLabel' : (this.suffix.toLowerCase() === 'celular' ? '#celularProgressLabel' : '#boeProgressLabel'))
            };

            this.progressInterval = null;
            this.bindEvents();
        }

        bindEvents() {
            const self = this;
            $(this.selectors.btnSistema).off('click').on('click', function() {
                self.processarExtracao('sistema');
            });

            $(this.selectors.btnIA).off('click').on('click', function() {
                self.processarExtracao('ia');
            });

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $(self.selectors.btnIA).show();
            });
        }

        getPayload(motor) {
            // Verifica qual aba de botão (tab) está com a classe active
            const isTabTexto = $(this.selectors.tabTexto).hasClass('active');
            const isTabPdf = $(this.selectors.tabPdf).hasClass('active');
            const isTabTextoPM = $(this.selectors.tabTextoPM).hasClass('active');
            const isTabPdfPM = $(this.selectors.tabPdfPM).hasClass('active');

            let formData = new FormData();
            let isEmpty = true;

            if (isTabTexto) {
                const texto = $(this.selectors.txtBoe).val().trim();
                if(texto) {
                    if (motor === 'ia') {
                        formData.append('texto', texto);
                    } else {
                        formData.append('textoBOE', texto);
                        formData.append('tipo', 'texto_pc');
                    }
                    isEmpty = false;
                }
            } else if (isTabPdf) {
                const file = $(this.selectors.pdfBoe)[0].files[0];
                if(file) {
                    if (motor === 'ia') {
                        formData.append('pdfBOE', file);
                    } else {
                        formData.append('pdfBOE', file);
                        formData.append('tipo', 'pdf_pc');
                    }
                    isEmpty = false;
                }
            } else if (isTabTextoPM) {
                const texto = $(this.selectors.txtBoePM).val().trim();
                if(texto) {
                    if (motor === 'ia') {
                        formData.append('texto', texto);
                    } else {
                        formData.append('textoBOE', texto);
                        formData.append('tipo', 'texto_pm');
                    }
                    isEmpty = false;
                }
            } else if (isTabPdfPM) {
                const file = $(this.selectors.pdfBoePM)[0].files[0];
                if(file) {
                    if (motor === 'ia') {
                        formData.append('pdfBOE', file);
                    } else {
                        formData.append('pdfBOE', file);
                        formData.append('tipo', 'pdf_pm');
                    }
                    isEmpty = false;
                }
            }

            if (motor !== 'ia') {
                formData.append('tipo_bo', (isTabTextoPM || isTabPdfPM) ? 'PM' : 'PC');
            }

            return isEmpty ? false : formData;
        }

        processarExtracao(motor) {
            const self = this;
            const payload = this.getPayload(motor);

            if (payload === null) return; // Erro já tratado no getPayload

            if (payload === false) {
                if (window.mostrarErro) window.mostrarErro("Por favor, cole o texto ou selecione um arquivo PDF primeiro.");
                else alert("Por favor, cole o texto ou selecione um arquivo PDF primeiro.");
                return;
            }

            const url = motor === 'ia' ? this.extractUrlIA : this.extractUrl;

            if (!url) {
                if (window.mostrarErro) window.mostrarErro("URL de extração não configurada.");
                else alert("URL de extração não configurada.");
                return;
            }

            payload.append('_token', $('meta[name="csrf-token"]').attr('content'));

            // Set UI to loading state
            this.setLoadingState(true, motor);

            $.ajax({
                url: url,
                type: 'POST',
                data: payload,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    self.setLoadingState(false, motor, true);
                    self.onSuccess(response, motor);
                },
                error: function(xhr) {
                    self.setLoadingState(false, motor, false);
                    console.error(xhr);
                    let erro = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "Erro na extração. Verifique o console.";
                    if (window.mostrarErro) window.mostrarErro(erro);
                    else alert(erro);
                }
            });

            this.simularProgresso(motor);
        }

        simularProgresso(motor) {
            let p = 0;
            $(this.selectors.progressWrapper).show();
            $(this.selectors.progressBar).css('width', '0%').removeClass('bg-success bg-danger').addClass('bg-primary');
            $(this.selectors.progressPercent).text('0%');
            
            const txtLabel = motor === 'ia' ? '🤖 Inteligência Artificial analisando...' : '⚙️ O sistema está lendo o documento...';
            $(this.selectors.progressLabel).text(txtLabel);

            clearInterval(this.progressInterval);
            this.progressInterval = setInterval(() => {
                const step = motor === 'ia' ? (Math.random() * 5) : (Math.random() * 10);
                p += step;
                if(p > 90) p = 90; // Pausa no 90% até terminar
                $(this.selectors.progressBar).css('width', p + '%');
                $(this.selectors.progressPercent).text(Math.round(p) + '%');
            }, motor === 'ia' ? 1000 : 800);
        }

        setLoadingState(isLoading, motor, isSuccess = true) {
            if (isLoading) {
                if (motor === 'ia') {
                    $(this.selectors.btnIA).prop('disabled', true).html('<span class="spinner-border spinner-border-sm mt-1 mb-1 me-2" role="status" aria-hidden="true"></span> Extraindo via IA...');
                    $(this.selectors.btnSistema).prop('disabled', true);
                } else {
                    $(this.selectors.btnSistema).prop('disabled', true).html('<span class="spinner-border spinner-border-sm mt-1 mb-1 me-2" role="status" aria-hidden="true"></span> Extraindo...');
                    $(this.selectors.btnIA).prop('disabled', true);
                }
            } else {
                clearInterval(this.progressInterval);
                const colorClass = isSuccess ? 'bg-success' : 'bg-danger';
                $(this.selectors.progressBar).css('width', '100%').removeClass('bg-primary').addClass(colorClass);
                $(this.selectors.progressPercent).text('100%');
                $(this.selectors.progressLabel).text(isSuccess ? '✅ Extração concluída!' : '❌ Erro na extração.');
                
                setTimeout(() => {
                    $(this.selectors.progressWrapper).hide();
                }, 2500);

                $(this.selectors.btnIA).prop('disabled', false).html('<i class="bi bi-robot"></i> Inteligência Artificial');
                $(this.selectors.btnSistema).prop('disabled', false).html('Processar pelo Sistema');
            }
        }
    }

    return Extractor;

})();

// Expõe globalmente
window.CoreExtractor = CoreExtractor;

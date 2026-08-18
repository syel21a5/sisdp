/**
 * Sisdepol AI Copilot
 * Gerencia a interface de chat lateral e a execução de comandos da API do DeepSeek
 */

document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('copilotChatBox');
    const copilotForm = document.getElementById('copilotForm');
    const copilotInput = document.getElementById('copilotInput');
    const btnCopilotSend = document.getElementById('btnCopilotSend');
    const btnCopilotClear = document.getElementById('btnCopilotClear');

    // Variável para armazenar o histórico da conversa
    let conversationHistory = [];

    if (btnCopilotClear) {
        btnCopilotClear.addEventListener('click', function() {
            // Mantém apenas a primeira mensagem (de boas vindas)
            const firstMessage = chatBox.firstElementChild;
            chatBox.innerHTML = '';
            if (firstMessage) {
                chatBox.appendChild(firstMessage);
            }
            conversationHistory = []; // Limpa o histórico
        });
    }

    // Função para adicionar uma mensagem no chat
    function addMessage(text, isUser = false) {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-start ' + (isUser ? 'justify-content-end' : '');
        
        let bubbleClass = isUser 
            ? 'bg-info text-dark p-2 rounded-3' 
            : 'bg-primary text-white p-2 rounded-3';
            
        let borderRadius = isUser 
            ? 'border-bottom-right-radius: 0 !important;' 
            : 'border-bottom-left-radius: 0 !important;';

        div.innerHTML = `
            <div class="${bubbleClass}" style="max-width: 85%; font-size: 0.9rem; ${borderRadius} white-space: pre-wrap;">
                ${text.replace(/\n/g, '<br>')}
            </div>
        `;
        
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll
    }

    // Função para adicionar indicador de "digitando..."
    function addLoading() {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-start';
        div.id = 'copilotLoading';
        div.innerHTML = `
            <div class="bg-primary text-white p-2 rounded-3" style="max-width: 85%; font-size: 0.9rem; border-bottom-left-radius: 0 !important;">
                <div class="spinner-border spinner-border-sm text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div> Processando...
            </div>
        `;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function removeLoading() {
        const loading = document.getElementById('copilotLoading');
        if (loading) loading.remove();
    }

    // Enter para enviar (sem shift)
    copilotInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            copilotForm.dispatchEvent(new Event('submit'));
        }
    });

    copilotForm.addEventListener('submit', function (e) {
        e.preventDefault();
        
        const message = copilotInput.value.trim();
        if (!message) return;

        // 1. Mostrar a mensagem do usuário
        addMessage(message, true);
        copilotInput.value = '';
        
        // 2. Coletar Contexto do Sistema
        const contexto = montarContexto();
        
        // Clonar histórico atual para enviar, ANTES de adicionar a nova mensagem
        const historyToSend = [...conversationHistory];
        
        // Adicionar mensagem do usuário ao histórico local
        conversationHistory.push({ role: 'user', content: message });

        // 3. Mostrar carregamento e desabilitar input
        addLoading();
        copilotInput.disabled = true;
        btnCopilotSend.disabled = true;

        // 4. Enviar para o Backend
        fetch('/api/copilot/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                context: contexto,
                history: historyToSend
            })
        })
        .then(response => response.json())
        .then(data => {
            removeLoading();
            copilotInput.disabled = false;
            btnCopilotSend.disabled = false;
            copilotInput.focus();

            if (data.success) {
                // Adicionar resposta da IA ao histórico local
                conversationHistory.push({ role: 'assistant', content: data.reply || '' });

                // Verificar se há uma chamada de função (tool call)
                if (data.tool_call) {
                    executarComando(data.tool_call, data.reply);
                } else {
                    addMessage(data.reply);
                }
            } else {
                // Em caso de erro, remove a mensagem do usuário do histórico para não corromper o contexto
                conversationHistory.pop();
                addMessage('⚠️ Erro: ' + (data.error || 'Falha ao comunicar com a IA.'));
            }
        })
        .catch(error => {
            console.error('Erro no Copilot:', error);
            removeLoading();
            copilotInput.disabled = false;
            btnCopilotSend.disabled = false;
            // Remover do histórico também se a rede falhar
            conversationHistory.pop();
            addMessage('⚠️ Erro de rede ao tentar conectar com a IA.');
        });
    });

    // Função que varre a tela para coletar informações para a IA
    function montarContexto() {
        let boeText = '';
        const boeEl = document.getElementById('inputBOE');
        if (boeEl) boeText = boeEl.value;

        let envolvidosInfo = {};
        
        // Se a estrutura de chips nova estiver presente
        if (window.envolvidosChips) {
            Object.keys(window.envolvidosChips).forEach(tipo => {
                if (window.envolvidosChips[tipo] && window.envolvidosChips[tipo].length > 0) {
                    envolvidosInfo[tipo] = window.envolvidosChips[tipo].map(chip => {
                        return {
                            nome: chip.nome,
                            id: chip.id || chip.pessoa_id,
                            papel: tipo
                        };
                    });
                }
            });
        } 
        // Fallback para OcorrenciasApp antigo
        else if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
            envolvidosInfo = window.OcorrenciasApp.envolvidos;
        }

        return {
            boe_numero: boeText,
            boe: window.OcorrenciasApp && window.OcorrenciasApp.textoBoeImportado ? window.OcorrenciasApp.textoBoeImportado : '',
            envolvidos: envolvidosInfo
        };
    }

    // Função que recebe a ordem da IA e aciona o Sisdepol
    function executarComando(toolCall, textoInformativo) {
        console.log('🤖 Comando recebido do Copilot:', toolCall);
        
        if (toolCall.name === 'abrir_editor') {
            try {
                const args = JSON.parse(toolCall.arguments);
                const pessoaId = args.pessoa_id;
                const tipoTermo = args.tipo_termo; // ex: 'Termo_de_Depoimento', 'Termo_de_Declaracao'
                let textoGerado = args.texto_gerado || '';

                // Opcional: mostrar o texto informativo
                if (textoInformativo) {
                    addMessage(textoInformativo);
                } else {
                    addMessage(`⏳ Abrindo editor: ${tipoTermo}...`);
                }

                const boe = (document.getElementById('inputNrBOE') ? document.getElementById('inputNrBOE').value : '');
                
                // Procurar qual papel original essa pessoa tem (vitima, autor, testemunha)
                // Isso é necessário porque abrirEditorOitiva espera o papel
                let papelOriginal = '';
                if (window.envolvidosChips) {
                    for (const [papel, lista] of Object.entries(window.envolvidosChips)) {
                        const encontrou = lista.find(p => String(p.id || p.pessoa_id) === String(pessoaId));
                        if (encontrou) {
                            papelOriginal = papel.charAt(0).toUpperCase() + papel.slice(1);
                            // Ajustar singular
                            if(papelOriginal.endsWith('s')) papelOriginal = papelOriginal.slice(0, -1);
                            break;
                        }
                    }
                }
                if (!papelOriginal) papelOriginal = 'Outro'; // fallback

                // Chama a função correta do OcorrenciasApp dependendo se tem pessoa ou não!
                if (String(pessoaId) === '0' || !pessoaId) {
                    // Documento genérico sem envolvido
                    if (window.OcorrenciasApp && typeof window.OcorrenciasApp.imprimirDocumentoInicio === 'function') {
                        window.OcorrenciasApp.imprimirDocumentoInicio(tipoTermo);
                    } else {
                        addMessage('⚠️ Erro: OcorrenciasApp.imprimirDocumentoInicio não encontrada.');
                    }
                } else {
                    // Documento associado a uma pessoa
                    if (window.OcorrenciasApp && typeof window.OcorrenciasApp.abrirEditorOitiva === 'function') {
                        window.OcorrenciasApp.abrirEditorOitiva(tipoTermo, boe, pessoaId, papelOriginal, textoGerado);
                    } else {
                        addMessage('⚠️ Erro: OcorrenciasApp.abrirEditorOitiva não encontrada no sistema.');
                    }
                }
            } catch (e) {
                console.error("Erro ao analisar argumentos da função:", e);
                addMessage('⚠️ Erro ao tentar processar o comando da IA.');
            }
        } else {
            addMessage('⚠️ Comando desconhecido: ' + toolCall.name);
        }
    }
});

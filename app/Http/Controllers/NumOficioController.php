<?php

namespace App\Http\Controllers;

use App\Services\NumeroOficioService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request as FacadesRequest;

class NumOficioController extends Controller
{
    protected $numeroOficioService;

    public function __construct(NumeroOficioService $numeroOficioService)
    {
        $this->numeroOficioService = $numeroOficioService;
    }

    /**
     * Método ESPECÍFICO para Auto de Apreensão
     */
    public function gerarAutoApreensao($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pecas.auto_de_apreensao', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * ✅ NOVO MÉTODO: Ofícios MP
     */
    public function gerarTermoOficiosMp($dados = null)
    {
        $numeroOficioJuiz = $this->numeroOficioService->gerarProximo();
        $numeroOficioPromotor = $this->numeroOficioService->gerarProximo();
        $numeroOficioDefensor = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('mp.oficios_mp', compact('numeroOficioJuiz', 'numeroOficioPromotor', 'numeroOficioDefensor', 'dadosArray'));
    }

    public function gerarTermoOficioFamiliaMp($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('mp.oficiofamilia_mp', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoRecolhimentoMp($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('mp.recolhimento_mp', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoRestituicao($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pecas.termo_de_restituicao', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoRenuncia($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pecas.termo_de_renuncia_representacao', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoRepresentacao($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pecas.termo_de_representacao', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoCompromisso($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pecas.termo_de_compromisso_juizo', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoTraumatologicoIML($dados = null)
    {
        $dados = $dados ?: request('dados');
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pericias.traumatologico_iml', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoTraumatologico($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        return view('pericias.traumatologico', compact('dadosArray'));
    }

    public function gerarPericiaEmVeiculo($dados = null)
    {
        $dados = $dados ?: request('dados');
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pericias.PericiaEmVeiculo', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaEmLocalDeCrime($dados = null)
    {
        $dados = $dados ?: request('dados');
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pericias.PericiaEmLocalDeCrime', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarExameEficienciaArmaComDisparo($dados = null)
    {
        $dados = $dados ?: request('dados');
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('pericias.ExameEficienciaArmaComDisparo', compact('numeroOficio', 'dadosArray'));
    }



    public function gerarPericiaPapiloscopicaEmVeiculo($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaPapiloscopicaEmVeiculo', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaPapiloscopicaEmLocalDeCrime($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaPapiloscopicaEmLocalDeCrime', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaPapiloscopicaConfrontacao($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaPapiloscopicaConfrontacao', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaPapiloscopicaEmPessoa($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaPapiloscopicaEmPessoa', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaPapiloscopicaEmObjeto($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaPapiloscopicaEmObjeto', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarExamePreliminarEntorpecentes($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.ExamePreliminarEntorpecentes', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaDefinitivaEntorpecentes($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaDefinitivaEntorpecentes', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaArrombamento($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaArrombamento', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarOficioProntuarioHospitalar($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.Of_ProntuarioHospital', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaDocumentoscopica($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaDocumentoscopica', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaLocal($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaLocal', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarPericiaInformatica($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pericias.PericiaInformatica', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarTermoAutorizacaoDados($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        // Termo de autorização não precisa gerar número de ofício
        return view('pecas.TermoAutorizacaoDados', compact('dadosArray'));
    }

    public function gerarOficioComunicaAdvogado($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.OficioComunicaAdvogado', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarOficioEncaminharVeiculo($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.OficioEncaminharVeiculo', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarCertidaoMidiasDrive($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        return view('pecas.CertidaoMidiasDrive', compact('dadosArray'));
    }

    public function gerarCIRemessaProcedimentos($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.CIRemessaProcedimentos', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarCIRemessaObjetosDocumentos($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.CIRemessaObjetosDocumentos', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarCIGenerica($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.CIGenerica', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarCIEncaminhamentoIITB($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.CIEncaminhamentoIITB', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarOficioBanco($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.OficioBanco', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarOficioCertidaoObito($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.OficioCertidaoObito', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarOficioIML($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.OficioIML', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarOficioPM($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        $numeroOficio = $dadosArray['num_oficio'] ?? null;
        if (empty($numeroOficio)) {
            $numeroOficio = $this->numeroOficioService->gerarProximo();
            $dadosArray['num_oficio'] = $numeroOficio;
        }

        return view('pecas.OficioPM', compact('numeroOficio', 'dadosArray'));
    }

    public function gerarCertidaoComparecimento($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        // Certidão não requer número de ofício, então não geramos.
        return view('pecas.CertidaoComparecimento', compact('dadosArray'));
    }

    public function gerarCertidaoComunicaFamiliaPreso($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        // Certidão não requer número de ofício
        return view('pecas.CertidaoComunicaFamiliaPreso', compact('dadosArray'));
    }

    public function gerarReciboPreso($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        // Recibo não requer número de ofício
        return view('pecas.ReciboPreso', compact('dadosArray'));
    }

    public function gerarReciboProcedimento($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        // Recibo não requer número de ofício
        return view('pecas.ReciboProcedimento', compact('dadosArray'));
    }

    public function gerarAutoDepositoFiel($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        return view('pecas.AutoDepositoFiel', compact('dadosArray'));
    }

    public function gerarTermoGuardaVeiculo($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        return view('pecas.TermoGuardaVeiculo', compact('dadosArray'));
    }

    public function gerarTermoApreensao($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        return view('pecas.TermoApreensao', compact('dadosArray'));
    }

    public function gerarCapaBOC($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);

        // Helper para formatar array em lista HTML
        $formatarListaHTML = function($arrayNomes) {
            if (empty($arrayNomes) || !is_array($arrayNomes)) return 'NENHUM CADASTRADO';
            $html = '<ul>';
            foreach ($arrayNomes as $nome) {
                if(is_array($nome)) {
                    $n = $nome['nome'] ?? '';
                    if(!empty($n)) $html .= '<li>' . mb_strtoupper($n) . '</li>';
                } else if (is_string($nome)) {
                    $html .= '<li>' . mb_strtoupper($nome) . '</li>';
                }
            }
            $html .= '</ul>';
            return $html;
        };

        // Formatando as listas para a capa
        $dadosArray['vitimas'] = isset($dadosArray['lista_vitimas']) ? $formatarListaHTML($dadosArray['lista_vitimas']) : (isset($dadosArray['vitimas']) ? $formatarListaHTML($dadosArray['vitimas']) : 'NENHUMA VÍTIMA CADASTRADA');
        $dadosArray['autores'] = isset($dadosArray['lista_autores']) ? $formatarListaHTML($dadosArray['lista_autores']) : (isset($dadosArray['autores']) ? $formatarListaHTML($dadosArray['autores']) : 'NENHUM INFRATOR CADASTRADO');
        $dadosArray['condutores'] = isset($dadosArray['lista_condutores']) ? $formatarListaHTML($dadosArray['lista_condutores']) : (isset($dadosArray['condutores']) ? $formatarListaHTML($dadosArray['condutores']) : 'NENHUM CONDUTOR CADASTRADO');
        $dadosArray['testemunhas'] = isset($dadosArray['lista_testemunhas']) ? $formatarListaHTML($dadosArray['lista_testemunhas']) : (isset($dadosArray['testemunhas']) ? $formatarListaHTML($dadosArray['testemunhas']) : 'NENHUMA TESTEMUNHA CADASTRADA');
        
        return view('pecas.CapaBOC', compact('dadosArray'));
    }

    public function gerarCapaTCO($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);

        // Helper para formatar array em lista HTML
        $formatarListaHTML = function($arrayNomes) {
            if (empty($arrayNomes) || !is_array($arrayNomes)) return 'NENHUM CADASTRADO';
            $html = '<ul>';
            foreach ($arrayNomes as $nome) {
                if(is_array($nome)) {
                    $n = $nome['nome'] ?? '';
                    if(!empty($n)) $html .= '<li>' . mb_strtoupper($n) . '</li>';
                } else if (is_string($nome)) {
                    $html .= '<li>' . mb_strtoupper($nome) . '</li>';
                }
            }
            $html .= '</ul>';
            return $html;
        };

        // Formatando as listas para a capa
        $dadosArray['vitimas'] = isset($dadosArray['lista_vitimas']) ? $formatarListaHTML($dadosArray['lista_vitimas']) : (isset($dadosArray['vitimas']) ? $formatarListaHTML($dadosArray['vitimas']) : 'NENHUMA VÍTIMA CADASTRADA');
        $dadosArray['autores'] = isset($dadosArray['lista_autores']) ? $formatarListaHTML($dadosArray['lista_autores']) : (isset($dadosArray['autores']) ? $formatarListaHTML($dadosArray['autores']) : 'NENHUM INFRATOR CADASTRADO');
        $dadosArray['condutores'] = isset($dadosArray['lista_condutores']) ? $formatarListaHTML($dadosArray['lista_condutores']) : (isset($dadosArray['condutores']) ? $formatarListaHTML($dadosArray['condutores']) : 'NENHUM CONDUTOR CADASTRADO');
        $dadosArray['testemunhas'] = isset($dadosArray['lista_testemunhas']) ? $formatarListaHTML($dadosArray['lista_testemunhas']) : (isset($dadosArray['testemunhas']) ? $formatarListaHTML($dadosArray['testemunhas']) : 'NENHUMA TESTEMUNHA CADASTRADA');
        
        return view('pecas.CapaTCO', compact('dadosArray'));
    }

    public function gerarOrdemServicoIntimacao($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        return view('pecas.OrdemServicoIntimacao', compact('dadosArray'));
    }

    public function gerarOficioRemessa($dados = null)
    {
        $dados = $dados ?: request('dados');
        $dadosArray = $this->processarDados($dados);
        
        // Formatar lista de autores e vítimas inline para o ofício
        $formatarInline = function($arrayNomes, $tipo = 'autor') {
            if (empty($arrayNomes) || !is_array($arrayNomes)) return 'NENHUM CADASTRADO';
            
            $lista = [];
            foreach ($arrayNomes as $p) {
                if(is_array($p)) {
                    $nome = $p['nome'] ?? '';
                    if(empty($nome)) continue;
                    
                    $info = "<strong>" . mb_strtoupper($nome) . "</strong>";
                    $detalhes = [];
                    
                    $alcunha = isset($p['alcunha']) ? trim($p['alcunha']) : '';
                    if (!empty($alcunha) && strtoupper($alcunha) !== 'NÃO INFORMADO' && strtoupper($alcunha) !== 'NULL') {
                        $detalhes[] = "conhecido como: " . $alcunha;
                    }
                    
                    $cpf = isset($p['cpf']) ? trim($p['cpf']) : '';
                    if (!empty($cpf) && strtoupper($cpf) !== 'NÃO INFORMADO' && strtoupper($cpf) !== 'NULL') {
                        $detalhes[] = "CPF: " . $cpf;
                    }
                    
                    $rg = isset($p['rg']) ? trim($p['rg']) : '';
                    if (!empty($rg) && strtoupper($rg) !== 'NÃO INFORMADO' && strtoupper($rg) !== 'NULL') {
                        $detalhes[] = "RG: " . $rg;
                    }
                    
                    if (!empty($detalhes)) {
                        $info .= ", " . implode(', ', $detalhes);
                    }
                    if ($tipo === 'autor' && !empty($p['natureza_fato'])) {
                        $info .= ", por infringir as penas do(a) " . $p['natureza_fato'];
                    }
                    $lista[] = $info;
                } else if (is_string($p)) {
                    $lista[] = "<strong>" . mb_strtoupper($p) . "</strong>";
                }
            }
            
            if (count($lista) == 0) return 'NENHUM CADASTRADO';
            if (count($lista) == 1) return $lista[0];
            
            $ultimo = array_pop($lista);
            return implode('; ', $lista) . ' e ' . $ultimo;
        };
        
        $dadosArray['autores_qualificados'] = isset($dadosArray['lista_autores']) ? $formatarInline($dadosArray['lista_autores'], 'autor') : (isset($dadosArray['autores']) ? $formatarInline($dadosArray['autores'], 'autor') : 'NENHUM AUTOR CADASTRADO');
        $dadosArray['vitimas_qualificadas'] = isset($dadosArray['lista_vitimas']) ? $formatarInline($dadosArray['lista_vitimas'], 'vitima') : (isset($dadosArray['vitimas']) ? $formatarInline($dadosArray['vitimas'], 'vitima') : 'NENHUMA VÍTIMA CADASTRADA');

        // Tratamento Singular/Plural
        $qtdAutores = isset($dadosArray['lista_autores']) ? count($dadosArray['lista_autores']) : (isset($dadosArray['autores']) ? count($dadosArray['autores']) : 0);
        $dadosArray['texto_indiciado'] = $qtdAutores > 1 ? 'indiciados' : 'indiciado';
        
        $qtdVitimas = isset($dadosArray['lista_vitimas']) ? count($dadosArray['lista_vitimas']) : (isset($dadosArray['vitimas']) ? count($dadosArray['vitimas']) : 0);
        $dadosArray['texto_vitima'] = $qtdVitimas > 1 ? 'vítimas' : 'vítima';

        // Gera o número do ofício
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray['numero_oficio'] = $numeroOficio;

        return view('pecas.OficioRemessa', compact('dadosArray'));
    }

    /**
     * Processa dados codificados ou via Cache (UUID)
     */
    private function processarDados($dados)
    {
        if (!$dados) return [];

        $cached = null;
        // 1. Verificar se é um UUID (Cache)
        if (strlen($dados) === 36 || strpos($dados, 'session_') === 0) {
            $key = str_replace('session_', '', $dados);
            $cached = Cache::get('doc_sessao_' . $key);
        }

        // 2. Fallback para Base64 (Legado) ou JSON direto
        if (!$cached) {
            try {
                // Tenta decodificar como JSON direto primeiro
                $decoded = json_decode($dados, true);
                if (!is_array($decoded)) {
                    // Se não for JSON direto, tenta Base64
                    $decoded = json_decode(base64_decode($dados), true);
                }
                $cached = is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                $cached = [];
            }
        }

        if (empty($cached)) return [];

        // 3. Enriquecer dados dos envolvidos a partir do banco de dados (se houver id)
        $tipos = ['autores', 'vitimas', 'testemunhas', 'condutores', 'outros'];
        foreach ($tipos as $tipo) {
            if (isset($cached[$tipo]) && is_array($cached[$tipo])) {
                foreach ($cached[$tipo] as $key => $pessoa) {
                    if (!empty($pessoa['id'])) {
                        // Verifica se o TipoPenal já veio preenchido no JSON
                        $temTipoPenal = !empty($pessoa['tipopenal']) && strtoupper(trim($pessoa['tipopenal'])) !== 'NÃO INFORMADO';
                        
                        $pessoaBd = \Illuminate\Support\Facades\DB::table('cadpessoa')->where('IdCad', $pessoa['id'])->first();
                        if ($pessoaBd) {
                            if (!$temTipoPenal && !empty($pessoaBd->TipoPenal)) {
                                $cached[$tipo][$key]['tipopenal'] = strtoupper($pessoaBd->TipoPenal);
                            }
                            if (empty($pessoa['fianca']) && !empty($pessoaBd->Fianca)) {
                                $cached[$tipo][$key]['fianca'] = $pessoaBd->Fianca;
                            }
                            if (empty($pessoa['fianca_ext']) && !empty($pessoaBd->FiancaExt)) {
                                $cached[$tipo][$key]['fianca_ext'] = strtoupper($pessoaBd->FiancaExt);
                            }
                            if (!isset($pessoa['fianca_pago']) && isset($pessoaBd->FiancaPago)) {
                                $cached[$tipo][$key]['fianca_pago'] = (bool) $pessoaBd->FiancaPago;
                            }
                        }
                    }
                }
            }
        }

        // Também enriquecer chaves individuais legadas (autor1, autor2, etc.)
        for ($i = 1; $i <= 5; $i++) {
            $chave = "autor$i";
            if (isset($cached[$chave]) && is_array($cached[$chave]) && !empty($cached[$chave]['id'])) {
                $temTipoPenal = !empty($cached[$chave]['tipopenal']) && strtoupper(trim($cached[$chave]['tipopenal'])) !== 'NÃO INFORMADO';
                $pessoaBd = \Illuminate\Support\Facades\DB::table('cadpessoa')->where('IdCad', $cached[$chave]['id'])->first();
                if ($pessoaBd) {
                    if (!$temTipoPenal && !empty($pessoaBd->TipoPenal)) {
                        $cached[$chave]['tipopenal'] = strtoupper($pessoaBd->TipoPenal);
                    }
                    if (empty($cached[$chave]['fianca']) && !empty($pessoaBd->Fianca)) {
                        $cached[$chave]['fianca'] = $pessoaBd->Fianca;
                    }
                    if (empty($cached[$chave]['fianca_ext']) && !empty($pessoaBd->FiancaExt)) {
                        $cached[$chave]['fianca_ext'] = strtoupper($pessoaBd->FiancaExt);
                    }
                    if (!isset($cached[$chave]['fianca_pago']) && isset($pessoaBd->FiancaPago)) {
                        $cached[$chave]['fianca_pago'] = (bool) $pessoaBd->FiancaPago;
                    }
                }
            }
        }

        return $cached;
    }

    /**
     * Método adicional: apenas retorna o número (para uso via AJAX/API)
     */
    public function gerarNumero()
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();

        return response()->json([
            'success' => true,
            'numero_oficio' => $numeroOficio
        ]);
    }

    /**
     * NOVO MÉTODO: Gera múltiplos números sequenciais
     */
    public function gerarMultiplosNumeros($quantidade = 2)
    {
        $numeros = [];
        for ($i = 0; $i < $quantidade; $i++) {
            $numeros[] = $this->numeroOficioService->gerarProximo();
        }

        return response()->json([
            'success' => true,
            'numeros_oficio' => $numeros
        ]);
    }
    /**
     * ✅ NOVO MÉTODO: APFD Ofícios DINÂMICO
     * Gera TRÊS números sequenciais e carrega a view dinâmica
     */
    public function gerarTermoOficiosDinamico($dados = null)
    {
        // Gera TRÊS números sequenciais
        $numeroOficioJuiz = $this->numeroOficioService->gerarProximo();
        $numeroOficioPromotor = $this->numeroOficioService->gerarProximo();
        $numeroOficioDefensor = $this->numeroOficioService->gerarProximo();

        $dadosArray = $this->processarDados($dados);

        return view('apfd.oficios.oficios_apfd_dinamico', compact('numeroOficioJuiz', 'numeroOficioPromotor', 'numeroOficioDefensor', 'dadosArray'));
    }

    /**
     * ✅ NOVO MÉTODO: APFD Ofícios ÚNICO (Juiz, Promotor, Defensor mesma página)
     * Gera UM número sequencial
     */
    public function gerarTermoOficiosUnico($dados = null)
    {
        // Gera UM número sequencial
        $numeroOficio = $this->numeroOficioService->gerarProximo();

        $dadosArray = $this->processarDados($dados);

        return view('apfd.oficios.oficios_apfd_unico', compact('numeroOficio', 'dadosArray'));
    }
}

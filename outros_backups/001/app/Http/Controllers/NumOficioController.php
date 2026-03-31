<?php

namespace App\Http\Controllers;

use App\Services\NumeroOficioService;
use Illuminate\Http\Request;

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



    /* LEGACY METHODS REMOVED */



    /**
     * ✅ NOVO MÉTODO: Ofícios MP
     * Gera TRÊS números sequenciais para MP
     */
    public function gerarTermoOficiosMp($dados = null)
    {
        // Gera TRÊS números sequenciais
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

    /**
     * ✅ NOVO MÉTODO: Ofício Recolhimento MP
     */
    public function gerarTermoRecolhimentoMp($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);
        return view('mp.recolhimento_mp', compact('numeroOficio', 'dadosArray'));
    }


    /**
     * Método específico para Termo de Restituição
     */
    public function gerarTermoRestituicao($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pecas.termo_de_restituicao', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Método específico para Termo de Renúncia de Representação
     */
    public function gerarTermoRenuncia($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pecas.termo_de_renuncia_representacao', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Método específico para Termo de Representação
     */
    public function gerarTermoRepresentacao($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pecas.termo_de_representacao', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Método específico para Termo de Compromisso Juízo
     */
    public function gerarTermoCompromisso($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pecas.termo_de_compromisso_juizo', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Método específico para Termo Traumatológico IML
     */
    public function gerarTermoTraumatologicoIML($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pericias.traumatologico_iml', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Método específico para Perícia em Veículo
     */
    public function gerarPericiaEmVeiculo($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pericias.PericiaEmVeiculo', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Método específico para Perícia em Local de Crime
     */
    public function gerarPericiaEmLocalDeCrime($dados = null)
    {
        $numeroOficio = $this->numeroOficioService->gerarProximo();
        $dadosArray = $this->processarDados($dados);

        return view('pericias.PericiaEmLocalDeCrime', compact('numeroOficio', 'dadosArray'));
    }

    /**
     * Processa dados codificados
     */
    private function processarDados($dados)
    {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $dadosArray = [];
                }
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return $dadosArray;
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

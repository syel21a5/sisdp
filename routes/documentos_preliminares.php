<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_Avaliacao_Controller;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_AvaliacaoIndireta_Controller;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_ExameConstatacaoDanos_Controller;

use App\Http\Controllers\Documentos\PecasController\GerarPdf_ConstatacaoDanosIndireta_Controller;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_EficienciaArmaFogo_Controller;

Route::middleware(['auth', 'permission:preliminares'])->group(function () {

    // =============================================
    // ROTAS POST PARA GERAR PDFs
    // =============================================

    // 🔽 ROTAS POST PARA AVALIAÇÃO/PERÍCIAS PRELIMINARES
    Route::post('/termo-de-avaliacao-portaria', [GerarPdf_Avaliacao_Controller::class, 'gerarPdfAvaliacaoPortaria'])->name('termo.avaliacao.portaria.pdf');
    Route::post('/termo-de-avaliacao-termo', [GerarPdf_Avaliacao_Controller::class, 'gerarPdfAvaliacaoTermo'])->name('termo.avaliacao.termo.pdf');

    // 🔽 ROTAS EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO (POST)
    Route::post('/exame-danos-portaria', [GerarPdf_ExameConstatacaoDanos_Controller::class, 'gerarPdfExameDanosPortaria'])->name('exame.danos.portaria.pdf');
    Route::post('/exame-danos-termo', [GerarPdf_ExameConstatacaoDanos_Controller::class, 'gerarPdfExameDanosTermo'])->name('exame.danos.termo.pdf');

    // 🔽 ROTAS CONSTATAÇÃO INDIRETA (POST - GERAÇÃO PDF)
    Route::post('/constatacao-indireta-portaria', [GerarPdf_ConstatacaoDanosIndireta_Controller::class, 'gerarPdfConstatacaoIndiretaPortaria'])->name('constatacao.indireta.portaria.pdf');
    Route::post('/constatacao-indireta-termo', [GerarPdf_ConstatacaoDanosIndireta_Controller::class, 'gerarPdfConstatacaoIndiretaTermo'])->name('constatacao.indireta.termo.pdf');

    // 🔽 ROTAS EFICIÊNCIA DE ARMA DE FOGO (POST - GERAÇÃO PDF)
    Route::post('/eficiencia-arma-portaria', [GerarPdf_EficienciaArmaFogo_Controller::class, 'gerarPdfEficienciaArmaPortaria'])->name('constatacao.eficiencia.arma.portaria.pdf');
    Route::post('/eficiencia-arma-termo', [GerarPdf_EficienciaArmaFogo_Controller::class, 'gerarPdfEficienciaArmaTermo'])->name('constatacao.eficiencia.arma.termo.pdf');

    // 🔽 ROTAS POST PARA VISUALIZAÇÃO (PRG Pattern para evitar erro de reenvio)
    Route::post('/avaliacao-portaria-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_portaria_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/avaliacao-portaria/session_' . $key);
    })->name('avaliacao.portaria.view.post');

    Route::post('/avaliacao-termo-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_termo_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/avaliacao-termo/session_' . $key);
    })->name('avaliacao.termo.view.post');

    // 🔽 ROTAS GET PARA VISUALIZAÇÃO AVALIAÇÃO/PERÍCIAS PRELIMINARES
    Route::get('/avaliacao-portaria/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_portaria_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Avaliacao_Portaria', compact('dadosArray'));
    })->name('avaliacao.portaria');

    Route::get('/avaliacao-termo/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_termo_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Avaliacao_Termo', compact('dadosArray'));
    })->name('avaliacao.termo');

    // 🔽 ROTAS PARA AVALIAÇÃO INDIRETA
    Route::post('/avaliacao-indireta-portaria-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_indireta_portaria_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/avaliacao-indireta-portaria/session_' . $key);
    })->name('avaliacao.indireta.portaria.view.post');

    Route::post('/avaliacao-indireta-termo-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_indireta_termo_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/avaliacao-indireta-termo/session_' . $key);
    })->name('avaliacao.indireta.termo.view.post');

    Route::get('/avaliacao-indireta-portaria/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_indireta_portaria_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Avaliacao_Indireta_Portaria', compact('dadosArray'));
    })->name('avaliacao.indireta.portaria');

    Route::get('/avaliacao-indireta-termo/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_indireta_termo_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Avaliacao_Indireta_Termo', compact('dadosArray'));
    })->name('avaliacao.indireta.termo');

    // 🔽 ROTAS PARA EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO
    Route::post('/exame-danos-portaria-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_exame_danos_portaria_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/exame-danos-portaria/session_' . $key);
    })->name('exame.danos.portaria.view.post');

    Route::post('/exame-danos-termo-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_exame_danos_termo_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/exame-danos-termo/session_' . $key);
    })->name('exame.danos.termo.view.post');

    Route::get('/exame-danos-portaria/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_exame_danos_portaria_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Exame_Danos_Portaria', compact('dadosArray'));
    })->name('exame.danos.portaria.view');

    Route::get('/exame-danos-termo/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_exame_danos_termo_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Exame_Danos_Termo', compact('dadosArray'));
    })->name('exame.danos.termo.view');

    // 🔽 ROTAS PARA CONSTATAÇÃO INDIRETA (PRG + Views)
    Route::post('/constatacao-indireta-portaria-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_constatacao_indireta_portaria_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/constatacao-indireta-portaria/session_' . $key);
    })->name('constatacao.indireta.portaria.view.post');

    Route::post('/constatacao-indireta-termo-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_constatacao_indireta_termo_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/constatacao-indireta-termo/session_' . $key);
    })->name('constatacao.indireta.termo.view.post');

    Route::get('/constatacao-indireta-portaria/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_constatacao_indireta_portaria_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Constatacao_Indireta_Portaria', compact('dadosArray'));
    })->name('constatacao.indireta.portaria.view');

    Route::get('/constatacao-indireta-termo/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_constatacao_indireta_termo_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Constatacao_Indireta_Termo', compact('dadosArray'));
    })->name('constatacao.indireta.termo.view');

    // 🔽 ROTAS PARA EFICIÊNCIA DE ARMA DE FOGO (PRG + Views)
    Route::post('/eficiencia-arma-portaria-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_eficiencia_arma_portaria_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/eficiencia-arma-portaria/session_' . $key);
    })->name('constatacao.eficiencia.arma.portaria.view.post');

    Route::post('/eficiencia-arma-termo-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;
        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('prelim_eficiencia_arma_termo_' . $key, $dadosArray);
        session()->save();
        return redirect()->to('/eficiencia-arma-termo/session_' . $key);
    })->name('constatacao.eficiencia.arma.termo.view.post');

    Route::get('/eficiencia-arma-portaria/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_eficiencia_arma_portaria_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Eficiencia_Arma_Portaria', compact('dadosArray'));
    })->name('constatacao.eficiencia.arma.portaria.view');

    Route::get('/eficiencia-arma-termo/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('prelim_eficiencia_arma_termo_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }
        return view('pecas.exames_preliminares.Auto_Eficiencia_Arma_Termo', compact('dadosArray'));
    })->name('constatacao.eficiencia.arma.termo.view');

});

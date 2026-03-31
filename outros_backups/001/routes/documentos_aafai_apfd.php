<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAICondutorController;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAIVitima1Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAIVitima2Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAIVitima3Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAIAutor1Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAIAutor2Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAIAutor3Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAITestemunha1Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAITestemunha2Controller;
use App\Http\Controllers\Documentos\aafai\GerarPdfAAFAITestemunha3Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDCondutorController;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDVitima1Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDVitima2Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDVitima3Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDTestemunha1Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDTestemunha2Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDTestemunha3Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDAutor1Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDAutor2Controller;
use App\Http\Controllers\Documentos\apfd\GerarPdfAPFDAutor3Controller;
use App\Http\Controllers\Documentos\apfd\pecas\GerarPdfNotadeCulpa_1AutorController;
// Removidos: GerarPdfNotadeCulpa_2AutorController e GerarPdfNotadeCulpa_3AutorController


Route::middleware(['auth'])->group(function () {

    // 🔽 ROTA POST DO PDF AAFAI (EXISTENTES)
    Route::post('/aafai-condutor', [GerarPdfAAFAICondutorController::class, 'gerarPdfAAFAICondutor'])->name('termo.aafai.condutor.pdf');
    Route::post('/termo-aafai-vitima1', [GerarPdfAAFAIVitima1Controller::class, 'gerarPdfAAFAIVitima1'])->name('termo.aafai.vitima1.pdf');
    Route::post('/termo-aafai-vitima2', [GerarPdfAAFAIVitima2Controller::class, 'gerarPdfAAFAIVitima2'])->name('termo.aafai.vitima2.pdf');
    Route::post('/termo-aafai-vitima3', [GerarPdfAAFAIVitima3Controller::class, 'gerarPdfAAFAIVitima3'])->name('termo.aafai.vitima3.pdf');
    Route::post('/termo-aafai-autor1', [GerarPdfAAFAIAutor1Controller::class, 'gerarPdfAAFAIAutor1'])->name('termo.aafai.autor1.pdf');
    Route::post('/termo-aafai-autor2', [GerarPdfAAFAIAutor2Controller::class, 'gerarPdfAAFAIAutor2'])->name('termo.aafai.autor2.pdf');
    Route::post('/termo-aafai-autor3', [GerarPdfAAFAIAutor3Controller::class, 'gerarPdfAAFAIAutor3'])->name('termo.aafai.autor3.pdf');
    Route::post('/termo-aafai-testemunha1', [GerarPdfAAFAITestemunha1Controller::class, 'gerarPdfAAFAITestemunha1'])->name('termo.aafai.testemunha1.pdf');
    Route::post('/termo-aafai-testemunha2', [GerarPdfAAFAITestemunha2Controller::class, 'gerarPdfAAFAITestemunha2'])->name('termo.aafai.testemunha2.pdf');
    Route::post('/termo-aafai-testemunha3', [GerarPdfAAFAITestemunha3Controller::class, 'gerarPdfAAFAITestemunha3'])->name('termo.aafai.testemunha3.pdf');

    // 🔽 ROTA POST DO PDF APFD (EXISTENTES - VERSÕES PADRÃO)
    Route::post('/apfd-condutor', [GerarPdfAPFDCondutorController::class, 'gerarPdfAPFDCondutor'])->name('termo.apfd.condutor.pdf');
    Route::post('/termo-apfd-vitima1', [GerarPdfAPFDVitima1Controller::class, 'gerarPdfAPFDVitima1'])->name('termo.apfd.vitima1.pdf');
    Route::post('/termo-apfd-vitima2', [GerarPdfAPFDVitima2Controller::class, 'gerarPdfAPFDVitima2'])->name('termo.apfd.vitima2.pdf');
    Route::post('/termo-apfd-vitima3', [GerarPdfAPFDVitima3Controller::class, 'gerarPdfAPFDVitima3'])->name('termo.apfd.vitima3.pdf');
    Route::post('/termo-apfd-testemunha1', [GerarPdfAPFDTestemunha1Controller::class, 'gerarPdfAPFDTestemunha1'])->name('termo.apfd.testemunha1.pdf');
    Route::post('/termo-apfd-testemunha2', [GerarPdfAPFDTestemunha2Controller::class, 'gerarPdfAPFDTestemunha2'])->name('termo.apfd.testemunha2.pdf');
    Route::post('/termo-apfd-testemunha3', [GerarPdfAPFDTestemunha3Controller::class, 'gerarPdfAPFDTestemunha3'])->name('termo.apfd.testemunha3.pdf');
    Route::post('/termo-apfd-autor1', [GerarPdfAPFDAutor1Controller::class, 'gerarPdfAPFDAutor1'])->name('termo.apfd.autor1.pdf');
    Route::post('/termo-apfd-autor2', [GerarPdfAPFDAutor2Controller::class, 'gerarPdfAPFDAutor2'])->name('termo.apfd.autor2.pdf');
    Route::post('/termo-apfd-autor3', [GerarPdfAPFDAutor3Controller::class, 'gerarPdfAPFDAutor3'])->name('termo.apfd.autor3.pdf');
    Route::post('/notadeculpa-apfd-1autor', [GerarPdfNotadeCulpa_1AutorController::class, 'GerarPdfNotadeCulpa_1Autor'])->name('termo.apfd.notadeculpa.1autor.pdf');
    Route::post('/notadeculpa-apfd-dinamica', [GerarPdfNotadeCulpa_1AutorController::class, 'GerarPdfNotadeCulpa_1Autor'])->name('termo.apfd.notadeculpa.dinamica.pdf');

    // ✅ NOVAS ROTAS PARA VERSÕES COM FIANÇA
    Route::post('/termo-apfd-autor1-com-fianca', [GerarPdfAPFDAutor1Controller::class, 'gerarPdfAPFDAutor1ComFianca'])->name('termo.apfd.autor1.com.fianca.pdf');
    Route::post('/termo-apfd-autor2-com-fianca', [GerarPdfAPFDAutor2Controller::class, 'gerarPdfAPFDAutor2ComFianca'])->name('termo.apfd.autor2.com.fianca.pdf');
    Route::post('/termo-apfd-autor3-com-fianca', [GerarPdfAPFDAutor3Controller::class, 'gerarPdfAPFDAutor3ComFianca'])->name('termo.apfd.autor3.com.fianca.pdf');

    // ✅ NOVAS ROTAS PARA VERSÕES SEM FIANÇA
    Route::post('/termo-apfd-autor1-sem-fianca', [GerarPdfAPFDAutor1Controller::class, 'gerarPdfAPFDAutor1SemFianca'])->name('termo.apfd.autor1.sem.fianca.pdf');
    Route::post('/termo-apfd-autor2-sem-fianca', [GerarPdfAPFDAutor2Controller::class, 'gerarPdfAPFDAutor2SemFianca'])->name('termo.apfd.autor2.sem.fianca.pdf');
    Route::post('/termo-apfd-autor3-sem-fianca', [GerarPdfAPFDAutor3Controller::class, 'gerarPdfAPFDAutor3SemFianca'])->name('termo.apfd.autor3.sem.fianca.pdf');

    // 🔽 ROTAS GET PARA VISUALIZAÇÃO AAFAI/APFD (TODAS AS QUE ESTAVAM NO WEB.PHP)
    Route::get('/aafai-condutor/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_condutor', compact('dadosArray'));
    })->name('aafai.condutor');

    Route::get('/apfd-vitima1/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_vitima1', compact('dadosArray'));
    })->name('apfd.vitima1');

    // 🔽 ROTA GET DO APFD - CONDUTOR
    Route::get('/apfd-condutor/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_condutor', compact('dadosArray'));
    })->name('apfd.condutor');

    Route::get('/aafai-vitima2/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_vitima2', compact('dadosArray'));
    })->name('aafai.vitima2');

    Route::get('/apfd-vitima2/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_vitima2', compact('dadosArray'));
    })->name('apfd.vitima2');

    Route::get('/aafai-vitima3/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_vitima3', compact('dadosArray'));
    })->name('aafai.vitima3');

    Route::get('/apfd-vitima3/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_vitima3', compact('dadosArray'));
    })->name('apfd.vitima3');

    // 🔽 ROTA GET DO AAFAI - AUTOR 1
    Route::get('/aafai-autor1/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_autor1', compact('dadosArray'));
    })->name('aafai.autor1');

    // 🔽 ROTA GET DO APFD - AUTOR 1
    Route::get('/apfd-autor1/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor1', compact('dadosArray'));
    })->name('apfd.autor1');

    Route::get('/notadeculpa-apfd-1autor/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.pecas.notadeculpa_dinamica', compact('dadosArray'));
    })->name('apfd.notadeculpa.1autor');

    Route::get('/notadeculpa-dinamica/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.pecas.notadeculpa_dinamica', compact('dadosArray'));
    })->name('apfd.notadeculpa.dinamica');

    Route::get('/aafai-autor2/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_autor2', compact('dadosArray'));
    })->name('aafai.autor2');

    // 🔽 ROTA GET DO APFD - AUTOR 2
    Route::get('/apfd-autor2/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor2', compact('dadosArray'));
    })->name('apfd.autor2');

    Route::get('/aafai-autor3/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_autor3', compact('dadosArray'));
    })->name('aafai.autor3');

    // 🔽 ROTA GET DO APFD - AUTOR 3
    Route::get('/apfd-autor3/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor3', compact('dadosArray'));
    })->name('apfd.autor3');

    // 🔽 ROTA GET DO AAFAI - TESTEMUNHA 1
    Route::get('/aafai-testemunha1/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_testemunha1', compact('dadosArray'));
    })->name('aafai.testemunha1');

    // 🔽 ROTA GET DO APFD - TESTEMUNHA 1
    Route::get('/apfd-testemunha1/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_testemunha1', compact('dadosArray'));
    })->name('apfd.testemunha1');

    Route::get('/aafai-testemunha2/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_testemunha2', compact('dadosArray'));
    })->name('aafai.testemunha2');

    // 🔽 ROTA GET DO APFD - TESTEMUNHA 2
    Route::get('/apfd-testemunha2/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_testemunha2', compact('dadosArray'));
    })->name('apfd.testemunha2');

    Route::get('/aafai-testemunha3/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('aafai.aafai_testemunha3', compact('dadosArray'));
    })->name('aafai.testemunha3');

    // 🔽 ROTA GET DO APFD - TESTEMUNHA 3
    Route::get('/apfd-testemunha3/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_testemunha3', compact('dadosArray'));
    })->name('apfd.testemunha3');

    // ✅ NOVAS ROTAS GET PARA AS VERSÕES COM/SEM FIANÇA
    Route::get('/apfd-autor1-com-fianca/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor1_com_fianca', compact('dadosArray'));
    })->name('apfd.autor1.com.fianca');

    Route::get('/apfd-autor1-sem-fianca/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor1_sem_fianca', compact('dadosArray'));
    })->name('apfd.autor1.sem.fianca');

    Route::get('/apfd-autor2-com-fianca/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor2_com_fianca', compact('dadosArray'));
    })->name('apfd.autor2.com.fianca');

    Route::get('/apfd-autor2-sem-fianca/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor2_sem_fianca', compact('dadosArray'));
    })->name('apfd.autor2.sem.fianca');

    Route::get('/apfd-autor3-com-fianca/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor3_com_fianca', compact('dadosArray'));
    })->name('apfd.autor3.com.fianca');

    Route::get('/apfd-autor3-sem-fianca/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.apfd_autor3_sem_fianca', compact('dadosArray'));
    })->name('apfd.autor3.sem.fianca');

});

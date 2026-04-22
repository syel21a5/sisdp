<?php
namespace App\Http\Controllers\Documentos\Pericias;

use Illuminate\Http\Request;
use App\Services\PdfService;
use App\Http\Controllers\Controller;

class GerarTraumatologicoHospitalarController extends Controller
{
    public function gerarPdf(Request $request)
    {
        // Validar os dados recebidos - ADICIONADOS NOVOS CAMPOS
        $request->validate([
            'conteudo' => 'required|string',
            'delegacia' => 'nullable|string',
            'cidade' => 'nullable|string',
            'delegado' => 'nullable|string',
            'escrivao' => 'nullable|string',
            'nome' => 'nullable|string',
            'nascimento' => 'nullable|string',
            'idade' => 'nullable|string',
            'rg' => 'nullable|string',
            'cpf' => 'nullable|string',
            'mae' => 'nullable|string',
            'pai' => 'nullable|string',
            'endereco' => 'nullable|string',
            'boe' => 'nullable|string',
            'data_comp' => 'nullable|string',
            'numeroOficio' => 'nullable|string'
        ]);

        $dados = $request->all();

        // ✅ REMOVER BOM E CARACTERES INVÁLIDOS - SOLUÇÃO DEFINITIVA
        $dados['conteudo'] = PdfService::cleanContent($dados['conteudo']);

        

        // Converter imagens para base64
        $image1Base64 = PdfService::imageToBase64(public_path('images/b_PE.jpg'));
        $image2Base64 = PdfService::imageToBase64(public_path('images/b_PCPE.png'));

        // ✅ ADICIONAR NÚMERO DO BOE NO RODAPÉ - CORRIGIDO
        $textoRodape = '
        <div style="line-height: 1.4;">
            RUA VALDEVINO JOSÉ PRAXEDES, nº S/N, MANOELA VALADARES, AFOGADOS DA INGAZEIRA-PE<br>
            Telefone: (87) 3838-8777 | (87) 3838-8778 | (87) 3838-8780
        </div>';

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* MARGEM PADRÃO PARA TODAS AS PÁGINAS - AJUSTADA */
        @page {
            margin: 125px 30px 70px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt !important;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            text-align: center;
            margin-bottom: 5px;
            height: 90px;
        }
        .content {
            margin-top: 15px;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        .page-break {
            page-break-before: always;
            padding-top: 40px !important;
        }
        p {
            margin: 0.3em 0;
            padding: 0;
            line-height: 1.3;
            text-align: justify;
        }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .orgao-principal {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 2px;
            line-height: 1.1;
        }
        .orgao-secundario {
            font-size: 10.5pt;
            margin-bottom: 1px;
            line-height: 1.1;
        }
        .delegacia-info {
            margin-top: 3px;
            font-weight: bold;
            font-size: 11.5pt;
            line-height: 1.1;
        }
        .header table {
            margin-bottom: 0 !important;
        }
        .header td {
            padding: 0 5px !important;
        }
        .header img {
            width: 85px !important;
            height: 85px !important;
            margin-top: -3px !important;
            object-fit: contain;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .table-quesitos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12pt;
        }
        .table-quesitos td {
            border: 1px solid black;
            padding: 6px 8px;
            vertical-align: top;
        }
        .page-break {
            page-break-before: always;
            padding-top: 40px !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="width: 85px; vertical-align: middle; text-align: center; padding: 0 5px;">
                    ' . ($image1Base64 ? '<img src="' . $image1Base64 . '" alt="Brasão PE" style="width: 85px; height: 85px; object-fit: contain; margin-top: -10px !important;">' : '') . '
                </td>
                <td style="vertical-align: middle; text-align: center; padding: 0 5px;">
                    <div class="orgao-principal">
                        POLÍCIA CIVIL DE PERNAMBUCO - PCPE
                    </div>
                    <div class="orgao-secundario">
                        Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2
                    </div>
                    <div class="orgao-secundario">
                        Gerência de Controle Operacional do Interior - 2 – GCOI - 2
                    </div>
                    <div class="orgao-secundario">
                        20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª DESEC
                    </div>
                    <div class="delegacia-info">
                        ' . ($dados['delegacia'] ?? 'NÃO INFORMADO') . ' – ' . ($dados['cidade'] ?? 'NÃO INFORMADO') . '
                    </div>
                </td>
                <td style="width: 85px; vertical-align: middle; text-align: center; padding: 0 5px;">
                    ' . ($image2Base64 ? '<img src="' . $image2Base64 . '" alt="Brasão PCPE" style="width: 85px; height: 85px; object-fit: contain; margin-top: -10px !important;">' : '') . '
                </td>
            </tr>
        </table>
    </div>
    <div class="footer">
        ' . $textoRodape . '
    </div>
    <div class="content">' . $dados['conteudo'] . '</div>
</body>
</html>';

        return PdfService::generatePdf($html, 'traumatologico-hospitalar-' . date('Y-m-d-His') . '.pdf');
    }

    // Função para converter imagem para base64
}

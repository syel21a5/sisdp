<?php
namespace App\Http\Controllers\Documentos\Pericias;

use Illuminate\Http\Request;
use App\Services\PdfService;
use App\Http\Controllers\Controller;

class GerarTraumatologicoIMLController extends Controller
{
    public function gerarPdfTraumatologicoIML(Request $request)
    {
        // Validar os dados recebidos - ADICIONADOS NOVOS CAMPOS
        $request->validate([
            'conteudo' => 'required',
            'delegacia' => 'sometimes|string',
            'cidade' => 'sometimes|string',
            'delegado' => 'sometimes|string',
            'escrivao' => 'sometimes|string',
            'nome' => 'sometimes|string',
            'nascimento' => 'sometimes|string',
            'idade' => 'sometimes|string',
            'rg' => 'sometimes|string',
            'cpf' => 'sometimes|string',
            'mae' => 'sometimes|string',
            'pai' => 'sometimes|string',
            'endereco' => 'sometimes|string',
            'boe' => 'sometimes|string',
            'data_comp' => 'sometimes|string',
            'numeroOficio' => 'sometimes|string'
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
            font-size: 12.5pt !important;
            line-height: 1.6;
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
            margin: 0.4em 0;
            padding: 0;
            line-height: 1.6;
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
            font-size: 12.5pt;
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
        .ql-font-arial, .ql-font-arial p, .ql-font-arial span, .ql-font-arial div, .ql-font-arial strong {
            font-family: Arial, sans-serif !important;
        }
        .ql-font-times-new-roman, .ql-font-times-new-roman p, .ql-font-times-new-roman span, .ql-font-times-new-roman div, .ql-font-times-new-roman strong {
            font-family: "Times New Roman", Times, serif !important;
        }
        .ql-font-courier-new, .ql-font-courier-new p, .ql-font-courier-new span, .ql-font-courier-new div, .ql-font-courier-new strong {
            font-family: "Courier New", monospace !important;
        }
        .ql-font-georgia, .ql-font-georgia p, .ql-font-georgia span, .ql-font-georgia div, .ql-font-georgia strong {
            font-family: Georgia, serif !important;
        }
        .ql-font-verdana, .ql-font-verdana p, .ql-font-verdana span, .ql-font-verdana div, .ql-font-verdana strong {
            font-family: Verdana, sans-serif !important;
        }
        .ql-font-segoe-ui, .ql-font-segoe-ui p, .ql-font-segoe-ui span, .ql-font-segoe-ui div, .ql-font-segoe-ui strong {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif !important;
        }
        .ql-size-10pt, .ql-size-10pt p, .ql-size-10pt span, .ql-size-10pt div, .ql-size-10pt strong {
            font-size: 10pt !important;
        }
        .ql-size-11pt, .ql-size-11pt p, .ql-size-11pt span, .ql-size-11pt div, .ql-size-11pt strong {
            font-size: 11pt !important;
        }
        .ql-size-12pt, .ql-size-12pt p, .ql-size-12pt span, .ql-size-12pt div, .ql-size-12pt strong {
            font-size: 12pt !important;
        }
        .ql-size-14pt, .ql-size-14pt p, .ql-size-14pt span, .ql-size-14pt div, .ql-size-14pt strong {
            font-size: 14pt !important;
        }
        .ql-size-16pt, .ql-size-16pt p, .ql-size-16pt span, .ql-size-16pt div, .ql-size-16pt strong {
            font-size: 16pt !important;
        }
        .ql-size-18pt, .ql-size-18pt p, .ql-size-18pt span, .ql-size-18pt div, .ql-size-18pt strong {
            font-size: 18pt !important;
        }
        .ql-size-20pt, .ql-size-20pt p, .ql-size-20pt span, .ql-size-20pt div, .ql-size-20pt strong {
            font-size: 20pt !important;
        }
        .ql-align-center {
            text-align: center !important;
        }
        .ql-align-justify {
            text-align: justify !important;
        }
        .ql-align-right {
            text-align: right !important;
        }
        .ql-align-left {
            text-align: left !important;
        }
        .ql-editor {
            border: none !important;
            padding: 0 !important;
        }
        .ql-clipboard {
            display: none !important;
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

        return PdfService::generatePdf($html, 'traumatologico-iml-' . date('Y-m-d-His') . '.pdf');
    }

    // Função para converter imagem para base64
}

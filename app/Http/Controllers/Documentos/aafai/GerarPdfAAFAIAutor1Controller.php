<?php
namespace App\Http\Controllers\Documentos\aafai;

use Illuminate\Http\Request;
use App\Services\PdfService;
use App\Http\Controllers\Controller;

class GerarPdfAAFAIAutor1Controller extends Controller
{
    public function gerarPdfAAFAIAutor1(Request $request)
    {
        // Validar os dados recebidos
        $request->validate([
            'conteudo' => 'required',
            'delegacia' => 'sometimes|string',
            'cidade' => 'sometimes|string',
        ]);

        $dados = $request->all();

        // ✅ REMOVER BOM E CARACTERES INVÁLIDOS
        $dados['conteudo'] = PdfService::cleanContent($dados['conteudo']);

        

        // Converter imagens para base64
        $image1Base64 = PdfService::imageToBase64(public_path('images/b_PE.jpg'));
        $image2Base64 = PdfService::imageToBase64(public_path('images/b_PCPE.png'));

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
                /* MESMO ESTILO DO INTERROGATÓRIO - APENAS ALTEREI O TÍTULO */
                @page {
                    margin: 125px 30px 100px 30px;
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
                    bottom: -70px;
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
                /* ESTILOS DO QUILL PRESERVADOS */
                .ql-font-arial, .ql-font-arial p, .ql-font-arial span, .ql-font-arial div, .ql-font-arial strong {
                    font-family: Arial, sans-serif !important;
                }
                .ql-font-times-new-roman, .ql-font-times-new-roman p, .ql-font-times-new-roman span, .ql-font-times-new-roman div, .ql-font-times-new-roman strong {
                    font-family: "Times New Roman", Times, serif !important;
                }
                .ql-align-center { text-align: center !important; }
                .ql-align-justify { text-align: justify !important; }
                .ql-align-right { text-align: right !important; }
                .ql-align-left { text-align: left !important; }
                .ql-editor { border: none !important; padding: 0 !important; }
                .ql-clipboard { display: none !important; }
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

        return PdfService::generatePdf($html, 'aafai-autor1-' . date('Y-m-d-His') . '.pdf');
    }

    // Função para converter imagem para base64 (MANTIDA)
}

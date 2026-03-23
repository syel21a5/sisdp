<?php
namespace App\Http\Controllers\Documentos\mp;

use Illuminate\Http\Request;
use App\Services\PdfService;
use App\Http\Controllers\Controller;

class GerarPdfRecolhimentoMpController extends Controller
{
    /**
     * ✅ MÉTODO PRINCIPAL PARA GERAR PDF MP
     */
    public function GerarPdfRecolhimentoMp(Request $request)
    {
        return $this->processarPdf($request, 'oficios-mp-');
    }

    /**
     * ✅ MÉTODO ÚNICO PARA PROCESSAMENTO DO PDF
     */
    private function processarPdf(Request $request, $prefixoArquivo)
    {
        // Validar os dados recebidos
        $request->validate([
            'conteudo' => 'required',
            'delegacia' => 'sometimes|string',
            'cidade' => 'sometimes|string',
            'numero_oficio_juiz' => 'sometimes|string',
            'numero_oficio_promotor' => 'sometimes|string',
            'numero_oficio_defensor' => 'sometimes|string',
        ]);

        $dados = $request->all();

        // ✅ DECODIFICAR OS DADOS DE TODAS AS PESSOAS
        $dadosCondutor = [];
        $dadosVitima1 = [];
        $dadosVitima2 = [];
        $dadosVitima3 = [];
        $dadosTestemunha1 = [];
        $dadosTestemunha2 = [];
        $dadosTestemunha3 = [];
        $dadosAutor1 = [];
        $dadosAutor2 = [];
        $dadosAutor3 = [];

        // Função auxiliar para decodificar dados
        function decodificarDados($dados, $chave) {
            if (isset($dados[$chave]) && is_string($dados[$chave])) {
                return json_decode($dados[$chave], true) ?? [];
            } elseif (isset($dados[$chave]) && is_array($dados[$chave])) {
                return $dados[$chave];
            }
            return [];
        }

        // Decodificar todos os dados
        $dadosCondutor = decodificarDados($dados, 'condutor');
        $dadosVitima1 = decodificarDados($dados, 'vitima1');
        $dadosVitima2 = decodificarDados($dados, 'vitima2');
        $dadosVitima3 = decodificarDados($dados, 'vitima3');
        $dadosTestemunha1 = decodificarDados($dados, 'testemunha1');
        $dadosTestemunha2 = decodificarDados($dados, 'testemunha2');
        $dadosTestemunha3 = decodificarDados($dados, 'testemunha3');
        $dadosAutor1 = decodificarDados($dados, 'autor1');
        $dadosAutor2 = decodificarDados($dados, 'autor2');
        $dadosAutor3 = decodificarDados($dados, 'autor3');

        // ✅ PROCESSAR DADOS MÚLTIPLOS
        $dadosProcessados = [];

        // Dados básicos do formulário principal
        $dadosProcessados['delegacia'] = $dados['delegacia'] ?? 'NÃO INFORMADO';
        $dadosProcessados['cidade'] = $dados['cidade'] ?? 'NÃO INFORMADO';
        $dadosProcessados['delegado'] = $dados['delegado'] ?? '';
        $dadosProcessados['escrivao'] = $dados['escrivao'] ?? '';
        $dadosProcessados['boe'] = $dados['boe'] ?? '';
        $dadosProcessados['ip'] = $dados['ip'] ?? 'NÃO INFORMADO';
        $dadosProcessados['data_ext'] = $dados['data_ext'] ?? 'NÃO INFORMADO';

        // ✅ Dados do CONDUTOR
        $dadosProcessados['condutor'] = [
            'nome' => $dadosCondutor['nome'] ?? 'NÃO INFORMADO',
            'alcunha' => $dadosCondutor['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosCondutor['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosCondutor['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosCondutor['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosCondutor['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosCondutor['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosCondutor['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosCondutor['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosCondutor['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosCondutor['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosCondutor['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosCondutor['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosCondutor['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados da VÍTIMA 1
        $dadosProcessados['vitima1'] = [
            'nome' => $dadosVitima1['nome'] ?? 'VÍTIMA 1',
            'alcunha' => $dadosVitima1['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosVitima1['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosVitima1['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosVitima1['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosVitima1['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosVitima1['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosVitima1['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosVitima1['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosVitima1['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosVitima1['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosVitima1['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosVitima1['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosVitima1['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados da VÍTIMA 2
        $dadosProcessados['vitima2'] = [
            'nome' => $dadosVitima2['nome'] ?? 'VÍTIMA 2',
            'alcunha' => $dadosVitima2['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosVitima2['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosVitima2['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosVitima2['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosVitima2['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosVitima2['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosVitima2['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosVitima2['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosVitima2['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosVitima2['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosVitima2['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosVitima2['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosVitima2['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados da VÍTIMA 3
        $dadosProcessados['vitima3'] = [
            'nome' => $dadosVitima3['nome'] ?? 'VÍTIMA 3',
            'alcunha' => $dadosVitima3['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosVitima3['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosVitima3['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosVitima3['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosVitima3['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosVitima3['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosVitima3['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosVitima3['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosVitima3['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosVitima3['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosVitima3['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosVitima3['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosVitima3['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados da TESTEMUNHA 1
        $dadosProcessados['testemunha1'] = [
            'nome' => $dadosTestemunha1['nome'] ?? 'TESTEMUNHA 1',
            'alcunha' => $dadosTestemunha1['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosTestemunha1['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosTestemunha1['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosTestemunha1['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosTestemunha1['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosTestemunha1['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosTestemunha1['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosTestemunha1['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosTestemunha1['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosTestemunha1['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosTestemunha1['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosTestemunha1['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosTestemunha1['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados da TESTEMUNHA 2
        $dadosProcessados['testemunha2'] = [
            'nome' => $dadosTestemunha2['nome'] ?? 'TESTEMUNHA 2',
            'alcunha' => $dadosTestemunha2['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosTestemunha2['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosTestemunha2['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosTestemunha2['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosTestemunha2['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosTestemunha2['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosTestemunha2['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosTestemunha2['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosTestemunha2['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosTestemunha2['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosTestemunha2['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosTestemunha2['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosTestemunha2['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados da TESTEMUNHA 3
        $dadosProcessados['testemunha3'] = [
            'nome' => $dadosTestemunha3['nome'] ?? 'TESTEMUNHA 3',
            'alcunha' => $dadosTestemunha3['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosTestemunha3['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosTestemunha3['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosTestemunha3['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosTestemunha3['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosTestemunha3['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosTestemunha3['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosTestemunha3['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosTestemunha3['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosTestemunha3['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosTestemunha3['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosTestemunha3['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosTestemunha3['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados do AUTOR 1 (CORRIGIDO)
        $dadosProcessados['autor1'] = [
            'nome' => $dadosAutor1['nome'] ?? 'AUTOR 1',
            'alcunha' => $dadosAutor1['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosAutor1['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosAutor1['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosAutor1['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosAutor1['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosAutor1['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosAutor1['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosAutor1['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosAutor1['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosAutor1['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosAutor1['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosAutor1['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosAutor1['endereco'] ?? 'NÃO INFORMADO',
            // ✅ ADICIONAR ESTES CAMPOS:
            'fianca' => $dadosAutor1['fianca'] ?? 'NÃO INFORMADO',
            'fianca_ext' => $dadosAutor1['fianca_ext'] ?? 'NÃO INFORMADO',
            'tipopenal' => $dadosAutor1['tipopenal'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados do AUTOR 2
        $dadosProcessados['autor2'] = [
            'nome' => $dadosAutor2['nome'] ?? 'AUTOR 2',
            'alcunha' => $dadosAutor2['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosAutor2['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosAutor2['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosAutor2['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosAutor2['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosAutor2['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosAutor2['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosAutor2['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosAutor2['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosAutor2['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosAutor2['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosAutor2['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosAutor2['endereco'] ?? 'NÃO INFORMADO'
        ];

        // ✅ Dados do AUTOR 3
        $dadosProcessados['autor3'] = [
            'nome' => $dadosAutor3['nome'] ?? 'AUTOR 3',
            'alcunha' => $dadosAutor3['alcunha'] ?? 'NÃO INFORMADO',
            'nascimento' => $dadosAutor3['nascimento'] ?? 'NÃO INFORMADO',
            'idade' => $dadosAutor3['idade'] ?? 'NÃO INFORMADO',
            'estcivil' => $dadosAutor3['estcivil'] ?? 'NÃO INFORMADO',
            'naturalidade' => $dadosAutor3['naturalidade'] ?? 'NÃO INFORMADO',
            'rg' => $dadosAutor3['rg'] ?? 'NÃO INFORMADO',
            'cpf' => $dadosAutor3['cpf'] ?? 'NÃO INFORMADO',
            'profissao' => $dadosAutor3['profissao'] ?? 'NÃO INFORMADO',
            'instrucao' => $dadosAutor3['instrucao'] ?? 'NÃO INFORMADO',
            'telefone' => $dadosAutor3['telefone'] ?? 'NÃO INFORMADO',
            'mae' => $dadosAutor3['mae'] ?? 'NÃO INFORMADO',
            'pai' => $dadosAutor3['pai'] ?? 'NÃO INFORMADO',
            'endereco' => $dadosAutor3['endereco'] ?? 'NÃO INFORMADO'
        ];

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
                /* ✅✅✅ MARGENS CORRIGIDAS PARA PÁGINAS IGUAIS */
                @page {
                    margin: 105px 30px 60px 30px;
                }

                /* ✅✅✅ RESET COMPLETO DAS MARGENS */
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12.5pt !important;
                    line-height: 1.6;
                    margin: 0 !important;
                    padding: 0 !important;
                    color: #000;
                }

                .header {
                    position: fixed;
                    top: -100px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    margin: 0 !important;
                    padding: 0 !important;
                    height: 90px;
                }

                .content {
                    margin-top: 10px !important;
                    padding: 0 20px !important;
                    position: relative;
                    z-index: 1;
                }

                /* ✅✅✅ QUEBRA DE PÁGINA CORRIGIDA */
                .page-break {
                    page-break-before: always !important;
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                    height: 0 !important;
                }

                /* ✅✅✅ GARANTIR QUE SEGUNDA PÁGINA COMECE NO TOPO */
                .page-break + p,
                .page-break + div {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }

                p {
                    margin: 0.4em 0 !important;
                    padding: 0 !important;
                    line-height: 1.6;
                    text-align: justify;
                }

                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    font-size: 10pt;
                    line-height: 1.3;
                    border-top: 1px solid #ccc;
                    padding-top: 4px;
                    margin: 0 !important;
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
                    width: 100%;
                    border-collapse: collapse;
                }

                .header td {
                    padding: 0 5px !important;
                    vertical-align: middle;
                }

                .header img {
                    width: 85px !important;
                    height: 85px !important;
                    margin-top: -3px !important;
                    object-fit: contain;
                }

                /* ✅✅✅ ESTILOS PARA GARANTIR ALINHAMENTO PERFEITO */
                .conteudo-pagina {
                    margin-top: 15px !important;
                    padding: 0 !important;
                }

                .primeira-pagina {
                    margin-top: 15px !important;
                }

                .segunda-pagina {
                    margin-top: 15px !important;
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

                .no-break { page-break-inside: avoid !important; break-inside: avoid !important; page-break-after: avoid !important; }
                .no-break p { margin: 0.2em 0 !important; line-height: 1.45 !important; }
            </style>
        </head>
        <body>
            <div class="header">
                <table>
                    <tr>
                        <td style="width: 85px; text-align: center;">
                            ' . ($image1Base64 ? '<img src="' . $image1Base64 . '" alt="Brasão PE">' : '') . '
                        </td>
                        <td style="text-align: center;">
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
                        <td style="width: 85px; text-align: center;">
                            ' . ($image2Base64 ? '<img src="' . $image2Base64 . '" alt="Brasão PCPE">' : '') . '
                        </td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                ' . $textoRodape . '
            </div>

            <div class="content">
                ' . $dados['conteudo'] . '
            </div>
        </body>
        </html>';

        return PdfService::generatePdf($html, $prefixoArquivo . date('Y-m-d-His') . '.pdf');
    }

    // Função para converter imagem para base64
}

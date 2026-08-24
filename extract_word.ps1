$word = New-Object -ComObject Word.Application
$word.Visible = $false

$files = @(
    "PericiaGrafotecnica_01.doc",
    "PericiaGrafotecnica_02.doc",
    "PericiaGrafotecnica_03.doc",
    "PericiaResidencia_LUMINOL.doc",
    "PeríciaInformaticaCelular.doc",
    "Advogado_AcompanharInquiricao.doc",
    "Advogado_AcompanharReinquiricao.doc",
    "ReproducaoSimulada_AdvAcompanhar.doc",
    "AutorizacaoExtrairDados_CPU.doc",
    "EncaminharVeiculo_CIRETRAN.doc"
)

$basePath = "I:\PHP\sisdepol.syel.com.br\Arquivos_localhost\DocsModelo_WORD\PericiasAvulsas\"

foreach ($file in $files) {
    Write-Output "--- $file ---"
    try {
        $doc = $word.Documents.Open($basePath + $file)
        Write-Output $doc.Content.Text
        $doc.Close()
    } catch {
        Write-Output "Error opening $($file): $_"
    }
}

$word.Quit()

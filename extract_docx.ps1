$word = New-Object -ComObject Word.Application
$word.Visible = $false

$file = "CERTIDAO - DRIVE.docx"
$basePath = "I:\PHP\sisdepol.syel.com.br\Arquivos_localhost\DocsModelo_WORD\DOCSDIVERSOS\"

Write-Output "--- $file ---"
try {
    $doc = $word.Documents.Open($basePath + $file)
    Write-Output $doc.Content.Text
    $doc.Close()
} catch {
    Write-Output "Error opening $($file): $_"
}

$word.Quit()

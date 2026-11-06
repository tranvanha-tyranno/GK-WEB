<#
  Export a .docx to .pdf through Word COM, in isolation from any editing work.
  Usage: powershell -File tools/export-report-pdf.ps1 -Docx <path> -Pdf <path>
#>
param(
  [Parameter(Mandatory = $true)][string]$Docx,
  [Parameter(Mandatory = $true)][string]$Pdf
)
$ErrorActionPreference = 'Stop'
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
try {
  $doc = $word.Documents.Open($Docx, $false, $true)
  Write-Output ('Opened. Pages: ' + $doc.ComputeStatistics(2))
  $doc.ExportAsFixedFormat($Pdf, 17)
  Write-Output ('Exported: ' + $Pdf)
  $doc.Close(0)
}
finally {
  try { $word.Quit() } catch { }
  try { [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null } catch { }
}
Write-Output 'PDF EXPORT DONE'

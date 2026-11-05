param([string]$Source = 'tmp/report-build/report.html', [string]$Target = 'submission/report.docx', [string]$Pdf = 'submission/report.pdf')
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$sourcePath = [System.IO.Path]::GetFullPath((Join-Path $root $Source))
$targetPath = [System.IO.Path]::GetFullPath((Join-Path $root $Target))
$pdfPath = [System.IO.Path]::GetFullPath((Join-Path $root $Pdf))
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($sourcePath, $false, $false)
    $document.PageSetup.PaperSize = 7
    $document.PageSetup.TopMargin = $word.CentimetersToPoints(1.9)
    $document.PageSetup.BottomMargin = $word.CentimetersToPoints(1.8)
    $document.PageSetup.LeftMargin = $word.CentimetersToPoints(2.2)
    $document.PageSetup.RightMargin = $word.CentimetersToPoints(2.0)
    foreach ($image in $document.InlineShapes) {
        try {
            $image.LinkFormat.SavePictureWithDocument = $true
            $image.LinkFormat.BreakLink()
        } catch {}
    }
    foreach ($table in $document.Tables) {
        $table.Rows.Item(1).HeadingFormat = -1
        $table.Rows.Item(1).Range.ParagraphFormat.KeepWithNext = -1
        $table.Rows.AllowBreakAcrossPages = 0
    }
    $contents = $document.Content.Duplicate
    if ($contents.Find.Execute('Mục lục')) {
        $contents.Paragraphs.Item(1).Style = 'Normal'
        $contents.Paragraphs.Item(1).Range.Font.Size = 18
        $contents.Paragraphs.Item(1).Range.Font.Name = 'Calibri'
        $contents.Paragraphs.Item(1).Range.Font.Bold = -1
        $contents.Paragraphs.Item(1).OutlineLevel = 10
        $contents.Paragraphs.Item(1).Range.ParagraphFormat.PageBreakBefore = -1
        $figures = $document.Range($contents.End, $document.Content.End)
        if ($figures.Find.Execute('Danh mục hình')) {
            $tocRange = $document.Range($contents.Paragraphs.Item(1).Range.End, $figures.Paragraphs.Item(1).Range.Start)
            $tocRange.Delete() | Out-Null
            $tocRange.Collapse(1)
            $document.TablesOfContents.Add($tocRange, $true, 1, 1, $false, '', $true, $true) | Out-Null
        }
    }
    $document.Sections.Item(1).Footers.Item(1).PageNumbers.Add(2) | Out-Null
    $document.Fields.Update() | Out-Null
    $document.SaveAs2($targetPath, 16)
    $document.ExportAsFixedFormat($pdfPath, 17)
    Write-Host ('Exported Word and PDF. Pages: ' + $document.ComputeStatistics(2))
} finally {
    if ($document) { $document.Close(0) }
    if ($word) { $word.Quit() }
    if ($document) { [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($document) }
    if ($word) { [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) }
}

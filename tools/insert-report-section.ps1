<#
  Insert section 4.12 and Appendix B (verification screenshots) into the Word
  report, then overwrite report.docx and report.pdf.

  All Vietnamese strings live in tmp/report-build/insert-spec.json which is read
  as UTF-8 explicitly, so this script stays pure ASCII and is immune to
  PowerShell 5.1 codepage problems.

  Usage:  powershell -NoProfile -ExecutionPolicy Bypass -File tools/insert-report-section.ps1
  Params: -Spec, -Target, -Pdf
#>
param(
  [string]$Spec = 'tmp/report-build/insert-spec.json',
  [string]$Target = 'submission/report.docx',
  [string]$Pdf = 'submission/report.pdf'
)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$specPath = Join-Path $root $Spec
$targetPath = Join-Path $root $Target
$pdfPath = Join-Path $root $Pdf

$data = [System.IO.File]::ReadAllText($specPath, [System.Text.Encoding]::UTF8) | ConvertFrom-Json

# Keep a rollback copy before overwriting.
$backupDir = Join-Path $root 'tmp/report-build'
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
$backup = Join-Path $backupDir ('report-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.docx')
Copy-Item $targetPath $backup -Force
Write-Output ('Backup: ' + $backup)

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
try {
  $doc = $word.Documents.Open($targetPath, $false, $false)

  # Styles.Item(name) khong dang tin cay theo ngon ngu giao dien Word;
  # duyet theo NameLocal la cach chac chan nhat. Chuan hoa NFC ca hai ve vi
  # ten trong docx va chuoi trong JSON co the khac dang to hop dau.
  function Same-Text($a, $b) {
    if ($null -eq $a -or $null -eq $b) { return $false }
    $fa = [System.Text.NormalizationForm]::FormC
    return ($a.Normalize($fa) -ceq $b.Normalize($fa))
  }
  function Get-StyleByLocal($local) {
    foreach ($s in $doc.Styles) { if (Same-Text $s.NameLocal $local) { return $s } }
    throw ('Style not found by NameLocal (len ' + [string]$local.Length + ')')
  }
  $bodyStyle = Get-StyleByLocal $data.styles.body
  $bodyTextStyle = Get-StyleByLocal $data.styles.bodyText
  $capStyle = $doc.Styles.Item(-35)
  $h1Style = $doc.Styles.Item(-2)
  $h2Style = $doc.Styles.Item(-3)
  $sel = $word.Selection

  function Write-Para($style, $text) {
    $sel.Style = $style
    $sel.ParagraphFormat.Alignment = 3
    $sel.TypeText($text)
    $sel.TypeParagraph() | Out-Null
  }
  function Write-Caption($seqLabel, $prefix, $desc) {
    $sel.Style = $capStyle
    $sel.ParagraphFormat.Alignment = 1
    $sel.TypeText($prefix)
    if ($seqLabel) {
      $r1 = $sel.Range; $doc.Fields.Add($r1, -1, ' STYLEREF 1 \s ', $false) | Out-Null
      $sel.TypeText('.')
      $r2 = $sel.Range; $doc.Fields.Add($r2, -1, (' SEQ ' + $seqLabel + ' \* ARABIC \s 1 '), $false) | Out-Null
    }
    $sel.TypeText(': ' + $desc)
    $sel.TypeParagraph() | Out-Null
  }
  function Write-Figure($relPath, $seqLabel, $prefix, $desc) {
    $imgPath = Join-Path $root $relPath
    if (-not (Test-Path $imgPath)) { throw ('Missing image: ' + $imgPath) }
    $sel.Style = $bodyTextStyle
    $sel.ParagraphFormat.Alignment = 1
    $shape = $sel.InlineShapes.AddPicture($imgPath, $false, $true)
    $shape.LockAspectRatio = -1
    $maxW = $word.CentimetersToPoints(15)
    $maxH = $word.CentimetersToPoints(15.5)
    $scale = [Math]::Min($maxW / $shape.Width, $maxH / $shape.Height)
    if ($scale -lt 1) { $shape.Width = [Math]::Round($shape.Width * $scale, 1) }
    $sel.ParagraphFormat.Alignment = 1
    $sel.TypeParagraph() | Out-Null
    Write-Caption $seqLabel $prefix $desc
  }
  function Write-Table($table) {
    Write-Caption $data.seqTable $data.tablePrefix $table.caption
    $rowCount = $table.rows.Count + 1
    $colCount = $table.header.Count
    $tbl = $doc.Tables.Add($sel.Range, $rowCount, $colCount)
    try { $tbl.Style = $doc.Styles.Item('Table Grid') } catch { }
    $tbl.Borders.Enable = 0
    $tbl.PreferredWidth = 100
    $tbl.PreferredWidthType = 2
    $widths = @(45, 27, 28)
    for ($c = 1; $c -le $colCount; $c++) {
      $tbl.Columns.Item($c).PreferredWidth = $widths[$c - 1]
      $tbl.Columns.Item($c).PreferredWidthType = 2
    }
    for ($c = 1; $c -le $colCount; $c++) {
      $cell = $tbl.Cell(1, $c)
      $cell.Range.Text = $table.header[$c - 1]
      $cell.Range.Font.Bold = -1
      $cell.Range.ParagraphFormat.Alignment = 1
      $cell.VerticalAlignment = 1
      $cell.Shading.BackgroundPatternColor = 16183533
    }
    $tbl.Rows.Item(1).HeadingFormat = -1
    for ($r = 0; $r -lt $table.rows.Count; $r++) {
      for ($c = 1; $c -le $colCount; $c++) {
        $tbl.Cell($r + 2, $c).Range.Text = $table.rows[$r][$c - 1]
      }
    }
    $after = $tbl.Range
    $after.Collapse(0)
    $after.Select()
  }

  # Locate the insertion anchor: the Heading 1 that starts chapter 5.
  # Only the integer start position is kept; holding COM paragraph objects
  # across the enumeration proves unreliable.
  $target = $null
  $anchorStart = -1
  $lvl1 = @()
  foreach ($p in $doc.Paragraphs) {
    if ($p.OutlineLevel -eq 1 -or $p.OutlineLevel -eq 2) {
      $cand = $p.Range.Text.Trim()
      $lvl1 += $cand
      if (Same-Text $cand $data.anchorHeading) { $anchorStart = $p.Range.Start; $target = $cand }
    }
  }
  Write-Output ('Anchor matched: ' + [string]($anchorStart -ge 0) + ' at ' + $anchorStart + ' / level-1 count: ' + $lvl1.Count)
  if ($anchorStart -lt 0) {
    Write-Output ('Level-1 headings found: ' + ($lvl1 -join ' | '))
    throw ('Anchor heading not found: ' + $data.anchorHeading)
  }
  $doc.Range($anchorStart, $anchorStart).Select()
  $sel.Collapse(1)
  # TypeParagraph at the start of a paragraph leaves the caret in the paragraph
  # that holds the FOLLOWING text, which would drag the chapter-5 title down
  # through every style change. InsertParagraphBefore creates a genuinely empty
  # working paragraph above the heading instead.
  $sel.Range.InsertParagraphBefore() | Out-Null
  $doc.Range($anchorStart, $anchorStart).Select()
  $sel.Collapse(1)

  # Section 4.12
  $sel.Style = $h2Style
  $sel.TypeText($data.section.heading)
  $sel.TypeParagraph() | Out-Null
  foreach ($para in $data.section.paragraphs) { Write-Para $bodyStyle $para }
  if ($data.section.table) { Write-Table $data.section.table }
  foreach ($fig in $data.section.figures) { Write-Figure $fig.path $data.seqFigure $data.figurePrefix $fig.desc }
  # NOTE: do not delete the trailing empty paragraph here; Range.Delete on it
  # was observed to strip the text of the following chapter-5 heading. The
  # chapter heading carries pageBreakBefore, so the blank line is harmless.
  Write-Output ('Section 4.12 inserted; shapes so far: ' + $doc.InlineShapes.Count)

  # Appendix B at the end of the document (optional in the spec)
  if ($data.appendix) {
    $sel.EndKey(6) | Out-Null
    $sel.TypeParagraph() | Out-Null
    $sel.Style = $h1Style
    $sel.TypeText($data.appendix.heading)
    $headPara = $sel.Range.Paragraphs.Item(1).Range
    $headPara.ListFormat.RemoveNumbers()
    $headPara.ParagraphFormat.PageBreakBefore = -1
    $headPara.ParagraphFormat.Alignment = 1
    $sel.TypeParagraph() | Out-Null
    foreach ($para in $data.appendix.paragraphs) { Write-Para $bodyStyle $para }
    $n = 0
    foreach ($fig in $data.appendix.figures) {
      Write-Figure $fig.path $null $fig.label $fig.desc
      $n++
      if ($n % 8 -eq 0) { Write-Output ('  appendix figures: ' + $n + '/' + $data.appendix.figures.Count) }
    }
    Write-Output ('Appendix inserted; total shapes: ' + $doc.InlineShapes.Count)
  }

  # Refresh fields, table of contents and list of figures
  Write-Output 'Updating fields...'
  $doc.Fields.Update()
  foreach ($toc in $doc.TablesOfContents) { $toc.Update() }
  Write-Output 'Fields updated.'

  $doc.Save()
  Write-Output ('Saved: ' + $targetPath)
  $doc.Close(0)
}
catch {
  Write-Output ('REAL ERROR: ' + $_.Exception.GetType().FullName + ': ' + $_.Exception.Message)
  Write-Output ('AT: ' + $_.InvocationInfo.PositionMessage)
  throw
}
finally {
  try { $word.Quit() } catch { }
  try { [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null } catch { }
}
Write-Output 'INSERT DONE'

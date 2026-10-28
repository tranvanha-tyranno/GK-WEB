$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$data = Get-Content -LiteralPath (Join-Path $root 'docs/slides.json') -Raw -Encoding UTF8 | ConvertFrom-Json
$out = Join-Path $root 'submission'
$renders = Join-Path $root 'tmp/slides-render'
[System.IO.Directory]::CreateDirectory($out) | Out-Null
[System.IO.Directory]::CreateDirectory($renders) | Out-Null
function RGB([int]$r,[int]$g,[int]$b) { return $r + 256*$g + 65536*$b }
$ink = RGB 34 30 47
$muted = RGB 116 106 132
$purple = RGB 105 82 176
$paper = RGB 251 250 253
$line = RGB 222 216 232
function Text($slide,[string]$value,[double]$x,[double]$y,[double]$w,[double]$h,[double]$size,[int]$color,[bool]$bold=$false) {
    $shape = $slide.Shapes.AddTextbox(1,$x,$y,$w,$h)
    $shape.TextFrame.MarginLeft=0; $shape.TextFrame.MarginRight=0
    $shape.TextFrame.MarginTop=0; $shape.TextFrame.MarginBottom=0
    $shape.TextFrame.WordWrap=-1
    $shape.TextFrame.TextRange.Text=$value
    $shape.TextFrame.TextRange.Font.Name='Aptos'
    $shape.TextFrame.TextRange.Font.Size=$size
    $shape.TextFrame.TextRange.Font.Color.RGB=$color
    $shape.TextFrame.TextRange.Font.Bold= $(if($bold){-1}else{0})
    $shape.TextFrame.TextRange.ParagraphFormat.SpaceAfter=8
    return $shape
}
function Picture($slide,[string]$relative,[double]$x,[double]$y,[double]$w,[double]$h) {
    $p=$slide.Shapes.AddPicture((Join-Path $root $relative),0,-1,0,0,-1,-1)
    $scale=[Math]::Min($w/$p.Width,$h/$p.Height)
    $p.LockAspectRatio=0
    $p.Width=$p.Width*$scale; $p.Height=$p.Height*$scale
    $p.Left=$x+($w-$p.Width)/2; $p.Top=$y+($h-$p.Height)/2
    return $p
}
$powerpoint=$null;$deck=$null
try {
    $powerpoint=New-Object -ComObject PowerPoint.Application
    $deck=$powerpoint.Presentations.Add(0)
    $deck.PageSetup.SlideWidth=960; $deck.PageSetup.SlideHeight=540
    $index=0
    foreach($item in $data.slides){
        $index++
        $slide=$deck.Slides.Add($index,12)
        $slide.FollowMasterBackground=0
        $slide.Background.Fill.ForeColor.RGB=$paper
        if($item.kind -eq 'cover'){
            Text $slide $item.eyebrow 55 55 850 20 11 $purple $true | Out-Null
            Text $slide $item.title 55 124 810 145 40 $ink $true | Out-Null
            Text $slide $item.body 57 292 790 110 22 $muted | Out-Null
            Text $slide $item.footer 57 456 790 25 14 $muted | Out-Null
        } else {
            Text $slide $item.eyebrow 48 31 850 18 10 $purple $true | Out-Null
            Text $slide $item.title 48 69 866 65 30 $ink $true | Out-Null
            switch($item.kind){
                'split' {
                    Text $slide $item.body 48 158 405 325 21 $ink | Out-Null
                    Picture $slide $item.image 488 154 420 310 | Out-Null
                }
                'image' {
                    Picture $slide $item.image 48 138 866 292 | Out-Null
                    Text $slide $item.body 48 449 866 48 17 $muted | Out-Null
                }
                'chart' {
                    Picture $slide $item.image 48 132 866 296 | Out-Null
                    Text $slide $item.body 48 445 866 50 17 $muted | Out-Null
                }
                'table' {
                    $rowCount=$item.rows.Count+1;$cols=$item.columns.Count
                    $tableShape=$slide.Shapes.AddTable($rowCount,$cols,48,151,866,270)
                    $table=$tableShape.Table
                    for($c=1;$c -le $cols;$c++){
                        $table.Cell(1,$c).Shape.TextFrame.TextRange.Text=[string]$item.columns[$c-1]
                    }
                    for($r=2;$r -le $rowCount;$r++){
                        for($c=1;$c -le $cols;$c++){
                            $table.Cell($r,$c).Shape.TextFrame.TextRange.Text=[string]$item.rows[$r-2][$c-1]
                        }
                    }
                    for($r=1;$r -le $rowCount;$r++){
                        for($c=1;$c -le $cols;$c++){
                            $cell=$table.Cell($r,$c);$tf=$cell.Shape.TextFrame
                            $tf.MarginLeft=13;$tf.MarginRight=10;$tf.MarginTop=9;$tf.MarginBottom=8
                            $tf.VerticalAnchor=3
                            $tf.TextRange.Font.Name='Aptos';$tf.TextRange.Font.Size=17
                            $tf.TextRange.Font.Color.RGB=$ink
                            $cell.Shape.Fill.ForeColor.RGB=$(if($r -eq 1){RGB 234 228 245}else{RGB 255 255 255})
                            if($r -eq 1){$tf.TextRange.Font.Bold=-1}
                            for($b=1;$b -le 4;$b++){$cell.Borders.Item($b).ForeColor.RGB=$line;$cell.Borders.Item($b).Weight=.5}
                        }
                    }
                    Text $slide $item.body 48 446 866 48 17 $muted | Out-Null
                }
                default { Text $slide $item.body 48 158 850 320 24 $ink | Out-Null }
            }
        }
        Text $slide ('RELAY / 503073') 48 515 600 15 9 $muted | Out-Null
        Text $slide ($index.ToString('00')+' / 12') 845 515 85 15 9 $muted | Out-Null
        try {$slide.NotesPage.Shapes.Placeholders.Item(2).TextFrame.TextRange.Text=$item.notes} catch {}
    }
    $deck.SaveAs((Join-Path $out 'slides.pptx'),24)
    $deck.SaveAs((Join-Path $out 'slides.pdf'),32)
    for($i=1;$i -le $deck.Slides.Count;$i++){
        $deck.Slides.Item($i).Export((Join-Path $renders ('slide-{0:D2}.png' -f $i)),'PNG',1440,810)
    }
    Write-Host ('Exported '+$deck.Slides.Count+' editable slides, PDF and QA PNGs.')
} finally {
    if($deck){$deck.Close()}
    if($powerpoint){$powerpoint.Quit()}
    if($deck){[void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($deck)}
    if($powerpoint){[void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($powerpoint)}
}

<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once 'metadata.php';

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/fonts',
    ]),
    'fontdata' => $fontData + [
        'me_quran' => [
            'R' => 'me_quran.ttf',
            'I' => 'me_quran.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 100,
        ]
    ],
    'default_font' => 'Arial',
    'tempDir' => __DIR__ . '/tmp',
    'mode' => 'utf-8',
    'format' => [105, 148],
]);

$mpdf->SetDisplayMode('fullwidth');

//$mpdf->setFooter('{PAGENO}');

$arabic = file('QuranText/quran-uthmani.txt');
$translation = file('QuranText/ru.kuliev.txt');

if (count($arabic)!=6236 || count($translation)!=6236) {
    die("must be 6236");
}

$mpdf->WriteHTML('
<div style="width:100%;text-align:center;">Священный</div><br /><br />
<div style="width:100%;text-align:center;font-size:35pt;">Коран</div>
<br /><br /><div style="width:100%;text-align:center">+ Смысловой перевод Эльмира Кулиева.</div>
<br /><br /><br /><br /><br /><div style="width:100%;text-align:center;font-size:8pt;">Формат оптимизирован для чтения на е-инк ридеров (Kindle, Kobo и тд).</div>
<pagebreak />');

$lastSuraNum = -1;
$sura = $suras[1];

$mpdf->WriteHTML('<tocpagebreak links="on" />');

for ($i=0; $i<200; ++$i) {
    $aya = $arabic[$i];
    $tr = $translation[$i];

    $parts = explode("|", $aya, 3);

    $suraNum = $parts[0];
    $ayaNum = $parts[1];
    $ayaText = $parts[2];

    $trText = explode("|", $tr, 3)[2];

    if ($lastSuraNum != $suraNum) {
        $lastSuraNum = $suraNum;
        $sura = $suras[$lastSuraNum];
        if ($lastSuraNum > 1) {
            $mpdf->WriteHTML('<pagebreak />');
        }
        $mpdf->WriteHTML('<tocentry content="'.htmlspecialchars($sura[5], ENT_QUOTES).'" />
        <bookmark content="'.htmlspecialchars($sura[5], ENT_QUOTES).'" />');
        $mpdf->WriteHTML('<div style="width:100%;text-align:center;font-size:18pt;color:red;" dir="rtl">'.$sura[4].'</div>');
        $mpdf->WriteHTML('<div style="width:100%;text-align:center;font-size:12pt;color:red;">'.$sura[5].'</div><br />');
    }

    if ($ayaNum % 20 == 0) {
        $mpdf->WriteHTML('<bookmark content="'.($ayaNum).'" level="1" />');
    }

    $mpdf->WriteHTML('<div style="font-size:8pt;">'.($ayaNum).'</div>');
    $mpdf->WriteHTML('<div lang="ar" style="font-family:me_quran;font-size:17pt;" dir="rtl">'.$ayaText.'</div>');
    $mpdf->WriteHTML('<br /><div lang="en" style="font-size:10pt;" dir="ltr">'.$trText.'</div><br />');
    $mpdf->WriteHTML('<center><hr style="width:80%;color:green;background-color:green;" /></center>');
}

$mpdf->Output();

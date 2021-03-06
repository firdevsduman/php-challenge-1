<?php


# değişkenleri, sabitleri tanımla
$inboxPath = __DIR__ . '/data/tmp';
$outboxPath = __DIR__ . '/data/out';
$archivePath = __DIR__ . '/data/arc';

$filePathList = glob("$inboxPath/*.txt");
$fileCount = count($filePathList);

if (0 === $fileCount) {
    echo "yeni dosya yok!" . PHP_EOL;
    exit(0);
}

echo "$fileCount adet dosya bekliyor!" . PHP_EOL;

# bekleyen dosya var mı, yoksa sonlandır

# dosyaları tek tek oku

#her bir dosyaya :
foreach ($filePathList as $filePath) {
    echo "mevcut dosya : " . pathinfo($filePath, PATHINFO_BASENAME) . PHP_EOL;

    $xmlArray = [
        'order' => [
            'header' => [],
            'lines' => [],
        ],
    ];

    # dosya içeriğini satır satır array e dök
    $fileLines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    # header ve detail title & value eşleşmelerini yap
    foreach ($fileLines as $i => $line) {
        $fileLines[$i] = explode(';', $line);

        if ($i === 1) {
            $xmlArray['order']['header'] = array_combine($fileLines[0], $fileLines[1]);
        }

        if ($i > 2) {
            $line = array_combine($fileLines[2], $fileLines[$i]);

            if (
                !empty($line['itemDescription'])
                && !empty($line['price'])
                && substr_count($line['price'], ',') < 2
                && 0 !== strpos($line['price'], ',')
            ) {
                $xmlArray['order']['lines'][] = $line;
            }
        }
    }

    # xml i oluştur ve out dizinine bırak
    $xml = new SimpleXMLElement('<order/>');

    $header = $xml->addChild('header');
    foreach ($xmlArray['order']['header'] as $key => $value) {
        $header->addChild($key, $value);
    }

    $lines = $xml->addChild('lines');
    array_map(
        function ($item) use ($lines) {
            $line = $lines->addChild('line');
            foreach ($item as $key => $value) {
                $line->addChild($key, $value);
            }
        },
        $xmlArray['order']['lines']
    );

    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;

    if (false === file_put_contents("$outboxPath/output.xml", $dom->saveXML())) {
        echo "output dosya yazılamadı!" . PHP_EOL;
        exit(1);
    }

    echo "output yaratıldı!" . PHP_EOL;
}

# fin!
<?php

$row = 0;
/* input dosyasını okuyoruz.*/
if (($handle = fopen("input.txt", "r")) == TRUE) {

    $doc = new DOMDocument();
    $doc->encoding = 'utf-8';
    $doc->formatOutput = true;
    /* order ve header input dosyasında olmadığı için elle oluşturdum. */
    $order = $doc->createElement('order');
    $order = $doc->appendChild($order);

    $header = $doc->createElement('header');
    $order->appendChild($header);

    /* lines da elle oluşturuldu. */
    $lines = $doc->createElement('lines');
    $order->appendChild($lines);
    /* csv dosyasının içini toptan alıyoruz. */
    $csvFileContent = file_get_contents("input.txt");
    /* her bir satır için bi döngü yazdım. */
    $dataRowCounter=0;
    while (($data = fgetcsv($handle, 1000, PHP_EOL)) == TRUE) {
        $num = count($data);

        $dataRow=$data[0];
        $cells = explode(";", $dataRow);
        if ($dataRowCounter>=3) {
            $line = $doc->createElement('line');
            $lines->appendChild($line);
        }

        /* satır içindeki her bir hücre için işlem yapacak bir döngü yazdım. */
        $cellCounter=0;
        foreach ($cells as $cell)
        {
            if ($dataRowCounter==0)
            {

                $valueOfCell = explode( ";",explode(PHP_EOL, $csvFileContent)[$dataRowCounter+1])[$cellCounter];
                $headerElement = $doc->createElement(  str_replace( "\r","",$cell));
                $headerElement->nodeValue=str_replace("\r","",$valueOfCell);
                $header->appendChild($headerElement);
            }
            /* 3.satırdan itibaren line verileri geliyor bunun için ayrı bir if koşulu yazdım. */
            if ($dataRowCounter>=3)
            {
                $nodeNameOfCell = explode( ";",explode(PHP_EOL, $csvFileContent)[2])[$cellCounter];
                $valueOfCell = $cell;
                $lineElement = $doc->createElement( str_replace( "\r","",$nodeNameOfCell));
                $lineElement->nodeValue=$valueOfCell;
                $line->appendChild($lineElement);
            }

            $cellCounter++;
        }
        $dataRowCounter=$dataRowCounter+1;
    }
    /* output dosyasına kaydedip, dosyayı serbest bıraktım. */
    $doc->save('output.xml');
    fclose($handle);
}

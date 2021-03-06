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
        $valueOfItemCode = "";
        $valueOfPrice = "";
        $dataRow=$data[0];
        $valueOfCell="";
        $cells = explode(";", $dataRow);
        if ($dataRowCounter>=3) {

            /*Her bir satırla daha işlem yapmaya başlamadan önce, o satırdaki ItemCode taginin içine gelecek veriyi daha baştan tespit ediyorum.*/
            $valueOfItemCode = trim(explode( ";",explode(PHP_EOL, $csvFileContent)[$dataRowCounter])[1], "");
            $valueOfPrice = trim(explode( ";",explode(PHP_EOL, $csvFileContent)[$dataRowCounter])[3], "");

            if($valueOfItemCode!=="") {
                $line = $doc->createElement('line');
                $lines->appendChild($line);
            }
        }

        /* satır içindeki her bir hücre için işlem yapacak bir döngü yazdım. */
        $cellCounter=0;
        foreach ($cells as $cell)
        {

                if ($dataRowCounter == 0) {
                    $nameOfCell = explode(";", explode(PHP_EOL, $csvFileContent)[$dataRowCounter])[$cellCounter];
                    $valueOfCell = explode(";", explode(PHP_EOL, $csvFileContent)[$dataRowCounter + 1])[$cellCounter];

                    if ($nameOfCell=="dateCreated" OR $nameOfCell=="dateSend")
                    {
                        $valueOfCell = DateTime::createFromFormat('YmdHis', $valueOfCell)->format('Y-m-d H:i:s');
                    }

                    $headerElement = $doc->createElement(str_replace("\r", "", $cell));
                    $headerElement->nodeValue = str_replace("\r", "", $valueOfCell);
                    $header->appendChild($headerElement);


                }
            if($valueOfItemCode!=="") {
                /* 3.satırdan itibaren line verileri geliyor bunun için ayrı bir if koşulu yazdım. */
                if ($dataRowCounter >= 3) {
                    $nodeNameOfCell = explode(";", explode(PHP_EOL, $csvFileContent)[2])[$cellCounter];
                    $valueOfCell = $cell;
                    $lineElement = $doc->createElement(str_replace("\r", "", $nodeNameOfCell));

                    $nodeNameOfCell = trim( $nodeNameOfCell,'\n').' ';

                  /* echo $valueOfCell;
                   if ($nodeNameOfCell=="deliveryDateLatest")
                    {
                        echo "deneme";
                        echo DateTime::createFromFormat('ddMMYYYY', "06Jul2018")->format('YYmd');
                    }*/

                    if($nodeNameOfCell=="itemDescription"){
                        $valueOfCell=str_replace("ı","i",$valueOfCell);
                        $valueOfCell=str_replace('İ','I',$valueOfCell);
                        $valueOfCell=str_replace('ö','o',$valueOfCell);
                        $valueOfCell=str_replace('Ö','O',$valueOfCell);
                        $valueOfCell=str_replace('ü','u',$valueOfCell);
                        $valueOfCell=str_replace('Ü','U',$valueOfCell);
                        $valueOfCell=str_replace('ç','c',$valueOfCell);
                        $valueOfCell=str_replace('Ç','C',$valueOfCell);
                        $valueOfCell=str_replace('ş','s',$valueOfCell);
                        $valueOfCell=str_replace('Ş','S',$valueOfCell);
                        $valueOfCell=str_replace('ğ','g',$valueOfCell);
                        $valueOfCell=str_replace('Ğ','G',$valueOfCell);
                    }




                    $lineElement->nodeValue = $valueOfCell;
                    $line->appendChild($lineElement);



                }
            }

            $cellCounter++;
        }
        $dataRowCounter=$dataRowCounter+1;
    }
    /* output dosyasına kaydedip, dosyayı serbest bıraktım. */
    $doc->save('output.xml');
    fclose($handle);
}

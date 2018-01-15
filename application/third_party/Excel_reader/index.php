<?php
    // If you need to parse XLS files, include php-excel-reader
    require('php-excel-reader/excel_reader2.php');

    require('SpreadsheetReader.php');

    $Reader = new SpreadsheetReader('2.xlsx');
    foreach ($Reader as $Row)
    {
        var_dump($Row);
    }
?>
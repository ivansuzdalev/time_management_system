<?php
namespace App\Services;

class Csv
{
    public static function outputCSV($data, $useKeysForHeaderRow = true) {
        if(!empty($data)){
            if ($useKeysForHeaderRow) {
                array_unshift($data, array_keys(reset($data)));
            }
        
            $outputBuffer = fopen("php://output", 'w');
            foreach($data as $v) {
                fputcsv($outputBuffer, $v,",",'"',"\\");
            }
            fclose($outputBuffer);
        }
    }

    public static function arrToCSV($data, $useKeysForHeaderRow = true) {
        if(!empty($data)){
            if ($useKeysForHeaderRow) {
                array_unshift($data, array_keys(reset($data)));
            }
            
            $f = fopen('php://memory', 'r+');
            foreach ($data as $item) {
                fputcsv($f, $item,",",'"',"\\");
            }
            rewind($f);
            return stream_get_contents($f);
            
        } else return false;
    }

    public static function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        // Open a memory "file" for read/write...
        $fp = fopen('php://temp', 'r+');
        // ... write the $input array to the "file" using fputcsv()...
        fputcsv($fp, $input, $delimiter, $enclosure);
        // ... rewind the "file" so we can read what we just wrote...
        rewind($fp);
        // ... read the entire line into a variable...
        $data = fread($fp, 1048576);
        // ... close the "file"...
        fclose($fp);
        // ... and return the $data to the caller, with the trailing newline from fgets() removed.
        return rtrim($data, "\n");
    }

}

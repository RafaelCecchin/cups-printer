<?php

namespace RafaelCecchin\CupsPrinter;

class CupsPrinter
{
    public $printerServer;
    public $printerName;

    function __construct(String $printerServer, String $printerName) 
    {
        $this->printerServer = $printerServer;
        $this->printerName = $printerName;
    }

    public function printData(String $data) 
    {
        $tempFile = $this->createTempFile($data);
        $requestId = $this->printFile($tempFile);
        $this->removeTempFile($tempFile);

        return $requestId;
    }

    public function printFile($fileLocation)
    {
        $cmdPrintCommand = $this->cmdPrintCommand($fileLocation);
        $cmdOutput = $this->execPrint($cmdPrintCommand);
        $requestId = $this->extractRequestId($cmdOutput);

        return $requestId;
    }

    public function cmdPrintCommand(String $fileLocation)
    {
        $cmd = "lp -h $this->printerServer -d $this->printerName $fileLocation";

        return $cmd;
    }

    public function obPrint($callback)
    {
        ob_start();
        $callback();
        $out = ob_get_clean();
        $requestId = $this->printData($out);
        return $requestId;
    }

    private function execPrint(String $cmdPrintCommand) 
    {
        exec($cmdPrintCommand, $cmdOutput, $returnVar);

        return $cmdOutput;
    }

    private function createTempFile(String $data, String $prefix =  'print_')
    {
        $tempDir = sys_get_temp_dir();
        $tempFile = tempnam($tempDir, $prefix);
        $file = fopen($tempFile, 'w');
        fwrite($file, $data);
        fclose($file);

        return $tempFile;
    }

    private function removeTempFile(String $tempFile)
    {
        unlink($tempFile);
    }

    private function extractRequestId($cmdOutputArray) 
    {
        $pattern = '/request id is ([A-Za-z0-9\-]+)/';

        if (empty($cmdOutputArray)) {
            return null;
        }

        $message = $cmdOutputArray[0];
        $match = preg_match($pattern, $message, $matches);

        if (!$match) {
            return null;
        }

        return $matches[1];
    }
}
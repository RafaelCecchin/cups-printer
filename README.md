# cups-printer 🖨️

After spending a lot of time searching for a simple solution to print files directly from PHP, I decided to create my own Composer library. 🚀

```bash
composer require rafaelcecchin/cups-printer
```

Initially, I thought it would be easier to find a solution that fits different environments, but I was mistaken. Many of the existing solutions for printing with CUPS + PHP are outdated, involve a lot of code, and overlook the fact that many developers use Windows for development and Linux for production environments. 🖥️➡️🐧

This project utilizes the CUPS command:
```bash
lp -h [printerServer] -d [printerName] [fileLocation]
```

"Okay, but what about the cases you mentioned, where I work with a Windows development environment and a Linux production environment?" 🌐

It will work too, and the solution is simpler than you might think. I'll explain more about this below. 👇

## How to Use

Here’s a code snippet demonstrating the functions of the library. 📜

```php
use RafaelCecchin\CupsPrinter\CupsPrinter;

$printerServer = '192.168.0.100';
$printerName = 'L8360CDW-Rafael';

$printer = new CupsPrinter($printerServer, $printerName);

/* Print an existing file */
$fileLocation = '/var/docs/test.pdf';
$printer->printFile($fileLocation);

/* Save the content to a temporary file before printing */
$output = $pdf->output();
$printer->printData($output);

/* Save the buffer content to a temporary file before printing */
$print = $printer->obPrint(function () use ($pdf) {
    $pdf->stream("dompdf_out.pdf", array("Attachment" => false));
});
```

The only requirement is that the host must have cups-client installed. 🛠️

On Linux, you can run:
```bash
sudo apt-get update
sudo apt-get install cups-client
```

On Windows, one solution is to download Linux from the Windows Store. Yes, this is now possible through WSL technology. Just access the Windows Store, search for the Linux distribution you want, and install `cups-client` inside it.

In your project, you should create a class that extends `CupsPrinter` and, using polymorphism principles, override the `cmdPrintCommand` function. Here’s an example of how I did it. 🧩

```php
use RafaelCecchin\CupsPrinter\CupsPrinter;

class Printer extends CupsPrinter
{
    function __construct(String $printerServer, String $printerName)
    {
        parent::__construct($printerServer, $printerName);
    }

    public function cmdPrintCommand(String $fileLocation)
    {
        $env = getenv('APP_ENV');
        $cmd = '';

        if ($env == 'DEV') {
            $cmd .= "ubuntu run ";
            $fileLocation = $this->convertWindowsPathToUnix($fileLocation);
        }

        $cmd .= "lp -h $this->printerServer -d $this->printerName $fileLocation";
        
        return $cmd;
    }

    private function convertWindowsPathToUnix(String $fileLocation)
    {
        $fileLocation = str_replace('\\', '/', $fileLocation);
        $fileLocation = preg_replace_callback(
            '/^([a-zA-Z]):/',
            function ($matches) {
                return '/mnt/' . strtolower($matches[1]);
            },
            $fileLocation
        );

        return $fileLocation;
    }
}
```

In this case, I downloaded Ubuntu from the Windows Store. To run a command inside it, I prepend `ubuntu run ` before the `lp` command and also adjust the file path to the Unix format, adding `/mnt/` before the drive letter.

Simple, right? 😎

I hope this solution makes sense to you! 😊
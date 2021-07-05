<?php

namespace App\Libraries;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\App;
use mikehaertl\shellcommand\Command;
use Symfony\Component\Process\Process;
use Dompdf\Options;
use Dompdf\FontMetrics;
use Dompdf\Dompdf;


class WaterMark
{
    public static function make($text,$inputFile, $outputFile)
    {
        error_reporting(0);

        if (file_exists( public_path('helloWorld.docx')))
        {
            gc_collect_cycles();
            unlink('helloWorld.docx');
        }

        $arr2 = str_split($text, 22);
        $options = new Options();
        $options->set('isPhpEnabled', 'true');
        $dompdf = new Dompdf($options);



        $pdf = App::make('dompdf.wrapper');

                $pdf->loadHtml('<p></p>');
                $pdf->setPaper('L');
                $pdf->output();
                $canvas = $pdf->getDomPDF()->getCanvas();
                $height = $canvas->get_height();
                $width = $canvas->get_width();
                $fontMetrics = new FontMetrics($canvas,$options);
                $font = $fontMetrics->getFont('times');
                $txtHeight = $fontMetrics->getFontHeight($font, 95);
                $textWidth = $fontMetrics->getTextWidth($text, $font, 85);
                $canvas->set_opacity(.1,"Multiply");

                $incr = 250;


                for($i=0;$i<count($arr2);$i++)
                {
                    if($i+1==count($arr2))
                    {
                        $canvas->page_text(100, $incr-50, $arr2[$i], null,
                        50, array(0,0,0),2,2,-30);
                        break;
                    }

                    $canvas->page_text(10, $incr, $arr2[$i], null,
                    50, array(0,0,0),2,2,-30);
                    $incr = $incr+50;
                }

                file_put_contents(public_path('Mypdf.pdf'), $pdf->output());
                $pdf = new Pdf(public_path('Mypdf.pdf'));
                $pdf->saveImage(public_path());

        //





        $path = public_path($inputFile);
        $command = new Command('pdftk ' . $path . ' dump_data | findstr NumberOfPages');
        if ($command->execute()) {
            $page_length = (int)(substr($command->getOutput(), strpos($command->getOutput(), ": ") + 1));
            // $ima = public_path('p1.png'); // water marked image
            $ima = base_path().'\public.jpeg';
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $section = $phpWord->addSection();
            for ($i = 0; $i < $page_length; $i++) {
                $section->addPageBreak();
                $header = $section->addHeader();
                $header->addWatermark($ima, [
                    'width' => 646,
                    'marginTop' => 106,
                    'marginLeft' => -50,
                    'posHorizontal' => 'absolute',
                    'posVertical' => 'absolute'
                ]);
            }
            $objWriter->save('helloWorld.docx');
            $path = public_path('helloWorld.docx');
            $process = new Process(['C:\Program Files\LibreOffice\program\soffice', '--headless', '--convert-to', 'pdf', $path, '--outdir', public_path()]);
            $process->run();
            $file =  public_path('helloWorld.pdf');

            $command2 = new Command('pdftk helloWorld.pdf multistamp ' . "$inputFile" . ' output ' . $outputFile);
            if ($command2->execute()) {

                // added to delete multiple files

                unlink('helloWorld.docx');
                // unlink('helloWorld.pdf');
                unlink('Mypdf.pdf');


                return true;
            } else {
                // error
                echo 'error';
            }
        } else {
            echo $command->getError();
            // $exitCode = $command->getExitCode();
        }
        return false;
    }
}

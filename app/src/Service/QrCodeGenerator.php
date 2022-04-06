<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class QrCodeGenerator extends AbstractController
{
//    private $GOTENBERG_URL;

    private $path = [];

    public function __construct(ParameterBagInterface $parameterBag){
//        $this->GOTENBERG_URL = $parameterBag->get('app.gotenberg.url');
    }

    private function generatePdf($templatePath, $parameters): string
    {
        do{
            $directoryPath = "../var/cache/".uniqid();
        }while(file_exists($directoryPath));

        mkdir($directoryPath, 0777, true);

        $file = fopen($directoryPath.'/index.html', "w+");
        fputs($file, $this->renderView($templatePath, $parameters));
        fclose($file);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->GOTENBERG_URL.'/forms/chromium/convert/html',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'files'=> new \CURLFile($directoryPath.'/index.html'),
                'paperWidth'=>"8.27",
                'paperHeight'=>"11.7",
                'printBackground'=>true
            ],
            CURLOPT_HTTPHEADER => array(
                'Content-Type: multipart/form-data'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $file = fopen($directoryPath.'/non-signed-contract.pdf', "w+");
        fputs($file, $response);
        fclose($file);

        while(!file_exists($directoryPath.'/non-signed-contract.pdf')){
            sleep(0.01);
        }

        try {
            $this->signPdf->signPdf(realpath($directoryPath.'/non-signed-contract.pdf'), realpath($directoryPath).'/contract.pdf');
            $this->delete_files($directoryPath.'/non-signed-contract.pdf');
        }catch (ProcessFailedException $e){
            dd($e);
        }

        return $directoryPath;
    }

    public function downloadFromTemplate($templatePath, $parameters = []): bool
    {
        $directoryPath = $this->generatePdf($templatePath, $parameters);
        $this->path[] = $directoryPath;
        $response = $this->file($directoryPath.'/contract.pdf', 'contrat.pdf');
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Length', filesize($directoryPath.'/contract.pdf'));
        $response->send();
        $this->cleanFiles();
        return true;
    }

    public function generateFromTemplate($templatePath, $parameters = []): string
    {
        $directoryPath = $this->generatePdf($templatePath, $parameters);
        $this->path[] = $directoryPath;
        return $directoryPath.'/contract.pdf';
    }

    public function cleanFiles(){
        if($this->path){
            foreach ($this->path as $path){
                try {
                    $this->delete_files($path);
                }
                catch (\ErrorException $e){return false;}
            }
        }
        return true;
    }

    private function delete_files($target) {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK );
            foreach( $files as $file ){
                $this->delete_files( $file );
            }
            rmdir( $target );
        } elseif(is_file($target)) {
            unlink( $target );
        }
    }
}

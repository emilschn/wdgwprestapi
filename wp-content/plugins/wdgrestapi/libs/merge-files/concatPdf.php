<?php

use setasign\Fpdi\Fpdi;

if (!class_exists('FPDF')) {
	require_once dirname(__FILE__) . '/../fpdf/fpdf.php';
}
if (!class_exists('Fpdi')) {
	require_once dirname(__FILE__) . '/../fpdi/src/autoload.php';
}

class WDGRESTAPI_Lib_ConcatPdf extends Fpdi
{
    protected $files = array();

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function concatFiles()
    {
        foreach($this->files AS $file) {

			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$file_name = pathinfo($file, PATHINFO_FILENAME);
			$dir_name = pathinfo($file, PATHINFO_DIRNAME);

			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $file = ' . $file, FALSE );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $ext = ' . $ext, FALSE );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $file_name = ' . $file_name, FALSE );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $dir_name = ' . $dir_name, FALSE );

			// si le fichier n'est pas un pdf, on le transforme en pdf
			if ( $ext != 'pdf') {
				$wdgrestapi = WDGRESTAPI::instance();
				$wdgrestapi->add_include_lib( 'merge-files/pdf' );
				$fileToConcat = $dir_name . '/' . $file_name . '.pdf';
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $fileToConcat = ' . $fileToConcat, FALSE );
				$imageToPdf = new WDGRESTAPI_Lib_PDF('L','mm','A4');
				$imageToPdf->AddPage();
				$imageToPdf->AddCenteredResizedImage( $file );
				$imageToPdf->Output('F', $fileToConcat);

			} else {
				$fileToConcat = $file;
			}
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $fileToConcat = ' . $fileToConcat, FALSE );
			// on ajoute toutes les pages de tous les pdf
            $pageCount = $this->setSourceFile($fileToConcat);
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_ConcatPdf::concatFiles > $pageCount = ' . $pageCount, FALSE );
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pageId = $this->ImportPage($pageNo);
                $s = $this->getTemplatesize($pageId);
                $this->AddPage($s['orientation'], $s);
                $this->useImportedPage($pageId);
            }
        }
    }
	
	public function Error($msg) {
		throw new Exception($msg); 
	}
}
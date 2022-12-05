<?php

class WDGRESTAPI_Lib_MergeFiles {

	public static function mergeRectoVersoFiles( $recto, $verso, $random_filename ) {

		$recto_name_exploded = explode( '.', $recto );
		$recto_extension = strtolower( end( $recto_name_exploded ) );
		
		$verso_name_exploded = explode( '.', $verso );
		$verso_extension = strtolower( end( $verso_name_exploded ) );


		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > $recto = ' . $recto, FALSE );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > $verso = ' . $verso, FALSE );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > $random_filename = ' . $random_filename, FALSE );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > $recto_extension = ' . $recto_extension, FALSE );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > $verso_extension = ' . $verso_extension, FALSE );
					
		
		try {
			$wdgrestapi = WDGRESTAPI::instance();
		} catch(Exception $e) {
			return $e->getMessage;
		}
		if ($recto_extension != 'pdf' && $verso_extension  != 'pdf'){
			// les deux fichiers sont des images	
			try {
				$wdgrestapi->add_include_lib( 'merge-files/pdf' );
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > les deux fichiers sont des images ' , FALSE );
				$pdf = new WDGRESTAPI_Lib_PDF('L','mm','A4');
				$pdf->SetTitle('Justificatif_identite.pdf');
				$pdf->AddPage();
				$pdf->AddCenteredResizedImage($recto );
				$pdf->AddPage();
				$pdf->AddCenteredResizedImage($verso );
				$pdf->Output('F', $random_filename);
				return TRUE;
			} catch(Exception $e) {
				return $e->getMessage;
			}
		} else {
			// au moins un des fichiers est un pdf			
			try {
				$wdgrestapi->add_include_lib( 'merge-files/concatPdf' );
				WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_MergeFiles::mergeRectoVersoFiles > au moins un des fichiers est un pdf	' , FALSE );
				$pdf = new WDGRESTAPI_Lib_ConcatPdf();
				$pdf->setFiles(array($recto, $verso));
				$pdf->concatFiles();	
				$pdf->Output('F', $random_filename);
				return TRUE;
			} catch(Exception $e) {
				return $e->getMessage;
			}
		}		
	}
}
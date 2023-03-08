<?php

if (!class_exists('FPDF')) {
	require_once dirname(__FILE__) . '/../fpdf/fpdf.php';
}

class WDGRESTAPI_Lib_PDF extends FPDF {

    public const DPI = 150;
    public const MM_IN_INCH = 25.4;
    public const A4_HEIGHT = 297;
    public const A4_WIDTH = 210;
    // tweak these values (in pixels)
    protected const MAX_WIDTH = 1650;
    protected const MAX_HEIGHT = 1150;

	/* 	PORTRAIT MODE

		// tweak these values (in pixels)
		const MAX_WIDTH = 1150;
		const MAX_HEIGHT = 1650;

		$pdf->AddPage("P");

		A4 @ 300 dpi - 3507x2480 pix
		A4 @ 200 dpi - 2338 x 1653 pix
		A4 @ 150 dpi - 1753x1240 pix
		A4 @ 72 dpi - 841x595 pix

	*/

	// Retrieve PNG width and height without downloading/reading entire image.
	public static function getPngSize( $img_loc ) {
		$handle = fopen( $img_loc, "rb" ) or die( "Invalid file stream." );

		if ( ! feof( $handle ) ) {
			$new_block = fread( $handle, 24 );
			if ( $new_block[0] == "\x89" &&
				$new_block[1] == "\x50" &&
				$new_block[2] == "\x4E" &&
				$new_block[3] == "\x47" &&
				$new_block[4] == "\x0D" &&
				$new_block[5] == "\x0A" &&
				$new_block[6] == "\x1A" &&
				$new_block[7] == "\x0A" ) {
					if ( $new_block[12] . $new_block[13] . $new_block[14] . $new_block[15] === "\x49\x48\x44\x52" ) {
						$width  = unpack( 'H*', $new_block[16] . $new_block[17] . $new_block[18] . $new_block[19] );
						$width  = hexdec( $width[1] );
						$height = unpack( 'H*', $new_block[20] . $new_block[21] . $new_block[22] . $new_block[23] );
						$height  = hexdec( $height[1] );

						return array( $width, $height );
					}
				}
			}

		return false;
	}


	// Retrieve JPEG width and height without downloading/reading entire image.
	public static function getJpegSize($img_loc) {
		$handle = fopen($img_loc, "rb") or die("Invalid file stream.");
		$new_block = NULL;
		if(!feof($handle)) {
			$new_block = fread($handle, 32);
			$i = 0;
			if($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
				$i += 4;
				if($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
					// Read block size and skip ahead to begin cycling through blocks in search of SOF marker
					$block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
					$block_size = hexdec($block_size[1]);
					while(!feof($handle)) {
						$i += $block_size;
						$new_block .= fread($handle, $block_size);
						if($new_block[$i]=="\xFF") {
							// New block detected, check for SOF marker
							$sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
							if(in_array($new_block[$i+1], $sof_marker)) {
								// SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
								$size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
								$unpacked = unpack("H*", $size_data);
								$unpacked = $unpacked[1];
								$height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
								$width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
								return array($width, $height);
							} else {
								// Skip block marker and read block size
								$i += 2;
								$block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
								$block_size = hexdec($block_size[1]);
							}
						} else {
							return FALSE;
						}
					}
				}
			}
		}
		return FALSE;
	}


    protected function pixelsToMM($val) {
        return $val * self::MM_IN_INCH / self::DPI;
    }

    protected function resizeToFit($imgFilename) {
		// en fonction de l'extension
		$file_name_exploded = explode( '.', $imgFilename );
		$extension = strtolower( end( $file_name_exploded ) );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::resizeToFit > $imgFilename = ' . $imgFilename, FALSE );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::resizeToFit > $extension = ' . $extension, FALSE );
		switch ( $extension ) {
			case 'jpg':
			case 'jpeg':
				list($width, $height) = self::getJpegSize($imgFilename);
				break;
			case 'png':
				list($width, $height) = self::getPngSize($imgFilename);
				break;
			case 'gif':
			default :
				list($width,$height) = getimagesize( $imgFilename );
				break;
		}

		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::resizeToFit > $width = ' . $width, FALSE );
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::resizeToFit > $height = ' . $height, FALSE );
		if ( !isset($width) || $width == 0) {
			list($width,$height) = getimagesize( $imgFilename );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::resizeToFit > recalcul $width = ' . $width, FALSE );
			WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::resizeToFit > recalcul $height = ' . $height, FALSE );
		}

        $widthScale = $width > 0 ? self::MAX_WIDTH / $width : 1;
        $heightScale = $height > 0 ? self::MAX_HEIGHT / $height : 1;

        $scale = min($widthScale, $heightScale);

        return array(
            round($this->pixelsToMM($scale * $width)),
            round($this->pixelsToMM($scale * $height))
        );
    }

	protected function prepareImage($file)
	{
		$imagetype = exif_imagetype($file);
		switch ($imagetype) {
			case IMAGETYPE_JPEG:
				$path = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
				$image = imagecreatefromjpeg($file);
				imageinterlace($image, false);
				imagejpeg($image, $path);
				imagedestroy($image);
				break;
			case IMAGETYPE_PNG:
				$path = sys_get_temp_dir() . '/' . uniqid() . '.png';
				$image = imagecreatefrompng($file);
				imageinterlace($image, false);
				imagesavealpha($image, true);
				imagepng($image, $path);
				imagedestroy($image);
				break;
			default:
				return $file;
		}
		return $path;
	}

    public function AddCenteredResizedImage($img) {
		WDGRESTAPI_Lib_Logs::log( 'WDGRESTAPI_Lib_PDF::AddCenteredResizedImage > $img = ' . $img, FALSE );
		
        list($width, $height) = $this->resizeToFit($img);

		$imgDeinterlace =  $this->prepareImage($img);

        // you will probably want to swap the width/height
        // around depending on the page's orientation
        $this->Image(
            $imgDeinterlace, 
			(self::A4_HEIGHT - $width) / 2,
            (self::A4_WIDTH - $height) / 2,
            $width,
            $height
        );
    }
	
	public function Error($msg) {
		throw new Exception($msg); 
	}
}

<?php
class Zend_View_Helper_Thumbnail extends Zend_View_Helper_Abstract {
	protected $defaults = array (
			'upscale' => false, // size up
			'crop' => true, // 1 => true, 0 => false
			'silent' => true, // generate error or try to open file anyway? (
			                  // extension problems )
			'cache' => false, // cache
			'default' => false, // default image
			'background' => array (
					255,
					255,
					255 
			) 
	);
	// background, default = white
	protected $extensions = array (
			'jpg',
			'png',
			'gif' 
	);
	protected $root_dir;
	protected function imagecreatefrom($filename, $ext) {
		ob_start ();
		
		switch (strtolower ( $ext )) {
			case 'jpeg' :
			case 'jpg' :
				$image = imagecreatefromjpeg ( $filename );
				break;
			case 'png' :
				$image = imagecreatefrompng ( $filename );
				break;
			case 'gif' :
				$image = imagecreatefromgif ( $filename );
				break;
		}
		
		ob_end_clean ();
		return $image;
	}
	protected function imagesaveto($image, $filename, $ext) {
		switch (strtolower ( $ext )) {
			case 'jpeg' :
			case 'jpg' :
				$image = imagejpeg ( $image, $filename );
				break;
			case 'png' :
				$image = imagepng ( $image, $filename );
				break;
			case 'gif' :
				$image = imagegif ( $image, $filename );
				break;
		}
		
		return true;
	}
	public function __construct() {
		$this->root_dir = $_SERVER ['DOCUMENT_ROOT'];
	}
	public function thumbnail($file, $w, $h, $output = "thumbs/%file%", $options = array()) {
		foreach ( $this->defaults as $key => $val ) :
			if (! isset ( $options [$key] ))
				$options [$key] = $val;
		endforeach
		;
		
		// make full path
		if (mb_strpos ( $file, $this->root_dir ) !== 0)
			$file = $this->root_dir . $file;
		
		if (! file_exists ( $file ) || ! is_file ( $file ))
			return $options ["default"];
			
			// cut filename and extension
		$fileinfo = pathinfo ( $file );
		$fileinfo ['basename'] = mb_substr ( $fileinfo ['basename'], 0, - mb_strlen ( $fileinfo ['extension'] ) - 1 );
		
		// create folder into current file dir
		if (strrpos ( $output, '/' ) !== false) {
			$dir = explode ( '/', $output );
			if (! file_exists ( $fileinfo ['dirname'] . '/' . $dir [0] )) {
				mkdir ( $fileinfo ['dirname'] . '/' . $dir [0] );
				chmod ( $fileinfo ['dirname'] . '/' . $dir [0], 0777 );
			}
		}
		
		$output = $fileinfo ['dirname'] . '/' . str_replace ( "%file%", $fileinfo ['basename'], $output ) . '.' . $fileinfo ['extension'];
		
		if ($options ['cache'] && file_exists ( $output ))
			return mb_substr ( $output, mb_strlen ( $this->root_dir ) );
		
		$image = $this->imagecreatefrom ( $file, $fileinfo ['extension'] );
		
		if (! $image)
			if ($options ['silent'])
				foreach ( $this->extensions as $ext ) {
					if ($ext != strtolower ( $fileinfo ['extension'] )) {
						$image = $this->imagecreatefrom ( $file, $ext );
						
						if ($image)
							break;
					}
				}
			else
				return FALSE;
			// silent
		
		if (! $image)
			return FALSE;
		
		if (! $this->defaults ["upscale"])
			if ((imagesx ( $image ) < $w && $h == 0) || (imagesx ( $image ) < $w && $h == 0))
				return mb_substr ( $file, mb_strlen ( $this->root_dir ) );
		
		if ($h == 0) {
			$h = (imagesy ( $image ) / imagesx ( $image )) * $w;
		}
		
		if ($w == 0) {
			$w = (imagesx ( $image ) / imagesy ( $image )) * $h;
		}
		
		$k = array (
				$w / imagesx ( $image ),
				$h / imagesy ( $image ) 
		);
		$k = ! $options ['crop'] ? min ( $k ) : max ( $k );
		
		$picture = imagecreatetruecolor ( $w, $h );
		
		$bgcolor = imagecolorallocate ( $picture, $options ['background'] [0], $options ['background'] [1], $options ['background'] [2] );
		imagefill ( $picture, 0, 0, $bgcolor );
		
		// get coordinates
		$size = array (
				imagesx ( $image ) * $k,
				imagesy ( $image ) * $k 
		);
		
		$x = ($w - $size [0]) / 2;
		$y = ($h - $size [1]) / 2;
		
		$f = imagecopyresampled ( $picture, $image, $x, $y, 0, 0, imagesx ( $image ) * $k, imagesy ( $image ) * $k, imagesx ( $image ), imagesy ( $image ) );
		
		$this->imagesaveto ( $picture, $output, $fileinfo ['extension'] );
		
		if (file_exists ( $output ))
			return mb_substr ( $output, mb_strlen ( $this->root_dir ) );
		else
			return FALSE;
	}
}
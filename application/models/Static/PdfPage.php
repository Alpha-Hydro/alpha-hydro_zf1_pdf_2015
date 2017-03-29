<?php
error_reporting(E_ERROR);
/**
 * PDF Page
 *
 * @author Bakin Vlad
 * @package PDF Generation
 * @category pdf
 */

/**
 * Fonts loader class
 *
 * @package PDF Generation
 * @author Bakin Vlad
 */
class Model_Static_Fonts {

    /**
     * Fonts for load with names
     */
    protected static $Model_Static_Fonts = array('Franklin Gothic Demi Cond' => 'Franklin Gothic Demi Cond.ttf', 'Arial Narrow Bold' => 'Arial Narrow Bold.ttf', 'Arial Narrow' => 'Arial Narrow.ttf', 'Arial' => 'Arial.ttf');

    /**
     * Already loaded fonts
     */
    protected static $loaded = array();

    
    /**
     * Get or load font
     *
     * @param string $font Font name
     * @return Zend_Pdf_Font Selected font
     */
    public static function get($font) {

        if (empty(Model_Static_Fonts::$loaded[$font])) {
            if (!isset(Model_Static_Fonts::$Model_Static_Fonts[$font]))
                exit('NO FONT SELECTED');
            Model_Static_Fonts::$loaded[$font] = Zend_Pdf_Font::fontWithPath(APPLICATION_ROOT . '/files/pdf/fonts/' . Model_Static_Fonts::$Model_Static_Fonts[$font]);
        }
        return Model_Static_Fonts::$loaded[$font];
    }

}

/**
 * PDF Page class
 *
 * @property float fontSizeFormat
 * @property float fontSizeFormatDiscription
 * @property float fontSizeFormatHeader
 * @property float fontSizeFormatSnoski
 * @package PDF Generation
 * @author Bakin Vlad
 */
class Model_Static_PdfPage extends Zend_Pdf_Page {
    /**
     * A5 size in points for PDF
     */
//    const SIZE_A5 = '419:595:';
//    const SIZE_PAGE = '595:842:';

    /**
     * Logo width ( for draw )
     *
     * @used-by $this::__constructor()
     */
    const LOGO_WIDTH = 100;
    /**
     * Logo height ( for draw )
     *
     * @used-by $this::__constructor()
     */
    const LOGO_HEIGHT = 15;

    /**
     * Number page block width ( for draw )
     *
     * @used-by $this::__constructor()
     */
    const PAGENUM_WIDTH = 45;

    /**
     * Current page
     */
    protected $pageNum = 1;

    /**
     * MARGINS [top, right, bottom, left]
     */
    protected $MARGIN = array('top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0);
    
    protected $pageFormat;

    public $print;
    /**
     * Side icons
     */
    protected $SIDEICONS = array(
                                    '3',
                                    '1624',
                                    '86',
                                    '121',
                                    '2',
                                    '18',
                                    '1',
                                    '5',
                                    "7",
                                    "6",
                                    "4",
                                );
    protected $dumpStyle;

    /**
     * Set side icons
     * 
     * @param array $iconSet Icons
     */
    public function setSideIcons( $iconSet ){
        $this->SIDEICONS = $iconSet;
    }
    
    /**
     * Current page offset ( vertical cursor )
     */
    protected $currentPosition = 0;

    /**
     * get this page number
     *
     * @return int Page number
     */
    public function getPageNumber() {
        return $this -> pageNum;
    }

    /**
     * Get page margins
     * @return array Page margins [top, right, bottom, left]
     */
    public function getMargins() {
        return $this -> MARGIN;
    }

    /**
     * Set page margins
     * @return array Page margins [top, right, bottom, left]
     */
    public function setMargins($top, $right, $bottom, $left) {
        return $this -> MARGIN = array('top' => $top, 'right' => $right, 'bottom' => $bottom, 'left' => $left);
    }

    /**
     * Get width of page ( without margins )
     *
     * @return int Page width
     * @api
     */
    public function getWidth() {
        return (parent::getWidth() - ($this -> MARGIN['left']) - ($this -> MARGIN['right']));
    }

    /**
     * Get height of page ( without margins )
     *
     * @return int Page height
     * @api
     */
    public function getHeight() {
        return (parent::getHeight() - $this -> MARGIN['top'] - $this -> MARGIN['bottom']);
    }

    /**
     * draw category breadcrumbs
     *
     * @param string $category Category bcn string
     */
    public function drawCategory($category) {
        $style = new Zend_Pdf_Style();
        $style -> setLineWidth($this -> getWidth() - $this::LOGO_WIDTH - 30 * 2 - 10 * 2);
        if($this->pageFormat == 'A4'){
            $style->setFont(Model_Static_Fonts::get("Arial"), 6);
        }else {
            $style->setFont(Model_Static_Fonts::get("Arial"), 4);
        }

        $curPos = $this->getCurrentPosition();
	    $height = $this->getTextBlockHeight($category, $style);
        $y = -2;
        if( $height > 16  ){
            $y = 1;
        }
        $this->drawTextBlock($category, ($this -> pageNum % 2) ? $this::LOGO_WIDTH + 30 + 10 : 30 + 10, $y, $style);
        $this->setCurrentPosition($curPos);
    }

    /**
     * Initalize product - draw image(header-line)
     *
     * @param int $offset vertical offset
     */
    public function init($offset = 0) {
        $this->drawPic(APPLICATION_ROOT . '/files/pdf/bar.png', 5, $this -> getHeight() - 15 - $offset, $this -> getWidth(), 2);
    }

    /**
     * Page constructor
     *
     * @param int $page Page number
     * @param bool $initalize Initalize this page at null offset
     * @param array $margin Change default margins
     * @api
     */
    // В зависимости от параметров  переданных при создание объекта, вносим нужные нам значения.
    public function __construct($page = 1, $initalize = true, $margin = array(), $format = 'A5', $format_size = '419:595:', $print = false) {
        if ($margin)
            $this -> MARGIN = $margin;

        parent::__construct($format_size);

        $this->pageNum = $page;
        $this->pageFormat = $format;
        $this->print = $print;

        if($this -> pageFormat == 'A4'){
            $this->fontSizeFormat = 7.0;
            $this->fontSizeFormatDiscription = 8.0;
            $this->fontSizeFormatHeader = 7.0;
            $this->fontSizeFormatSnoski = 6.5;
        }else{
            $this->fontSizeFormat = 5.5;
            $this->fontSizeFormatDiscription = 6.5;
            $this->fontSizeFormatHeader = 5.0;
            $this->fontSizeFormatSnoski = 4.5;
        }
        $this->dumpStyle = new Zend_Pdf_Style();
        $this->dumpStyle -> setFont(Model_Static_Fonts::get("Arial Narrow"), 15);
        // init / draw line
        if ($initalize)
            $this -> init();

        // init / draw logo
        $this -> drawPic(APPLICATION_ROOT . '/files/pdf/alfa-hydro.png', ($page % 2) ? 0 : $this -> getWidth() - $this::LOGO_WIDTH, 10, $this::LOGO_WIDTH, $this::LOGO_HEIGHT);

        // init / draw category background
        $this -> drawHorizontalLine(($page % 2) ? $this::LOGO_WIDTH + 30 : 30, ($page % 2) ? $this -> getWidth() - 30 : $this -> getWidth() - $this::LOGO_WIDTH - 40, 0, $this::LOGO_HEIGHT, new Zend_Pdf_Color_Html("#e5e5e5"));

        // write page number
        $this -> saveGS();
        $this -> setFillColor(new Zend_Pdf_Color_Html("white"));
        $this -> setFont(Model_Static_Fonts::get("Arial Narrow Bold"), 7.75);

        $this -> drawHorizontalLine(($page % 2) ? $this -> getWidth() + $this -> MARGIN['right'] - $this::PAGENUM_WIDTH : -$this -> MARGIN['left'], ($page % 2) ? $this -> getWidth() + $this -> MARGIN['right'] : -$this -> MARGIN['left'] + $this::PAGENUM_WIDTH, 0, Model_Static_PdfPage::LOGO_HEIGHT, new Zend_Pdf_Color_Html("#0095da"));
        if($this->pageFormat == "A4"){
            $this -> drawTextBlock($page, (($page % 2) ? $this -> getWidth() + $this -> MARGIN['right'] - $this::PAGENUM_WIDTH + 10 : -$this -> MARGIN['left'] + 25), -3);
        }else{
            $this -> drawTextBlock($page, (($page % 2) ? $this -> getWidth() + $this -> MARGIN['right'] - $this::PAGENUM_WIDTH + 10 : -$this -> MARGIN['left'] + 10), -3);
        }
            $this -> restoreGS();

            // start position - top of page

            $this -> currentPosition = $this -> getHeight();
    }

    /**
     * Getting image dimensions
     *
     * @param string $image Image
     *
     * @return array [x/y, x, y]
     */
    private function __imageDimensions($image) {
        if ($image != ''){
            $image = new Imagick($image);

            $retValue["k"] = $image->getImageWidth() / $image->getImageHeight();
            $retValue["w"] = $image->getImageWidth();
            $retValue["h"] = $image->getImageHeight();

		    return $retValue;
	    } else
            return NULL;
    }

    /**
     * Get drawing sizes and coordinates for image by mode
     *
     * @param string $image Image
     * @param int $w Width
     * @param int $h Height
     * @param int $mode Draw mode: 0 - lower, 1 - high
     *
     * @return array [szImg/szStandartImg, width, height, x, y]
     *
     * @deprecated not needed, combine with drawPic
     */
    public function picSize($image, $w, $h, $mode = 0) {
        $k = $this -> __imageDimensions($image);
        $ret = 1;
        $x = $y = 0;
        if ($k) {
            if ($mode == 1) {
                if ($k["k"] < 1) {          // portrait
                    $x += ($w - ($h / $k["h"]) * $k["w"]) / 2;
                    $w = ($h / $k["h"]) * $k["w"];
                } elseif ($k["k"] > 1) {    // landscape
                    $y -= ($h - ($w / $k["w"]) * $k["h"]) / 2;
                    $h = ($w / $k["w"]) * $k["h"];
                }
            } elseif ($mode == 2) {
                if ($k["k"] > 1) {          // landscape
                    $ret = ($h / $k["h"]) * $k["w"] / $w;
                    $w = ($h / $k["h"]) * $k["w"];
                } elseif ($k["k"] < 1) {    // portrait
                    $x += ($w - ($h / $k["h"]) * $k["w"]) / 2;
                    $w = ($h / $k["h"]) * $k["w"];
                }
            }
        }

		return array($ret, $w, $h, $x, $y);
    }

    /**
     * Draw picture at coordinates
     * Supports unsupported format (.gif) and resize image by mode
     *
     * @param string $image Image
     * @param int $x x coordinate
     * @param int $y y coordinate
     * @param int $w Width
     * @param int $h Height
     * @param int $mode Draw mode: 0 - lower, 1 - high
	 * @param int $calcCurrentPosition: 0 - do not calculate $this->currentPosition, 1 - calculate it
     *
     * @return int szImg/szStandardImg
     *
     * @api
     */
    public function drawPic($image, $x, $y, $w, $h, $mode = 0, $calcCurrentPosition = 1) {
        // MODE: 0 - lower, 1 - high

	    $img = @imagecreatefromgif($image);
        if ($img) {
            $image = substr($image, 0, -4) . '.png';
            imagepng($img, $image);
        }

        $sz = $this -> picSize($image, $w, $h, $mode);

        $x += $sz[3];
        $y += $sz[4];
        $w = $sz[1];//($sz[1] > 150)?150:$sz[1];
        $h = $sz[2];//($sz[2] > 150)?150:$sz[2];

        if ($this -> pageNum % 2 == 0)
            $x -= ($sz[0] - 1) * 75;
        // TODO: change this hack

        // Рисуем c другим позиционированием
        try {
            $this -> drawImage(Zend_Pdf_Image::imageWithPath($image), $this -> MARGIN['left'] + $x, $y + $this -> MARGIN['bottom'] - $h, $this -> MARGIN['left'] + $x + $w, $y + $this -> MARGIN['bottom']);
        } catch(Exception $e) {
        	$this -> drawImage(Zend_Pdf_Image::imageWithPath(APPLICATION_ROOT."/files/pdf/placeholder.png"), $this -> MARGIN['left'] + $x, $y + $this -> MARGIN['bottom'] - $h, $this -> MARGIN['left'] + $x + $w, $y + $this -> MARGIN['bottom']);
            //throw new Exception('Error image file. Format not supported. Image: '.$image, 2);
        }

        if ($this -> currentPosition > $y - $h - $this -> MARGIN['bottom'] - 15 && $calcCurrentPosition == 1)
            $this -> currentPosition = $y - $h - $this -> MARGIN['bottom'] - 15;

        return $sz[0];
    }

    /**
     * Draw horizontal line by coordinates with $width width and $color color
     *
     * @param int $x1 Start x coordinate
     * @param int $x2 End x coordinate
     * @param int $y y coordinate
     * @param int $width Line width
     * @param Zend_Pdf_Color $color Line color
     *
     * @api
     */
    public function drawHorizontalLine($x1, $x2, $y, $width, $color) {
        $this -> saveGS();
        $this -> setLineColor($color);
        $this -> setLineWidth($width);
        $this -> drawLine($this -> MARGIN['left'] + $x1, $y + $this -> MARGIN['bottom'], $this -> MARGIN['left'] + $x2, $y + $this -> MARGIN['bottom']);
        $this -> restoreGS();
    }

    /**
     * Get wrapped text
     *
     * @param string $string String
     * @param Zend_Pdf_Style $style Style(font, fontsize, line width)
     * @param bool $force Use force wrap ( break words )
     *
     * @return string Wrapped string
     */
    private function getWrappedText($string, Zend_Pdf_Style $style, $force = false) {
        $wrappedText = '';
        $max_width = $style -> getLineWidth();
        $lines = explode("\n", $string);
        foreach ($lines as $line) {
            $words = explode(' ', $line);

            // Force wrap like:
            // Fo
            // rc
            // e
            //
            if ($force) {
                $array = array('');
                foreach ($words as $word) {
                    $width = $this -> widthForStringUsingFontsize($word, $style->getFont(), $style->getFontsize());

                    if ($width > $max_width) {
                        $width = intval($width / $max_width + 1);
                        $w = str_split($word, 2 * intval(strlen($word) / (2 * $width)));

                        foreach ($w as $wd)
                            if ($this -> widthForStringUsingFontsize($array[count($array) - 1] . $wd, $style -> getFont(), $style -> getFontsize()) < $max_width)
                                $array[count($array) - 1] .= $wd;
                            else
                                $array[] = $wd;
                    } else
                        $array[] = $word;
                }
                $words = $array;
            }

            $word_count = count($words);
            $i = 0;
            $wrappedLine = '';

            while ($i < $word_count) {
                if ($this -> widthForStringUsingFontsize($wrappedLine . ' ' . $words[$i], $style -> getFont(), $style -> getFontsize()) < $max_width) {
                    if (!empty($wrappedLine)) {
                        $wrappedLine .= ' ';
                    }
                    $wrappedLine .= $words[$i];
                } else {
                    $wrappedText .= $wrappedLine . "\n";
                    $wrappedLine = $words[$i];
                }
                $i++;
            }
            $wrappedText .= $wrappedLine . "\n";
        }

        return ltrim($wrappedText);
    }

    /**
     * Get width for string using font size
     *
     * @param string $string String
     * @param Zend_Pdf_Font $font Font
     * @param int $fontsize Font size
     *
     * @return int width of string
     */
    public function widthForStringUsingFontsize($string, $font, $fontsize) {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i <= strlen($drawingString); $i++)
            $characters[] = (@ord($drawingString[$i++])<<8) | @ord($drawingString[$i]);

        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontsize;
        return $stringWidth;
    }

    /**
     * Draw text with wrapping and selected style
     *
     * @param string $text String
     * @param int $x x coordinate
     * @param int $y y coordinate
     * @param Zend_Pdf_Style $style Text style (font, size, color etc)
     * @param int $line_offset Line spacing
     * @param bool $force Use force wrapping
     *
     * @return int Current Y position
     *
     * @api
     */
    public function drawTextBlock($text, $x, $y, $style = NULL, $line_offset = 0, $force = false) {
        if ($style) {

            $this -> saveGS();
            $lines = explode("\n", $this -> getWrappedText($text, $style, $force));
            $this -> setFont($style -> getFont(), $style -> getFontsize());
            foreach ($lines as $line) {
                $this -> drawText(trim($line), $this -> MARGIN['left'] + $x, $this -> MARGIN['bottom'] + $y, 'UTF-8');
                $y -= $style -> getFontsize() + $line_offset;
            }
            $this -> restoreGS();

            // DEBUG
            //$this->drawHorizontalLine( $x, $x + $style->getLineWidth(), $s_y, $s_y-$y, new Zend_Pdf_Color_Html("red"));

            if ($this -> currentPosition > $y - $this -> MARGIN['bottom'])
                $this -> currentPosition = $y - $this -> MARGIN['bottom'];
            return $y;

        } else
            return parent::drawText($text, $this -> MARGIN['left'] + $x, $this -> MARGIN['bottom'] + $y, 'UTF-8');

    }

    /**
     * Get textblock height
     *
     * @see $this::drawTextBlock
     *
     * @param string $text String
     * @param Zend_Pdf_Style $style Text style (font, size, color etc)
     * @param int $line_offset Line spacing
     * @param bool $force Use force wrapping
     *
     * @return int Height of text block
     *
     * @api
     */
    public function getTextBlockHeight($text, $style, $line_offset = 0, $force = false) {
        return count(explode("\n", $this -> getWrappedText($text, $style, $force))) * ($style -> getFontsize() + $line_offset);
    }

    /**
     * Draw table or part of table by coordinates with or without header
     *
     * @param array[][] $table Table values
     * @param int $x x coordinate
     * @param int $y y coordinate
     * @param int $width Table width
     * @param int $max_y Minimal y coordinate
     * @param array[] $header Table header
     * @param int $start_row start row of table
     *
     * @return int End row
     *
     * @api
     */
    public function drawTable($table, $x, $y, $width, $max_y = 0, $header = NULL, $start_row = 0, $params = false, $note = '') {
        if (!$table)
            return NULL;
	
        if ($start_row > count($table) - 1) {
            if ($this -> currentPosition > $y - $this -> MARGIN['bottom'])
                $this -> currentPosition = $y - $this -> MARGIN['bottom'];
            return NULL;
        }
        $max_widths = $this -> getTableColumnsMaxWidths($table, $params);
        $avg_widths = $this -> getTableColumnsAverageWidths($table, $params);

        //	parent::drawText(implode(',',$max_widths), $this -> MARGIN['left'], $this -> MARGIN['bottom'], 'UTF-8');
        //вычисляем ширину столбцов, в зависимости от того, какую таблицу рисуем выбираем нужную формулу
        if($params){
            $awidth = array_sum($max_widths) - 0;
        }
        else{
            $awidth = array_sum($avg_widths) - $avg_widths[0] - 5;
            $widths = array($max_widths[0]);
        }
	
        // Подгоняем ширину под заданную, через процентные соотношения
        foreach ($avg_widths as $i => $a_w){
            if ($params){
                $widths[] = (($a_w)/ $awidth) * ($width);
            }
            elseif($i != 0 ){
                $widths[] = ($a_w/ $awidth) * ($width - $widths[0]- 10) ;
            }
        }

        // запомним наш $x  и текущий $y
        $coords = array($x, $y);

        // write header ( if exists )
        if ($header) {
            //создаем сностки для таблици с параметрами. В зависимости от количества столбцов и длинны названия параметра, заменяем его и записываем в сноски
            $snoski = '';
            $s_number = 1;
            $widthSnoski = $width;
            ($this->pageFormat == 'A4')?$countChar=30:$countChar=10;
            foreach ($header as $i => $column){

                if ( count($header) > 7  && strlen($column) >= $countChar && $i != 0 || $column == 'Типоразмер'){
                    if($widthSnoski < ($this->widthForStringUsingFontsize($s_number.'* - '.$column.'; ',  Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatSnoski) + $this->widthForStringUsingFontsize($snoski,  Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatSnoski)))
                    {$snoski .=  chr('0x0D').chr('0x0A'); $widthSnoski*=2;}
                    $snoski .= $s_number.'* - '.$column.'; ';
                    $header[$i] = $s_number.'*';
                    $s_number ++;
                }elseif($this->pageFormat == 'A5'){
                    $header[$i] = str_replace('(', chr('0x0D').chr('0x0A').'(', $column);
                }

            }
		    $snoski .= ' '.$note;
            $this -> saveGS();
            $style = new Zend_Pdf_Style();

            $style -> setFillColor(new Zend_Pdf_Color_Html("white"));
            $style -> setFont(Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatHeader);// Bold

            $this -> setStyle($style);

            // calculate height of header
            $height = 0;
	   

            foreach ($header as $i => $column) {
                $style -> setLineWidth($widths[$i]);
		        $height = max($height, $this -> getTextBlockHeight(
				trim(( $column)), $style, 0, true));
            }
	    
            // if we can't write 2 line - exit
            if ($y - $height - $max_y < 16) {
                $this -> restoreGS();
                return 0;
            }
	    
            // else - draw header background
            $this -> drawHorizontalLine($coords[0]+5, $coords[0] + $width, ($this->pageFormat == 'A4')?($y - ($height / 2) + 5.5):($y - ($height / 2) + 3.5), $height, new Zend_Pdf_Color_Html("#0095da"));
            // here - write header

            foreach ($header as $i => $col) {
                $style -> setLineWidth($widths[$i]);
		
                $width_text = $this->widthForStringUsingFontsize($col, $style->getFont(), $style->getFontSize());
                if($widths[$i]>$width_text && $i != 0) {
                    $iLeft = $x + (($widths[$i] - $width_text) / 2) ;
                }else{
                    $iLeft = $x + 8;
                }
                $y = min($y, $this -> drawTextBlock(str_replace(array(''),array(''),trim($col)), $iLeft + 1 * ($i == 0), $coords[1] - 3, $style, 0, true));
                $x += $widths[$i];
            }

            $this -> restoreGS();
        }

        // make offset(3)
        
        if($this->pageFormat == 'A4'){
            $y -= 2;
        }
        elseif($this->pageFormat == 'A5'){
            $y -= 0;
        }

        // write table data
        $coords[0]+=5;
        $width-= 5;
        for ($c_row = $start_row; $c_row < count($table); $c_row++) {
            $rowset = $table[$c_row];

            $x = $coords[0];
            $line_y = $y ;

            // for table with headers odd lines have bg
            if ($c_row % 2 != 0 && $header) {

                $style = new Zend_Pdf_Style();
                $style -> setLineWidth($widths[$i] + 10);
                $style -> setFont(Model_Static_Fonts::get("Arial Narrow" . ($i == 0 && !$header ? ' Bold' : '')), $this->fontSizeFormat);
                $height = 0;
                foreach ($rowset as $i => $col) {
                    $style -> setLineWidth($widths[$i]);
                    $height = max($height, $this -> getTextBlockHeight($col, $style));
                }
                if($this->pageFormat == 'A4'){
                    $this -> drawHorizontalLine($x, $x + $width, $y + (3 - intval(($height - 2) / 6 - 1.5) * 2), $height - 4 , new Zend_Pdf_Color_Html("#e7e7e7"));
                }elseif($this->pageFormat == 'A5'){
                    $this -> drawHorizontalLine($x, $x + $width, $y + (2 - intval(($height - 2) / 6 - 1) * 2), $height - 4 , new Zend_Pdf_Color_Html("#e7e7e7"));
                }
                
            }

            foreach ($rowset as $i => $col) {
                $style = new Zend_Pdf_Style();
                if($params){
                    $style -> setFont(Model_Static_Fonts::get("Arial Narrow" . ($i == 0 && !$header ? ' Bold' : '')), $this->fontSizeFormat);
                }
                else{
                    $style -> setFont(Model_Static_Fonts::get("Arial Narrow" . ($i == 0 && !$header ? ' Bold' : '')), $this->fontSizeFormatDiscription);
                }
                $style -> setLineWidth($widths[$i]);
                // пишем клетку, и запоминаем макс высоту ( для многострочности )
				
                // выравнивание по центру значения таблицы с параметрами
                $width_text = $this->widthForStringUsingFontsize(trim($col), $style->getFont(), $style->getFontSize());
                if($widths[$i]>$width_text && $params && $i != 0){
                    $iLeft = $x + (($widths[$i] - $width_text) / 2);
                    $line_y = min($line_y, $this -> drawTextBlock(str_replace('  ', ' ', trim($col)), $iLeft , $y , $style, -2));
                }elseif($params && $i == 0){
                    $iLeft = $x + 10;
                    $line_y = min($line_y, $this -> drawTextBlock(str_replace('  ', ' ', trim($col)), $iLeft , $y, $style, -2));
                }else{
                    $iLeft = $x;
                    $line_y = min($line_y, $this -> drawTextBlock(str_replace('  ', ' ', trim($col)), $iLeft , $y + 4, $style));
                }
                
                $x += $widths[$i];
            }

            $heightSnoski = 0;
                $y = $line_y ;
            if ($header) {
                $style = new Zend_Pdf_Style();
                $style -> setFont(Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatSnoski);
                $style -> setLineWidth($width);
                $heightSnoski = $this -> getTextBlockHeight($snoski, $style, 0, true);

            }

                // проверим, не вышли ли бы за границы высоты, но не в последнем элементе
            if(($y - 11 - $heightSnoski) < 8  && count($table) == 1){
               if ($header) {
                    $this -> drawTextBlock( $snoski, 5, $y, $style);
                    //$page -> setCurrentPosition($page -> getCurrentPosition());
               }

               if ($this -> currentPosition > $y - $this -> MARGIN['bottom']){
                   $this -> currentPosition = $y - $this -> MARGIN['bottom'];
               }

               return NULL;
            }

            if (($y - $max_y - $heightSnoski) < 8  && $c_row != count($table) - 1 )  {//
                if ($header) {
                    $this -> drawTextBlock( $snoski, 5, $y, $style);
                    //$page -> setCurrentPosition($page -> getCurrentPosition());
                }

                if ($this -> currentPosition > $y - $this -> MARGIN['bottom']){
                    $this -> currentPosition = $y - $this -> MARGIN['bottom'];
                }

                return $c_row;
            }

        }
	    //$this -> drawTextBlock($y,  -20, $y + 10, $style);

        if ($this -> currentPosition > $y - $this -> MARGIN['bottom']){
            $this -> currentPosition = $y - $this -> MARGIN['bottom'];
        }

	    // пишем сноски в конце блока с парамаетрами
        if ($header) {
            $style = new Zend_Pdf_Style();
            $style -> setFont(Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatSnoski);
            $style -> setLineWidth($width);

            $this -> drawTextBlock( $snoski, 5, $y, $style);
            //$this -> drawTextBlock( $note, 5, $this -> getCurrentPosition() + 25, $style);

            //$this -> setCurrentPosition($this -> getCurrentPosition());
        }

        return NULL;
    }

    /**
     * Get current position ( cursor )
     *
     * @return int Cursor
     */
    public function getCurrentPosition() {
        return $this -> currentPosition;
    }

    /**
     * Set current position ( cursor )
     *
     * @param int $position Cursor
     */
    public function setCurrentPosition($position) {
        $this -> currentPosition = $position;
    }

    /**
     * Get average width of table column
     *
     * @param array[][] $table Table
     * @used-by $this::drawTable()
     *
     * @return array[] average widths
     */
    private function getTableColumnsAverageWidths($table, $params = false) {
        $widths = array();
	
        foreach ($table as $rowset) {
            foreach ($rowset as $i => $col) {
                //if($i == 0){
                //  $font_style = "b" ;
                //  continue;
                //}
                $font_style = "n";
                if (empty($widths[$i]))
                    $widths[$i] = 0;
                if ($params) {
                    if (count($rowset) <= 10) {
                        $widths[$i] += $this->widthForStringUsingFontsize(trim($col), Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormat) + 20;
                    } else {
                        $widths[$i] += $this->widthForStringUsingFontsize(trim($col), Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormat) + 5;
                    }
                } else {
                    $widths[$i] += $this->widthForStringUsingFontsize(trim($col), Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatDiscription) + 20;
                }
            }
        }
        foreach ($table[0] as $i => $col){
            $widths[$i] = $widths[$i] / count($table);
        }
	
        return $widths;
    }

    /**
     * Get maximal width of table column
     *
     * @param array[][] $table Table
     * @used-by $this::drawTable()
     *
     * @return array[] max widths
     */
    private function getTableColumnsMaxWidths($table, $params = false) {

        $widths = array();

        if( count($table) != 1 ){
            foreach ($table[0] as $col){
                $widths[] = 0;
            }
        }

        foreach ($table as $j => $rowset){
            foreach ($rowset as $i => $col) {
                if($j == 0 && count($table) != 1 ){
                    continue;
                }
                if($params){
                    if (count($rowset) <= 10) {
                        $widths[$i] = max(array($this->widthForStringUsingFontsize(trim($col), Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormat) + 20 , $widths[$i]));
                    }else{
                        $widths[$i] = max(array($this->widthForStringUsingFontsize(trim($col), Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormat) + 5 , $widths[$i]));
                    }
                }else{
                    $widths[$i] = max(array($this->widthForStringUsingFontsize(trim($col), Model_Static_Fonts::get("Arial Narrow"), $this->fontSizeFormatDiscription) + 15, $widths[$i]));
                }
            }
        }

        //Zend_Debug::dump($widths);die();
        return $widths;
    }
    
    /**
     * Width and height of side icons
     */
    const SIDEICON_SIZE = 39.5;
    
    /**
     * Drawing side icons ( index icons )
     * 
     * @param string $active Active icon
     */
    // В зависимости от параметров, мы выбираем папку с картинками, их размер, их позиционирование и добавляем на страницу.
    public function drawSideIcons($active = NULL) {
        
        $categoriesModel = new Model_DbTable_Categories();
	
        foreach ( $this->SIDEICONS as $i=>$icon ){
		if ( $icon == $active && $this->pageFormat == "A4" && $this->print ) $icon .= '.activebz.png';
		elseif ( $this->pageFormat == "A4" && $this->print  ) $icon .= '.a4.png';
		elseif ( $icon == $active && $this->pageFormat == "A4" && !$this->print) $icon .= '.active.a4.png'; 
		elseif ( $this->pageFormat == "A4" && !$this->print ) $icon .= '.a4.small.png'; 
		elseif ( $icon == $active ) $icon .= '.active.png';           
		else $icon .= '.png';
		
		if($this->print && $this->pageFormat == "A4"){
			$ss1 = $this::SIDEICON_SIZE - 14;
			$ss2 = $this::SIDEICON_SIZE + 14;
		}else{
			$ss1 = $this::SIDEICON_SIZE;
			$ss2 = $this::SIDEICON_SIZE;
		}
		if (file_exists(APPLICATION_ROOT . '/files/pdf/sideIcons/'.$icon)) {
			
			$this->drawPic(APPLICATION_ROOT . '/files/pdf/sideIcons/'.$icon,
			       ($this->getPageNumber() % 2) ? $this -> getWidth() + $this -> MARGIN['right'] - $ss2 : -$this -> MARGIN['left'] + $ss2,
			       ($this->pageFormat == "A4")? $this->getHeight() - 5 - $this::SIDEICON_SIZE*$i  - $i*29:$this->getHeight() - $this::SIDEICON_SIZE*$i,
			       ($this->pageFormat == "A4")?(($this->getPageNumber() % 2) ? $ss2 : -$ss2):(($this->getPageNumber() % 2) ? $this::SIDEICON_SIZE : -$this::SIDEICON_SIZE),
			       ($this->pageFormat == "A4")?$this::SIDEICON_SIZE + 30:$this::SIDEICON_SIZE, 0, 0);
		}
             
        }
        
    }

}
//90,91,92,93,94,95,96,97,98,100,101,1520,1573,102,103,104,83,106,108,87,116
//960,961,962,963,964,965,966,967,968,969,976,977,978,979,980,981,982,983,984,985,986,987,988,989,990,991,1406
//960,961,962,963,964,965,966,967,968,969,976,977,978,979,980,981,982,983,984,985,986,987,988,989,990,991,1406,958,959,973,974,975
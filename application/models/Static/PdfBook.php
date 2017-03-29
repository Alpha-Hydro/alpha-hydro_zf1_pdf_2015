<?php

error_reporting(E_ALL);

/**
 * PDF Book
 *
 * @author Bakin Vlad
 * @package PDF Generation
 * @category pdf
 */
GLOBAL $OLD_CATEGORY;

/**
 * Pdf generator for product or category
 *
 * @property string old_category
 * @property int category_page
 * @property int product_page
 * @package PDF Generation
 * @author Bakin Vlad
 */
class Model_Static_PdfBook {
	/**
	 * Log instance
	 */
	 public $logger;

	/**
	 * Log instance
	 */
	 public $logWriter;
	 
	/**
	 * Except this products from generation by id
	 */
	public $exceptionProductList = array();	 
	     	
    /**
     * Current product category
     */
    protected $category;
    
    /**
     * Zend_Pdf book
     */
    protected $book;

    /**
     * Current page
     *
     * @var Model_Static_PdfPage
     *
     */
    protected $page;
    
    /**
     * Page margins, left and right swap each page
     */
    protected $margins = array();
    
    /**
     * Current active side icon, NULL - don't draw side icons
     */
    protected $sideIconActive = NULL;
    
    /*
     * size page str $format_size 
     */
    protected $format_size;
    /*
     * format page str $format 
     */
    protected $format;

    /*
     * Добавлены дополнительно
     */
    //protected $print;
    //protected $old_category;
    //protected $category_page;

    /**
     * Create new book
     *
     * @param string $format
     * @param bool $print
     * @internal param int $page First page
     * @internal param bool $init Initialize page? draw HR
     *
     */
    
    //В зависимости от формата выставляем значения для объекта.
    public function __construct( $format, $print = false) {
	    if ($format == 'A4'){
		   
		    if( $print == 'true' ) $print = TRUE;
		    else $print = FALSE;
		    if($print){
			    $this->format_size = '625:872:';
		    }
		    else{
			    $this->format_size = '595:842:';
		    }
		    $this->format = 'A4';
		    $this->print = $print;
	    }
	    elseif($format == 'A5'){
		    $this->format_size = '419:595:';
		    $this->format = 'A5';
		    $this->print = $print;
	    } 
    }
    
    protected function createBook($page = 1, $init = true) {
        $this->book = new Zend_Pdf();
        $this->page = new Model_Static_PdfPage($page, $init, $this->margins, $this->format, $this->format_size, $this->print);

        return $this->page;
    }
    
    /**
     * Swap margins
     * 
     * @param array $margins Margins to swap
     * 
     * @return array Swapped margins
     */
    private function swapMargins( $margins ){
        $left = $margins['left'];
        $margins['left'] = $margins['right'];
        $margins['right'] = $left;
        
        return $margins;
    }
    
    /**
     * Add new page to book
     * 
     * @param bool $init Initialize page by 0 offset
     * 
     * @return Model_Static_PdfPage Added page
     */
    protected function addPage($init = true) {
            
        if ( $this->sideIconActive )
             $this->page->drawSideIcons($this->sideIconActive );
               
        $this->book->pages[] = $this->page;
        
        $this->page = new Model_Static_PdfPage($this->page->getPageNumber() + 1, $init, $this->swapMargins( $this -> page -> getMargins() ), $this->format, $this->format_size, $this->print );
       
        return $this->page;
    }
    
    /**
     * Pop last page from book
     * 
     * @return Model_Static_PdfPage Last page
     */
    protected function lastPage() {

        $this -> page = array_pop($this->book-> pages);

        return $this->page;
    }
    
    /**
     * Size of product image
     */
    const IMAGE_SIZE = 75;


    /**
     * returns full image path+filename of product image
     *
     * @param string $filename
     * @return string fullpath
     *
     * @api
     */
    public function getProductImageFullpath($filename){
        if($this ->print){
            $path = APPLICATION_ROOT . '/files/images/product_tiff/' . substr($filename, 0, strripos($filename, ".")).'.tif';
        } else {
            $path = APPLICATION_ROOT . '/files/images/product/' . $filename;
        }
        return $path;
    }

    
    /**
     * Draw product to book
     * Creates new if book isn't created
     * 
     * @param Zend_Db_Table_Row $product Product
     * 
     * @var string $DEBUG Enable auto-calculate height debug
     * 
     * @return Zend_Pdf Generated book
     * 
     * @api
     */
    
    //Функция рендеринга продуктов.
    public function byProduct($product, $for_catalog = false) {
	
        $category =  $product->findManyToManyRowset("Model_DbTable_Categories", "Model_DbTable_CategoryXref") -> current();
        if ( $category )
        	$this->category = $category->getBcnName();
        else 
        	$this->category = "Неизвестно";


        $productParams = $product->getParams();

        if (!$for_catalog) {
            $this -> margins = array('top' => 35, 'right' => 20, 'bottom' => 35, 'left' => 20);
        }

        if (is_null($this -> book)) {
            $page = $this -> createBook(1, $for_catalog);
        } else {
            $page = $this -> lastPage();
        }

        $offset = ($for_catalog == false) ? 0 : $page -> getHeight() - $page->getCurrentPosition() - 1;


        if (!$for_catalog) {
            $page->setMargins(35,20,35,20);
        }

        $noteStyle = new Zend_Pdf_Style();
        $noteStyle -> setFont(Model_Static_Fonts::get("Arial Narrow"), ($this->format == 'A4')?6.5:4.5);
        $noteStyle -> setLineWidth($page -> getWidth() - 20);

        $style = new Zend_Pdf_Style();
        $style -> setFont(Model_Static_Fonts::get("Arial Narrow"), ($this->format == 'A4')?8.0:6.5);
        $style -> setLineWidth($page -> getWidth());


        $paramsLinesCount = 0;
        foreach ($productParams as $param){
            $paramsLinesCount += count(explode("\n", $param->value));
        }
        $paramsLinesCount++;
        // расчет высоты всего продукта, проверка влезает ли он на страницу, если не влезает смотрим сколько именно не влезает и есть ли субпродукты,
        // которые мы може отрисовать на этой странице и перенести отстаток на другую страницу
	    $productHeightWithoutTable = 10 + max(array(80, ($this->format == 'A4')?$paramsLinesCount * 12: $paramsLinesCount * 8 )) + (($product->description) ? $page->getTextBlockHeight(trim($product -> description), $style, 3) : 0);


        $subproductsModel = new Model_DbTable_Subproducts ();
        $select = $subproductsModel->select()->order('order ASC');
        $subProducts = $product->findDependentRowset ( "Model_DbTable_Subproducts", 'SubproductsRel' , $select );

        $productHeight = 20 + max(array(80, ($this->format == 'A4')?$paramsLinesCount * 12: $paramsLinesCount * 8 )) // images
                         + (($product->description) ? $page->getTextBlockHeight(trim($product -> description), $style, 3) : 0) // description
                         + ((count($subProducts) <= 30) ? ($this->format == 'A4')?count($subProducts) * 12 + 10 :count($subProducts) * 8 + 12 + 15 : 0) // subProducts table
                         + ($product -> note ? $page -> getTextBlockHeight($product -> note, $noteStyle) + 10 : 0); // note*/
        if ($offset) {
            if ($page -> getHeight() - $offset - 10 < ((count($subProducts) <= 30)?$productHeight:intval($productHeight)+40) ){

                if($page->getPageNumber() > 1) {
                    $page -> drawCategory(isset($this->old_category)?$this->old_category:$this->category);
                    $this->old_category = NULL;
                    $page = $this -> addPage();
                    $offset = 0;
                }
            } else {
                if($page->getCurrentPosition() < 700  && $for_catalog) $offset-=5;
                $page -> init($offset);
                $this->old_category = $this->category;
            }
        }else{
            $this->old_category = $this->category;
        }
	

        $this->product_page = $page->getPageNumber();
        
        // DEBUG auto-height calculate
        if ( isset($_REQUEST['DEBUG']) ) {
            $page -> drawHorizontalLine(-20, 575, $page -> getHeight() - $offset, 1, new Zend_Pdf_Color_Html('green'));
            $page -> drawHorizontalLine(-20, 575, $page -> getHeight() - $offset - $productHeight, 2, new Zend_Pdf_Color_Html('red'));
            $page -> drawTextBlock(count($subProducts), -20, $page -> getHeight() - $offset - $productHeight);
        }

        
        // --- block / information

        $page -> setFont(Model_Static_Fonts::get("Franklin Gothic Demi Cond"), 14);
        $page -> drawTextBlock($product -> sku, 5, $page -> getHeight() - $offset);
	
        $page -> setFont(Model_Static_Fonts::get("Franklin Gothic Demi Cond"), 10);
        $page -> drawTextBlock($product -> name, 5, $page -> getHeight() - 10 - $offset);


        // --- block / images
        
        $images = array($product -> image);
        if ($product -> a_images)
            $images[] = $product -> a_images[0];

        $x = 0;

        if ($page -> getPageNumber() % 2 == 0) {    // если картинки справа (иконки слева)
            $images = array_reverse($images);
            // здесь нужно посчитать правильные ширины отступов начала изображений
            $x = $page -> getWidth() - 5;

            foreach ($images as $image) {
                $sizes = $page->picSize($this->getProductImageFullpath($image),$this::IMAGE_SIZE,$this::IMAGE_SIZE,2);
                //$x = $x - $this::IMAGE_SIZE * $sizes[0];
                $x = $x - $this::IMAGE_SIZE ;
                //echo $image." ";
            }
		    $x = $x - 5 * (count($images) - 1) ;
        } else {    // картинки слева - просто задаем базовый отступ по x
		    $x+=5;
	    }


        //echo $x." ";

        $count = 0;
        // count of images (x75)
    	//в зависимости от задачи, выбираем папку с картинками, за это отвечает параметр $print
        foreach ($images as $image) {
            if($this ->print){
                //$c = $page -> drawPic(APPLICATION_ROOT . '/files/images/product_tiff/' . substr($image, 0, strripos($image, ".")).'.tif', $x, $page -> getHeight() - 20 - $offset, $this::IMAGE_SIZE, $this::IMAGE_SIZE, isset($images[1]) ? 1 : 2,1);
                $c = $page -> drawPic($this->getProductImageFullpath($image), $x, $page -> getHeight() - 20 - $offset, $this::IMAGE_SIZE, $this::IMAGE_SIZE, 2,1);
            }else{
                $c = $page -> drawPic($this->getProductImageFullpath($image), $x, $page -> getHeight() - 20 - $offset, $this::IMAGE_SIZE, $this::IMAGE_SIZE, isset($images[1]) ? 1 : 2,1);
            }
            if ($page -> getPageNumber() % 2 == 0) {    // если картинки справа, то не считаем полную ширину 
                $x += $this::IMAGE_SIZE  + 5;
            } else {
                $x += $this::IMAGE_SIZE * $c + 5;
            }
            $count += $c;
        }

        // --- block / params

        $offsetX = ($page -> getPageNumber() % 2) ? $this::IMAGE_SIZE * $count + 15 : 10;
        $tableWidth = $page->getWidth() - $this::IMAGE_SIZE*$count - 25;
        if (count($productParams) > 0) {
            $params = array();
            foreach ($productParams as $productParam) {
                $params[] = array($productParam -> name, str_replace(array(' '.chr('0x0D').chr('0x0A'),chr('0x0D').chr('0x0A')),', ',$productParam -> value));
            }
            $page -> drawTable($params, $offsetX, $page -> getHeight() - 30  - $offset, $tableWidth);
        }


        // --- block / description
        
        if ($product -> description) {
            $style = new Zend_Pdf_Style();
            $style -> setFont(Model_Static_Fonts::get("Arial Narrow"), ($this->format == 'A4')?8.0:6.5);
            $style -> setLineWidth($page -> getWidth());
            if( $for_catalog ){
                $page -> drawTextBlock(str_replace(array('      ','       ','        '),'. ',trim($product->description)), 5, $page -> getCurrentPosition() + 25, $style, 3);//chr('0x0D').chr('0x0A')
                $page -> setCurrentPosition($page -> getCurrentPosition() + 10);
            }else{
                $page -> drawTextBlock(str_replace(array('      ','       ','        '),chr('0x0D').chr('0x0A'),trim($product->description)), 5, $page -> getCurrentPosition() + 15, $style, 3);
                $page -> setCurrentPosition($page -> getCurrentPosition()+15);
            }
	    
        }

        // --- block / sub products

        $params = array();

        /*$subproductsModel = new Model_DbTable_Subproducts ();
        $select = $subproductsModel->select()->order('order ASC');
        $subProducts = $product->findDependentRowset ( "Model_DbTable_Subproducts", 'SubproductsRel' , $select );*/

        $subproductParams = $product->getSubParams();

        foreach ($subProducts as $subProduct) {
            $productParams = $subProduct->getParamsValues();

            $row = array($subProduct -> sku);
            foreach ($productParams as $productParam) {
                $row[] = $productParam -> value;
            }

            $params[] = $row;
        }
 
        if ( count($subProducts) > 0 ){
            $header = array('Название');
            foreach ($subproductParams as $subproductParam)
                $header[] = $subproductParam -> name;
        }

        if ($product -> note)
            $noteHeight = $page -> getTextBlockHeight($product -> note, $noteStyle);
        else
            $noteHeight = 0;
//
//	$noteHeight = 0;

        $curRow = 0;   
        if($params) {
            $page -> setCurrentPosition($page -> getCurrentPosition() + 20); // move table-description
            while ($curRow !== NULL) {
                /** @var array[] $header */
                $curRow = $page->drawTable($params, 0, $page -> getCurrentPosition(), $page -> getWidth() - 5, $noteHeight, $header, $curRow, true, $product -> note);
				
                if ($curRow !== NULL) {
                    $page->drawCategory($this->category);					
		}
//		else {
//	                if ($product -> note && $for_catalog) {					
//				$page -> drawTextBlock($product -> note, 5, $page -> getCurrentPosition() + 24, $noteStyle);
//			}elseif($product -> note){
//				$page -> drawTextBlock($product -> note, 5, $page -> getCurrentPosition() + 62, $noteStyle);
//			}					
//		}
//		$page -> drawTextBlock($curRow, -20, $page -> getHeight() - $offset - $productHeight);
                $page = $this -> addPage(false);
		if( $curRow !== NULL ) $curRow++;
            }
        }
        else {
            $this -> AddPage(false);
	}
        
        return $this -> book;
    }
    
    /**
     * Get category childs.
     * @param Zend_Db_Table_Row $category Category
     * 
     * @return array Lowest level categories
     */
    private function categoryChilds( $category ){
        
        $childs = $category->findDependentRowset( "Model_DbTable_Categories" );
        
        $categories = array();
        
        if ( count($childs) > 0 ) foreach ( $childs as $child ){
            $categories = array_merge($categories, $this->categoryChilds($child));
        } else 
            return array($category);
				        
        return $categories;
    }

    /**
     * Generate PDF book by category
     *
     * @param Zend_Db_Table_Row $category Category
     * @param int $page Start page number
     *
     * @param null $except
     * @param int $secondTime
     * @param array $return_categories
     * @return Zend_Pdf Generated book
     *
     * @api
     */
    public function byCategory($category, $page = 1, $except = null, $secondTime = 0, $return_categories = array()){
    	$this->logger->log('Start to generate new pdf', Zend_Log::INFO);
	    $this->category_page = $page; // переопределяем номер страницы
        $categories = $this->categoryChilds($category);

		if($except) {
			foreach($except as $exceptId) {				
				foreach($categories as $key=>$category) {					
					if($category->id == $exceptId) {
						unset($categories[$key]);
					}
				}
			}
		}

		//return $categories -> возвращает корректно

        if($this->format == 'A4' && $this->print){
            $this->margins = array('top'=>49, 'right'=>54, 'bottom'=>34, 'left'=>44);
        }
        else{
            $this->margins = array('top'=>35, 'right'=>40, 'bottom'=>20, 'left'=>30);
        }
        
        if ( $page % 2 == 0 ) $this->margins = $this->swapMargins($this->margins);

        //return $this->margins -> возвращает корректно

        $this->createBook($this->category_page);

        $this->addPage(false);
        $productIndexModel = new Model_DbTable_ProductIndex();
		
        $this->sideIconActive = $category->findTopParent()->id;
        
		$i = 0; //счетчик категорий
		$count_category = 0; //счетчик категорий в которых есть товары
		$flag = 0;
		$old_category = array(); //списко категорий которые уже были отрендарины
		$pat = 0; //счетчик частей категорий

        //return count($categories); //-> возвращает корректно
        foreach ($categories as $category) {
            $continue = false;
            $this->logger->log('$return_categories ='.count($return_categories), Zend_Log::INFO);
            foreach ( $return_categories as $cat){
                if($category->id == $cat){
                    //проверка на то что категория уже была
                    $continue = true;
                    break;
                }
            }
            if ($continue){
                //если категория уже была проскакиваем ее
                continue;
            }
            $this->logger->log('Generate subcategory (id:'.$category->id.', '.$i.' from '.count($categories).')', Zend_Log::INFO);
            $products = $category -> findManyToManyRowset("Model_DbTable_Products", "Model_DbTable_CategoryXref");
            //count($products) -> возвращает корректно

            $firstCPage = array();
            $count_exceptions = 0;

            foreach($products as $key => $product) {
                $exceptIt = false;

                if( !is_null( $this->exceptionProductList ) ){
                    foreach($this->exceptionProductList as $exceptProduct) {
                        if($product->id === $exceptProduct) {
                            $count_exceptions++;
                            $exceptIt = true;
                            break;
                        }
                    }
                }

                if(!$exceptIt && !is_null($product)) {
                    $this->logger->log('Generate product ('.$product->id.')', Zend_Log::INFO);
                    $this->byProduct($product, true);

                    // add product index
                    $row = $productIndexModel->createRow();
                    $row->product_id = $product->id;
                    $row->page = $this->product_page;
                    $firstCPage[] = $this->product_page;
                    $row->save();
                }

            }

            if(count($products) <= $count_exceptions || count($products) == 0){
                //если товаров в категории нет пропускаем ее счет
                $this->logger->log('Not products in '.$category->id.' categories', Zend_Log::INFO);
            }else{
                $curCategory = $category->getParent(2);
                if(isset($curCategory->id)) {
                    $this->addCategoryToIndex($curCategory->id, $firstCPage[0], 2);
                }
                 $count_category++;
                 $old_category[] = $category->id;//записываем id категории которую мы уже отрисовали
            }
            ++$i;
            //проверяем на то что это не счет страниц и разделяем информацию на части, по 10 категорий
            if( $count_category != $flag && $count_category%20 == 0 && $secondTime == 1){
                $flag = $count_category;
                $this->logger->log('Save pat - '.$pat, Zend_Log::INFO);
                $pat++;
                $this->finishBook();
                $saveFilename = str_repeat("0", 4 - strlen(($this->category_page))).$this->category_page.'-'.($this->category_page+count($this -> book -> pages)-1).'.pdf';
                $this->logger->log($saveFilename, Zend_Log::INFO);
                $this->book->save(APPLICATION_ROOT .'/files/pdf/book/' . $saveFilename);
                $this->byCategory($category, count($this -> book -> pages) + $this->category_page, $except, 1, $old_category);

            }
        }

        //return count($firstCPage);

	    //если прошли все интерации и $i равна количеству категорий то возвращаем категорию.
        if( $i == count($categories)){
            $this->logger->log('Finishing...', Zend_Log::INFO);
            $this->finishBook();
            $this->book-> end_pages = $this->category_page;
            return $this->book;
        }
    }

	public function addCategoryToIndex($category_id, $page, $depth = 0) {
		$categoryIndexModel = new Model_DbTable_CategoryIndex();
		
		$issetRow = $categoryIndexModel->fetchRow(
			$categoryIndexModel->select()->where('category_id = ?', $category_id)
		);
										
		if(!$issetRow) {		
			$rowC = $categoryIndexModel->createRow();
			$rowC->category_id = $category_id;
			$rowC->page = $page;
			$rowC->depth = $depth;
			$rowC->save();
		}				
	}
    
    /**
     * Count of columns on index page
     */
    const INDEXPAGE_COLUMNS = 3;
    
    /**
     * Count of rows on index page
     */    
    const INDEXPAGE_ROWS    = 130;
    
    /**
     * Adds index pages
     * 
     * @param int $page Start page
     * 
     * @return Zend_Pdf Generated book
     */
    public function IndexPages( $page = 1 ){
    			
        $this->category = "Предметный указатель";
	if($this->format == 'A4' && $this->print){	
	        $this->margins = array('top'=>34, 'right'=>64, 'bottom'=>34, 'left'=>44);
	}else{
		$this->margins = array('top'=>20, 'right'=>50, 'bottom'=>20, 'left'=>30);        
	}
        if ( $page % 2 == 0 ) $this->margins = $this->swapMargins($this->margins);
        
        $productIndexModel = new Model_DbTable_ProductIndex();
        $indexes = $productIndexModel->getAdapter()->fetchAll("SELECT p.sku AS sku, pi.page AS page, pi.product_id as product_id FROM products AS p INNER JOIN productIndex AS pi ON pi.product_id = p.id WHERE p.parent_id IS NULL ORDER BY p.sku" );
							       
        $page = $this->createBook( $page, false );
        $colWidth = $page->getWidth()/$this::INDEXPAGE_COLUMNS - 20;        
        $pointWidth = $page->widthForStringUsingFontsize( '.', Model_Static_Fonts::get('Arial Narrow'), ( $this->format == 'A4')?7.0:4.5);		
        
        for ( $p = 0; $p < intval(count($indexes)/($this::INDEXPAGE_COLUMNS * $this::INDEXPAGE_ROWS ) + 1); $p++ ){
            $page->setFont( Model_Static_Fonts::get('Arial Narrow'), ( $this->format == 'A4')?7.0:4.5 );
            
            for ( $column = 0; $column < $this::INDEXPAGE_COLUMNS; $column++ )
                for ( $row = 0; $row < $this::INDEXPAGE_ROWS; $row++ ){                	
                    $productIndex = (object) $indexes[ $p*$this::INDEXPAGE_COLUMNS*$this::INDEXPAGE_ROWS + $this::INDEXPAGE_ROWS * $column + $row ];
                    
                    /* write */					             
                    if ( !trim($productIndex->sku) ) continue;
                    
                    $curColWidth = $colWidth - $page->widthForStringUsingFontsize( $productIndex->page, Model_Static_Fonts::get('Arial Narrow'),( $this->format == 'A4')?7.0: 4.5);
                    
                    // append `.` to page
                    $textWidth = $page->widthForStringUsingFontsize( $productIndex->sku, Model_Static_Fonts::get('Arial Narrow'),( $this->format == 'A4')?7.0: 4.5);
                    $productIndex->sku .= str_repeat( '.', intval(($curColWidth-$textWidth)/($pointWidth/2)));                 
                    
                    $page->drawTextBlock($productIndex->sku, ($colWidth+20)*$column, $page->getHeight()-$row*6);
//                    $page->drawTextBlock(intval($page->getWidth()).'--'.$colWidth.'--'.(($colWidth+20)*$column + $curColWidth).'--'.intval($curColWidth-$textWidth), ($colWidth+20)*$column, $page->getHeight()-$row*6);
                    $page->drawTextBlock($productIndex->page,
                                        ($colWidth+20)*$column + $curColWidth,
                                        $page->getHeight()-$row*6);										
                }

            $page->drawCategory( $this->category );            
            $page->drawSideIcons( "A" );  
                        
            $page = $this->addPage(false);                
        }
        
        return $this->book;
    }

    // функция генерации страниц нашего продукта. Сделано так, что бы мы могли писать номер страници. Берем картинки  и накладываем на них информацию.
    public function generateOurProduction( $page = 1 , $print = false ){
	$this->category = "Наше производство";
	if($this->format == 'A4' && $this->print){	
	        $this->margins = array('top'=>49, 'right'=>54, 'bottom'=>34, 'left'=>44);
	}else{
		$this->margins = array('top'=>35, 'right'=>40, 'bottom'=>20, 'left'=>30);
	}
        if ( $page % 2 == 0 ) $this->margins = $this->swapMargins($this->margins);
	
	$page = $this->createBook( $page, false );
	$page->print = $print;
		try {
			for( $i = 1; $i < 3; $i++){
				//Здесь происходит изменение картинок нашего продукта.
//				if( $this -> print){
//					$image = Zend_Pdf_Image::imageWithPath(APPLICATION_PATH . '/../files/pdf/ourProduction-'.$i.'.tif');
//					$page->drawSideIcons( "4" );
//					$page->drawImage($image, ((int)$page -> getPageNumber() % 2 == 0)? 24: 10, 0, 618, 871);
//				}else{
					$image = Zend_Pdf_Image::imageWithPath(APPLICATION_PATH . '/../files/pdf/ourProduction-'.$i.'.png');
					$page->drawSideIcons( "4" );
					$page->drawImage($image, ((int)$page -> getPageNumber() % 2 == 0)? 30: 5, 0, 588, 841);
//				}
//				595:842
				$page = $this->addPage(false); 
			}
		} catch (Zend_Pdf_Exception $e) {
			var_dump($e->getMessage());
		}

	    return $this->book;
    }

    public function generateContents( $page = 1 ){
	   $INDEXPAGE_ROWS = $this::INDEXPAGE_ROWS-44;
        $this->category = "Содержание";
	if($this->format == 'A4' && $this->print){	
		$this->margins = array('top'=>34, 'right'=>64, 'bottom'=>34, 'left'=>44);
	}else{
		$this->margins = array('top'=>20, 'right'=>50, 'bottom'=>20, 'left'=>30);
	}
        if ( $page % 2 == 0 ) $this->margins = $this->swapMargins($this->margins);
        
        $categoryIndexModel = new Model_DbTable_CategoryIndex();
        $indexes = $categoryIndexModel->getAdapter()->fetchAll("SELECT c.name AS name, ci.page AS page, ci.category_id as category_id, ci.depth as depth FROM categories AS c INNER JOIN categoryIndex AS ci ON ci.category_id = c.id ORDER BY ci.page, ci.depth" );

        $page = $this->createBook( $page, false );
        $colWidth = $page->getWidth();       
        $pointWidth = $page->widthForStringUsingFontsize( '.', Model_Static_Fonts::get('Arial Narrow'), ( $this->format == 'A4')?9.0: 4.5);
        
        for ( $p = 0; $p < intval(count($indexes)/$INDEXPAGE_ROWS + 1); $p++ ){
            $page->setFont(Model_Static_Fonts::get('Arial Narrow'), ( $this->format == 'A4')?9.0: 4.5);
			$page->setFillColor(new Zend_Pdf_Color_Html("#000000"));
                        
                for($row = 0; $row < $INDEXPAGE_ROWS; $row++) {
                    $productIndex = (object) $indexes[$p*$INDEXPAGE_ROWS + $row];
                    
                    /* write */
                    if ( !trim($productIndex->name) ) continue;
                    
                    $curColWidth = $colWidth - $page->widthForStringUsingFontsize( $productIndex->page, Model_Static_Fonts::get('Arial Narrow'), ( $this->format == 'A4')?9.0: 4.5);                   
                    
					switch($productIndex->depth) {
						case 0:
							$nameFont = 'Arial Narrow Bold';
							$page->setFillColor(new Zend_Pdf_Color_Html("#ef7900"));			
							break;
						case 1:
							$nameFont = 'Arial Narrow Bold';
							$page->setFillColor(new Zend_Pdf_Color_Html("#000000"));
							break;
						case 2:
						default:							
							$nameFont = 'Arial Narrow';
							$page->setFillColor(new Zend_Pdf_Color_Html("#000000"));
							break;															
					}
					
                    // append `.` to page
                    $page->setFont(Model_Static_Fonts::get($nameFont), ( $this->format == 'A4')?9.0: 4.5);
                    $textWidth = $page->widthForStringUsingFontsize( $productIndex->name, Model_Static_Fonts::get($nameFont), ( $this->format == 'A4')?9.0: 4.5);                                    					
					$points = str_repeat(chr('0x2E'), intval(($curColWidth-$textWidth- intval($productIndex->depth*10))/2 - 5 ));					
					
                    $page->drawTextBlock($productIndex->name, intval($productIndex->depth*10), $page->getHeight()-$row*9);
					
					$page->setFont(Model_Static_Fonts::get('Arial Narrow'), ( $this->format == 'A4')?9.0: 4.5);
					$page->setFillColor(new Zend_Pdf_Color_Html("#000000"));
										
                    $page->drawTextBlock($points,
                                        $textWidth+$productIndex->depth*10,
                                        $page->getHeight()-$row*9);			
										
                    $page->drawTextBlock($productIndex->page,
                                        $curColWidth,
                                        $page->getHeight()-$row*9);
                }
            $page->drawCategory($this->category);            
            $page->drawSideIcons("A");  
                        
            $page = $this->addPage(false);                
        }
        
        return $this->book;
    }
    
    /**
     * Finish last page of current book
     * Write category to last page
     * 
     * @return Zend_Pdf Generated book
     */
    public function finishBook(){
    	error_log(0);
        $page = $this->lastPage();
        $page->drawCategory( $this->category );
        
        $this->addPage(false);
		
		if(!is_null($this->logger))$this->logger->log('Finished', Zend_Log::INFO);
        
        return $this->book;
    }
}
?>
<?php
/**
 * Book index controller
 *
 * @author Bakin Vlad
 * @package PDF Generation
 * @category Site
 * @subpackage Controllers
 */

/**
 * Book index controller
 *
 * @package PDF Generation
 * @author Bakin Vlad
 * @subpackage Controllers
 */

class Book_IndexController extends Zend_Controller_Action {

    /**
     *
     */
    const PDFBOOK_DIR = APPLICATION_ROOT."/files/pdf/book";
    const PDFCOVER_DIR = APPLICATION_ROOT."/files/pdf/cover";
	
	protected $secondTime = 0;
	protected $contentPages = 1;

    /**
     * Default action, show PDF book generation form
     *
     */
    public function indexAction() {    
	if ( !Zend_Auth::getInstance()->hasIdentity() ) {
		    throw new Zend_Exception ( "Page not found", 404 );
	    }
        $categoriesModel = new Model_DbTable_Categories();

        $this -> view -> categories = $categoriesModel -> fetchAll($categoriesModel -> select() -> where('`parent_id` IS NULL') -> order("order"));
    }

    /**
     * Show generation console
     *
     * @throws Zend_Exception
     * @internal param array $_POST post form
     */
    public function generateAction() {

	    if ( !Zend_Auth::getInstance()->hasIdentity() ) {
		    throw new Zend_Exception ( "Page not found", 404 );
	    }

        $categoriesModel = new Model_DbTable_Categories();				

        $this -> view -> categories = array();
        $this -> view -> parents = array();
						
		if (null != ($categories = $this -> getRequest() -> getParam("categories"))) {					
			foreach ($categories as $category) {							
				$categoryObj = $categoriesModel -> find($category) -> current();							
				$children = $categoryObj -> findDependentRowset("Model_DbTable_Categories") -> toArray();							
				foreach($children as $child) {
					$categoryIds[] = $child['id'];
				}				
			}
		}
																	
        //if (null != ($categoryIds = $this -> getRequest() -> getParam("categories")))
        foreach ($categoryIds as $categoryId) {
            $category = $categoriesModel -> find($categoryId) -> current();
            $this -> view -> parents[$category -> id] = $category -> name;

            $children = $category -> findDependentRowset("Model_DbTable_Categories") -> toArray();

            if(empty($children)) {
                $children[] = $category->toArray();
            }

            $exceptionList = array();
            if($this->getRequest()->getParam("except")) {
                $exceptionList = explode(',', $this -> getRequest() -> getParam("except"));
                foreach($children as $key=>$child_cat) {
                    foreach($exceptionList as $except) {
                        if($child_cat['id'] == (int) trim($except) || $child_cat['parent_id'] == (int) trim($except)) {
                            unset($children[$key]);
                        }
                    }
                }
            }

            $exceptionProductsList = array();
            if($this->getRequest()->getParam("except_products")) {
                $exceptionProductsList = explode(',', $this -> getRequest() -> getParam("except_products"));
            }

            /* DEBUG */
            /*foreach($children as $key=>$child_cat) {
                if($child_cat['id'] != '25') {
                    unset($children[$key]);
                }
            }*/
            /* END DEBUG */

            $this -> view -> categories = array_merge($this -> view -> categories, $children);
            $this -> view -> except = $exceptionList;
            $this -> view -> exceptProducts = $exceptionProductsList;
        }
    }

    /**
     * Generate selected category book and save it
     *
     * @throws Zend_Exception
     * @internal param int $page Current page
     * @internal param int $id Category ID
     * @internal param string $pageEormat передается пользрвателем либо A5 либо A4
     * @internal param string $print передается пользователем либо true либо false
     */
    public function bycategoryAction() {


        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
            throw new Zend_Exception ( "Page not found", 404 );
        }
        set_time_limit(3600);

        $this->_helper->layout->disableLayout();

        $page = $this -> getRequest() -> getParam("page");
        $categoryId = $this -> getRequest() -> getParam("id");
        $exceptionList = $this->getRequest()->getParam("except");
        $this->secondTime = (int) $this->getRequest()->getParam("second_time");
        $pageFormat = $this->getRequest()->getParam("pageFormat");
        $print = $this->getRequest()->getParam("print");

       /* $page = 3;
        $categoryId = 89;
        $exceptionList = null;
        $this->secondTime = 0;
        $pageFormat = "A4";
        $print = true;*/


        $pdfBook = new Model_Static_PdfBook($pageFormat , $print);
        $categoriesModel = new Model_DbTable_Categories();

		$pdfBook->logger = new Zend_Log();
		$pdfBook->logWriter = new Zend_Log_Writer_Stream(APPLICATION_ROOT.'/book.log');
		$pdfBook->logger->addWriter($pdfBook->logWriter);
		$pdfBook->logger->log('get params', Zend_Log::INFO);

		$category = $categoriesModel->find($categoryId)->current();
		$pdfBook->logger->log('get oject category', Zend_Log::INFO);

		$pdfBook->exceptionProductList = $this->getRequest()->getParam("except_products");
        $pdfBook->logger->log('get params product', Zend_Log::INFO);

        //передаем id категории и запускаем процесс генерации данной категории
        $book = $pdfBook->byCategory($category, $page, $exceptionList,$this->secondTime);
        $pdfBook->logger->log('Return generated pdfBook', Zend_Log::INFO);

        /*try{
            $book = $pdfBook->byCategory($category, $page, $exceptionList,$this->secondTime);
            $pdfBook->logger->log('Return generated pdfBook', Zend_Log::INFO);
            $this->view->assign([
                'page' => $page,
                'categoryId' => $categoryId,
                'exceptionList' => $exceptionList,
                'secondTime' => $this->secondTime,
                'pageFormat' => $pageFormat,
                'print' => $print,
                'category' => $category,
                'exceptionProductList' => $pdfBook->exceptionProductList,
                'book' => $book,
            ]);
        }catch (ErrorException $exception){
            $pdfBook->logger->log($exception->getMessage(), Zend_Log::ALERT);
        }*/

		$topParent = $category->findTopParent();
		$pdfBook -> addCategoryToIndex($topParent->id, $page, 0);
		$pdfBook -> addCategoryToIndex($category->parent_id, $page, 1);

		//завершение  генерации одной категории
		$this -> view -> nextpage = $book -> end_pages;
		$this -> view -> endpage = $this -> view -> nextpage + count($book -> pages);
		$saveFilename = str_repeat("0", 4 - strlen($this -> view -> nextpage)). $this -> view -> nextpage . '-' . ($this -> view -> endpage - 1) . '.pdf';
		$pdfBook->logger->log('Saving pages', Zend_Log::INFO);


		if($this->secondTime == 1) {
			$book->save($this::PDFBOOK_DIR . '/' . $saveFilename);
		}
    }

	// генерация контента всего каталога
	public function generateContents($startPage = 3, $pageFormat = 'A5', $print = false) {
		if ( !Zend_Auth::getInstance()->hasIdentity() ) {
		    throw new Zend_Exception ( "Page not found", 404 );
		}
		$pdfBook = new Model_Static_PdfBook($pageFormat, $print);
		
		$book = $pdfBook -> generateContents($startPage);
		$this->contentPages = count($book -> pages);
		$this -> view -> nextpage = $startPage + count($book -> pages);
		
		$saveFilename = 'contents-'.$startPage.'.pdf';
		
		$book -> save($this::PDFBOOK_DIR . '/' . $saveFilename);
	}

    /**
     * Finish currently generating book
     * Add product indexes pages
     *
     * @throws Zend_Exception
     * @internal param int $page Current page
     */
    public function finishbookAction() {
	if ( !Zend_Auth::getInstance()->hasIdentity() ) {
		throw new Zend_Exception ( "Page not found", 404 );
	}
        $this -> _helper -> layout -> disableLayout();
        $page = $this -> getRequest() -> getParam("page");
	$startPage = $this -> getRequest() -> getParam("startPage");
	$pageFormat = $this->getRequest()->getParam("pageFormat");	
	$print = $this->getRequest()->getParam("print");
	$pdfBook = new Model_Static_PdfBook($pageFormat, $print);
	//если формат А4 то мы добавляем дополнительные файлы в конечный архив и также рендерим страници " наше предложение" с нужной номерацией
	if($pageFormat == "A4"){
		$bookOurProduction = $pdfBook -> generateOurProduction($page, $print);
		$page += 2;
		$saveFilenameOurProduction = "ourProduction.pdf";
		$bookOurProduction -> save($this::PDFBOOK_DIR . '/' . $saveFilenameOurProduction);
		
		$dir  = $this::PDFCOVER_DIR;
		$files = scandir($dir);
		//перебераем все файлы которые числяться как добавочные и добавляем их в архив
		foreach($files as $file){
			if(is_dir($file)) continue;
			$front = Zend_Pdf::load($this::PDFCOVER_DIR. '/' .$file);
			$front -> save($this::PDFBOOK_DIR. '/' .$file);
		}
	}
	
        $book = $pdfBook -> IndexPages($page);
        $this -> view -> nextpage = $page + count($book->pages);
		
        $saveFilename = str_repeat("0", 4 - strlen($page)) . $page . '-' . $this -> view -> nextpage . '.pdf';
        
        $book -> save($this::PDFBOOK_DIR . '/' . $saveFilename);
		
		if($this->secondTime == 0) {
			$this->generateContents($startPage, $pageFormat, $print);
		}
		
		$this -> view -> page = $this->contentPages + $startPage;
		
		$this->zipResults();
    }

    /**
     * Prepare to new PDF book
     * clear folder and indexes table
     */
    public function newbookAction() {
	if ( !Zend_Auth::getInstance()->hasIdentity() ) {
		throw new Zend_Exception ( "Page not found", 404 );
	}
        $this -> _helper -> layout -> disableLayout();
        // clear indexes
        $categoryIndex = new Model_DbTable_CategoryIndex();               
        $productIndex = new Model_DbTable_ProductIndex();
		$categoryIndex -> truncate();
        $productIndex -> truncate();

        // clear folder
        $files = scandir($this::PDFBOOK_DIR);
        foreach ($files as $file) {
            $file = $this::PDFBOOK_DIR . '/' . $file;

            if (is_file($file))
                unlink($file);
        }
    }
	
	public function zipResults() {
	if ( !Zend_Auth::getInstance()->hasIdentity() ) {
	    throw new Zend_Exception ( "Page not found", 404 );
	}
		$path = $this::PDFBOOK_DIR;
		$zip = new ZipArchive;
		$zip->open($path.'catalog.zip', ZipArchive::CREATE);
		if (false !== ($dir = opendir($path)))
		     {
		         while(false !== ($file = readdir($dir)))
		         {
		             if ($file != '.' && $file != '..')
		             {
		             	$zip->addFile($path.DIRECTORY_SEPARATOR.$file, $file);
		             }
		         }
		     }
		     else
		     {
		         die('Can\'t read dir');
		     }
		$zip->close();									
	}

}
?>
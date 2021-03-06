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
     * @var Model_DbTable_Categories()
     */
    protected $_categoriesModel;

    /**
     * @var Zend_Config
     */
    protected $config;

	public function init()
    {
        if ( !Zend_Auth::getInstance()->hasIdentity() ) {
            throw new Zend_Exception ( "Page not found", 404 );
        }

        $this->_categoriesModel = new Model_DbTable_Categories();
        $this->config = new Zend_Config(require APPLICATION_PATH.'/config/pdf_catalog.php');
    }

    /**
     * Default action, show PDF book generation form
     *
     */
    public function indexAction() {

        $this->view->assign([
            'categories' => $this->_categoriesModel->fetchAll(
                $this->_categoriesModel
                    ->select()
                    ->where('parent_id IS NULL OR parent_id = 0')
                    ->order("order")
            ),
            'except_category' => $this->config->get('except_category')->toArray(),
            'except_product' => $this->config->get('except_product')->toArray(),
        ]);
    }

    /**
     * Show generation console
     *
     * @throws Zend_Exception
     * @internal param array $_POST post form
     */
    public function generateAction() {

        $categoryIds = [];
        if (null != ($categories = $this -> getRequest() -> getParam("categories"))) {
			foreach ($categories as $category) {							
				$categoryObj = $this->_categoriesModel -> find($category) -> current();
				$children = $categoryObj -> findDependentRowset("Model_DbTable_Categories") -> toArray();							
				foreach($children as $child) {
					$categoryIds[] = $child['id'];
				}				
			}
		}

        $exceptionList = [];
        $exceptionProductsList = [];
        $categoriesChild = [];
        $parents = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->_categoriesModel->find($categoryId)-> current();
            $parents[$category->id] = $category->name;
            $children = $category->findDependentRowset("Model_DbTable_Categories") -> toArray();

            if(empty($children)) {
                $children[] = $category->toArray();
            }

            if($this->getRequest()->getParam("except")) {
                $exceptionList = explode(',', $this->getRequest()->getParam("except"));
                foreach($children as $key => $child_cat) {
                    foreach($exceptionList as $except) {
                        if($child_cat['id'] == (int) trim($except) || $child_cat['parent_id'] == (int) trim($except)) {
                            unset($children[$key]);
                        }
                    }
                }
            }

            if($this->getRequest()->getParam("except_products")) {
                $exceptionProductsList = explode(',', $this->getRequest()->getParam("except_products"));
            }

            $categoriesChild = array_merge($categoriesChild, $children);

        }

        $this->view->assign([
            'categories' => $categoriesChild,
            'except' => $exceptionList,
            'exceptProducts' => $exceptionProductsList,
            'parents' => $parents,
        ]);
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

        set_time_limit(3600);

        $this->_helper->layout->disableLayout();

        $page = ($this -> getRequest() -> getParam("page"))
            ? $this -> getRequest() -> getParam("page")
            : 3;
        $categoryId = ($this -> getRequest() -> getParam("id"))
            ? $this -> getRequest() -> getParam("id")
            : 779;
        $exceptionList = ($this->getRequest()->getParam("except"))
            ? $this->getRequest()->getParam("except")
            : null;
        $this->secondTime = ($this->getRequest()->getParam("second_time"))
            ? (int) $this->getRequest()->getParam("second_time")
            : 0;
        $pageFormat = ($this->getRequest()->getParam("pageFormat"))
            ? $this->getRequest()->getParam("pageFormat")
            :"A4";
        $print = ($this->getRequest()->getParam("print"))
            ? $this->getRequest()->getParam("print")
            : true;


        $pdfBook = new Model_Static_PdfBook($pageFormat , $print);

		$pdfBook->logger->log('get params', Zend_Log::INFO);

		$category = $this->_categoriesModel->find($categoryId)->current();
		$pdfBook->logger->log('get oject category', Zend_Log::INFO);

		$pdfBook->exceptionProductList = $this->getRequest()->getParam("except_products");
        $pdfBook->logger->log('get params product', Zend_Log::INFO);

        //передаем id категории и запускаем процесс генерации данной категории
        $book = $pdfBook->byCategory($category, $page, $exceptionList,$this->secondTime);
        $pdfBook->logger->log('Return generated pdfBook', Zend_Log::INFO);

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
		$pdfBook = new Model_Static_PdfBook($pageFormat, $print);
		
		$book = $pdfBook->generateContents($startPage);
		$this->contentPages = count($book -> pages);
		$this->view->nextpage = $startPage + count($book->pages);
		
		$saveFilename = 'contents-'.$startPage.'.pdf';
		
		$book->save($this::PDFBOOK_DIR . '/' . $saveFilename);
	}

    /**
     * Finish currently generating book
     * Add product indexes pages
     *
     * @throws Zend_Exception
     * @internal param int $page Current page
     */
    public function finishbookAction() {

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
	
        $book = $pdfBook->IndexPages($page);
        $this->view->nextpage = $page + count($book->pages);
		
        $saveFilename = str_repeat("0", 4 - strlen($page)) . $page . '-' . $this->view->nextpage . '.pdf';
        
        $book->save($this::PDFBOOK_DIR . '/' . $saveFilename);
		
		if($this->secondTime == 0) {
			$this->generateContents($startPage, $pageFormat, $print);
		}
		
		$this->view->page = $this->contentPages + $startPage;
		
		$this->zipResults();
		$pdfBook->logger->log("Happy End!!!", Zend_Log::INFO);
    }

    /**
     * Prepare to new PDF book
     * clear folder and indexes table
     */
    public function newbookAction() {

        $this->_helper->layout-> disableLayout();
        // clear indexes
        $categoryIndex = new Model_DbTable_CategoryIndex();               
        $productIndex = new Model_DbTable_ProductIndex();
		$categoryIndex->truncate();
        $productIndex->truncate();

        // clear folder
        $files = scandir($this::PDFBOOK_DIR);
        foreach ($files as $file) {
            $file = $this::PDFBOOK_DIR . '/' . $file;

            if (is_file($file))
                unlink($file);
        }
    }
	
	public function zipResults() {

		$path = $this::PDFBOOK_DIR;
		$zip = new ZipArchive;

		$file_name = 'catalog_'.date("Ymd").'.zip';
		$zip->open($path.$file_name, ZipArchive::CREATE);
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
		$this->view->zipFile = $file_name;
	}

}
?>
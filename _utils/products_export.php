<?
$debug =  isset($_GET['debug']);
error_reporting(E_ALL);

define('HOST', 'http://alpha-hydro.com');
define('STORE_DIR', 'productsExportImages');
define('ROOT', realpath(__DIR__ . '/../'));
$config = simplexml_load_file(ROOT . '/application/config/application.xml');
$dbConfig = $config->production->resources->db->params;

$conn = mysql_connect($dbConfig->host, $dbConfig->username, $dbConfig->password);

if (!$conn) {
    die("Не удалось подключиться к серверу БД");
}

$storeDirPath = ROOT . '/_utils/' . STORE_DIR;
if (file_exists($storeDirPath)) {
    // Очистим директорию с фотографиями для последуюего наполнения
    $list = scandir(ROOT . '/_utils/' . STORE_DIR);
    foreach($list as $file) {
        unlink(ROOT . '/_utils/' . STORE_DIR . '/' . $file);
    }
} else {
    mkdir(ROOT . '/_utils/' . STORE_DIR);
    chmod(ROOT . '/_utils/' . STORE_DIR, 0775);
}


// Соберём новые данные
if (mysql_select_db($dbConfig->dbname, $conn)) {
    $query = 'SELECT sp.sku, p.image, p.a_images, cx.category_id, p.id FROM subproducts sp
        JOIN products p ON p.id = sp.parent_id
        JOIN categories_xref cx ON cx.product_id = p.id';

    $result = mysql_query($query, $conn);

    $data = $copied = array();
    $copiedCounter = 0;
    while ($row = mysql_fetch_row($result)) {
        list($name, $image, $images, $catId, $id) = $row;

        $name = str_replace(' ', '', $name);
        $url = HOST . '/catalog/products/view/category/' . $catId . '/id/' . $id;

        if (!empty($images)) {
            $images = unserialize($images);
            if (!empty($images[0])) {
                $image = $images[0];
            }
        }
        $imgPath = ROOT . '/files/images/product/' . $image;
        $storePath = ROOT . '/_utils/' . STORE_DIR . '/';

        if (file_exists($imgPath)) {
            if (in_array($imgPath, $copied)) continue;

            $info = pathinfo($imgPath);
            copy($imgPath, $storePath . $name . '.' .$info['extension']);
            $copied[] = $imgPath;
            $copiedCounter++;
        }

        $data[] = array(
            $name, $url
        );
    }
}

if (count($data)) {
    if ($debug) {
        echo 'строк: ' . count($data) . '<br />';
        echo 'изображений скопировано: ' . $copiedCounter . '<br />';
    } else {
        $output = fopen("php://output",'w') or die("Can't open php://output");
        header("Content-Type:application/csv");
        header("Content-Disposition:attachment;filename=productsList.csv");
        foreach($data as $row) {
            fputcsv($output, $row, ';');
        }
        fclose($output) or die("Can't close php://output");
    }
} else {
    echo 'Что-то не так';
}

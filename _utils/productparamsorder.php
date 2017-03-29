<?
// Скрипт выставляет начальные значения поля order в таблице product_params
// исходя из порядка, установленного сортировкой по полю name при выборке в пределах одного product_id

// убрать для запуска
die();

error_reporting(E_ALL);
echo "Старт<br>";



$conn = mysql_connect("localhost","alphahydro","alphahydro556");
if (!$conn) {die("Не удалось подключиться к серверу БД");}

if (mysql_select_db("alphahydro",$conn)) {
	echo "База выбрана<br>";

    $query = "SELECT DISTINCT `product_id` FROM `product_params`";
    $result = mysql_query($query,$conn);
    while ($row = mysql_fetch_row($result))
    {
        $query = "SELECT `id` FROM `product_params` WHERE `product_id`=".$row[0]." ORDER BY name ASC";
        $resultprops = mysql_query($query,$conn);
        $order = 1;
        while ($rowprop = mysql_fetch_row($resultprops))
        {
            $query = "UPDATE `product_params` SET `order`=".$order." WHERE `id`=".$rowprop[0];
            echo mysql_query($query,$conn);
            //echo $query;
            echo "<br>\n";
            $order++;
        }

    }

}

echo "Конец<br>";


?>


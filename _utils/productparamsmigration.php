<?
// Скрипт производит миграцию в новую структуру БД (с таблицами subproducts и выведенными в отдельные таблицы параметрами)

// убрать для запуска
die();

error_reporting(E_ALL);
echo "Старт<br>";



$conn = mysql_connect("localhost","alphahydro","alphahydro556");
if (!$conn) {die("Не удалось подключиться к серверу БД");}

if (mysql_select_db("alphahydro2015",$conn)) {  // НОВАЯ БАЗА!!!
	echo "База выбрана<br>";

    // чистим таблицы
    $query = "TRUNCATE subproducts";
    mysql_query($query,$conn);
    $query = "TRUNCATE subproduct_params";
    mysql_query($query,$conn);
    $query = "TRUNCATE subproduct_params_values";
    mysql_query($query,$conn);
    // убираем Null в поле order у продуктов
    $query = "UPDATE products SET `order` = 0 WHERE `order` IS NULL";
    mysql_query($query,$conn);

    // выбрать все основные продукты
    $query = "SELECT `id` FROM `products` WHERE `parent_id` IS NULL";
    $result = mysql_query($query,$conn);
    while ($row = mysql_fetch_row($result))
    {
        // выбрать все его подпродукты
        $query = "SELECT `id`,`parent_id`,`sku`,`name`,`add_date`,`mod_date`,`order` FROM `products` WHERE `parent_id`=".$row[0]." ORDER BY `order` ASC";
//        echo $query."</br>";
        flush();
        $resultsubproducts = mysql_query($query,$conn);
        if ($resultsubproducts == false) {
            echo "Нет подпродуктов: <span style='color: red'>".$query."</span></br>";
        }

        $first_subproduct = true;
        $subproduct_props = array();
        while ($rowsubproduct = mysql_fetch_row($resultsubproducts)) {
            // заносим подпродукт в новую таблицу
            $query = "INSERT INTO `subproducts` VALUES (NULL,".$rowsubproduct[1].",'".$rowsubproduct[2]."','".$rowsubproduct[3]."','".$rowsubproduct[4]."','".$rowsubproduct[5]."',".$rowsubproduct[6].")";
            mysql_query($query,$conn);
            $subproduct_id = mysql_insert_id();
            //echo $query."</br>";

            // заносим в таблицу subproduct_params и в массив свойства первого подпродукта
            if ($first_subproduct) {
                $query = "SELECT `id`,`name`,`order` FROM `product_params` WHERE `product_id` = ".$rowsubproduct[0]." ORDER BY `order` ASC";
                echo $query."</br>";
                $resultsubproduct_props = mysql_query($query,$conn);
                while ($rowsubproduct_props = mysql_fetch_row($resultsubproduct_props)) {
                    $query = "INSERT INTO `subproduct_params` VALUES (NULL,".$row[0].",'".$rowsubproduct_props[1]."',".$rowsubproduct_props[2].")";
                    //echo $query."</br>";
                    mysql_query($query,$conn);
                    $subproduct_prop_id = mysql_insert_id();    // внутренний id свойства
                    $subproduct_props[] = array($subproduct_prop_id, $rowsubproduct_props[1], $rowsubproduct_props[2]);
                }
            }

            // заносим в таблицу subproduct_params_values значения свойств подпродуктов
            foreach ($subproduct_props as $prop) {
                // ищем свойство текущего подпродукта с нужным именем
                $query = "SELECT `id`,`name`,`value`,`order` FROM `product_params` WHERE `product_id` = ".$rowsubproduct[0]." AND `name` = '".$prop[1]."'";
                $resultsubproduct_props = mysql_query($query,$conn);
                if ($rowsubproduct_props = mysql_fetch_row($resultsubproduct_props)) {
                    $query = "INSERT INTO `subproduct_params_values` VALUES ($subproduct_id,".$prop[0].",'".$rowsubproduct_props[2]."')";
                    mysql_query($query,$conn);
                    //echo $query."</br>";
                } else {
                    echo "Не найдено: <span style='color: red'>".$query."</span></br>";
                }

            }


            $first_subproduct = false;

            // грохнуть параметры подпродуктов из таблицы product_params
            $query = "DELETE FROM `product_params` WHERE `product_id` = ".$rowsubproduct[0].";";
            mysql_query($query,$conn);

        }

        //echo "</br>";
        //var_dump($subproduct_props); die();

    }

    // грохнуть подпродукты продукта из таблицы products
    $query = "DELETE FROM `products` WHERE `parent_id` = ".$row[0].";";
    mysql_query($query,$conn);

    // удалить продукты в products, которые имеют родителя (ведь таких уже быть не должно)
    $query = "DELETE FROM `products` WHERE `parent_id` IS NOT NULL";
    mysql_query($query,$conn);

    // удалить свойства в product_params, которые не ссылаются на существующий в таблице products продукт
    $query = "DELETE FROM `product_params` WHERE `product_params`.`id` IN (SELECT * FROM ( SELECT `product_params`.`id` FROM `product_params`  LEFT JOIN `products` ON `product_params`.`product_id` = `products`.`id` WHERE `products`.`id` IS NULL) as id)";
    mysql_query($query,$conn);

}

echo "Конец<br>";


?>


<?
// Скрипт был создан для обновления в БД новых картинок чертежей, которые сделали специалисты Альфы.
// Запуск отработан и не нужен далее.

// убираем когда нужно запустить
die(); 

error_reporting(E_ALL);
echo "Старт<br>";

$ndirct = "images/png_renamed"; 

$nhdl=opendir($ndirct); 
while ($nfile = readdir($nhdl)) { 
	if (($nfile!=".")&&($nfile!="..")) 
        { 
                $filelist[] = $nfile; 
        } 
} 
closedir($nhdl); 


$conn = mysql_connect("localhost","alphahydro","alphahydro556");
if (!$conn) {die("Не удалось подключиться к серверу БД");}

if (mysql_select_db("alphahydro",$conn)) {
	echo "База выбрана<br>";

	foreach ($filelist as $filename)
	{
		$arr1 = explode(".",$filename);
		$arr2 = explode("_",$arr1[0]);
		echo "ID=".$arr2[0]."; SKU=".$arr2[1]."; ";
		$query = "SELECT id, a_images, sku FROM `products` WHERE `id`=".$arr2[0];
		$result = mysql_query($query,$conn);
		if($row = mysql_fetch_row($result)) 
		{
			var_dump ($row);
			$a = array();
			$a[] = $filename;
			$query = "UPDATE `products` SET `a_images`='".serialize($a)."' WHERE `id`=".$arr2[0];
			echo mysql_query($query,$conn);
			echo $query;
			echo "<br>\n";
		}
		
	}
}

echo "Конец<br>";




?>


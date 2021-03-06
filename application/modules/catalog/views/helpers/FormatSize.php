<?php
/**
 *
* @author Vladislav
* @version
*/

/**
 * Format size helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_FormatSize {

	function formatSize($size) {
		$units = array(' Б', ' Кб', ' Мб', ' Гб', ' Тб');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	}
}
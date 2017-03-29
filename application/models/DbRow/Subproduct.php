<?php
/**
 * Row class for Model_DbTable_Subproducts
 *
 * @author Dmitry Bykov
 * @package Default
 * @category Site
 * @subpackage Models
 */


class Model_DbRow_Subproduct extends Zend_Db_Table_Row {

    public function delete(){
        // сначала грохнем параметры подтовара
        $db = $this->getTable()->getAdapter();

        $where = array();
        $where[] = "subproduct_id = ".$this->id;
        $db->delete("subproduct_params_values",$where);

        return parent::delete();
    }



    // значения параметров товара по порядку
    public function getParamsValues(){
        if ($this->parent_id){

            $db = $this->getTable()->getAdapter();
            // SELECT * FROM `subproduct_params_values` INNER JOIN `subproduct_params` ON `subproduct_params_values`.`param_id` =`subproduct_params`.`id` WHERE `subproduct_id` = 44077 ORDER BY `subproduct_params`.`order`
            $query  = $db->select()
                ->from("subproduct_params_values")
                ->join("subproduct_params","subproduct_params_values.param_id = subproduct_params.id")
                ->where('subproduct_id = ?',$this->id)
                ->order('subproduct_params.order ASC');
            //echo $query->__toString();
            $stmt = $db->query($query);
            $params = Array();
            while ($param = $stmt->fetchObject()) {
                $params[] = $param;
            }
            return $params;

        } else {
            return null;
        }
    }

    // сохранение в базу данных новых параметров
    public function saveNewParams($newParams){

        $oldParams = $this->getParamsValues ();

        $db = $this->getTable()->getAdapter();
        // проходим по массиву новых параметров, апдейтим существующие, с нулевым param_id - добавляем.
        for($i = 0; $i < count($newParams); $i++) {
            if ($newParams [$i]["param_id"] != ""){
                // апдейт значения параметра

                // для начала надо проверить существование значения параметра (может быть это новый подпродукт)
                // если можно, заменить это на более зендовое
                $result = $db->fetchRow("SELECT * FROM subproduct_params_values WHERE param_id = ".$newParams [$i]["param_id"]." AND subproduct_id = ".$this->id);
                if ($result) {
                    $data = array(
                        'value' => $newParams [$i]["value"]
                    );
                    $where = array();
                    $where[] = "param_id = ".$newParams [$i]["param_id"];
                    $where[] = "subproduct_id = ".$this->id;

                    $db->update("subproduct_params_values",$data,$where);

                } else {    // параметр для нового подпродукта
                    $data = array(
                        'subproduct_id' => $this->id,
                        'param_id' => $newParams [$i]["param_id"],
                        'value' => $newParams [$i]["value"]
                    );
                    $db->insert("subproduct_params_values",$data);
                }
                // апдейт параметра (наименование и порядок)
                $data = array(
                    'name' => $newParams [$i]["name"],
                    'order' => $newParams [$i]["order"]
                );
                $where = array();
                $where[] = "id = ".$newParams [$i]["param_id"];
                $db->update("subproduct_params",$data,$where);

            } else {
                if ($this->parent_id){
                    // новый параметр
                    $data = array(
                        'product_id' => $this->parent_id,
                        'name' => $newParams [$i]["name"],
                        'order' => $newParams [$i]["order"]
                    );
                    $db->insert("subproduct_params",$data);
                    $newid = $db->lastInsertId();

                    if ($newid) {
                        // запилить этот параметр для всех подпродуктов товара
                        $productsModel = new Model_DbTable_Products ();
                        $parentProduct = $productsModel->find ( $this->parent_id )->current ();
                        if ($parentProduct) {
                            $select = $productsModel->select()->order('order ASC');
                            $allSubproducts = $parentProduct->findDependentRowset("Model_DbTable_Subproducts", 'SubproductsRel', $select);
                            foreach ($allSubproducts as $currentSubproduct){
                                $data = array(
                                    'subproduct_id' => $currentSubproduct->id,
                                    'param_id' => $newid,
                                    'value' => ($this->id == $currentSubproduct->id)?$newParams [$i]["value"]:0
                                );
                                $db->insert("subproduct_params_values",$data);
                            }
                        }
                    }

                }
            }
        }

        // теперь проходим по массиву старых параметров и смотрим какие надо удалить
        for($i = 0; $i < count($oldParams); $i++) {
            $found = false;
            for($j = 0; $j < count($newParams); $j++) {
                if ($oldParams [$i]->param_id == $newParams [$j]["param_id"]){
                    $found = true;
                }
            }
            if (!$found) {
                // удалить параметр с индексом $i
                $where = array();
                $where[] = "param_id = ".$oldParams [$i]->param_id;
                $db->delete("subproduct_params_values",$where);
                $where = array();
                $where[] = "id = ".$oldParams [$i]->param_id;
                $db->delete("subproduct_params",$where);
            }
        }
    }


}
?>
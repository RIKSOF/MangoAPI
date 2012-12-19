<?php

namespace app\models;

class Objects extends \lithium\data\Model {

    public static function count($conditions) {
  
	    $count = self::find('count', array('conditions' => $conditions));

        return array( 'count' => $count );
  
  }

}

?>

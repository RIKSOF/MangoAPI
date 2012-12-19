<?php

namespace app\models;

class Objects extends \lithium\data\Model {


    /**
	 * The `count` method allows you to count documents matching the criteria specified in $conditions
	 *
	 * @param array $conditions The json search query as expected by MongoDB
	 *
	 * @return json object which only has a member count
	 */
    public static function count($conditions) {
  
	    $count = self::find('count', array('conditions' => $conditions));

        return array( 'count' => $count );
  
    }

}

?>

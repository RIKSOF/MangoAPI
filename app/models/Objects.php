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
   
   /**
	 * The `count` method allows you to count documents matching the criteria specified in $conditions
	 *
	 * @param array $conditions The json search query as expected by MongoDB
	 *
	 * @return json object which only has a member count
	 */ 
   public static function findAndDelete( $conditions = array() ) {
    
    $result = array();
    
    // Delete by search criteria only if it exists. Otherwise mong will delete all documents in this collection
    if ( count($conditions) ) {
        $result[] = Objects::remove($conditions);
    }    
    
    return $result;
  }

}

?>

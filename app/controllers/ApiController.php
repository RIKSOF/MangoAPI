<?php

namespace app\controllers;

use app\models\Objects;

class ApiController extends \lithium\action\Controller {

	public function index($id = "", $type = "") {

		$results = "";
		
		if($_REQUEST['q'] && $_REQUEST['q'] == "") { // Normal API

		  if($type == "") { // api/<id>

		    if($id == "") {
		
		      if($_REQUEST['method'] == "post") { // CREATE

	  		    $requestArray = json_decode($_REQUEST['requestJsonString'], true);

		        $object = Objects::create($requestArray);
		    
		        $object->save();
		
			    $lastInsertId = (string) $object->data("_id");
		
		        $results = json_encode(array("_id" => $lastInsertId)); // return insertId
		    
		      }

		    }
		    else { 
		
		      if($_REQUEST['method'] == "get") { // RETRIEVE

		  	    $queryResult = Objects::first(array('conditions' => array("_id" => $id)));
		
		  	    $results = json_encode($queryResult->to('array'));
		  	
		      }
		      else { // UPDATE
		  
	  		    $requestArray = json_decode($_REQUEST['requestJsonString'], true);
			    $reset = ($_REQUEST['reset'] == "true" ? true:false);
			
			    if($reset) { // Delete all fields expect for the ones specified
			
			      // This feature is not implemented yet
			
			      $results = json_encode(array("update" => "failure"));
			
			    }
			    else {
			
			      $success = Objects::update($requestArray, array('_id' => $id));
			  
			      $results = json_encode(array("update" => "success"));
			
			    }
		  
		      }
		  
		    }
		
		  }
		  else { // api/<id>/<type>
		
		    if($_REQUEST['method'] == "post") { // CREATE
		  
	  		  $requestArray = json_decode($_REQUEST['requestJsonString'], true);

		      $object = Objects::create($requestArray);
		    
		      $object->save();
		
			  $lastInsertId = (string) $object->data("_id");

			  // Add the _id to the main object

	  		  $queryResult = Objects::first(array('conditions' => array("_id" => $id)));
		
              $queryResultArray = $queryResult->to('array');
		
              if(!$queryResultArray[$type]) {
            
                $queryResultArray[$type] = array();
            
              }

              array_push($queryResultArray[$type], $lastInsertId);
            
              unset($queryResultArray["_id"]);
            
			  $success = Objects::update($queryResultArray, array('_id' => $id));

			  // 
			
			  $results = json_encode(array("_id" => $lastInsertId)); // return insertId            
		  
		    }
		    else { // RETRIEVE
		  
	  		  $queryResult = Objects::first(array('conditions' => array("_id" => $id)));
		
              $queryResultArray = $queryResult->to('array');
		
              if($queryResultArray[$type]) {
            
			    $subQueryResults = Objects::all(array('conditions' => array("_id" => $queryResultArray[$type])));
		
			    $results = json_encode($subQueryResults->to('array'));
            
              }
              else {

			    $results = json_encode(array());
            
              }
		  		  
  		    }
		  
		  }
		
		}
		else { // Query
		
		  $queryResults = Objects::find('all', array('conditions' => json_decode($_REQUEST['q'], true)));

		  $results = json_encode($queryResults->to('array'));
		
		}
		
		$this->set(compact("results"));
		
		return $this->render(array('layout' => false));
	
	}

	public function to_string() {
		return "";
	}

	public function to_json() {
		return $this->render(array('json' => ''));
	}
}

?>

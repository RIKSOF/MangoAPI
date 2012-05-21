<?php

namespace app\controllers;

use app\models\Objects;

class ApiController extends \lithium\action\Controller {

	public function index($id = "", $type = "") {

		$results = "";

		if($id == "") {
		
		  if($_POST['requestJsonString'] != "") { // CREATE

	  		$requestArray = json_decode($_POST['requestJsonString'], true);

		    $object = Objects::create($requestArray);
		    
		    $object->save();
		
			$lastInsertId = (string) $object->data("_id");
		
		    $results = json_encode(array("_id" => $lastInsertId)); // return insertId
		    
		  }

		}
		else { 
		
		  if($_POST['requestJsonString'] == "") { // RETRIEVE

		  	$queryResult = Objects::first(array('conditions' => array("_id" => $id)));
		
		  	$results = json_encode($queryResult->to('array'));
		  	
		  }
		  else { // UPDATE
		  
	  		$requestArray = json_decode($_POST['requestJsonString'], true);
			$reset = ($_POST['reset'] == "true" ? true:false);
			
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

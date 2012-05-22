<?php

namespace app\controllers;

use app\models\Objects;

class ApiController extends \lithium\action\Controller {

  public function index($id = "", $type = "") {

	// Variable Initializations

    $results = "";

	$typePrefixed = "_".$type;
	
	// Algo

	if($type == "") { // api/<id>
	
	  if(!isset($_REQUEST['q'])) {
	  
		if($id == "") {

		  if($_REQUEST['method'] == "post") { // CREATE

			$insertId = $this->createDocument(json_decode($_REQUEST['requestJsonString'], true));

		    $results = array("_id" => $insertId);
		    		
		  }

		}
		else { 

		  if($_REQUEST['method'] == "get") { // RETRIEVE

	  	    $results = $this->getDocument($id);
	  	
		  }
		  else { // UPDATE
	  
	  		$status = $this->updateDocument($id, json_decode($_REQUEST['requestJsonString'], true));
	  
			$results = array("success" => $status);
	  
		  }
	  
		}

	  }
	  else { // Search
	  
	    $results = $this->searchDocuments(json_decode($_REQUEST['q'], true));

	  }
	  
	}
	else { // api/<id>/<type>

	  if($_REQUEST['method'] == "post") { // CREATE

		$insertId = $this->createDocument(json_decode($_REQUEST['requestJsonString'], true));

		// Get the main object

		$queryResultArray = $this->getDocument($id);
		
		// Add the _id to the main object
		
		if(!isset($queryResultArray[$typePrefixed])) {
		
		  $queryResultArray[$typePrefixed] = array();
		
		}

		array_push($queryResultArray[$typePrefixed], $insertId);
		
	  	$this->updateDocument($id, $queryResultArray);
	  
		// 
	
		$results = array("_id" => $insertId);
	  
	  }
	  else { // RETRIEVE
	  
		$queryResultArray = $this->getDocument($id);
		
		if($queryResultArray[$typePrefixed]) {
		
	      $results = $this->searchDocuments(array("_id" => $queryResultArray[$typePrefixed]));

		}
		else {

	      $results = array();
		
		}
	  		  
      }
	  
	}
		
    $resultsJsonString = json_encode($results);
		
 	$this->set(compact("resultsJsonString"));
		
	return $this->render(array('layout' => false));
	
  }

  public function to_string() {
	
	return "";
  
  }

  public function to_json() {
	
	return $this->render(array('json' => ''));
  
  }
  
  // Custom (private) function 
  
  private function createDocument($data) {
  
    $object = Objects::create($data);

    $object->save();

	return (string) $object->data("_id");
  
  }
  
  private function getDocument($id) {

	$queryResult = Objects::first(array('conditions' => array("_id" => $id)));

    return $queryResult->to('array');
  
  }
  
  private function updateDocument($id, $data) {
  
    if(isset($data["_id"])) {

	  unset($data["_id"]);
	 
	}
  
	return Objects::update($data, array('_id' => $id));

  }
  
  private function searchDocument($conditions) {
  
	$queryResults = Objects::find('all', array('conditions' => $conditions));

	$results = $queryResults->to('array');
  
  }

}

?>

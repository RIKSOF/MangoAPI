<?php

namespace app\controllers;

use app\models\Objects;

class ApiController extends \lithium\action\Controller {

	public function index($id = "", $subType = "") {

		$results = "";

		if($_POST['requestData'] == "") { 
		
		  if($id != "") { // GET
			
			$queryResult = Objects::first(array('conditions' => array("_id" => $id)));
			
			if($subType == "") {
			
				$results = json_encode($queryResult->to('array'));			
			}
			else {

				$subQueryResults = Objects::all(array('conditions' => array("type" => $subType, $queryResult->type."_id" => $id)));
				
				$results = json_encode($subQueryResults->to('array'));
			
			}

		  }
		
		}
		else {
		
		  $requestJson = json_decode($_POST['requestData'], true);

		  $requestJsonId = $requestJson["_id"];
		  
		  if($id == "") {
		  
		    if($requestJsonId == "") { // INSERT (Alone)
		
			  $success = Objects::create($requestJson)->save();
			
			  $results = json_encode(array("success" => true));
		
		    }
		    else { // UPDATE
		  
		      unset($requestJson["_id"]);

		      $success = Objects::update($requestJson, array('_id' => $requestJsonId));
		    
		      $results = json_encode(array("success" => true));

		    }
		  
		  }
		  elseif ($subType != "") { // INSERT (as link to another object)

		    $queryResult = Objects::first(array('conditions' => array("_id" => $id)));
		    
			$requestJson["type"] = $subType;
			$requestJson[$queryResult->type."_id"] = $id;

			$success = Objects::create($requestJson)->save();
			
			$results = json_encode(array("success" => true));
		  
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

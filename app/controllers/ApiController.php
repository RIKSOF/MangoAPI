<?php

//
// Copyright 2012 RIKSOF
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//

namespace app\controllers;

use app\models\Objects;

class ApiController extends \lithium\action\Controller {

  public function index($id = "", $type = "") {

	// Variable Initializations

    $results = "";
    $statusMessage = "";

	$typePrefixed = "_".$type;
	
	// Algo

	if($type == "") { // URL: api/<id>
	
	  if(!isset($_REQUEST['q'])) {
	  
		if($id == "") {

		  if($_REQUEST['method'] == "post") { // CREATE

			$insertId = $this->createDocument(json_decode($_REQUEST['requestJsonString'], true));

		    $statusMessage = array("_id" => $insertId);
		    		
		  }

		}
		else { 

		  if($_REQUEST['method'] == "get") { // RETRIEVE

	  	    $results = $this->getDocument($id);
	  	
		  }
		  else { // UPDATE
	  
	  		$status = $this->updateDocument($id, json_decode($_REQUEST['requestJsonString'], true));
	  
			$statusMessage = array("success" => $status);
	  
		  }
	  
		}

	  }
	  else { // Search
	  
	    $results = $this->searchDocuments(json_decode($_REQUEST['q'], true));

	  }
	  
	}
	else { // URL: api/<id>/<type>

	  if($_REQUEST['method'] == "post") { // CREATE (a new object which refers to the main object)
	  
	    $insertId = $this->createTypedDocument(json_decode($_REQUEST['requestJsonString'], true), $id, $type);

		$statusMessage = array("_id" => $insertId);
	  
	  }
	  else { // RETRIEVE
	  
		$document = $this->getDocument($id);
		
		if(isset($document[$typePrefixed])) {
		
	      $results = $this->searchDocuments(array("_id" => $document[$typePrefixed]));

		}
	  		  
      }
	  
	}

	// Realtime Update Notification
	
	if($results != "") {

      $deviceId = "";
      
      if(isset($_REQUEST['deviceId']) && $_REQUEST['deviceId'] != "") {
      
        $deviceId = $_REQUEST['deviceId'];
      
      }
	
	  if($deviceId != "") {

		// Token
		
	    $tokenId = "";
	    
	    $existingToken = Objects::first(array('conditions' => array("__deviceId" => $deviceId)));
	    
	    if($existingToken) { // Token already exists

		  $existingTokenArray = $existingToken->to('array');

	      $tokenId = $existingTokenArray["_id"];
	      
	    }
	    else{ // Create a new token
	    
	      $tokenId = $this->createDocument(array("__deviceId" => $deviceId));
	    
	    }
	    
	    // Add the Token
	    
	    if($type != "" || isset($_REQUEST['q'])) {
	    
	      foreach ($results as $result) {
	  
			$this->updateTokenList($result, $tokenId);
	  
	      }
	    
	    }
	    else {
	    
	      $this->updateTokenList($results, $tokenId);
	    
	    }
	    
	  }
	
	}

	// Return results
	
	$responseJsonString = "";
	
	if($results != "") {

      $responseJsonString = json_encode($results);
      
    }
    elseif ($statusMessage != "") {
    
      $responseJsonString = json_encode($statusMessage);
    
    }
    else {
    
      $responseJsonString = json_encode(array());
    
    }
		
 	$this->set(compact("responseJsonString"));
		
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

  private function createTypedDocument($data, $mainDocumentId, $type) {
  
  	$typePrefixed = "_".$type;

	$insertId = $this->createDocument($data);

    // Get the main object

	$mainDocument = $this->getDocument($mainDocumentId);
		
	// Add the _id to the main object
		
	if(!isset($mainDocument[$typePrefixed])) {
		
	  $mainDocument[$typePrefixed] = array();
		
	}

	array_push($mainDocument[$typePrefixed], $insertId);

	$mainDocument[$typePrefixed] = array_unique($mainDocument[$typePrefixed]);

  	$this->updateDocument($mainDocumentId, $mainDocument);
	  
	return $insertId;
  
  }
  
  private function getDocument($id) {

	$queryResult = Objects::first(array('conditions' => array("_id" => $id)));

    return $queryResult->to('array');
  
  }
  
  private function updateDocument($id, $changeData, $triggerRealtimeUpdateNotification = true) {
  
    if(isset($changeData["_id"])) {

	  unset($changeData["_id"]);
	 
	}
  
    // Trigget Realtime Update Notification
  
    if($triggerRealtimeUpdateNotification == true) {

      $originalObject = $this->getDocument($id);
      
      $deviceIds = array();
      
      foreach($originalObject["_token"] as $tokenId) {
      
        $tokenObject = $this->getDocument($tokenId);
        
        array_push($deviceIds, $tokenObject["__deviceId"]);
      
      }

	  // Trigger Now
      
      file_put_contents("/home/zeeshan/Desktop/update_notification.txt",

    				    "Notify the following deviceId(s):\n\n".
    				    implode(", ", $deviceIds)."\n\n".
    				    "to update the objects with _id(s):\n\n".
    				    $id."\n\n\n\n\n\n", 

    				    FILE_APPEND);
    				    
    }
  
    //
  
	return Objects::update($changeData, array('_id' => $id));

  }
  
  private function searchDocuments($conditions) {
  
	$objects = Objects::find('all', array('conditions' => $conditions));
	
	return $objects->to('array');
  
  }

  private function updateTokenList($objectArray, $tokenId) {
  
    if(!isset($objectArray["_token"])) {

      $objectArray["_token"] = array();

    }

    array_push($objectArray["_token"], $tokenId);

    $objectArray["_token"] = array_unique($objectArray["_token"]);

    $this->updateDocument($objectArray["_id"], $objectArray, false);
  
  }

}

?>

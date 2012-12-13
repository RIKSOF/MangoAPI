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
use app\models\Queries;

class ApiController extends \lithium\action\Controller {

  public function index($id = "", $type = "", $typedId = "") {

	// Variable Initializations

	$deviceId = (isset($_REQUEST['deviceId']) ? $_REQUEST['deviceId']:"");
	$method = (isset($_REQUEST['method']) ? $_REQUEST['method']:"get");
	$requestJsonString = (isset($_REQUEST['requestJsonString']) ? $_REQUEST['requestJsonString']:"");

	$requestArray = "";
	
    $typePrefixed = "_".$type;
    
    $results = "";
    $statusMessage = "";
    
	// Validate Request
	
	$requestArray = $this->decodeJsonString($requestJsonString);

    if($method == "post" && $requestArray == false) {
      
      $statusMessage = array("error" => "Malformed JSON");
      
	}
	else { // Algo

	  if($type == "") { // URL: api/<id>
	
	    if(!isset($_REQUEST['q'])) {
	  
		  if($id == "") {

		    if($method == "post") { // CREATE

			  $insertId = $this->createDocument($requestArray);

		      $statusMessage = array("_id" => $insertId);

		    }

		  }
		  else { 

		    if($method == "get") { // RETRIEVE

	  	      $results = $this->getDocument($id);
	  	
		    }
		    elseif($method == "post") { // UPDATE
	  
	  		  $status = $this->updateDocument($id, $requestArray);
	  
			  $statusMessage = array("success" => $status);
	  
		    }
		    elseif($method == "delete") { // DELETE
		    
		      $this->deleteDocument($id);
		    
		    }
	  
		  }

	    }
	    else { // Search

		  $qArray = $this->decodeJsonString($_REQUEST['q']);
		  
    	  if($qArray == false) {
      
      		$statusMessage = array("error" => "Malformed JSON");
	
	        //If the user has requested to count number of objects for this query
		  } elseif (isset( $_REQUEST['count'] )){
            
            $results = $this->countDocuments($qArray);
            Queries::create($qArray)->save();
            
            //Or the user has asked to return objects for this query
		  } elseif ( $method == "delete" ) {
		    
		    $results = $this->deleteDocumentsByQuery($qArray);
		    
		  } else {	
	  
	        //Set offset if given in query otherwise default to 0
	        $offset = 0;
	        if(isset($_REQUEST['offset'])) {
	            $offset = $_REQUEST['offset'];
	        }
	        
    	    //Set limit if given in query otherwise default to 1000
	        $limit = 1000;
	        if(isset($_REQUEST['limit'])) {
	            $limit = $_REQUEST['limit'];
	        }
	        
	      	$results = $this->searchDocuments($qArray, $offset, $limit);

			// Save Queries
			
      		Queries::create($qArray)->save();
      
	      }

	    }
	  
	  }
	  else { // URL: api/<id>/<type>

	    if($method == "post") { // CREATE (a new object which refers to the main object)
	  
	      $insertId = $this->createTypedDocument($requestArray, $id, $type);

		  $statusMessage = array("_id" => $insertId);
	  
 	    }
	    elseif($method == "get") { // RETRIEVE

		  $document = $this->getDocument($id);
		
		  if(isset($document[$typePrefixed])) {
		
	        $results = $this->searchDocuments(array("_id" => $document[$typePrefixed]["_member"]));

		  }
	  		  
        }
	    elseif($method == "delete") { // DELETE

		  $this->deleteDocument($id, $type, $typedId);
	    
	    }
	  
	  }

	  // Update Tokens (to be used for Realtime Update Notifications)
	
	  if($results != "") {

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
	    
	      if($type != "") {
	    
	    	$originalObject = $this->getDocument($id);
	    	
	    	$this->updateTokenList($originalObject, $tokenId, $type);
	    
	      }
	      elseif (isset($_REQUEST['q'])) {	
	    
	        foreach ($results as $result) {
	  
			  $this->updateTokenList($result, $tokenId);
	  
	        }
	        
	      }
	      else { // Normal
	    
	        $this->updateTokenList($results, $tokenId);
	    
	      }
	    
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
		
	return $this->render(array("data" => compact("responseJsonString"),
							   "layout" => false));
	
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

    // Add the _id of the main object
    
	if(!isset($data["__related"])) {
		
	  $data["__related"] = array();
	
	}
	
	array_push($data["__related"], (object) array("_id" => $mainDocumentId, "__to" => $type));

	// Create Typed Object
	    
	$insertId = $this->createDocument($data);

    // Get the main object

	$mainDocument = $this->getDocument($mainDocumentId);
		
	// Add the _id to the main object
		
	if(!isset($mainDocument[$typePrefixed])) {
		
	  $mainDocument[$typePrefixed] = array("_member" => array(), "_token" => array());
	
	}
	
	array_push($mainDocument[$typePrefixed]["_member"], $insertId);

	$mainDocument[$typePrefixed]["_member"] = array_unique($mainDocument[$typePrefixed]["_member"]);

  	$this->updateDocument($mainDocumentId, $mainDocument, true, $type);
	  
	return $insertId;
  
  }
  
  private function getDocument($id) {

	$queryResult = Objects::first(array('conditions' => array("_id" => $id)));
	
	if($queryResult) {
	
      return $queryResult->to('array');
      
    }
    else {
    
      return array();
    
    }
  
  }
  
  private function updateDocument($id, $changedData, $triggerRealtimeUpdateNotification = true, $type = "") {
  
    if(isset($changedData["_id"])) {

	  unset($changedData["_id"]);
	 
	}
	
	$updateStatus = Objects::update($changedData, array('_id' => $id));
  
    // Trigger Realtime Update Notification
  
    $deviceId = $_REQUEST['deviceId'];  
    
    $notification = "";   
  
    if($triggerRealtimeUpdateNotification == true) {
    
      $originalObject = $this->getDocument($id);
      
      $deviceIds = array();
      
      if($type == "") {

        foreach($originalObject["_token"] as $tokenId) {
      
          $tokenObject = $this->getDocument($tokenId);
        
          if($tokenObject["__deviceId"] != $deviceId) { // Because the one triggering this should not get this notification
        
            array_push($deviceIds, $tokenObject["__deviceId"]);
            
          }
      
        }

	    // Trigger Now

        if(count($deviceIds) > 0) {
      
          $notification = "Notify the following deviceId(s):\n\n".
					      implode(", ", $deviceIds)."\n\n".
					      "to update the object with _id: ".$id."\n\n\n\n\n\n";
					      
		}

      }
      else {
      
        foreach($originalObject["_".$type]["_token"] as $tokenId) {
      
          $tokenObject = $this->getDocument($tokenId);

          if($tokenObject["__deviceId"] != $deviceId) { // Because the one triggering this should not get this notification
        
            array_push($deviceIds, $tokenObject["__deviceId"]);
            
          }
      
        }
      
	    // Trigger Now
      
        if(count($deviceIds) > 0) {

          $notification = "Notify the following deviceId(s):\n\n".
    			  		  implode(", ", $deviceIds)."\n\n".
    			  		  "to update the sub-type '".$type."' of the object with _id: ".$id."\n\n\n\n\n\n";
    			  		  
    	}

      }

	  if($notification != "") {

        
        file_put_contents("/home/zeeshan/Desktop/rtun.txt", $notification, FILE_APPEND);
        
      }
          				    
    }
  
    //
  
	return $updateStatus;

  }
  
  private function searchDocuments($conditions, $offset, $limit) {
  
	$objects = Objects::find('all', array('conditions' => $conditions, 'offset' => $offset, 'limit' => $limit));

    return $objects->to('array');
  
  }

  private function countDocuments($conditions) {
  
	$count = Objects::find('count', array('conditions' => $conditions));

    $objects = array( 'count' => $count );

    return $objects;
  
  }

  private function updateTokenList($objectArray, $tokenId, $type = "") {
  
    if($type == "") { // Normal Token

      if(!isset($objectArray["_token"])) {

        $objectArray["_token"] = array();

      }

      array_push($objectArray["_token"], $tokenId);

      $objectArray["_token"] = array_unique($objectArray["_token"]);

      $this->updateDocument($objectArray["_id"], $objectArray, false);
      
    }
    else { // Typed
  
  	  $typePrefixed = "_".$type;  
  	  
	  if(isset($objectArray[$typePrefixed]["_token"])) {
	
        array_push($objectArray[$typePrefixed]["_token"], $tokenId);

        $objectArray[$typePrefixed]["_token"] = array_unique($objectArray[$typePrefixed]["_token"]);

        $this->updateDocument($objectArray["_id"], $objectArray, false);

	  }
    
    }
  
  }

  private function decodeJsonString($jsonString) {
  
    $array = json_decode($jsonString, true);
    
    if(json_last_error() == JSON_ERROR_SYNTAX) {
    
      return false;
    
    }
    else {
    
      return $array;
    
    }
  
  }
  
  private function deleteDocumentsByQuery( $conditions = array(), $ids = array() ) {
    
    Objects::remove($conditions);
    
  }
  
  private function deleteDocument($id, $type = "", $typedId = "") {

    if($type == "") { // api/<id>
    
      $originalDocument = $this->getDocument($id);
      
      if(isset($originalDocument["__related"])) {
		
	  	foreach($originalDocument["__related"] as $related) {
	  
	  	  $parentDocument = $this->getDocument($related["_id"]);
	  	
		  if(isset($parentDocument["_".$related["__to"]])) {
        
            $parentDocument["_".$related["__to"]]["_member"] = array_diff($parentDocument["_".$related["__to"]]["_member"], array($id));
          
          }

          // Then update the Parent Object
      
          $this->updateDocument($related["_id"], $parentDocument, true, $related["__to"]);

        }
	
      }
      
      // Finally delete the original Document itself
      
      Objects::remove(array("_id" => $id));
    
    }
    else {

      $parentDocument = $this->getDocument($id);

      if($typedId == "") { // api/<id>/<type>

        // Delete the sub Document Ids
      
        if(isset($parentDocument["_".$type])) {
        
          $parentDocument["_".$type]["_member"] = array();
        
        }
        
      }
      else { // api/<id>/<type>/<typedId>
      
        // Delete the sub Document Id
      
        if(isset($parentDocument["_".$type])) {
        
          $parentDocument["_".$type]["_member"] = array_diff($parentDocument["_".$type]["_member"], array($typedId));
          
        }
      
      }

      // Then finally Update the Original Object itself as well
      
      $this->updateDocument($id, $parentDocument, true, $type);
    
    }

  }

}

?>

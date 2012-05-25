<html>

  <head>

    <meta charset="utf-8">

    <title>Get/Post System</title>

	<style>
	
	  body, table {
	  
	    font-family: arial;
	    font-size: 12px;
	    
	  }
	  
	</style>
	
    <script src="js/jquery.js"></script>

    <script>

	  function send() {

		if($("input:radio[name=method]:checked").val() == "post" && $("#request").val() == "") {
				
		  alert("Please provide the Request JSON or use the GET method.");
				
		}
		else {
		
		  $("#response").html("");
		
		  $.ajax({

		    data: {
		  
		      deviceId: $("#deviceId").val(),
		      method: $("input:radio[name=method]:checked").val(),
		      requestJsonString: $("#request").val()
		    
		    },

		    type: "post", // $("input:radio[name=method]:checked").val(),

		    url: $("#url").val(),

		    success: function(responseJson) {

		  	  $("#response").html(JSON.stringify(responseJson, undefined, 2));

			  if(!responseJson.error) {		  	
		  	  
		  	  	$("#request").val("");
		  	  
		  	  }

		    }

		  });

	    }
	    
	  }

      $(document).ready(function(){

		$("#url").keyup(function(event){

			if(event.keyCode == 13){

			  send();

			}

		});
		
		$("#method-get").click(function() {

		  $("#request").attr("disabled", "disabled");
		  $("#request").val("");

		});

		$("#method-post").click(function() {

		  $("#request").removeAttr("disabled");

		});

	  });

    </script>

  </head>

  <body>

	<table>
		
	  <tr>
	  
	  	<td valign = "top">URL:</td>

	  	<td><input type = "text" id = "url" value = "api/" style = "width: 400px;" /></td>
	  	
	  	<td valign = "top"><input type = "button" value = "Send" onclick = "send();" /></td>
	  
	  <tr>
		
	  <tr>
	  
	  	<td valign = "top">Device Id:</td>

	  	<td><input type = "text" id = "deviceId" value = "0001" style = "width: 400px;" /></td>
	  	
	  <tr>
		
	  <tr>
	  
	  	<td valign = "top">Method:</td>

	  	<td>
	  	
	  	  <input type = "radio" name = "method" id = "method-get" value = "get" checked = "checked" /> <label for = "method-get">GET</label>

	  	  <input type = "radio" name = "method" id = "method-post" value = "post" /> <label for = "method-post">POST</label>
	  	  
	  	  <input type = "radio" name = "method" id = "method-delete" value = "delete" /> <label for = "method-delete">DELETE</label>
	  	  
	  	</td>
	  
	  <tr>
		
	  <tr>
	  
	  	<td valign = "top">Request:</td>

	  	<td><textarea id = "request" style = "width: 400px; height: 100px;"></textarea></td>
	  	
	  	<td valign = "top"><a href = "javascript: $('#request').val('');">Clear</a></td>
	  
	  <tr>
	  
	  <tr>
	  
	  	<td valign = "top">Response:</td>

	  	<td style = "padding: 5px;">
	  	  
	  	  <pre id = "response"></pre>
	  	  
	  	</td>
	  
	  <tr>
	  
	</table>

  </body>

</html>

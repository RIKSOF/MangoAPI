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
				
		  alert("Please provide the Request JSON.");
				
		}
		else {
		
		  $.ajax({

		    data: {
		  
		      method: $("input:radio[name=method]:checked").val(),
		      requestJsonString: $("#request").val()
		    
		    },

		    type: $("input:radio[name=method]:checked").val(),

		    url: $("#url").val(),

		    success: function(response) {

			  responseJson = $.parseJSON(response);

		  	  $("#response").html(JSON.stringify(responseJson, undefined, 2));
		  	
		  	  $("#request").val("");

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

	  	<td><input type = "text" id = "url" value = "api/index/" style = "width: 400px;" /></td>
	  	
	  	<td valign = "top"><input type = "button" value = "Send" onclick = "send();" /></td>
	  
	  <tr>
		
	  <tr>
	  
	  	<td valign = "top">Method:</td>

	  	<td>
	  	
	  	  <input type = "radio" name = "method" id = "method-post" value = "post" checked = "checked" /> <label for = "method-post">POST</label> <input type = "radio" name = "method" id = "method-get" value = "get" /> <label for = "method-get">GET</label>
	  	  
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

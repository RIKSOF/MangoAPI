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

		$.ajax({

		  data: {
		  
		    requestData: $("#request").val()
		  
		  },

		  type: "POST",

		  url: $("#url").val(),

		  success: function(response) {

			responseJson = $.parseJSON(response);

		  	$("#response").html(JSON.stringify(responseJson, undefined, 2));

		  }

		});

	  }

      $(document).ready(function(){

		$("#url").keyup(function(event){

			if(event.keyCode == 13){

			  send();

			}

		});

	  });

    </script>

  </head>

  <body>

	<table>
		
	  <tr>
	  
	  	<td valign = "top">URL:</td>

	  	<td><input type = "text" id = "url" value = "api/index/4fb4e529b034e92143000000/activity" style = "width: 400px;" /> <input type = "button" value = "Send" onclick = "send();" /></td>
	  
	  <tr>
		
	  <tr>
	  
	  	<td valign = "top">Request:</td>

	  	<td><textarea id = "request" style = "width: 400px; height: 100px;"></textarea></td>
	  
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

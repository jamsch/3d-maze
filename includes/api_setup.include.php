<?php
		// Include constant variables
		require_once __DIR__ . '/config.include.php';

	  	// Set up the API
	  	$api = new API("localhost", 
	  				   $DATABASE, 
	  				   $TABLE, 
	  				   $USERNAME, 
	  				   $PASSWORD);

	  	$api->setup($columns);
	  	$api->set_default_order("id");
	  	$api->set_pretty_print(true);
?>
<?php
require_once('../secure/config.php');
require_once('classes/MCAPI.class.php');
require_once('classes/db.class.php');


// create an instance of the MC API class, which will be used to call the MailChimp web services
$api = new MCAPI(MC_APIKEY);


// create a new database connection used for the log operations
$log_db = new Db($db_log_connection['connection']['host'], $db_log_connection['connection']['username'], $db_log_connection['connection']['password'], $db_log_connection['connection']['database']);

// loop through the connections defined in config.php
foreach($db_to_mc_connections as $conn) {
	
	// create a new database connection for each connection defined in config.php
	$db = new Db($conn['connection']['host'], $conn['connection']['username'], $conn['connection']['password'], $conn['connection']['database']);
	
	// loop through the queries defined for each connection
	foreach($conn['queries'] as $query) {
		// run each query and load the data from the tables		
		$rows = $db->getData($query['query']);
		
		// log the import operation
		$log_data = array(
			'database' => $conn['connection']['database'],
			'table' => $query['table'],
			'city' => $query['city']
		);
		$log_db->insertData($db_log_connection['log_import_query'], $log_data);
		$import_id = $log_db->getLastInsertedId();
		
		// loop through the subscribers
		foreach($rows as $row) {
			$successfulImport = 1;
			$mcSubscriberId = '';
			
			// save the current subscriber to MailChimp
			try {
				
				$mInfo = $api->listSubscribe($conn['mc_list_id'], $row[EMAIL_FIELD], array('EMAIL'=>$row[EMAIL_FIELD]), $email_type='html', $double_optin=false, $update_existing=false, $replace_interests=true, $send_welcome=false);
				
				if ($mInfo) {
					//success
					
					//get id
					$res = $api->listMemberInfo($conn['mc_list_id'], $row[EMAIL_FIELD]);
					
					$mcSubscriberId = $res['data'][0]['id'];
					
					if (!$mcSubscriberId) {
						//error
						$successfulImport = 99;
					}
				} else {
					//error
					$successfulImport = $api->errorCode;
				}
			} catch(Exception $e) {
				$successfulImport = 97;
			}

			// log the subscriber import operation
			$subscriberData = array(
				'import_id' => $import_id,
				'email' => $row[EMAIL_FIELD], 
				'import_status' => $successfulImport,
				'mc_subscriber_id' => $mcSubscriberId
			);
			$log_db->insertData($db_log_connection['log_subscriber_import_query'], $subscriberData);
		}		
	}
	
	// close the current connection since it's not needed anymore
	$db->close();
}

// close the database connection used for logging operations
$log_db->close();
?>
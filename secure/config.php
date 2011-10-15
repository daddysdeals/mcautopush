<?php
// Your MailChimp API Key
define('MC_APIKEY', '1234567890qwertyuiopasdfghjkl-us1');

// The field from the database queries used to load the e-mail of the subscribers
define('EMAIL_FIELD', 'email');

// The time interval used by the CRON job (minutes)
define('CRON_INTERVAL', 60); // run every hour

/* The array containing all the database connections from where data needs to be imported
 * 
 * Each database connection is defined as an array, containing the DB credentials, 
 * the queries use to extract data and the ID of the MailChimp list where the subscribers will be added
 * Example:
 * 
 * array(
 * 		The ID of the list
 * 		'mc_list_id' => 'mc_api_list_id',
 * 		
 * 		The database connection credentials
 * 		'connection' => array(
 * 			'host' => 'database.domain.com',
 * 			'username' => 'username_here',
 * 			'password' => 'password_here'
 * 			'database' => 'database_name'
 * 		),
 * 
 * 		The SQL queries used to extract data
 * 		If the field holding the e-mails has a different name than "email", use an ALIAS
 * 		'queries' => array(
 * 			array(
 * 				'city' => 'Cape Town', 'query' => 'SELECT email FROM user_list1 WHERE city_id = 7;'
 * 			),
 * 			array(
 * 				'city' => 'Johannesburg', 'query' => 'SELECT email_address AS email FROM user_list2 WHERE city_id = 1;'
 * 			)
 * 		),
 *
 *		The following is used to get data of emails of unsubscribers
 *		'unsub_queries' => array(
 *			array(
 *				'city' => 'Cape Town', 
 *				'query' => 'SELECT email FROM unsubscribers WHERE city_id=7 AND user_id is NULL AND TIMESTAMPDIFF(MINUTE, created_datetime, NOW())+60 < '.CRON_INTERVAL,
 *				'table' => 'unsubscribers emails'
 *			),
 *			array(
 *				'city' => 'Johannesburg', 
 *				'query' => 'SELECT email FROM unsubscribers WHERE city_id=1 AND user_id is NULL AND TIMESTAMPDIFF(MINUTE, created_datetime, NOW())+60 < '.CRON_INTERVAL,
 *				'table' => 'unsubscribers emails'
 *			),
 *		),
 *
 *		Clear unsubscribers table
 *		'unsub_clear_query' => array(
 *			'DELETE FROM unsubscribers WHERE city_id=1 OR city_id = 10'
 *		)
 * 
 * 
 */ 
$db_to_mc_connections = array(
	array(
		'mc_list_id' => '9fe89912a1',
		'connection' => array(
			'host' => 'localhost',			
			'username' => 'db_user',
			'password' => 'db_pass',
			'database' => 'database1'
		),
		'queries' => array(
			array(
				'city' => 'Cape Town', 
				'query' => 'SELECT email FROM table1 WHERE city_id = 1 AND TIMESTAMPDIFF(MINUTE,date_created, NOW()) < ' . CRON_INTERVAL, 
				'table' => 'table1'
			),
			array(
				'city' => 'Johannesburg', 
				'query' => 'SELECT email_address AS email FROM table2 WHERE city_id = 10 AND TIMESTAMPDIFF(MINUTE,date_created, NOW()) < ' . CRON_INTERVAL,
				'table' => 'table2')
		),
		'unsub_queries' => array(
			array(
				'city' => 'Cape Town', 
				'query' => 'SELECT email FROM unsubscribers WHERE city_id=1 AND user_id is NULL AND TIMESTAMPDIFF(MINUTE, created_datetime, NOW())+60 < '.CRON_INTERVAL,
				'table' => 'unsubscribers users'
			),
			array(
				'city' => 'Johannesburg', 
				'query' => 'SELECT email FROM unsubscribers WHERE city_id=10 AND user_id is NULL AND TIMESTAMPDIFF(MINUTE, created_datetime, NOW())+60 < '.CRON_INTERVAL,
				'table' => 'unsubscribers emails'
			)
		),
		'unsub_clear_query' => array(
			'DELETE FROM unsubscribers WHERE city_id=1 OR city_id = 10'
		)
	),
	array(
		'mc_list_id' => 'e7b72063a1',
		'connection' => array(
			'host' => 'localhost',
			'username' => 'db_user',
			'password' => 'db_pass',
			'database' => 'database2'
		),
		'queries' => array(
			array(
				'city' => 'Cape Town',
				'query' => 'SELECT email FROM table1 WHERE city_id = 1 AND TIMESTAMPDIFF(MINUTE,date_created, NOW()) < ' . CRON_INTERVAL,
				'table' => 'table1'
			),
			array(
				'city' => 'Johannesburg', 
				'query' => 'SELECT email FROM table1 WHERE city_id = 2 AND TIMESTAMPDIFF(MINUTE,date_created, NOW()) < ' . CRON_INTERVAL, 
				'table' => 'table1'
			)
		),
		'unsub_queries' => array(
			array(
				'city' => 'Cape Town', 
				'query' => 'SELECT email FROM unsubscribers WHERE city_id=1 AND user_id is NULL AND TIMESTAMPDIFF(MINUTE, created_datetime, NOW())+60 < '.CRON_INTERVAL,
				'table' => 'unsubscribers users'
			),
			array(
				'city' => 'Johannesburg', 
				'query' => 'SELECT email FROM unsubscribers WHERE city_id=10 AND user_id is NULL AND TIMESTAMPDIFF(MINUTE, created_datetime, NOW())+60 < '.CRON_INTERVAL,
				'table' => 'unsubscribers emails'
			)
		),
		'unsub_clear_query' => array(
			'DELETE FROM unsubscribers WHERE city_id=1 OR city_id = 10'
		)
	)
);

/*
 * An array containing the database credentials and the queries used to log each import operation
 */
$db_log_connection = array(
	'connection' => array(
		'host' => 'localhost',			
		'username' => 'db_user',
		'password' => 'db_pass',
		'database' => 'ddeals_admin'
	),
	
	// query used to log an import operation
	'log_import_query' => "INSERT INTO mc_imports (`database`, `table`, `city`) VALUES('{database}', '{table}', '{city}')",
	
	// query used to log the import of each subscriber
	'log_subscriber_import_query' => "INSERT INTO mc_imports_subscribers (import_id, email, import_status, mc_subscriber_id) VALUES({import_id}, '{email}', {import_status}, '{mc_subscriber_id}')"
);
?>
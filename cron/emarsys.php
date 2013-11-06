<?php

require_once(dirname(__FILE__) . "/../config/config.inc.php");

ini_set('mysql.connect_timeout', 36000);
ini_set('default_socket_timeout', 36000);

$dbConn = null;
$mailTo = 'alper@butigo.com';
$mailCc = array(
	'ramesh.ks@avishinc.com',
	'huseyin@butigo.com',
	'gangadhar.km@avishinc.com'
);

// mail/log information regarding the request
$headers = "From: Butigo Timed Scripts <destek@butigo.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

foreach ($mailCc as $email) {
	$headers .= "Cc: $email\r\n";
}

$log = true;
$logFilePath = _PS_LOG_DIR_ . "/emarsys.txt";
if (!file_exists($logFilePath)) {
	$logFile = @fopen($logFilePath, "w");
} else {
	$logFile = @fopen($logFilePath, "a");
}
if (!$logFile) {
	error_log("Log file is not writable : $logFilePath");
}

if ($log) {
	$logFile = @fopen($logFilePath, "a");
	fwrite($logFile, 'START----' . date("D M j G:i:s T Y") . "\n");
	fclose($logFile);
}

echo "Mail Headers Are As Follows:\r\n\r\n" . $headers;

/*function curl_get($url, $follow, $debug) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
	curl_setopt($ch, CURLOPT_TIMEOUT, 90);

	$result = curl_exec($ch);

	curl_close($ch);

	if ($debug == 1) {
		echo "\r\n" . $result . "\r\n";
	}

	return $result;
}*/

function getMySQLConnection() {
	global $dbConn;

	if (! $dbConn OR ! mysql_ping($dbConn)) {
        $dbConn = mysql_connect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_);

		if ($dbConn !== false) {
			if (! mysql_select_db(_DB_NAME_, $dbConn)) {
                $msg = "The database selection cannot be made:\r\n\r\n" . mysql_error($dbConn);
                mail($mailTo, '[EMARSYS] WELCOME2 FAILURE', $msg, $headers);
				die($msg);
			}

			/* UTF-8 support */
			if (! mysql_query('SET NAMES \'utf8\'', $dbConn)) {
                $msg = 'Fatal Error: No utf-8 support. Please check your MySQL server configuration.';
                mail($mailTo, '[EMARSYS] WELCOME2 FAILURE', $msg, $headers);
				die($msg);
			}

			/* Disable some MySQL limitations */
			mysql_query('SET GLOBAL SQL_MODE=\'\'', $dbConn);

            // set wait_timeout (internal mysql variable) to 10 hours
            mysql_query('set wait_timeout = 36000', $dbConn);
		} else {
            $msg = "Link to database cannot be established:\r\n\r\n" . mysql_error($dbConn);
            mail($mailTo, '[EMARSYS] WELCOME2 FAILURE', $msg, $headers);
			die($msg);
		}
	}

	return true;
}

if (_BU_ENV_ == 'production') {
	$already_set_array = array('esen1977@hotmail.com');
	$sailthruData = array();
	/**
	 * Put the whole block into try/catch because Harun asked to catch it
	 * as early as possible.
	 */
	try {
		$cur_day = date('d');
		$cur_month = date('m');
		$cur_year = date('Y');

        // TODO: ENABLE THE FOLLOWING ONCE THIS SCRIPT IS STABLE ENOUGH
		// $cutoff_yesterday = date("Y-m-d h:i:s", mktime(3, 0, 0, $cur_month, $cur_day - 1, $cur_year));
		// $cutoff_today = date("Y-m-d h:i:s", mktime(3, 0, 0, $cur_month, $cur_day, $cur_year));

		$date_after = '2012-10-23 00:00:00';
        $now = date("Y-m-d H:i:s");

		$selectQuery = '
			SELECT `id_customer`, `email`, `date_add`, `category_name`
			FROM `' . _DB_PREFIX_ . 'customer`
			WHERE `active` = 1
				AND `showroom_ready` = 0
				AND `date_add` > \'' . $date_after . '\'
				AND `category_name` IS NOT NULL
				AND `category_name` != ""
			ORDER BY `id_customer` ASC
		';

        /**
         * date_add() < $now added to below query because it was updating
         * the customers registered between the time the script is ran and
         * the time the selection query is executed and therefore haven't
         * been sent any e-mails.
         */
        $updateQuery = '
            UPDATE `' . _DB_PREFIX_ . 'customer`
            SET `showroom_ready` = 1
            WHERE `active` = 1
                AND `showroom_ready` = 0
                AND `date_add` > \'' . $date_after . '\'
                AND `date_add` < \'' . $now . '\'
                AND `category_name` IS NOT NULL
                AND `category_name` != ""
        ';

		// Let this commented query be there please
		/*$selectQuery = '
	        SELECT `id_customer`, `email`, `date_add`
	        FROM `'._DB_PREFIX_.'customer`
	        WHERE `active` = 1 AND `showroom_ready` = 1 AND `date_add` > \'2012-07-26 00:00:00\' AND `date_add` <= \'2012-08-10 23:59:59\'
	        ORDER BY `id_customer` ASC';*/			
        echo "Starting Selection Query: $now\r\n";
        echo "Select Query:\r\n\r\n$selectQuery\r\n\r\n";
		
		if ($log) {
			$logFile = @fopen($logFilePath, "a");
			fwrite($logFile, "Select Query: \n" . $selectQuery . "\n");
			fclose($logFile);
		}
		
		if (getMySQLConnection() && $result = mysql_query($selectQuery, $dbConn)) {
			$email_count = 0;
            $failure = 0;
            $num = mysql_num_rows($result);

			$now = date("Y-m-d H:i:s");
			
			if ($log) {
				$logFile = @fopen($logFilePath, "a");
				fwrite($logFile, "Select Query Result: \n" . $result . "\n");
				fclose($logFile);
			}
			
            echo "Selection Query (Result: $num) Has Completed Successfully: $now\r\n";
            echo "Starting Emarsys Requests...\r\n";

			while ($row = mysql_fetch_assoc($result)) {
				if (! in_array($row['email'], $already_set_array)) {
					/*$emarsys_url = "https://login.emarsys.net/u/register_bg.php?owner_id=119092141&f=2277&key_id=3&optin=y&inp_3=" . $row['email'] . "&inp_10763=yes";
					$response = curl_get($emarsys_url, 1, 0);*/
					$sailthruData[] = array('customerId' => $row['id_customer'],
											'customerEmail' => $row['email']
											);				

					if (! $response) {
                        $failure++;
						$cron_result = 'Adding ' . $row['email'] . " : FAILED\r\n";
					} else {
						$cron_result = 'Adding ' . $row['email'] . " : SUCCESSFULL\r\n";
					}

					echo $cron_result;

					$email_count++;
				}
			}

			$now = date("Y-m-d H:i:s");

			echo "\r\n$email_count Mails Are Sent to Emarsys with $failure failures: $now\r\n";

			mysql_free_result($result);
		} else {
            if ($log) {
				$logFile = @fopen($logFilePath, "a");
				fwrite($logFile, "Select Query Result Failed: \n");
				fclose($logFile);
			}
        }

        $now = date("Y-m-d H:i:s");
        echo "Starting Update Query: $now\r\n";
        echo "Update Query:\r\n\r\n$updateQuery\r\n\r\n";
			
		getMySQLConnection();
        mysql_query($updateQuery, $dbConn);

        $affectedRows = mysql_affected_rows($dbConn);
	 	$mysqlError = mysql_error($dbConn);

		mysql_close($dbConn);

        $now = date("Y-m-d H:i:s");
        echo "Update Query (Affected: $affectedRows) Successfully Executed: $now\r\n";

		Module::hookExec('sailThruMailSend', array('sailThruEmailTemplate' => 'Showroom-Ready',
													'customerList' => $sailthruData)
						);

		if ($mysqlError) {
			mail($mailTo, '[EMARSYS] WELCOME2 FAILURE', 'Following (mysql) error occured during execution: ' . $mysqlError, $headers);
			if ($log) {
				$logFile = @fopen($logFilePath, "a");
				fwrite($logFile, "Following (mysql) error occured during execution:\n".$mysqlError ."\n query affected: \n".$updateQuery."\n");
				fclose($logFile);
			}
		}

		mail($mailTo, '[EMARSYS] WELCOME2 SUCCESS', 'Total emails add count: ' . $email_count, $headers);
	} catch (Exception $e) {
        $error_msg=$e->getMessage();
		$errorData = $e->getTraceAsString();
		mail($mailTo, '[EMARSYS] WELCOME2 FAILURE', "Following exceptional error occured during execution:\r\n" . $errorData .'\n error message'.$error_msg, $headers);
		if ($log) {
			$logFile = @fopen($logFilePath, "a");
			fwrite($logFile, "Following exceptional error occured during execution:\n ".$error_msg."\n".print_r($errorData, TRUE));
			fclose($logFile);
		}
	}
}
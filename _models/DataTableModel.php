<?php
class DataTableModel {

	/**
	 * List all of the accounts from the database
	 * @param $start
	 * @param $length
	 * @param $order
	 * @param $draw
	 * @param null $search
	 * @return array
	 */
	public static function getAllAccounts($start, $length, $order, $draw, $search=null){
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// DataTable Arrangement according to its index usage on the dataTable
		$columns = array("", "id", "fn", "ln", "username", "active_loan");

		/**
		 * GET ALL OF THE DATA WITHOUT FILTERING
		 */
		// Select all of the data in the pending table
		$sql = "SELECT `id` FROM `pending_requests` WHERE 1";

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare);

		// Get the total amount of data without any filter or search
		$totalData = $database->mysqli_num_rows($prepare);

		/**
		 * GET ALL OF THE DATA WITH SEARCH FILTER
		 */
		// Generate SQL according to parameters
		$sql = "
		SELECT 
			# Account Table Columns
			a.`id`, a.`username`, a.`bin_active`, a.`uni_active`,
			# Account Info Columns
	    	ai.`fn`, ai.`ln`, ai.`ad`, ai.`email`, ai.`photo`, ai.`cn`, ai.`bdate`,
	    	# Check for an active loan in this account
			IF(COUNT(DISTINCT l.id) > 0, '<b class=\"text-success\">TRUE</b>', '<b class=\"text-danger\">FALSE</b>') as `active_loan`
	    	# Pending Request and Account Info Connectivity		
		FROM `accounts` a 
			LEFT JOIN `account_info` ai 
				ON a.id=ai.`accnt_id` 
            LEFT JOIN `loan` l
				ON l.cid=ai.`accnt_id`
                WHERE 1";

		$dict = array();

		if (!empty($search["value"])){
			$sql .= " AND ai.`fn` LIKE :SEARCH_STRING";
			$dict[":SEARCH_STRING"] = "%".$search["value"]."%";
		}

		// Process of querying data
		$sql .= " GROUP BY a.id ";
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, $dict);

		// Get the total amount of data without any filter or search
		$totalFiltered = $database->mysqli_num_rows($prepare);

		/**
		 * GET ALL OF THE DATA WITH SEARCH FILTER, ORDERING AND PAGE LIMITATION
		 */
		$direction = $order[0]["dir"];
		$columnName = $columns[$order[0]["column"]];
		$sql .= " ORDER BY `$columnName` $direction LIMIT $start, $length";

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, $dict);

		// Prepare the dataArray algorithm by adding extra column
		$dataArr = array();
		foreach($database->mysqli_fetch_assoc($prepare) as $row){
			$row["action"] = "";
			array_push($dataArr, $row);
		}

		// Return the generated JSOn
		return self::generateJSON($draw, $totalData, $totalFiltered, $dataArr);
	}

	/**
	 * Get the pending Transaction from database
	 * @param String $start
	 * @param String $length
	 * @param String $order
	 * @param String $draw
	 * @param String|null $search
	 * @return array
	 */
	public static function getPendingTransactions($start, $length, $order, $draw, $search=null){
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// DataTable Arrangement according to its index usage on the dataTable
		$columns = array("", "id", "fn", "ln", "type");

		/**
		 * GET ALL OF THE DATA WITHOUT FILTERING
		 */
		// Select all of the data in the pending table
		$sql = "SELECT `id` FROM `pending_requests` WHERE 1";

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare);

		// Get the total amount of data without any filter or search
		$totalData = $database->mysqli_num_rows($prepare);

		/**
		 * GET ALL OF THE DATA WITH SEARCH FILTER
		 */
		// Generate SQL according to parameters
		$sql = "
		SELECT 
			# Pending Request Columns
			pr.`id`, pr.`user_id`, pr.`parent_id`, pr.`type`,
			# Account Info Columns
	    	ai.`fn`, ai.`ln`, ai.`ad`, ai.`email`, ai.`photo`, ai.`cn`, ai.`bdate`,
	    	# Package Type
	    	up.`packg_type`
			# Pending Request and Account Info Connectivity		
		FROM `pending_requests` pr 
			LEFT JOIN `account_info` ai 
				ON pr.user_id=ai.`accnt_id` 
			LEFT JOIN `uni_packg` up
				ON up.`tid` = pr.`id`
		WHERE 1";

		$dict = array();

		if (!empty($search["value"])){
			$sql .= " AND ai.`fn` LIKE :SEARCH_STRING";
			$dict[":SEARCH_STRING"] = "%".$search["value"]."%";
		}

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, $dict);

		// Get the total amount of data without any filter or search
		$totalFiltered = $database->mysqli_num_rows($prepare);

		/**
		 * GET ALL OF THE DATA WITH SEARCH FILTER, ORDERING AND PAGE LIMITATION
		 */
		$direction = $order[0]["dir"];
		$columnName = $columns[$order[0]["column"]];
		$sql .= " ORDER BY `$columnName` $direction LIMIT $start, $length";

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, $dict);

		// Prepare the dataArray algorithm by adding extra column
		$dataArr = array();
		foreach($database->mysqli_fetch_assoc($prepare) as $row){
			$row["action"] = "";
			array_push($dataArr, $row);
		}

		// Return the generated JSOn
		return self::generateJSON($draw, $totalData, $totalFiltered, $dataArr);
	}


	/**
	 * Generate JSON from the given parameters
	 * @param $draw
	 * @param $totalData
	 * @param $totalFiltered
	 * @param $data
	 * @return array
	 */
	public static function generateJSON($draw, $totalData, $totalFiltered, $data){

		// Return the JSON data
		$generatedData = array(
			// For every request/draw by client-side , they send a number as a parameter.
			// When they receive a response/data they first check the draw number, so we are sending same number in draw.
			"draw" => intval($draw),

			// Total Number of Records
			"recordsTotal"=> intval( $totalData ),

			// Total number of records after searching, if there is no searching then the totalFiltered is the totalData
			"recordsFiltered" => intval( $totalFiltered ),

			// All of the data Array
			"data" => $data
		);

		return json_encode($generatedData, JSON_PRETTY_PRINT);
	}
}
    
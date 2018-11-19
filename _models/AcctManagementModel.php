<?php
class AcctManagementModel{


	/**
	 * Update Basic Account Information
	 * @param $id
	 * @param $fname
	 * @param $lname
	 * @param $cnumber
	 * @param $bdate
	 * @param $address
	 * @return bool
	 */
	public static function updateBasicInfo($id, $fname, $lname, $cnumber, $bdate, $address){
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		try {
			// Save the account auth data
			$prepared = $database->mysqli_prepare($connection, "
				INSERT INTO `account_info`
					(`accnt_id`, `fn`, `ln`, `ad`, `cn`, `bdate`) 
				VALUES
				 	(:USER_ID,:FIRST_NAME,:LAST_NAME,:ADDRESS,:CONTACT_NUM,:BDATE)
			    ON DUPLICATE KEY UPDATE
			    	`fn`=:FIRST_NAME,
	                `ln`=:LAST_NAME,
	                `ad`=:ADDRESS,
	                `cn`=:CONTACT_NUM,
	                `bdate`=:BDATE 
		    	");

			$database->mysqli_execute($prepared, array(
				":USER_ID" => $id,
				":FIRST_NAME" => $fname,
				":LAST_NAME" => $lname,
				":CONTACT_NUM" => $cnumber,
				":BDATE" => $bdate,
				":ADDRESS" => $address
			));

			return true;
		} catch(Exception $e){
			/**
			 * An exception has occured, which means that one of our database queries
			 * failed. Print out the error message.
			 */
			echo $e->getMessage();
			return false;
		}
	}


	public static function updatePrimaryInfo($id, $username, $email, $password=null){
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			// Save the account auth data
			$prepared = $database->mysqli_prepare($connection, "
				INSERT INTO `account_info`
					(`accnt_id`, `email`) 
				VALUES
				 	(:USER_ID,:EMAIL)
			    ON DUPLICATE KEY UPDATE
			    	`email`=:EMAIL
		    	");

			$database->mysqli_execute($prepared, array(
				":USER_ID" => $id,
				":EMAIL" => $email
			));


			// Save with or without new Password
			if ($password != null){
				$prepared = $database->mysqli_prepare($connection, "
					UPDATE `accounts` SET `username`=:USERNAME,`pass`=:NEW_PASS WHERE `id`=:USER_ID
				");

				$database->mysqli_execute($prepared, array(
					":USER_ID" => $id,
					":USERNAME" => $username,
					":NEW_PASS" => $password
				));
			}else{
				$prepared = $database->mysqli_prepare($connection, "
					UPDATE `accounts` SET `username`=:USERNAME WHERE `id`=:USER_ID
				");

				$database->mysqli_execute($prepared, array(
					":USER_ID" => $id,
					":USERNAME" => $username
				));
			}


			// Commit the changes when no error found.
			$database->mysqli_commit($connection);
			return true;

		} catch(Exception $e){
			/**
			 * An exception has occured, which means that one of our database queries
			 * failed. Print out the error message.
			 */
			echo $e->getMessage();


			//Rollback the transaction.
			$database->mysqli_rollback($connection);
			return false;
		}
	}
}
<?php

class TransactionsModel {
	public static function declineTransaction(String $tid){
		// DELETE FROM DATABASE algorithm
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		try {
			// Save the account auth data
			$prepared = $database->mysqli_prepare($connection, "
                DELETE FROM `pending_requests` WHERE `id`=:TRANSACTION_ID;
            ");

			$database->mysqli_execute($prepared, array(
				":TRANSACTION_ID" => $tid,
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

	public static function approveTransaction(String $tid, $userId, $parentID, String $transType){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		/**
		 * We will need to wrap our queries inside a TRY / CATCH block.
		 * That way, we can rollback the transaction if a query fails and a PDO exception occurs.
		 * Our catch block will handle any exceptions that are thrown.
		 */
		try {

			// Remove from the pending
			$prepared = $database->mysqli_prepare($connection,
				"DELETE FROM `pending_requests` WHERE `id`=:TRANSACTION_ID;"
			);
			$database->mysqli_execute($prepared, array(
				":TRANSACTION_ID" => $tid
			));

			// Approval of Binary Level
			if ($transType == "binary"){
				// Activate the binary account
				$prepared = $database->mysqli_prepare($connection,
					"UPDATE `accounts` SET `bin_active`='1' WHERE `id`=:USER_ID;"
				);
				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId
				));

				// Add to the pending bin path
				$prepared = $database->mysqli_prepare($connection,
					"INSERT INTO `pending_binpath`(`invitee_id`, `invitor_id`) VALUES (:USER_ID, :PARENT_ID)"
				);
				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId,
					":PARENT_ID" => $parentID
				));
			}
			// Approval of Uni level
			else{
				// Activate the uni level account
				$prepared = $database->mysqli_prepare($connection,
					"UPDATE `accounts` SET `uni_active`='1' WHERE `id`=:USER_ID;"
				);
				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId
				));

				// Add to the uni path
				$prepared = $database->mysqli_prepare($connection, "
              	INSERT INTO `unipath`(`anc`, `desc`, `parent`)
					(SELECT `anc`, :USER_ID AS `desc`, :PARENT_ID AS `parent` FROM `binpath` WHERE `desc`=:PARENT_ID) 
						UNION
 					(SELECT :USER_ID AS `enc`, :USER_ID AS `desc`, :PARENT_ID AS `parent`) 
 						ON DUPLICATE KEY UPDATE `parent`= :PARENT_ID;
            		");


				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId,
					":PARENT_ID" => $parentID
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


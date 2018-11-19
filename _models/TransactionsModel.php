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

	/**
	 * Approving Transaction for Binary and Uni level Algorithm
	 * @param String $tid
	 * @param $userId
	 * @param $parentID
	 * @param String $transType
	 * @param null $packType
	 * @param null $loanAmount
	 * @return bool
	 */
	public static function approveTransaction(String $tid, $userId, $parentID, String $transType, $packType=null, $loanAmount=null){
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

				// Save the loan
				$prepared = $database->mysqli_prepare($connection, "
              		INSERT INTO `loan`
              		(`cid`, `loan_type`, `loan_amount`, `gross_loan`, `loan_balance`, `loan_paid`, `lend_date`, `maturity_date`, `paid`) 
              			VALUES 
              		(:USER_ID, :PCKG_TYPE, :LOAN_AMOUNT, :GROSS_LOAN, :LOAN_BALANCE, 0, NOW(), NOW() + INTERVAL 1 YEAR, 'np')
                ");


				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId,
					":PCKG_TYPE" => $packType,
					":LOAN_AMOUNT" => $loanAmount,
					":GROSS_LOAN" => self::grossLoan($loanAmount),
					":LOAN_BALANCE" => self::grossLoan($loanAmount),
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

	private static function grossLoan($loanAmount){
		// Generate Total GrossLoan According to the loanAmount
		$interest = 0.05;
		$durationMonths = 12;

		return (1+ ($interest * $durationMonths)) * $loanAmount;
	}
}


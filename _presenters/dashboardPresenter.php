<?php
class dashboardPresenter {
    public static function get(){
        View::addVar("view_title", "Dashboard");

        // Import Jquery JS Library
	    View::addScript("http://".Route::domain()."/js/".md5("JQueryOnly").".min.js");

	    // Import dataTable library
	    View::addScript("//".Route::domain()."/js/".md5("dataTable").".min.js");
	    View::addCSS("//".Route::domain()."/css/".md5("dataTable").".min.css");

	    // Import Bootstrap
	    View::addCSS("//".Route::domain()."/css/".md5("Bootstrap").".min.css");
	    View::addScript("//".Route::domain()."/js/".md5("Bootstrap").".min.js");
    }

    public static function post(){
    	// Parameters to be accepted
    	Params::permit(
    		"search", "order", "start", "length", "draw", "decline", "approve", "parent_id", "user_id", "type",
		    "package_type", "loan_amount"
	    );


	    // When declining a user
    	if (Params::get("decline") != false){
    		$status = TransactionsModel::declineTransaction(
    			Params::get("decline")
		    );

    		// Do a server response whether it is successful or not.
    		echo  $status ? "1" : "0";
    		exit;
	    }

	    // When approving a user
	    if (Params::get("approve") != false && Params::get("parent_id") != false && Params::get("user_id") != false &&
		    Params::get("type") !== false){

    		$status = TransactionsModel::approveTransaction(
			    Params::get("approve"),
			    Params::get("user_id"),
			    Params::get("parent_id"),
			    Params::get("type"),
			    Params::get("package_type"),
			    Params::get("loan_amount")
		    );

		    // Do a server response whether it is successful or not.
		    echo  $status ? "1" : "0";

		    exit;
	    }

	    // When getting the Transactions
    	$SERVER_RESPONSE = DataTableModel::getPendingTransactions(
    		Params::get("start"),
		    Params::get("length"),
		    Params::get("order"),
		    Params::get("draw"),
		    Params::get("search")
	    );

    	echo $SERVER_RESPONSE;
		exit;
    }

    public static function put(){
        Route::returnCode(401);
    }

    // HTTP Header Method: DELETE
    // Usually used when about to delete a data
    public static function delete(){
        Route::returnCode(401);
    }
}
    
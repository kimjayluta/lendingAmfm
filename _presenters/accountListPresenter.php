<?php

class accountListPresenter {
    // HTTP Header Method: GET
    // Used to retrive a data or a view
    public function get(){
	    View::addVar("view_title", "Account List");

	    // Import Jquery JS Library
	    View::addScript("http://".Route::domain()."/js/".md5("JQueryOnly").".min.js");

	    // Import dataTable library
	    View::addScript("//".Route::domain()."/js/".md5("dataTable").".min.js");
	    View::addCSS("//".Route::domain()."/css/".md5("dataTable").".min.css");

	    // Import Bootstrap
	    View::addCSS("//".Route::domain()."/css/".md5("Bootstrap").".min.css");
	    View::addScript("//".Route::domain()."/js/".md5("Bootstrap").".min.js");

	    View::addCSS("/_layouts/accountList/index.css");

    }

    public function post(){
    	Params::permit(
    		// DataTable Parameters
    		"start", "length", "order", "draw", "search", "fname",
		    // Basic Info Parameters
		    "id", "fname", "lname", "cnumber", "bdate", "address",
		    // Primary Info Parameters
		    "username", "password", "email"
	    );

    	// Update Account Basic Information
    	if (Params::get("id") != false && Params::get("fname") != false && Params::get("lname") != false &&
		    Params::get("cnumber") != false && Params::get("bdate") != false && Params::get("address") != false){

    		// Start the process
    		AcctManagementModel::updateBasicInfo(
    			Params::get("id"),
			    Params::get("fname"),
			    Params::get("lname"),
			    Params::get("cnumber"),
			    Params::get("bdate"),
		        Params::get("address")
		    );

    		header("location: /accountList");
    		exit;
	    }

	    // Update Account Primary Information
	    if (Params::get("id") != false && Params::get("username") != false &&
	        Params::get("email") != false){


    		$pwd = Params::get("password");

    		// When the Password has 8 - 32 characters,
		    // Then Process with Password change
    		if (strlen($pwd) > 7 && strlen($pwd) < 33){
    			AcctManagementModel::updatePrimaryInfo(
    				Params::get("id"),
				    Params::get("username"),
				    Params::get("email"),
				    Params::get("password")
			    );
			    header("location: /accountList");
    			exit;
		    }

		    // Process Without Password Change
		    AcctManagementModel::updatePrimaryInfo(
			    Params::get("id"),
			    Params::get("username"),
			    Params::get("email")
		    );
		    header("location: /accountList");
    		exit;
	    }


	    // When getting the Transactions to the DataTable
	    $SERVER_RESPONSE = DataTableModel::getAllAccounts(
		    Params::get("start"),
		    Params::get("length"),
		    Params::get("order"),
		    Params::get("draw"),
		    Params::get("search")
	    );

	    echo $SERVER_RESPONSE;
	    exit;
    }

    // HTTP Header Method: PUT
    // Usually used when about to update a data
    public function put(){
        Route::returnCode(401);
    }

    // HTTP Header Method: DELETE
    // Usually used when about to delete a data
    public function delete(){
        Route::returnCode(401);
    }
}
    
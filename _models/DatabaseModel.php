<?php
class DatabaseModel {
    // Database instances
    private static $DB;
    private static $mainDBConnection;
    private static $lendingDBConnection;

    /**
     * Connect to the Database
     */
    public static function initConnections(){
        // Use the "cached" DB to minimize multiple initialization
        if (self::$DB != null){
            return self::$DB;
        }

        // Get authentication credentials from the configuration file
        $activeCredentials = Route::config(Route::config("activeDB"));

        // Initialize MYSQL PDO plugin
	    $DB = new Plugins\MySQLiPDO();

	    // Connect to the Main Database Server
	    $DBCon1 = $DB->mysqli_connect(
        	$activeCredentials["host"],
	        $activeCredentials["dbname"],
	        $activeCredentials["username"],
	        $activeCredentials["password"]
        );

        // Connect to the Lending Database Server
        $DBCon2 = $DB->mysqli_connect(
        	$activeCredentials["host"],
	        $activeCredentials["lendingDB"],
	        $activeCredentials["username"],
	        $activeCredentials["password"]
        );

        // When Failed to initialize the Plugin
        if (!$DB){
            echo "An unknown error occurs. Please try again later or contact the administrator. <a href='/'>Go back to homepage</a>";
            exit;
        }

        // Save DB Instances as methods
        self::$DB = $DB;
        self::$mainDBConnection = $DBCon1;
        self::$lendingDBConnection = $DBCon2;

        // Return the Database Connection
        return $DB;
    }

	/**
	 * Get the cached main Database connection
	 * @return mixed
	 */
    public static function getMainConnection(){
        return self::$mainDBConnection;
    }


	/**
	 * Get the cached lending Database connection
	 * @return mixed
	 */
	public static function getLendingConnection(){
		return self::$lendingDBConnection;
    }
}
<?php
// Load the Global Variable Class
require_once './global_vars.php';

// Load the Local Class Files Automatically when called
require_once './autoload.php';

$recordTime = Route::config("time_record");
$errorReport = Route::config("display_error");
if ($errorReport){
    // Reports all errors
    error_reporting(E_ALL);
    // Do not display errors for the end-users (security issue)
    ini_set('display_errors','Off');
    
    // Override the default error handler behavior
    set_exception_handler(function($exception) {
        error_log($exception);
    });
}
if ($recordTime){ Plugins\RecordTime::start(); }

// This Runs the whole application
new Route($_SERVER);

if ($recordTime){
    Plugins\RecordTime::end();
    echo Plugins\RecordTime::getTotal() . " ms server time.";
}

exit;

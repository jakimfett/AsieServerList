<?php

/**
 * Provides an API for interacting with the serverlist
 *
 * @author jakimfett
 */

if (isset($_POST['url']) OR isset($_GET['debug'])) {
    
    // Include the constants and API class
    require_once '../protected/config.php';
    require_once '../protected/includes/classes/class.api.php';
    
    
    // If the debug flag is passed in, set dummy data
    if (isset($_GET['debug']) AND !isset($_POST['url'])){
        $_POST['url'] = 'http://mc.picraft.com:8030/';
    }
    
    // Retrieve the server data from the POST'ed URL
    $server_info = api::getServerJSON($_POST['url']);
    
    // If $server_info isn't false, attempt to add or update the server in the database
    if ($server_info){
        $server_info->asie_url = $_POST['url'];
        // Instantiate the API object
        $api = new api();
        
        // Make update/add attempt
        $api->addOrUpdateServer($server_info);
        
        // Figure out if it's a success or a fail, 
        if($api->status === 'success'){
            header("HTTP/1.1 200 OK");
            die('Success');
        } else {
            header("HTTP/1.1 418 I'm a teapot");
            die($api->status);
        }
            
    } else {
        header("HTTP/1.1 408 Request Timeout");
        die("Server at <a href='{$_POST['url']}'>{$_POST['url']}</a> did not respond");
    }
    
} elseif (isset($_GET['masterlist'])) {
    // Grab the config for access to the API_KEYS
    require_once '../protected/config.php';
    
    // Validate that the person attempting to update the masterlist has access
    if (in_array($_GET['masterlist'], unserialize(API_KEYS))) {
        
        // Include the api class
        require_once '../protected/includes/classes/class.api.php';
        
        // Instantiate the api class
        $api = new api();
        
        // Make the update call
        $api->updateMasterlist();
        
        // Check to see if the masterlist updated properly
        if ($api->status === 'success'){
            header("HTTP/1.1 200 OK");
            die('Success');
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            die('<h1>500 Internal Server Error</h1><br/>masterlist not updated');
        }
    }
}

// Otherwise, throw a '403 Forbidden' header
header("HTTP/1.1 403 Forbidden");
die('<h1>403 Forbidden</h1>');
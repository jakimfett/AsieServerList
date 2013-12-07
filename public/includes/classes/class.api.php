<?php

/**
 * Functions used for interacting with the Asieserverlist API
 *
 * @author jakimfett
 */
require_once 'class.database.php';
class api extends database{
    function __construct(){
        
        // Status variable, just for fun
        $this->status = '';
        
        // Initiate the database connection
        $this->databaseConnection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_TABLE);
        
        if (isset($database->connect_error)){
            die('Can\'t connect to server');
        }
    }
    /**
     * 
     * @param $url          URL of the AsieLauncher Node.js server
     * @param $filename     Name of the JSON file on the AsieLauncher Node.js server
     * @return Object or FALSE
     */
    public static function getServerJSON($url, $filename = 'serverInfo.json'){
        // Attempt to get the JSON object from the provided URL
        $result = json_decode(file_get_contents($url.$filename));
        
        // Determine if a valid object was returned
        if ($result !== NULL){
            return $result;
        } else {
            return FALSE;
        }
    }
    
    /**
     * 
     * @param $server_id    A server ID
     * @param $server_ip    A server IP or URL
     * @return MySQL Object or FALSE
     */
    public function serverInDatabase($server_id, $server_ip) {
        // Instantiate the database object
        
        // Build the query
        $query = "SELECT `id`, `ip`, `hash` FROM `server` ";
        $query .= "WHERE `server_id`='{$server_id}' AND `ip`='{$server_ip}'";
        
        // Process the query
        $result = $this->databaseConnection->query($query);
        
        // Determine if the server is in the database
        if ($result->num_rows > 0 ) {
            return $result;
        } else {
            return FALSE;
        }
    }
    
    /**
     * 
     * @param $server_info  A serverInfo decoded JSON object
     * @return boolean
     */
    public function addOrUpdateServer($server_info){
        // Get hashes of the mod and plugin lists
        // @TODO when player counts are added to the server info, remove before hashing
        $hash = database::hashObject($server_info);
        
        // Returns false if the server isn't in the database already
        $server_in_database = $this->serverInDatabase($server_info->id, $server_info->ip);
        
        if ($server_in_database) {
            
            // Get the database row in array form
            $server_from_database = $server_in_database->fetch_assoc();
            
            // Check to see if any of the information has changed
            if($hash !== $server_from_database['hash']){
                // Update everything
                
                // Start building the query
                $query = "UPDATE `server` SET ";
                $query .="`server_id`='{$server_info->id}', `name`='{$server_info->name}', `description`='{$server_info->description}'";
                $query .= ", `owner`='{$server_info->owner}', `website`='{$server_info->website}', `hash`='$hash'";
                // Close out the query with the unique identifiers
                $query .="WHERE `id`='{$server_from_database['id']}' AND `ip`='{$server_from_database['ip']}'";
                
            } else {
                // Only update the player count and the update time
                // @TODO add an update time field
                $this->status = 'success';
                return TRUE;
            }
        } else {
            // If the server isn't in the database...put it there.
            
            // Start building the query
            $query = "INSERT INTO `server` ";
            $query .="(`server_id`, `ip`, `name`, `description`, `owner`, `website`, `hash`) ";
            $query .="VALUES ";
            $query .="('{$server_info->id}', '{$server_info->ip}', '{$server_info->name}', '{$server_info->description}', ";
            $query .="'{$server_info->owner}', '{$server_info->website}', '{$hash}' )";
        }
        
        if (isset($query)) {
            $result = $this->databaseConnection->query($query);
        
            if ($result) {
                $this->status = 'success';
                return TRUE;
            } else {
                $this->status = 'failure';
                return FALSE;
            }
        
        }
        
        // Should never get here, but just to be safe...
        $this->status = 'failure';
        return FALSE;
    }
    
    /**
     * 
     * @return boolean
     */
    public function updateMasterlist() {
        // Get all the servers from the database
        
        // @TODO get this info from somewhere creative. Possibly the config file?
        $master_list = array("name" => "demoServerList", "description" => "Mockup/demo for the AsieLauncher serverlist");
        
        // Load the masterlist with data
        $master_list['servers'] = $this->getAllServerData();
        
        // Verify that the data was fetched
        if ($master_list['servers']) {
            // Write the masterlist to the JSON file       
            $json_file = fopen('masterList.json', 'w');
            fwrite($json_file, json_encode($master_list));
            fclose($json_file);
            $this->status = 'success';
            return TRUE;
        } else {
            $this->status = 'failure';
            return FALSE;
        }
    }
}

?>

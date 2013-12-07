<?php
/**
 * Provides database interface functions
 *
 * @author jakimfett
 */
class database {
    function __construct() {
        
        $this->databaseConnection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_TABLE);
        
        if (isset($database->connect_error)){
            die('Can\'t connect to server');
        }
    }
    
    /**
     * 
     * @param type $object
     * @return hashed string representing the original object
     */
    public static function hashObject($object){
        return md5(serialize($object));
    }
    
    /**
     * 
     * @return Array or false
     */
    public function getAllServerData() {
        
        // Build the query
        $query = "SELECT * FROM `server`";
        
        // Run the query
        $result = $this->databaseConnection->query($query);
        
        // Verify that there are servers in the database
        if ($result){
            // Set the server info array
            $server_data = array();

            // Iterate through the entire list of servers, adding data to the array
            while($row = $result->fetch_assoc()) {
                $server_data[$row['server_id']] = $row;
            }
            
            // Return the data
            return $server_data;
        } else {
            return FALSE;
        }
    }
}
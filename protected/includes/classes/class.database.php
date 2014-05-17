<?php

/**
 * Provides database interface functions
 *
 * @author jakimfett
 */
class database {
    
    protected $databaseConnection;

    function __construct() {

        $this->databaseConnection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_TABLE);

        if (isset($this->databaseConnection->connect_error)) {
            die('Can\'t connect to server');
        }
    }

    /**
     * 
     * @param type $object
     * @return hashed string representing the original object
     */
    public static function hashObject($object) {
        return md5(serialize($object));
    }

    /**
     * 
     * @return Array or false
     */
    public function getAllServerData() {

        // Get the list of servers
        $query = "SELECT * FROM `server`";

        // Run the query
        $result = $this->databaseConnection->query($query);

        // Verify that there are servers in the database
        if ($result) {
            // Set the server info array
            $server_data = array();

            // Iterate through the entire list of servers, adding data to the array
            while ($row = $result->fetch_assoc()) {
                $server_data[$row['server_id']] = $row;

                // Build the query to get the list of addons for this server
                $addon_query = "SELECT A.`addon_id`, `type`, `name`, `version`, `description`, `authors`, `url` FROM `addon` A, `server_addon` SA";
                $addon_query .= " WHERE SA.`server_id`={$row['id']} AND A.`id`=SA.`addon_id` ";

                // Process the query
                $addon_result = $this->databaseConnection->query($addon_query);

                // Iterate through the returned addon list
                while ($addon_row = $addon_result->fetch_assoc()) {
                    $addon_row['authors'] = unserialize($addon_row['authors']);
                    if ($addon_row['type'] === 'Bukkit') {
                        $server_data[$row['server_id']]['plugins'][] = $addon_row;
                    } elseif ($addon_row['type'] === 'Forge') {
                        $server_data[$row['server_id']]['mods'][] = $addon_row;
                    }
                    if (!isset($server_data[$row['server_id']]['plugins'])) {
                        $server_data[$row['server_id']]['plugins'] = array();
                    }
                    if (!isset($server_data[$row['server_id']]['mods'])) {
                        $server_data[$row['server_id']]['mods'] = array();
                    }
                }
            }
            // Return the data
            return $server_data;
        } else {
            return FALSE;
        }
    }

    /**
     * 
     * @param $server_id    INT unique server key
     * @return boolean
     */
    public function removeAddonsFromServer($server_id) {
        // Build the query
        $query = "DELETE FROM `server_addon` WHERE `server_id`='{$server_id}'";

        // Run the query
        $result = $this->databaseConnection->query($query);

        // Return the result
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @description Check to see if an addon is in the database
     * @param $addon_id     string
     * @return boolean
     */
    public function addonInDatabase($addon_id, $addon_version) {
        // Build the query
        $query = "SELECT `id`, `version` FROM `addon` WHERE `addon_id`='{$addon_id}' AND `version`='{$addon_version}'";

        // Run the query
        $result = $this->databaseConnection->query($query);

        // Return the result
        if ($result->num_rows) {
            return $result->fetch_assoc();
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
        if ($result->num_rows > 0) {
            return $result;
        } else {
            return FALSE;
        }
    }

    /**
     * 
     * @param $addon    Object containing addon information
     * @return boolean
     */
    public function addAddonToDatabase($addon) {

        // Sanitize the input
        $addon = $this->sanitizeAddon($addon);
        
        // Build the query
        $query = "INSERT INTO `addon` ";
        $query .="(`addon_id`, `type`, `name`, `version`, `description`, `authors`, `url`) ";
        $query .="VALUES ";
        $query .="('{$addon->id}', '{$addon->type}', '{$addon->name}', '{$addon->version}'";
        $query .=", '{$addon->description}', '" . serialize($addon->authors) . "', '{$addon->url}' )";

        // Run the query
        $result = $this->databaseConnection->query($query);
        // Return the result
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function linkServerWithAddon($addon_id, $server_id) {
        // Build the query
        $query = "INSERT INTO `server_addon` ";
        $query .="(`server_id`, `addon_id`) ";
        $query .="VALUES ";
        $query .="('{$addon_id}', '{$server_id}')";

        // Run the query
        $result = $this->databaseConnection->query($query);

        // Return the result
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function sanitizeAddon($addon) {
        
        foreach ($addon as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $value_key => $value_value) {
                    $value[$value_key] = $this->databaseConnection->real_escape_string($value_value);
                }
                $addon->$key = $value;
            } else {
                $addon->$key = $this->databaseConnection->real_escape_string($value);
            }
        }
        if(isset($addon->a)){
            unset($addon->a);
        }
        
        return $addon;
    }

    public function updateAddonInDatabase($addon) {
        // @TODO use addon hashes to see if there's a changed version
    }

}
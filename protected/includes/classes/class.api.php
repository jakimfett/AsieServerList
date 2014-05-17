<?php

/**
 * Functions used for interacting with the Asieserverlist API
 *
 * @author jakimfett
 */
require_once 'class.database.php';

class api extends database {

    function __construct() {

        // Status variable, just for fun
        $this->status = '';
        $this->addonsChanged = false;

        // Initiate the database connection
        $this->databaseConnection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB);

        if (isset($database->connect_error)) {
            die('Can\'t connect to server');
        }
    }

    /**
     * 
     * @param $url          URL of the AsieLauncher Node.js server
     * @param $filename     Name of the JSON file on the AsieLauncher Node.js server
     * @return Object or FALSE
     */
    public static function getServerJSON($url, $filename = 'serverInfo.json') {
        // Attempt to get the JSON object from the provided URL
        $result = json_decode(file_get_contents($url . $filename));

        // Determine if a valid object was returned
        if ($result !== NULL) {
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
    public function addOrUpdateServer($server_info) {

        // Get hashes of the mod and plugin lists
        // @TODO when player counts are added to the server info, remove before hashing
        $hash = database::hashObject($server_info);


        // Returns false if the server isn't in the database already
        $server_in_database = $this->serverInDatabase($server_info->id, $server_info->ip);

        if ($server_in_database) {

            // Get the database row in array form
            $server_from_database = $server_in_database->fetch_assoc();

            // Check to see if any of the information has changed
            if ($hash !== $server_from_database['hash'] OR isset($_GET['debug'])) {
                // Update everything
                // Set the local variable so addons get updated
                $this->addonsChanged = TRUE;


                // Start building the query
                $query = "UPDATE `server` SET ";
                $query .="`server_id`='{$server_info->id}', `asie_url`='{$server_info->asie_url}', `name`='{$server_info->name}', `description`='{$server_info->description}'";
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
            // Set local variable so that addons get updated
            $this->addonsChanged = TRUE;

            // Start building the query
            $query = "INSERT INTO `server` ";
            $query .="(`server_id`, `ip`, `asie_url`, `name`, `description`, `owner`, `website`, `hash`) ";
            $query .="VALUES ";
            $query .="('{$server_info->id}', '{$server_info->ip}', '{$server_info->asie_url}', '{$server_info->name}', '{$server_info->description}', ";
            $query .="'{$server_info->owner}', '{$server_info->website}', '{$hash}' )";
        }

        if (isset($query)) {
            $result = $this->databaseConnection->query($query);

            if ($this->addonsChanged === TRUE) {
                $this->updateAddons($server_info);
            }
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

    public function updateAddons($server_info) {

        // Returns false if the server isn't in the database already
        $server_in_database = $this->serverInDatabase($server_info->id, $server_info->ip);

        // Check to make sure the server exists before adding mods to it
        if ($server_in_database) {
            // Get the database row in array form
            $server_from_database = $server_in_database->fetch_assoc();

            // Remove all addons from server
            // @TODO figure out how to update instead of removing/re-adding each time
            $addons_removed = $this->removeAddonsFromServer($server_from_database['id']);

            // Putting the mods and plugins into a single addons array
            $server_addons = array($server_info->mods, $server_info->plugins);

            // Usiung the combined mods/plugins array, do a double foreach
            foreach ($server_addons as $server_addon_list) {
                foreach ($server_addon_list as $addon) {
                    if (!isset($addon->version)) {
                        $addon->version = 0;
                    }
                    if (!isset($addon->id) AND isset($addon->name)) {
                        $addon->id = strtolower(preg_replace('/\s+/', '', $addon->name));
                    } elseif (!isset($addon->id) AND isset($addon->type)) {
                        $addon->id = $addon->name = strtolower(preg_replace('/\s+/', '', $addon->type));
                    }
                    // Check to see if the addon is in the database
                    $addon_unique = $this->addonInDatabase($addon->id, $addon->version);

                    if (!$addon_unique) {
                        // Add addon entry in database
                        $addon_status = $this->addAddonToDatabase($addon);

                        if ($addon_status) {
                            // Get the addon unique ID
                            $addon_unique = $this->addonInDatabase($addon->id, $addon->version);
                        } else {
                            // Database error. Blame the hardware.
                            header("HTTP/1.1 500 Internal Server Error");
                            die('<h1>500 Internal Server Error</h1><br/>Gremlins ate your query');
                        }
                    }

                    if ($addon_unique) {
                        // Add entry linking the server with the addon
                        $addon_linked = $this->linkServerWithAddon($server_from_database['id'], $addon_unique['id']);
                    } else {
                        // If you don't have an addon_unique array at this point,
                        // something has gone horribly wrong. Only Notch can save you now...
                        header("HTTP/1.1 500 Internal Server Error");
                        die('<h1>500 Internal Server Error</h1><br/>SWHW (Something Went Horribly Wrong) error');
                    }
                }
            }
        }
    }
}
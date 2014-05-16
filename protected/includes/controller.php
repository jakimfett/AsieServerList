<?php

require_once 'classes/class.utility.php';
$data = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . 'masterList.json'), true);

$utility = new Utility();

$servers = array();
// Counting here rather than in the for loop saves a bit of time
$server_count = count($data['servers']);
$bookmark = 0;
$per_page = 100;
for ($i = $bookmark; $i <= $bookmark + $per_page; $i ++) {
    if ($server_count <= $i) {
        // On the last page, stop processing on the last server
        $i = $bookmark + $per_page;
        break;
    }
    $server_array = array_slice($data['servers'], $i, 1);
    $single_server = array_shift($server_array);

    if ($utility->checkUrlResolves($single_server['asie_ip'])) {
        // Load server data into the servers array for display

        foreach ($single_server as $key => $value) {
            if ($key != 'mods' && $key != 'plugins') {
                $servers[$single_server['name']][$key] = $value;
            }
        }
        foreach ($single_server['plugins'] as $plugin_data) {
            $servers[$single_server['name']]['plugins'][$plugin_data["addon_id"]] = $plugin_data["version"];            
        }
        foreach ($single_server['mods'] as $mod_data) {
            $servers[$single_server['name']]['mods'][$mod_data["addon_id"]] = $mod_data["version"];            
        }
    } else {
        // Set server status as disabled
    }
}
unset($data);
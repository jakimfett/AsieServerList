<?php

$test_server_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

$data = json_decode(file_get_contents('http://'.$test_server_url.'/masterList.json'));
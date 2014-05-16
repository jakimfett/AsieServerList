<?php
$data = json_decode(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . 'masterList.json'));

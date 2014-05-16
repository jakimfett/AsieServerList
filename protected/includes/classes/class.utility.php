<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author jakimfett
 */
class Utility {

    public function checkUrlResolves($url = null) {
        if ($url) {
            $headers = @get_headers($url);
            if ($headers[0]) {
                return true;
            }
        }
        return false;
    }

}

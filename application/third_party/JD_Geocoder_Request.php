<?php

/**
 * LICENSE
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * In other words: use at your own risk
 *
 * @package         Geocoder
 * @dependencies  none
 * @copyright       Copyright (c) Jörg Drzycimski (http://www.drzycimski.com)
 * @license         New BSD License
 * @version         $Id: Geocoder/Request.php, v1.0 2011-01-25
 */

class JD_Geocoder_Request
{
    /**
    * @class vars
    */

    // Google´s geocode URL
    public $url = 'http://maps.google.com/maps/api/geocode/json?';

    // Params for request
    public $sensor      = "false"; // REQUIRED FOR REQUEST!
    public $language    = "en";

    // Class vars
    public $response            = '';
    public $country_long    = '';
    public $country_short   = '';
    public $region_long     = '';
    public $region_short    = '';
    public $city                    = '';
    public $address             = '';
    public $lat                     = '';
    public $lng                     = '';
    public $location_type   = '';
    public $zipcode = '';

    /**
    * Constructor
    *
    * @param mixed $config
    * @return void
    */
    public function __construct($config = null)
    {

    }

    /**
    * Forward search: string must be an address
    *
    * @param string $address
    * @return obj $response
    */
    public function forwardSearch($address)
    {
        return $this->_sendRequest("address=" . urlencode(stripslashes($address)));
    } // end forward

    /**
    * Reverse search: string must be latitude and longitude
    *
    * @param float $lat
    * @param float $lng
    * @return obj $response
    */
    public function reverseSearch($lat, $lng)
    {
        return $this->_sendRequest("latlng=" . (float) $lat . ',' . (float) $lng);
    } // end reverse

    /**
    * Search Address Components Object
    *
    * @param string $type
    * @return object / false
    */
    function searchAddressComponents($type) {
        foreach($this->response->results[0]->address_components as $k=>$found){
            if(in_array($type, $found->types)){
                return $found;
            }
        }
        return false;
    }

    /**
    * Send Google geocoding request
    *
    * @param string $search
    * @return object response (body only)
    */
    private function _sendRequest($search) {
        $url = $this->url . $search . '&language=' . strtolower($this->language) . '&sensor=' . strtolower($this->sensor);

        $resp_json = self::curl_file_get_contents($url);
        $this->response = json_decode($resp_json);

        if ($this->response->status == "OK") {
            // set some default values for reading
            $defaults = $this->_setDefaults();
            return $this->response;
        } else {
            //echo "Geocoding failed, server responded: " . $this->response->status;
            return false;
        }
    } // end request


     /**
    * Use CURL to make request
    *
    * @param URL
    * @return Contents
    */
    private function curl_file_get_contents($URL){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }

    /**
    * Parse JSON default values: map object values to readable content
    *
    * @param none
    * @return none
    */
    private function _setDefaults()
    {
        $country = $this->searchAddressComponents("country");

        $this->country_long = $country->long_name;
        $this->country_short    = $country->short_name;
        $region = $this->searchAddressComponents("administrative_area_level_2"); // Canvio des del nivell 1

        if($region){
            $this->region_long = $region->long_name;
            $this->region_short = $region->short_name;
        }
        /*$region = $this->searchAddressComponents("administrative_area_level_1"); // Canvio des del nivell 1
        $this->region_long = $region->long_name;
        $this->region_short = $region->short_name;*/
        $city = $this->searchAddressComponents("locality");
        if($city){
            $this->city = $city->short_name;
        }
        $this->address = $this->response->results[0]->formatted_address;
        $this->lat = $this->response->results[0]->geometry->location->lat;
        $this->lng = $this->response->results[0]->geometry->location->lng;
        $this->location_type = $this->response->results[0]->geometry->location_type;
        $zipcode = $this->searchAddressComponents("postal_code"); // Afegit
        if($zipcode){
            $this->zipcode  = $zipcode->short_name;
        } else {
            $this->zipcode = '';
        }
    } // end set

} // end class

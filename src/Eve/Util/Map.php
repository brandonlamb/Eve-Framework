<?php
namespace Eve\Util;
use Eve\Curl as Curl;

class Map
{
    /**
     * Open Street Map
     * @param  string            $address
     * @return array('latitude', 'longitude', 'address')
     */
    public static function openstreetmap($address)
    {
        $url = 'http://nominatim.openstreetmap.org/search/' . rawurlencode($address) . '?limit=1&format=json&email=user@example.com';

        // account new curl call to API server to login
        $curl = new Curl\Json($url, array());

        // Get api
        $api = $curl->exec();

        if (isset($api[0])) {
            return array('latitude' => $api[0]->lat, 'longitude' => $api[0]->lon, 'address' => $api[0]->display_name);
        } else {
            return array('latitude' => 0, 'longitude' => 0, 'address' => '');
        }
    }

    /**
     * Google Maps API v2.0
     * Returns the geo coordinates for an address
     * @param  string            $address
     * @return array('latitude', 'longitude', 'address')
     */
    public static function google($address)
    {
        // get google maps api key from config
        $apiKey = \Eve::app()->getComponent('config')->google['maps']['key'];

        $address = urlencode($address);
        $url = 'http://maps.google.com/maps/geo?q=' . $address . '&output=json&sensor=false&key=' . $apiKey;

        // account new curl call to API server to login
        $curl = new Curl\Json($url, array());

        // Get api
        $api = $curl->exec();

        if ($api->Status->code == 200) {
            $lon = (isset($api->Placemark[0]->Point->coordinates[0])) ? $api->Placemark[0]->Point->coordinates[0] : null;
            $lat = (isset($api->Placemark[0]->Point->coordinates[1])) ? $api->Placemark[0]->Point->coordinates[1] : null;

            return array('latitude' => $lat, 'longitude' => $lon, 'address' => $api->Placemark[0]->address);
        }

        return array('latitude' => 0, 'longitude' => 0, 'address' => '');
    }
}

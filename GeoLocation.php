<?php

namespace Royal\Library;
use Yii;
use yii\helpers\Json;

class GeoLocation
{
    public static function byIp($ip = null)
    {
        if ($ip === null)
        {
            $ip = Yii::app()->request->getUserHostAddress();
        }
        $location = @file_get_contents('http://ip-api.com/json/' . $ip);

        if (!empty($location) AND is_string($location))
        {
            $location = Json::decode($location);

            if (isset($location['status']) AND $location['status'] == 'success')
            {
                return ['region' => $location['regionName'], 'city' => $location['city']];
            }
        }

        return NULL;
    }
    
    public static function addressByLatLong($latitude, $longitude = '')
    {
        if (is_array($latitude) AND count($latitude) > 1)
        {
            $longitude = $latitude[1];
            $latitude = $latitude[0];
        }

        if (!empty($latitude) AND ! empty($longitude))
        {
            $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($latitude) . ',' . trim($longitude) . '&sensor=true';

            $response = @file_get_contents($url);

            if (!empty($response))
            {
                $response = Json::decode($response);

                if (isset($response['results'][0]['formatted_address']))
                {
                    return $response['results'][0]['formatted_address'];
                }
            }
        }

        return 'GeoLocation Not Found';
    }

    public static function mapUrlByLatLong($latitude, $longitude = '')
    {
        if (is_array($latitude) AND count($latitude) > 1)
        {
            $longitude = $latitude[1];
            $latitude = $latitude[0];
        }

        if (!empty($latitude) AND ! empty($longitude))
        {
            return 'https://www.google.com/maps/@' . trim($latitude) . ',' . trim($longitude) . ',13z';
        }

        return '';
    }
}

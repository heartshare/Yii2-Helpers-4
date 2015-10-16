<?php

namespace Royal\Library;
use Yii;

class Helpers
{

    public static function fa2en($number)
    {
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $num = range(0, 9);
        return str_replace($persian, $num, $number);
    }

    public static function getLocation($ip = null)
    {
        if ($ip === null)
        {
            $ip = Yii::app()->request->getUserHostAddress();
        }
        $location = @file_get_contents('http://ip-api.com/json/' . $ip);

        if (!empty($location) AND is_string($location))
        {
            $location = CJSON::decode($location);

            if (isset($location['status']) AND $location['status'] == 'success')
            {
                return ['region' => $location['regionName'], 'city' => $location['city']];
            }
        }

        return NULL;
    }
    
    public static function getLocationName($latitude, $longitude = '')
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
                $response = @CJSON::decode($response);

                if (isset($response['results'][0]['formatted_address']))
                {
                    return $response['results'][0]['formatted_address'];
                }
            }
        }

        return 'GeoLocation Not Found';
    }

    public static function getMapUrl($latitude, $longitude = '')
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

    public static function zip($sourcePath, $outZipPath, $isDirectory = true)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $z = new ZipArchive();
        $z->open($outZipPath, ZIPARCHIVE::CREATE);

        if ($isDirectory)
        {
            $z->addEmptyDir($dirName);
            self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
        }
        else
        {
            $z->addFile($sourcePath, $dirName);
        }

        return $z->close();
    }

    public static function copyDirectory($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source))
        {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source))
        {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest))
        {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read())
        {
            // Skip pointers
            if ($entry == '.' || $entry == '..')
            {
                continue;
            }

            // Deep copy directories
            self::copyDirectory("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }

    public static function removeDirectory($dir)
    {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file)
        {
            if ($file->isDir())
            {
                //chmod($file->getRealPath(), 0755);
                rmdir($file->getRealPath());
            }
            else
            {
                //chmod($file->getRealPath(), 0755);
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    private static function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle))
        {
            if ($f != '.' && $f != '..')
            {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip. 
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath))
                {
                    $zipFile->addFile($filePath, $localPath);
                }
                elseif (is_dir($filePath))
                {
                    // Add sub-directory. 
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }
}

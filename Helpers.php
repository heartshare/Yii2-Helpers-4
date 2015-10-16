<?php

namespace Royal\Library;

class Helpers
{
    public static function fa2en($number)
    {
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $num = range(0, 9);
        return str_replace($persian, $num, $number);
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

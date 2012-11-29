<?php
/**
 *
 * This file is part of the AltamiraBundle package.
 *
 * Copyright (c) 2012 Malwarebytes
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Malwarebytes\AltamiraBundle;


/**
 * Handles composer install/update
 */
class ScriptHandler
{

    // Must use function because no constructor to properly initialize property
    // http://stackoverflow.com/questions/5847905/cannot-use-concatenation-when-declaring-default-class-properties-in-php
    static private function getJSDir() {
        return __DIR__.DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."js";
    }
    static private function getLibsDir() {
        return __DIR__.DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."libs";
    }
    static private function getYUIBin() {
        return "java -jar ".escapeshellarg(static::getLibsDir().DIRECTORY_SEPARATOR."jqplot".DIRECTORY_SEPARATOR."extras".DIRECTORY_SEPARATOR."yuicompressor-2.4.2.jar");
    }


    // if this gets any bigger, break it up into separate methods
    public static function installJSDependencies($event) {
        echo "Installing JS Library dependencies for the AltamiraBundle\n";
        $dir = getcwd();

        ScriptHandler::gitSubmodulesUpdate();
        ScriptHandler::cleanPublicJSDir();


        echo "Compiling jqplot\n";
        chdir(static::getLibsDir().DIRECTORY_SEPARATOR."jqplot");
        exec('ant clean min', $output, $status);
        if ($status) {
            die("Ant failed with $status (is ant installed?)\n");
        }
        chdir($dir);

        echo "Compiling flot\n";
        $flotLib=static::getLibsDir() .DIRECTORY_SEPARATOR."flot";
        if (($files = scandir($flotLib)) ===false ) {
            die("failed to traverse through flot directory");
        }
        foreach ($files as $file) {
            if (substr($file,-3) == ".js") {
                exec(static::getYUIBin(). " ".escapeshellarg($flotLib.DIRECTORY_SEPARATOR.$file)." -o "
                    .escapeshellarg(static::getLibsDir().DIRECTORY_SEPARATOR."flot".DIRECTORY_SEPARATOR.substr($file,0,-2)."min.js"));
            }
        }

        echo "Compiling flot bubbles\n";
        $flotBubblesLib=static::getLibsDir() .DIRECTORY_SEPARATOR."flot-bubbles";
        if (($files = scandir($flotBubblesLib)) ===false ) {
            die("failed to traverse through flot bubbles directory");
        }

        foreach ($files as $file) {
            if (substr($file,-3) == ".js") {
                // minification did not work out, so commented min code.
                // exec(static::getYUIBin(). " ".escapeshellarg($flotBubblesLib.DIRECTORY_SEPARATOR.$file) ." -o "
                //    .escapeshellarg(static::getLibsDir().DIRECTORY_SEPARATOR."flot-bubbles".DIRECTORY_SEPARATOR.substr($file,0,-2)."min.js"));

                // straight copy to min.js for compatibility with min setting in altamira
                copy($flotBubblesLib.DIRECTORY_SEPARATOR.$file,static::getLibsDir().DIRECTORY_SEPARATOR."flot-bubbles".DIRECTORY_SEPARATOR.substr($file,0,-2)."min.js");
            }
        }

        echo "Installing jqplot\n";
        mkdir(static::getJSDir().DIRECTORY_SEPARATOR."jqplot",0777,true);
        $source = static::getLibsDir().DIRECTORY_SEPARATOR."jqplot".DIRECTORY_SEPARATOR."dist";
        $dest= static::getJSDir().DIRECTORY_SEPARATOR."jqplot";
        recursiveAssetsOnlyCopy($source,$dest);



        echo "Installing flot\n";
        mkdir(static::getJSDir().DIRECTORY_SEPARATOR."flot",0777,true);
        recursiveAssetsOnlyCopy(static::getLibsDir().DIRECTORY_SEPARATOR."flot",static::getJSDir().DIRECTORY_SEPARATOR."flot");


        echo "Installing flot-bubbles\n";
        mkdir(static::getJSDir().DIRECTORY_SEPARATOR."flot-bubbles",0777,true);
        recursiveAssetsOnlyCopy(static::getLibsDir().DIRECTORY_SEPARATOR."flot-bubbles",static::getJSDir().DIRECTORY_SEPARATOR."flot-bubbles");
    }

    public static function gitSubmodulesUpdate() {
        echo "Pulling latest submodule repositories from git\n";
        $status = null;
        $output = array();
        $dir = getcwd();
        chdir(__DIR__);
        exec('git submodule sync', $output, $status);
        if ($status) {
            chdir($dir);
            die("Running git submodule sync failed with $status\n");
        }
        exec('git submodule foreach git reset --hard', $output, $status);
        exec('git submodule foreach git clean -df', $output, $status);

        exec('git submodule update --init --recursive', $output, $status);
        chdir($dir);
        if ($status) {
            die("Running git submodule --init --recursive failed with $status\n");
        }
    }

    public static function cleanPublicJSDir() {
        echo "Clearing (possibly) stale js assets from public resource folder\n";
        if (is_dir(static::getJSDir())) {
            rrmdir(static::getJSDir());
        }
    }
}


function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function recursiveAssetsOnlyCopy($source,$dest) {
    foreach (
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        } else {
            if (substr($item,-3) == ".js" || substr($item,-3) == "css") {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }
}

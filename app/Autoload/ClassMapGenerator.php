<?php

namespace App\Autoload;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClassMapGenerator {
    /**
     * Generates a classmap and writes it to the specified file
     *
     * @param string $directory the directory to scan
     * @param string $file the target file for the classmap to be written to
     */
    public static function generate(string $directory, string $file) {
        file_put_contents($file, self::createMap($directory));
    }

    /**
     * Generates a classmap
     *
     * @param string $path the directory to scan
     * @return string the resulting classmap
     */
    public static function createMap(string $path) {
        $files      = self::findFiles($path);
        $classes    = [];

        foreach($files as $file) {
            $fqcn           = self::findFullyQualifiedClassName($file);
            $classes[$fqcn] = $file;
        }

        //Get the current timestamp
        $timestamp = date("d-m-Y H:m:s");

        //Generate the classmap
        $code = <<<CODE
<?php

// Do not modify - changes will be overwritten
// Automatically @generated by Shoutz0r at $timestamp

return %s;
CODE;

        // Return the code output
        return sprintf($code, var_export($classes, true));
    }

    /**
     * Find all files recursively in a directory
     *
     * @param string $path the directory to scan
     * @return array the files that have been found
     */
    private static function findFiles(string $path) : array {
        $files = [];
        $validExtensions = ['php'];

        //If the path is a directory, iterate through it
        if(is_dir($path)) {
            $it = new RecursiveDirectoryIterator($path);

            foreach(new RecursiveIteratorIterator($it) as $file)
            {
                if (self::hasFileExtension($file, $validExtensions)) {
                    $files[] = $file;
                }
            }
        }
        //If the path is a file, check if it is a valid extension
        else if(is_file($path)) {
            if(self::hasFileExtension($path, $validExtensions)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * Returns the fully qualified classname from a file (if applicable)
     *
     * @param string $filepath the target file to check
     * @return string the resulting fqcn, empty if not found
     */
    private static function findFullyQualifiedClassName(string $filepath) : string {
        $code = file_get_contents($filepath);
        $tokens = token_get_all($code);

        $class = $namespace = '';

        foreach($tokens as $token) {
            if($token[0] === T_NAMESPACE) {
                $namespace = $token[1];
            }
            else if($token[0] === T_CLASS) {
                $class = $token[1];
                break;
            }
        }

        return $namespace . '\\' . $class;
    }

    /**
     * Gets the extension from the specified file
     *
     * @param string $filename the filename to get the extension from
     * @return string the extension, empty if not applicable
     */
    private static function getFileExtension(string $filename) : string {
        $extension = explode('.', $filename);

        if(count($extension) <= 1) {
            return '';
        }

        //Since a file can theoretically contain multiple dots, we only want the last element
        return strtolower($extension[count($extension) - 1]);
    }

    /**
     * Checks if the filename matches (one of) the extension(s)
     *
     * @param string $filename the filename to check
     * @param array $validExtensions the valid extension(s)
     * @return bool true if matching
     */
    private static function hasFileExtension(string $filename, array $validExtensions) : bool {
        $fileExt = self::getFileExtension($filename);

        return in_array($fileExt, $validExtensions);
    }
}

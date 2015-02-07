<?php
/**
 * generate-symbols.php
 * @author Stefan Fodor
 * @year 2015
 */

function getHelperMapping( $root ) {

    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
        RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
    );


    //all helper paths
    $helper_files = array();
    foreach ($iter as $path => $dir) {

        //no dirs
        if ($dir->isDir()) {
            continue;
        }

        //and only php files
        if( pathinfo( $path, PATHINFO_EXTENSION) != "php" ) {
            continue;
        }

        //reduce path
        $relative_path = str_replace("./concrete/helpers/","",$path);
        $relative_path = str_replace("./helpers/","",$relative_path);

        $parts = explode("/",$relative_path);

        $parts[ count($parts)-1  ] = str_replace(".php","",$parts[ count($parts)-1  ]);

        //important
        $helper_name = implode("/",$parts);

        $class_name = "";
        foreach($parts as $thisPart) {
            $miniparts = explode("_",$thisPart);
            foreach($miniparts as $thisMiniPart) {
                $class_name .= ucfirst($thisMiniPart);
            }
        }
        $class_name .= "Helper";

        $helper_files[ $helper_name ] = $class_name;

    }

    return $helper_files;
}

/**
 * Get helpers
 */
$core_helpers = getHelperMapping('./concrete/helpers');
$my_helpers = getHelperMapping('./helpers');

$all_helpers = array_merge( $core_helpers, $my_helpers );

/**
 * Save to file
 */
$file_cont = '<?php namespace PHPSTORM_META { $STATIC_METHOD_TYPES = array(\\Loader::helper(\'\') => array(' . PHP_EOL;

foreach($all_helpers as $thisHelperName => $thisHelperClass ) {

    $file_cont .= '\'' . $thisHelperName . '\' instanceof \\' . $thisHelperClass . ',' . PHP_EOL;

}

$file_cont .= '));}';

file_put_contents(
    ".phpstorm.meta.php",
    $file_cont
);

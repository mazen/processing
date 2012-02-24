<?php

spl_autoload_register(function($class) {
    $file = str_replace(array('\\','_'), DIRECTORY_SEPARATOR, $class).'.php';

    if ( is_readable(__DIR__.'/../lib/'.$file)) {
        return include_once(__DIR__.'/../lib/'.$file);
    }
    if(is_readable(__DIR__.'/'.$file)) {
        return include_once(__DIR__.'/'.$file);
    }
});

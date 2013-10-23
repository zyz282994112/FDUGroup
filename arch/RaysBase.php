<?php
/**
 * Base framework bootstrap file
 * @author: Raysmond
 */

define('SYSTEM_PATH', dirname(__FILE__));

define('SYSTEM_CORE_PATH', SYSTEM_PATH . '/base');

define('CONTROLLER_PATH', SYSTEM_PATH . '/controllers');

define('MODEL_PATH', SYSTEM_PATH . '/models');

define('VIEW_PATH', SYSTEM_PATH . '/views');

define('UTILITIES_PATH',SYSTEM_PATH.'/utilities');

define('MODULES_PATH',SYSTEM_PATH.'/modules');


/**
 * Class RaysBase is a basic helper class for common framework functionalities.
 * @author: Raysmond
 */
class RaysBase
{

    public static $classMap = array();

    public static $moduleMap = array();

    public static $_app;

    public static $copyright;

    public static $_includePaths = array(
        CONTROLLER_PATH,
        MODEL_PATH,
        VIEW_PATH,
        SYSTEM_CORE_PATH,
        UTILITIES_PATH,
        MODULES_PATH,
    );

    public static function setApplication($app)
    {
        if (self::$_app === null && $app != null)
            self::$_app = $app;
        else {
            die("Application not found!");
        }
    }

    public static function app()
    {
        return self::$_app;
    }

    public static function createApplication($config){
        return new RWebApplication($config);
    }

    public static function getFrameworkPath(){
        return SYSTEM_PATH;
    }

    public static function importModule($moduleId){
        if(!isset(self::$moduleMap[$moduleId])){
            $path = Rays::app()->modulePath."/".$moduleId."/".$moduleId.self::app()->moduleFileExtension;
            self::$moduleMap[$moduleId] = $path;
            require($path);
        }
    }

    /**
     * Class autoload loader
     * This method is invoked within an __antoload() magic method
     * @param string $classname class name
     */
    public static function autoload($classname){
        if(isset(self::$classMap[$classname]))
            require(self::$classMap[$classname]);
        else{
            foreach(self::$_includePaths as $path)
            {
                $classFile = $path.DIRECTORY_SEPARATOR.$classname.'.php';
                if(is_file($classFile))
                {
                    require($classFile);
                    break;
                }
            }
        }
    }

    public static function getCopyright(){
        if(!isset(self::$copyright)){
            return "© Copyright ".self::app()->name." 2013, All Rights Reserved.";
        }
        else return self::$copyright;
    }
}

//禁用错误报告
//error_reporting(0);
//报告运行时错误
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
//报告所有错误
//error_reporting(E_ALL);

spl_autoload_register(array('RaysBase','autoload'));
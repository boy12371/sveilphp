<?php
/**
 * The router class file of the External Transaction Interface
 *
 * @copyright   Copyright 2009-2013 sveil.com
 * @license     LGPL (http://www.gnu.org/licenses/lgpl.html)
 * @author      Richard
 * @package     com.frame.router
 * @version     $Id: router.class.php 888 2013-05-20 11:43:00
 * @link        http://www.xyagree.com
 */

/**
 * The router class.
 *
 * @package com.frame.router
 */
class Router
{
    /**
     * The directory seperator.
     *
     * @var string
     * @access private
     */
    private $pathFix;

    /**
     * The base path of the External Transaction Interface
     *
     * @var string
     * @access private
     */
    private $basePath;

    /**
     * The root directory of the framwork($this->basePath/frame)
     *
     * @var string
     * @access private
     */
    private $frameRoot;

    /**
     * The root directory of the core library($this->basePath/lib)
     *
     * @var string
     * @access private
     */
    private $coreLibRoot;

    /**
     * The root directory of the app.
     *
     * @var string
     * @access private
     */
    private $appRoot;

    /**
     * The root directory of the app library($this->appRoot/lib).
     * 
     * @var string
     * @access private
     */
    private $appLibRoot;

    /**
     * The root directory of temp.
     * 
     * @var string
     * @access private
     */
    private $tmpRoot;

    /**
     * The root directory of cache.
     * 
     * @var string
     * @access private
     */
    private $cacheRoot;

    /**
     * The root directory of log.
     * 
     * @var string
     * @access private
     */
    private $logRoot;

    /**
     * The root directory of config.
     * 
     * @var string
     * @access private
     */
    private $configRoot;

    /**
     * The global $config object.
     *
     * @var string
     * @access public
     */
    public $config;

    /**
     * The global $dbh object, the database connection handler.
     *
     * @var string
     * @access public
     */
    public $dbh;

    /**
     * The value of the request.
     *
     * @var string
     * @access public
     */
    public $calling;

    /**
     * The construct function.
     *
     * Prepare all the paths, classes, super objects and so on.
     * Notice:
     * 1. You should use the createApp() method to get an instance of the router.
     * 2. If the $appRoot is empty, the framework will compute the appRoot according the $appName
     *
     * @param string $appName   the name of the app
     * @param string $appRoot   the root path of the app
     * @access protected
     * @return void
     */
    protected function __construct($appName, $appRoot)
    {
        $this->setPathFix();
        $this->setBasePath();
        $this->setConfigRoot();
        $this->setFrameRoot();
        $this->setCoreLibRoot();
        $this->setAppRoot($appName, $appRoot);
        $this->setAppLibRoot();
        $this->setTmpRoot();
        $this->setCacheRoot();
        $this->setLogRoot();

        $this->loadConfig();
        $this->setDebug();

        $this->connectDB();

        $this->setTimezone();

        $this->loadClass('dao', true);
        //$this->loadClass('sql', true);
    }

    /**
     * Create an application.
     *
     * <code>
     * <?php
     * $demo = router::createApp('demo');
     * ?>
     * or specify the root path of the app. Thus the app and framework can be seperated.
     * <?php
     * $demo = router::createApp('demo', '/home/app/demo');
     * ?>
     * </code>
     * @param string $appName   the name of the app
     * @param string $appRoot   the root path of the app
     * @param string $className the name of the router class. When extends a child, you should pass in the child router class name.
     * @static
     * @access public
     * @return object   the app object
     */
    public static function createApp($appName = 'admin', $appRoot = '', $className = 'router')
    {
        return new $className($appName, $appRoot);
    }

    //----------------------- path related methods -----------------------//

    /**
     * Set the path directory.
     *
     * @access protected
     * @return void
     */
    protected function setPathFix()
    {
        $this->pathFix = DIRECTORY_SEPARATOR;
    }

    /**
     * Set the base path.
     *
     * @access protected
     * @return void
     */
    protected function setBasePath()
    {
        $this->basePath = realpath(dirname(dirname(__FILE__))) . $this->pathFix;
    }

    /**
     * Set the config root.
     *
     * @access protected
     * @return void
     */
    protected function setConfigRoot()
    {
        $this->configRoot = $this->basePath . 'config' . $this->pathFix;
    }

    /**
     * Set the frame root.
     *
     * @access protected
     * @return void
     */
    protected function setFrameRoot()
    {
        $this->frameRoot = $this->basePath . 'frame' . $this->pathFix;
    }

    /**
     * Set the core library root.
     *
     * @access protected
     * @return void
     */
    protected function setCoreLibRoot()
    {
        $this->coreLibRoot = $this->basePath . 'lib' . $this->pathFix;
    }

    /**
     * Set the app root.
     *
     * @param string $appName
     * @param string $appRoot
     * @access protected
     * @return void
     */
    protected function setAppRoot($appName, $appRoot)
    {
        if(empty($appRoot))
        {
            $this->appRoot = $this->basePath . 'app' . $this->pathFix . $appName . $this->pathFix;
        }
        else
        {
            $this->appRoot = realpath($appRoot) . $this->pathFix;
        }
        if(!is_dir($this->appRoot)) $this->error("The app you call not found in {$this->appRoot}", __FILE__, __LINE__);
    }

    /**
     * Set the app lib root.
     *
     * @access protected
     * @return void
     */
    protected function setAppLibRoot()
    {
        $this->appLibRoot = $this->appRoot . 'lib' . $this->pathFix;
    }

    /**
     * Set the tmp root.
     *
     * @access protected
     * @return void
     */
    protected function setTmpRoot()
    {
        $this->tmpRoot = $this->appRoot . 'tmp' . $this->pathFix;
    }

    /**
     * Set the cache root.
     *
     * @access protected
     * @return void
     */
    protected function setCacheRoot()
    {
        $this->cacheRoot = $this->tmpRoot . 'cache' . $this->pathFix;
    }

    /**
     * Set the log root.
     *
     * @access protected
     * @return void
     */
    protected function setLogRoot()
    {
        $this->logRoot = $this->tmpRoot . 'log' . $this->pathFix;
    }

    /**
     * set Debug
     *
     * @access public
     * @return void
     */
    public function setDebug()
    {
        if($this->config->debug)
        {
            error_reporting(E_ALL & ~E_NOTICE);
            register_shutdown_function('saveSQL');
        }
    }

    /**
     * Set the time zone according to the config.
     *
     * @access public
     * @return void
     */
    public function setTimezone()
    {
        if(isset($this->config->timezone)) date_default_timezone_set($this->config->timezone);
    }

    /**
     * Get the $pathFix var
     *
     * @access public
     * @return string
     */
    public function getPathFix()
    {
        return $this->pathFix;
    }

    /**
     * Get the $basePath var
     *
     * @access public
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get the $configRoot var
     *
     * @access public
     * @return string
     */
    public function getConfigRoot()
    {
        return $this->configRoot;
    }

    /**
     * Get the $frameRoot var
     *
     * @access public
     * @return string
     */
    public function getFrameRoot()
    {
        return $this->frameRoot;
    }

    /**
     * Get the $coreLibRoot var
     *
     * @access public
     * @return string
     */
    public function getCoreLibRoot()
    {
        return $this->coreLibRoot;
    }

    /**
     * Get the $appRoot var
     *
     * @access public
     * @return string
     */
    public function getAppRoot()
    {
        return $this->appRoot;
    }

    /**
     * Get the $appLibRoot var
     *
     * @access public
     * @return string
     */
    public function getAppLibRoot()
    {
        return $this->appLibRoot;
    }

    /**
     * Get the $tmpRoot var
     *
     * @access public
     * @return string
     */
    public function getTmpRoot()
    {
        return $this->tmpRoot;
    }

    /**
     * Get the $cacheRoot var
     *
     * @access public
     * @return string
     */
    public function getCacheRoot()
    {
        return $this->cacheRoot;
    }

    /**
     * Get the $logRoot var
     *
     * @access public
     * @return string
     */
    public function getLogRoot()
    {
        return $this->logRoot;
    }

    /**
     * Get the $webRoot var.
     *
     * @access public
     * @return string
     */
    public function getWebRoot()
    {
        return $this->config->webRoot;
    }

    //-------------------- Request related methods. --------------------//

    /**
     * The entrance of parseing request. Get the calling value.
     * 
     * @access public
     * @return array
     */
    public function parseRequest()
    {
        global $calling;

        if(!$_REQUEST)
        {
            $this->error('Parameter Missing', __FILE__, __LINE__, 2);
        }
        elseif(!isset($_REQUEST['calling']))
        {
            $this->error('Parameter Error', __FILE__, __LINE__, 3);
        }
        elseif(empty($_REQUEST['calling']))
        {
            $this->error('Parameter Empty', __FILE__, __LINE__, 1);   // Parameter Empty
        }
        else
        {
            $calling = helper::Decode($_REQUEST['calling'], true);
            if (!isset($calling['methodName'])) $this->error('JSON Method Name Error', __FILE__, __LINE__, 35);
            if (!(isset($calling['token']) xor (isset($calling['productID']) && isset($calling['operatorID'])))) $this->error('JSON Parameter Empty', __FILE__, __LINE__, 36);
            if (!isset($calling['requestID'])) $this->error('JSON RequestID Missing', __FILE__, __LINE__, 37);
        }
        $this->loadMethod($calling['methodName']);
        $this->calling = $calling;
        return $calling;
    }

    /**
     * Load a method.
     *
     * 1. include the app lib file.
     * 2. instance the method class.
     *
     * @access public
     * @return object|bool the instance of the class or just true.
     */
    public function loadMethod($className)
    {
        $className = strtolower($className);

        // Search in $appLibRoot.
        $classFile = $this->appLibRoot . $className . '.class';
        if(!file_exists($classFile)) $this->error("class file $classFile not found", __FILE__, __LINE__, 35);

        helper::import($classFile);

        // Instance it.
        if(!class_exists($className)) $this->error("the class $className not found in $classFile", __FILE__, __LINE__, 35);
        if(!is_object($$className)) $$className = new $className();
        return $$className;
    }

    //-------------------- Tool methods.------------------//

    /**
     * The error handler.
     *
     * @param string    $message    error message
     * @param string    $file       the file error occers
     * @param int       $line       the line error occers
     * @param int       $errorCode  the error code
     * @access public
     * @return void
     */
    public function error($message, $file, $line, $errorCode)
    {
        // Log the error info.
        $log = "ERROR: $message in $file on line $line";
        if(isset($_SERVER['SCRIPT_URI'])) $log .= ", request: $_SERVER[SCRIPT_URI]";;
        $trace = debug_backtrace();
        extract($trace[0]);
        extract($trace[1]);
        $log .= ", last called by $file on $line through function $function.";
        try
        {
            // If errorCode exists, output the errorCode.
            if(isset($errorCode))
            {
                $log .= "Output the errorCode $errorCode.";
                throw new customException($errorCode);
            }
            $log .= 'At ' . helper::now() . "\n";
            error_log($log, 3, $this->logRoot.date(DATETIME2).'.log');
        }
        catch(customException $e)
        {
            echo $e->errorMessage();
            die();
        }
    }

    /**
     * Load a class file.
     *
     * First search in $appLibRoot, then $coreLibRoot.
     *
     * @param   string $className  the class name
     * @param   bool   $static     statis class or not
     * @access  public
     * @return  object|bool the instance of the class or just true.
     */
    public function loadClass($className, $static = false)
    {
        $className = strtolower($className);

        // Search in $coreLibRoot at first.
        $classFile = $this->coreLibRoot . $className . '.class';

        if(!file_exists($classFile)) $this->error("class file $classFile not found", __FILE__, __LINE__, 3);

        helper::import($classFile);

        // If staitc, return.
        if($static) return true;

        // Instance it.
        if(!class_exists($className)) $this->error("the class $className not found in $classFile", __FILE__, __LINE__, 3);
        if(!is_object($$className)) $$className = new $className();
        return $$className;
    }

    /**
     * Load config and return it as the global config object.
     *
     * If the module is common, search in $configRoot, else in $modulePath.
     *
     * @param   string $moduleName     module name
     * @param   bool  $exitIfNone     exit or not
     * @access  public
     * @return  object|bool the config object or false.
     */
    public function loadConfig()
    {
        $extConfigFiles = array();

        // Set the main config file and extension config file.
        $mainConfigFile = $this->configRoot . 'config';
        $myConfig       = $this->configRoot . 'my';
        if(file_exists($myConfig)) $extConfigFiles[] = $myConfig;

        // Set the files to include.
        if(!file_exists($mainConfigFile))
        {
            $this->error("config file $mainConfigFile not found", __FILE__, __LINE__);
            if(empty($extConfigFiles)) return false;  //  and no extension file, exit.
            $configFiles = $extConfigFiles;
        }
        else
        {
            $configFiles = array_merge(array($mainConfigFile), $extConfigFiles);
        }

        global $config;
        if(!is_object($config)) $config = new stdClass;
        static $loadedConfigs = array();
        foreach($configFiles as $configFile)
        {
            if(in_array($configFile, $loadedConfigs)) continue;
            include $configFile;
            $loadedConfigs[] = $configFile;
        }

        $this->config = $config;

        return $config;
    }

    /**
     * Connect to database.
     *
     * @access public
     * @return object|bool the database handler.
     */
    public function connectDB()
    {
        global $config, $dbh;
        
        try
        {
            $dbh = new PDO(
                    "mysql:host={$config->db->host}; port={$config->db->port}; dbname={$config->db->name}",
                    $config->db->user,
                    $config->db->passwd,
                    array(
                            PDO::ATTR_PERSISTENT => $config->db->persistent,
                            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                            PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ,
                            PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET @@sql_mode= ''",
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config->db->encoding}"
                    )
            );
            $this->dbh = $dbh;
            return $dbh;
        }
        
        catch (PDOException $e)
        {
            // Datebase Connection Error
            $this->error($e->getMessage(), __FILE__, __LINE__, 20);
        }
    }
}

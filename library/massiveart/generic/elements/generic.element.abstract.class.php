<?php

/**
 * GenericElementAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.elements
 * @subpackage GenericElementAbstract
 */

abstract class GenericElementAbstract {
  
  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Zend_Loader_PluginLoader_Interface
   */
  protected static $_pluginLoader;
  
  /**
   * @var GenericDataHelper
   */
  protected $_helper;
  
  /**
   * properties of the element
   * @var Array
   */
  protected $arrProperties = array();
    
  /**
   * @var GenericSetup
   */
  protected $setup;
  /**
   * property of the generic setup object
   * @return GenericSetup $setup
   */
  public function Setup(){
    return $this->setup;
  }
  
  /**
   * Constructor
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  } 
    
  /**
   * setGenericSetup
   * @param GenericSetup $objGenericSetup
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setGenericSetup(GenericSetup &$objGenericSetup){
    $this->setup = $objGenericSetup;
  }
  
  /**
   * __set
   * @param string $strName
   * @param mixed $mixedValue
   */
  public function __set($strName, $mixedValue) {      
    $this->arrProperties[$strName] = $mixedValue;
  }
  
  /**
   * __get
   * @param string $strName
   * @return mixed $mixedValue
   */
  public function __get($strName) {      
    if (array_key_exists($strName, $this->arrProperties)) {
      return $this->arrProperties[$strName];
    }
    return null;
  }
  
  /**
   * getProperties
   * @return array
   */
  public function getProperties() {      
    return $this->arrProperties;
  }
  
  /**
   * Method overloading
   *
   * @param  string $method
   * @param  array $args
   * @return mixed
   * @throws Zend_Controller_Action_Exception if helper does not have a direct() method
   */
  public function __call($strMethod, $arrArgs){
    try{
      $objHelper = $this->getHelper();
      if (!method_exists($objHelper, $strMethod)) {
        throw new Exception('Helper "' . $this->type . '" does not support overloading via '.$strMethod.'()');
      }
      return call_user_func_array(array($objHelper, $strMethod), $arrArgs); 
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * getHelper 
   */
  public function getHelper(){
    try{
      if(!$this->_helper instanceof GenericDataHelper){
        
        try {
          $class = $this->getPluginLoader()->load($this->type);
        } catch (Zend_Loader_PluginLoader_Exception $e) {            
          throw new Exception('Action Helper by name ' . $this->type . ' not found');
        }

        $this->_helper = new $class();

        if (!$this->_helper instanceof GenericDataHelperAbstract) {
            throw new Exception('Helper name ' . $this->type . ' -> class ' . $class . ' is not of type GenericDataHelperAbstract');
        }
        
        $this->_helper->setElement($this);
      }
      return $this->_helper;      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getPluginLoader
   * @return Zend_Loader_PluginLoader
   */
  public static function getPluginLoader(){
    if(null === self::$_pluginLoader){
      require_once 'Zend/Loader/PluginLoader.php';
      self::$_pluginLoader = new Zend_Loader_PluginLoader(array(
          'GenericDataHelper' => dirname(__FILE__).'/../data/helpers/',
      ));
    }
    return self::$_pluginLoader;
  }
}

?>
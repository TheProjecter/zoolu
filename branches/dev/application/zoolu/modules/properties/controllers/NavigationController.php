<?php

/**
 * NavigationController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-15: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Properties_NavigationController extends AuthControllerAction {
  
	private $intRootLevelId;
	private $intFolderId;
	
	private $intParentId;	
	
	private $intLanguageId;
	
  /**
   * @var Model_Categories
   */
  protected $objModelCategories;
  
  /**
   * @var Model_Contacts
   */
  protected $objModelContacts;
  
  /**
   * @var Model_Folders
   */
  protected $objModelFolders;
  
  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){
    $objPropertiesRootLevels = $this->getModelFolders()->loadAllRootLevels($this->core->sysConfig->modules->properties);
    
    $this->view->assign('rootLevels', $objPropertiesRootLevels);
  	$this->view->assign('categoryFormDefaultId', $this->core->sysConfig->form->ids->categories->default);
  	$this->view->assign('unitFormDefaultId', $this->core->sysConfig->form->ids->units->default);
  	$this->view->assign('contactFormDefaultId', $this->core->sysConfig->form->ids->contacts->default);
  }

  /**
   * catnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function catnavigationAction(){
    $this->core->logger->debug('properties->controllers->NavigationController->rootnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam('currLevel');
    $intCategoryTypeId = $objRequest->getParam('categoryTypeId');
    
    if($intCurrLevel == 1){
      $intItemId = 0;	
    }else{
      $intItemId = $objRequest->getParam("itemId");	
    }
    
    /**
     * get navigation
     */
    $this->getModelCategories();
    $objCatNavElements = $this->objModelCategories->loadCatNavigation($intItemId, $intCategoryTypeId);
    
    $this->view->assign('catelements', $objCatNavElements);
    $this->view->assign('currLevel', $intCurrLevel);
    $this->view->assign('categoryTypeId', $intCategoryTypeId);    
  }
  
  /**
   * contactnavigationAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function contactnavigationAction(){
    $this->core->logger->debug('properties->controllers->NavigationController->contactnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam('currLevel');
    
    if($intCurrLevel == 1){
      $intItemId = 0; 
    }else{
      $intItemId = $objRequest->getParam("itemId"); 
    }
    
    /**
     * get navigation
     */
    $this->getModelContacts();
    $objContactNavElements = $this->objModelContacts->loadNavigation($intItemId);
    
    $this->view->assign('elements', $objContactNavElements);
    $this->view->assign('currLevel', $intCurrLevel); 
  }
  
  /**
   * getModelCategories
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelCategories(){
    if (null === $this->objModelCategories) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Categories.php';
      $this->objModelCategories = new Model_Categories();
      $this->objModelCategories->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelCategories;
  }
  
  /**
   * getModelContacts
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelContacts(){
    if (null === $this->objModelContacts) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Contacts.php';
      $this->objModelContacts = new Model_Contacts();
      $this->objModelContacts->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelContacts;
  }
  
  /**
   * getModelFolders
   * @return Model_Folders
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelFolders(){
    if (null === $this->objModelFolders) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
      $this->objModelFolders = new Model_Folders();
      $this->objModelFolders->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelFolders;
  }
  
  /**
   * setRootLevelId
   * @param integer $intRootLevelId
   */
  public function setRootLevelId($intRootLevelId){
    $this->intRootLevelId = $intRootLevelId;  
  }
  
  /**
   * getRootLevelId
   * @param integer $intRootLevelId
   */
  public function getRootLevelId(){
    return $this->intRootLevelId;  
  }
  
  /**
   * setFolderId
   * @param integer $intFolderId
   */
  public function setFolderId($intFolderId){
    $this->intFolderId = $intFolderId;  
  }
  
  /**
   * getFolderId
   * @param integer $intFolderId
   */
  public function getFolderId(){
    return $this->intFolderId;  
  }
  
  /**
   * setParentId
   * @param integer $intParentId
   */
  public function setParentId($intParentId){
    $this->intParentId = $intParentId;  
  }
  
  /**
   * getParentId
   * @param integer $intParentId
   */
  public function getParentId(){
    return $this->intParentId;  
  }
  
  /**
   * setLanguageId
   * @param integer $intLanguageId
   */
  public function setLanguageId($intLanguageId){
    $this->intLanguageId = $intLanguageId;  
  }
  
  /**
   * getLanguageId
   * @param integer $intLanguageId
   */
  public function getLanguageId(){
    return $this->intLanguageId;  
  }
  

}

?>

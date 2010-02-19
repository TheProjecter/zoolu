<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * FolderController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-14: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Core_FolderController extends AuthControllerAction {

	/**
	 * @var GenericForm
	 */
	var $objForm;

	/**
   * request object instance
   * @var Zend_Controller_Request_Abstract
   */
  protected $objRequest;

	/**
   * @var Model_Folders
   */
  protected $objModelFolders;

  /**
   * @var CommandChain
   */
  protected $objCommandChain;

  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    $this->objRequest = $this->getRequest();
    $this->initCommandChain();
  }

  /**
   * initCommandChain
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  private function initCommandChain(){
    $this->core->logger->debug('core->controllers->FolderController->initCommandChain()');
    $this->objCommandChain = new CommandChain();

    if($this->objRequest->getParam('rootLevelTypeId') == $this->core->sysConfig->root_level_types->portals){
      $this->core->logger->debug('add page command');
      $this->objCommandChain->addCommand(new PageCommand());
    }

    if($this->objRequest->getParam('rootLevelTypeId') == $this->core->sysConfig->root_level_types->products){
      $this->core->logger->debug('add product command!');
      $this->objCommandChain->addCommand(new ProductCommand());
    }
  }

  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){ }

  /**
   * getaddformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getaddformAction(){
    $this->core->logger->debug('core->controllers->FolderController->getaddformAction()');

    try{
      $this->getForm($this->core->sysConfig->generic->actions->add);
      $this->addFolderSpecificFormElements();

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/core/folder/add');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      /**
       * get form title
       */
      $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

      /**
       * output of metainformation to hidden div
       */
      $this->setViewMetaInfos();

      $this->view->form = $this->objForm;

      $this->renderScript('folder/form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * addAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('core->controllers->FolderController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);
    $this->addFolderSpecificFormElements();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/core/folder/add');

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

      $arrFormData = $this->objRequest->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
	     * prepare form (add fields and region to the Zend_Form)
	     */
	    $this->objForm->prepareForm();

      if($this->objForm->isValid($arrFormData)){
        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/core/folder/edit');

        $intFolderId = $this->objForm->saveFormData();

        $this->objForm->Setup()->setElementId($intFolderId);
        $this->objForm->getElement('id')->setValue($intFolderId);
        $this->objForm->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);

        $this->view->assign('blnShowFormAlert', true);

        $arrArgs = array('ParentId'         => $intFolderId,
                         'LanguageId'       => $this->objRequest->getParam("languageId", $this->core->sysConfig->languages->default->id),
                         'GenericSetup'     => $this->objForm->Setup());
        if($this->objCommandChain->runCommand('addFolderStartElement', $arrArgs)){
          $this->view->assign('selectNavigationItemNow', true);
          $this->view->assign('itemId', 'folder'.$intFolderId);
        }

      }else{
        $this->view->assign('blnShowFormAlert', false);
      }
    }else{
    	/**
	     * prepare form (add fields and region to the Zend_Form)
	     */
	    $this->objForm->prepareForm();
    }

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    /**
     * output of metainformation to hidden div
     */
    $this->setViewMetaInfos();

    $this->view->form = $this->objForm;
    $this->renderScript('folder/form.phtml');
  }

  /**
   * geteditformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function geteditformAction(){
    $this->core->logger->debug('core->controllers->FolderController->geteditformAction()');

    try{
    	$this->getForm($this->core->sysConfig->generic->actions->edit);

      /**
       * load generic data
       */
      $this->objForm->loadFormData();
      $this->addFolderSpecificFormElements();

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/core/folder/edit');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      /**
       * get form title
       */
      $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

      /**
       * output of metainformation to hidden div
       */
      $this->setViewMetaInfos();

      $this->view->form = $this->objForm;

      $this->renderScript('folder/form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * editAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('core->controllers->FolderController->editAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);
    $this->addFolderSpecificFormElements();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

      $arrFormData = $this->objRequest->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/core/folder/edit');

      if($this->objForm->isValid($arrFormData)){
        $this->objForm->saveFormData();

        /**
         * update the folder start element
         */
        $arrArgs = array('LanguageId'       => $this->objRequest->getParam("languageId", $this->core->sysConfig->languages->default->id),
                         'GenericSetup'     => $this->objForm->Setup());
        $this->objCommandChain->runCommand('editFolderStartElement', $arrArgs);
        
        $this->view->assign('blnShowFormAlert', true);
      }else{
      	$this->view->assign('blnShowFormAlert', false);
      }
    }else{

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();
    }

    /**
     * output of metainformation to hidden div
     */
    $this->setViewMetaInfos();

    $this->view->form = $this->objForm;

    $this->renderScript('folder/form.phtml');
  }

  /**
   * changelanguageAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changelanguageAction(){
    $this->core->logger->debug('core->controllers->FolderController->changelanguageAction()');

    try{
      
     if(intval($this->objRequest->getParam('id')) > 0){
        $this->_forward('geteditform');
      }else{
        $this->_forward('getaddform');
      }      
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * setViewMetaInfos
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function setViewMetaInfos(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){
      $this->view->isurlfolder = $this->objForm->Setup()->getUrlFolder();
      $this->view->showinnavigation = $this->objForm->Setup()->getShowInNavigation();
      $this->view->folderId = $this->objForm->Setup()->getElementId();
      $this->view->version = $this->objForm->Setup()->getFormVersion();
      $this->view->publisher = $this->objForm->Setup()->getPublisherName();
      $this->view->changeUser = $this->objForm->Setup()->getChangeUserName();
      $this->view->publishDate = $this->objForm->Setup()->getPublishDate('d. M. Y');
      $this->view->changeDate = $this->objForm->Setup()->getChangeDate('d. M. Y, H:i');
      $this->view->statusOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT statusTitles.title AS DISPLAY FROM statusTitles WHERE statusTitles.idStatus = status.id AND statusTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM status', $this->objForm->Setup()->getStatusId());
      $this->view->creatorOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, CONCAT(fname, \' \', sname) AS DISPLAY FROM users', $this->objForm->Setup()->getCreatorId());

      $this->view->blnIsRootLevelChild = ($this->objForm->Setup()->getParentId() == 0) ? true : false;
      $this->view->navigationOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT navigationOptionTitles.title FROM navigationOptionTitles WHERE navigationOptionTitles.idNavigationOptions = navigationOptions.id AND navigationOptionTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM navigationOptions WHERE active = 1', $this->objForm->Setup()->getShowInNavigation());
      
      // && $this->objRequest->getParam('zoolu_module') != 2
      //if($this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->edit) 
      $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId());
    }
  }

  /**
   * deleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('core->controllers->FolderController->deleteAction()');

    $this->getModelFolders();

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
	    $this->objModelFolders->deleteFolderNode($this->objRequest->getParam("id"));

	    $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('folder/form.phtml');
  }

  /**
   * securityAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function securityAction(){
    $this->core->logger->debug('core->controllers->FolderController->securityAction()');
    try{
      $intFolderId = $this->objRequest->getParam('folderId');
      $this->view->folderSecurity = $this->getModelFolders()->getFolderSecurity($intFolderId);
      $this->view->folderId = $intFolderId;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * securityupdateAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function securityupdateAction(){
    $this->core->logger->debug('core->controllers->FolderController->securityupdateAction()');
    try{
      $intFolderId = $this->objRequest->getParam('folderId');

      $arrZooluSecurity = $this->objRequest->getParam('ZooluSecurity', array());
      $this->getModelFolders()->updateFolderSecurity($intFolderId, $arrZooluSecurity, $this->core->sysConfig->environment->zoolu);

      $arrWebsiteSecurity = $this->objRequest->getParam('WebsiteSecurity', array());
      $this->getModelFolders()->updateFolderSecurity($intFolderId, $arrWebsiteSecurity, $this->core->sysConfig->environment->website);

      $this->_forward('security');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * foldertreeAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function foldertreeAction(){
    $this->core->logger->debug('core->controllers->FolderController->foldertreeAction()');

    $intPortalId = $this->objRequest->getParam('portalId');
    $intFolderId = $this->objRequest->getParam('folderId');
    $strActionKey = $this->objRequest->getParam('key');

    $this->loadFolderTreeForPortal($intPortalId, $intFolderId);
    $this->view->assign('key', $strActionKey);
    $this->view->assign('overlaytitle', 'Ordner wählen');
  }

  /**
   * folderlistAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function folderlistAction(){
    $this->core->logger->debug('core->controllers->FolderController->folderlistAction()');

    $intPortalId = $this->objRequest->getParam('portalId');
    $intFolderId = $this->objRequest->getParam('folderId');

    $this->getModelFolders();
    $objFolderContent = $this->objModelFolders->loadFolderContentById($intFolderId);

    $this->view->assign('objFolderContent', $objFolderContent);
    $this->view->assign('intFolderId', $intFolderId);
    $this->view->assign('listTitle', $objFolderContent[0]->folderTitle);
  }

  /**
   * documenttreeAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function documentcheckboxtreeAction(){
    $this->core->logger->debug('core->controllers->FolderController->documentcheckboxtreeAction()');

    $this->getModelFolders();
    $objMediaRootLevels = $this->objModelFolders->loadAllRootLevels($this->core->sysConfig->modules->media, $this->core->sysConfig->root_level_types->documents);

    if(count($objMediaRootLevels) > 0){
      $objMediaRootLevel = $objMediaRootLevels->current();
      $this->intRootLevelId = $objMediaRootLevel->id;
      $objRootelements = $this->objModelFolders->loadRootLevelFolders($this->intRootLevelId);

      $this->view->assign('elements', $objRootelements);
      $this->view->assign('rootLevelId', $this->intRootLevelId);
      $this->view->assign('overlaytitle', 'Ordner w&auml;hlen');

      $this->view->assign('selectedRootLevelId', $this->objRequest->getParam('rootLevelId', -1));
      $this->view->assign('selectedFolderIds', $this->objRequest->getParam('folderIds', '[]'));
    }
  }

  /**
   * changeparentfolderAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changeparentfolderAction(){
    $this->core->logger->debug('core->controllers->FolderController->changeparentfolderAction()');

    $intFolderId = $this->objRequest->getParam('folderId');
    $intParentFolderId = $this->objRequest->getParam('parentFolderId');

    if($intFolderId > 0 && $intParentFolderId > 0){
      $this->getModelFolders();
      $this->objModelFolders->moveFolderToLastChildOf($intFolderId, $intParentFolderId);
    }

    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * changeparentrootfolderAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changeparentrootfolderAction(){
    $this->core->logger->debug('core->controllers->FolderController->changeparentrootfolderAction()');

    $intFolderId = $this->objRequest->getParam('folderId');
    $intRootFolderId = $this->objRequest->getParam('rootFolderId');

    if($intFolderId > 0 && $intRootFolderId > 0){
      $this->getModelFolders();
      $this->objModelFolders->moveFolderToLastChildOfRootFolder($intFolderId, $intRootFolderId);
    }

    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * loadFolderTreeForPortal
   * @param integer $intPortalId
   * @param integer $intFolderId
   * @param string $strJsAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function loadFolderTreeForPortal($intPortalId, $intFolderId){
    $this->core->logger->debug('core->controllers->FolderController->loadFolderTreeForPortal('.$intPortalId.','.$intFolderId.')');

    $this->getModelFolders();
    $objFolderTree = $this->objModelFolders->loadRootLevelFolders($intPortalId);

    $this->view->assign('elements', $objFolderTree);
    $this->view->assign('portalId', $intPortalId);
    $this->view->assign('folderId', $intFolderId);
  }

  /**
   * createForm
   * @param integer $intActionType
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
  	$this->core->logger->debug('core->controllers->FolderController->createForm('.$intActionType.')');

  	$this->view->module = ($this->objRequest->getParam('zoolu_module') != '') ? $this->objRequest->getParam('zoolu_module') : 0;
  	$this->view->core = $this->core;

  	$strFormId = $this->objRequest->getParam('formId');
    $intFormVersion = ($this->objRequest->getParam('formVersion') != '') ? $this->objRequest->getParam('formVersion') : null;
    $intElementId = ($this->objRequest->getParam('id') != '') ? $this->objRequest->getParam('id') : null;

    $objFormHandler = FormHandler::getInstance();
    $objFormHandler->setFormId($strFormId);
    $objFormHandler->setFormVersion($intFormVersion);
    $objFormHandler->setActionType($intActionType);
    $objFormHandler->setLanguageId($this->objRequest->getParam("languageId", $this->core->sysConfig->languages->default->id));
    $objFormHandler->setFormLanguageId(Zend_Auth::getInstance()->getIdentity()->languageId);
    $objFormHandler->setElementId($intElementId);

    $this->objForm = $objFormHandler->getGenericForm();

    /**
     * set page default & specific form values
     */
    $this->objForm->Setup()->setCreatorId((($this->objRequest->getParam('creator') != '') ? $this->objRequest->getParam('creator') : Zend_Auth::getInstance()->getIdentity()->id));
    $this->objForm->Setup()->setStatusId((($this->objRequest->getParam('idStatus') != '') ? $this->objRequest->getParam('idStatus') : $this->core->sysConfig->form->status->default));
    $this->objForm->Setup()->setRootLevelId((($this->objRequest->getParam('rootLevelId') != '') ? $this->objRequest->getParam('rootLevelId') : null));
    $this->objForm->Setup()->setRootLevelTypeId((($this->objRequest->getParam('rootLevelTypeId') != '') ? $this->objRequest->getParam('rootLevelTypeId') : null));
    $this->objForm->Setup()->setParentId((($this->objRequest->getParam('parentFolderId') != '') ? $this->objRequest->getParam('parentFolderId') : null));
    $this->objForm->Setup()->setUrlFolder((($this->objRequest->getParam('isUrlFolder') != '') ? $this->objRequest->getParam('isUrlFolder') : 1));
    $this->objForm->Setup()->setShowInNavigation((($this->objRequest->getParam('showInNavigation') != '') ? $this->objRequest->getParam('showInNavigation') : 0));

    /**
     * add currlevel hidden field
     */
    $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam('currLevel'), 'decorators' => array('Hidden'), 'ignore' => true));

    /**
     * add elementTye hidden field (folder, page, ...)
     */
    $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam('elementType'), 'decorators' => array('Hidden'), 'ignore' => true));

    /**
     * add zoolu_module hidden field
     */
    $this->objForm->addElement('hidden', 'zoolu_module', array('value' => $this->view->module, 'decorators' => array('Hidden'), 'ignore' => true));
  }

  /**
   * addFolderSpecificFormElements
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function addFolderSpecificFormElements(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){
      /**
       * add folder specific hidden fields
       */
      $this->objForm->addElement('hidden', 'creator', array('value' => $this->objForm->Setup()->getCreatorId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'idStatus', array('value' => $this->objForm->Setup()->getStatusId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objForm->Setup()->getRootLevelId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelTypeId', array('value' => $this->objForm->Setup()->getRootLevelTypeId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'parentFolderId', array('value' => $this->objForm->Setup()->getParentId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'isUrlFolder', array('value' => $this->objForm->Setup()->getUrlFolder(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInNavigation', array('value' => $this->objForm->Setup()->getShowInNavigation(), 'decorators' => array('Hidden'), 'ignore' => true));
    }
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
      $this->objModelFolders->setLanguageId($this->objRequest->getParam("languageId", $this->core->sysConfig->languages->default->id));
    }

    return $this->objModelFolders;
  }
}

?>

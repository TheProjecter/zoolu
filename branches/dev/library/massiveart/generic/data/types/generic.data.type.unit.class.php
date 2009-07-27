<?php

/**
 * GenericDataTypeUnit extends GenericDataTypeAbstract 
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeUnit
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypeUnit extends GenericDataTypeAbstract {
  
	/**
   * @var Model_Contacts
   */
  protected $objModelContacts;
	
	/**
	 * save
	 * @author Thomas Schedler <tsh@massiveart.com>
	 * @version 1.1
	 */
	public function save(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeUnit->save()');
		try{

      $this->getModelContacts()->setLanguageId($this->setup->getLanguageId());

			/**
			 * add|edit|newVersion core and instance data
			 */
			switch($this->setup->getActionType()){
				case $this->core->sysConfig->generic->actions->add:
				  
				  /**
				   * add unit node to the "Nested Set Model"
				   */
				  $this->setup->setElementId($this->objModelContacts->addUnitNode($this->setup->getParentId(), array('idGenericForms' => $this->setup->getGenFormId())));
				  
          if(count($this->setup->CoreFields()) > 0){
            foreach($this->setup->CoreFields() as $strField => $obField){
              $arrCoreData = array('idUnits' => $this->setup->getElementId(),
                                   'idLanguages'  => $this->setup->getLanguageId(), 
                                   $strField      => $obField->getValue());
      
              $this->getModelGenericData()->getGenericTable('unit'.((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')).'ies' : ucfirst($strField).'s'))->insert($arrCoreData);
            }
          }
          
          break;
				case $this->core->sysConfig->generic->actions->edit :

          if(count($this->setup->CoreFields()) > 0){        
            /**
             * for each core field, try to insert into the secondary table
             */
            foreach($this->setup->CoreFields() as $strField => $objField){
               
              $objGenTable = $this->getModelGenericData()->getGenericTable('unit'.((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')).'ies' : ucfirst($strField).'s'));
      
              $arrCoreData = array($strField => $objField->getValue());       
              $strWhere = $objGenTable->getAdapter()->quoteInto('idUnits = ?', $this->setup->getElementId());               
              $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());      
              $objGenTable->update($arrCoreData, $strWhere);
            }
          }
          break;
			}

			return $this->setup->getElementId();
		}catch (Exception $exc) {
			$this->core->logger->err($exc);
		}
	}

	/**
	 * load
	 * @author Thomas Schedler <tsh@massiveart.com>
	 * @version 1.0
	 */
	public function load(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeUnit->load()');
		try {
      
			$this->getModelContacts()->setLanguageId($this->setup->getLanguageId());
			$objUnitsData = $this->getModelContacts()->loadUnit($this->setup->getElementId());
			
		  if(count($objUnitsData) > 0){
        $objUnitData = $objUnitsData->current();

        if(count($this->setup->CoreFields()) > 0){
          /**
           * for each core field, try to select the secondary table
           */
          foreach($this->setup->CoreFields() as $strField => $objField){
            $objGenTable = $this->getModelGenericData()->getGenericTable('unit'.((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')).'ies' : ucfirst($strField).'s'));
            $objSelect = $objGenTable->select();

            $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array($strField));
            $objSelect->where('idUnits = ?', $objUnitData->id);

            $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();            
            
            foreach ($arrGenFormsData as $arrRowGenFormData) {
              foreach ($arrRowGenFormData as $column => $value) {
                if($column == $strField){
                  $objField->setValue($value);  
                }else{
                  $objField->$column = $value;
                }
              }
            }
            
          }
        }
      }	
		}catch (Exception $exc) {
			$this->core->logger->err($exc);
		}		 
	}
	
  /**
   * getModelContacts
   * @return Model_Contacts
   * @author Thomas Schedler <tsh@massiveart.com>
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
    }

    return $this->objModelContacts;
  }
}

?>
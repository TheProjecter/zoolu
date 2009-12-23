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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Modules
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Modules {

  /**
   * @var Model_Table_Modules
   */
  protected $objModuleTable;

  /**
   * @var Core
   */
  private $core;

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

  public function getModules(){
    $objSelect = $this->getModuleTable()->select()->where('active = 1');
    $objSelect->order('order');
    return $this->objModuleTable->fetchAll($objSelect);
  }

  /**
   * getModuleTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getModuleTable(){

    if($this->objModuleTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Modules.php';
      $this->objModuleTable = new Model_Table_Modules();
    }

    return $this->objModuleTable;
  }
}

?>
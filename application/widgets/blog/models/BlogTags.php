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
 * @package    application.widgets.blog.models
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_BlogTags
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-01-13: Florian Mathis
 *
 * @author Florian Mathis <flo@massiveart.com>
 * @version 1.1
 */

class Model_BlogTags {	
	/**
	 * @var Core
	 */
	protected $core;
	
	/**
	 * @var Model_Table_BlogEntriesTag
	 */
	protected $objBlogEntriesTagTable;
	
	/**
	 * @var string WidgetInstanceId 
	 */
	protected $strWidgetInstanceId;
	
	public function __construct() {
		$this->core = Zend_Registry::get('Core');
	}
	
	/**
	 * getTags
	 * @return array tags
	 * @author Florian Mathis <flo@massiveart.com>
	 * @version 1.0
	 */
	public function getTags(){
		$this->core->logger->debug('widgets->blog->Model_BlogTags->getTags()');
		
		$objSelect = $this->getBlogEntryTagTable()->select();
		$objSelect->setIntegrityCheck(false);
		$objSelect->from('tags', array('title', 'count(tagSubwidgets.idTags) AS c'));
		$objSelect->join('tagSubwidgets', 'tagSubwidgets.idTags = tags.id', array());
		$objSelect->join('subwidgets', 'subwidgets.subwidgetId = tagSubwidgets.subwidgetId');
		//$objSelect->join('widget_BlogEntries', 'widget_BlogEntries.subwidgetId = tagSubwidgets.subwidgetId', array());
		$objSelect->where('subwidgets.widgetInstanceId = ?', $this->strWidgetInstanceId);
		$objSelect->group('tags.title');
		
		$tags = array();
		foreach($this->objBlogEntriesTagTable->fetchAll($objSelect) AS $keywords) {
			$tags[$keywords['title']] = $keywords['c'];
		}
		
		ksort($tags);
		return $tags;
	}
	
	/**
	 * getTagsBySubwidgetId
	 * @param string $strSubwidgetId
	 * @return Zend_Db_Table_Abstract
	 * @author Florian Mathis <flo@massiveart.com>
	 * @version 1.0
	 */
	public function getTagsBySubwidgetId($strSubwidgetId) {
		$this->core->logger->debug('widgets->blog->Model_BlogTags->getTagsBySubwidgetId('.$strSubwidgetId.')');
		
		$objSelect = $this->getBlogEntryTagTable()->select();
		$objSelect->setIntegrityCheck(false);
		$objSelect->from('tags', array('title', 'count(tagSubwidgets.idTags) AS c'));
		$objSelect->join('tagSubwidgets', 'tagSubwidgets.idTags = tags.id', array());
		$objSelect->where('tagSubwidgets.subwidgetId = ?', $strSubwidgetId);
		echo $objSelect;
		return $this->objBlogEntriesTagTable->fetchAll($objSelect);
	}
	
	/**
	 * setInstanceId
	 * @param string $strWidgetInstanceId
	 * @author Florian Mathis <flo@massiveart.com>
	 * @version 1.0
	 */
	public function setInstanceId($strWidgetInstanceId){
		$this->strWidgetInstanceId = $strWidgetInstanceId;
	}
	
	/**
   * getBlogEntryTagTable
   * @return Zend_Db_Table_Abstract
   * @author Florian Mathis <flo@massiveart.com>
   * @version 1.0
   */
  public function getBlogEntryTagTable(){
    if($this->objBlogEntriesTagTable === null) {
      require_once GLOBAL_ROOT_PATH.'application/widgets/blog/models/tables/BlogEntriesTag.php';
      $this->objBlogEntriesTagTable = new Model_Table_BlogEntriesTag();
    }

    return $this->objBlogEntriesTagTable;
  }
}

?>
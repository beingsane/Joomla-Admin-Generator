<?php

	/*
	** Generated by Joomla Admin Generator
	** Author: Le Dinh Thuong
	** Email: navaroiss@gmail.com
	** 
	** Component:  com_agenda
	** Controller: CategoryController
	** File: category.php
	** Time: Tue, 17 Nov 2009 16:19:09
	*/
	
	defined('_JEXEC') or die('Restricted access');
	
	jimport('joomla.application.component.controller');

	include_once( dirname(__FILE__).DS.'interface.php' );

	class CategoryController extends JController implements Controller_Interface
	{

		var $c = '';
		var $option = '';
		var $tbl = 'agenda_category';
		var $pri_key = 'id';
		var $var = 'category';
		
		var $_ext = array(
			'image'=>array('png','gif','jpg','jpeg'),
			'video'=>array('mp4','avi','wmv','swf','flv'),
			'audio'=>array('mp3','wma','au'),
			'document'=>array('doc','pdf','ppt','xml','xls'),
			'compress'=>array('rar','zip','tgz','gz','tar'),
			'application'=>array('exe','php','java','sh','pl','py')
		);

		function CategoryController()
		{
			$GLOBALS['db'] = new AgendaModels();
                        $this->option = $_REQUEST['option'];
                        $this->c = $_REQUEST['c'];
		}

		function editRecord($edit)
		{
			global $db;
			// Toolbar
			$title = ($edit==false)?"Add a new record":"Edit a record";
			JToolBarHelper::title( JText::_( $title ), 'generic.png' );
			JToolBarHelper::save( 'save' );
			JToolBarHelper::apply('apply');
			JToolBarHelper::cancel( 'cancel' );

			$id =& JRequest::getVar($this->pri_key, '', 'get', 'int');
			$data = $db->loadRecord($this->getDataTbl(), array($this->pri_key=>$id)); 
			return Render_HTML::editRecord($edit, $data);
		}

		function saveRecord($_POST, $action = '', $task='')
		{
			global $db, $mainframe;
			$id = $_POST[$this->pri_key];

			$data = new stdClass();
			foreach($_POST[$this->var] as $k=>$v)
			{
				$data->$k = $v;
			}

                        if(count($_FILES)>=1)
                        {
				jimport('joomla.filesystem.file');
				
                        	foreach($_FILES as $k=>$v)
                        	{
                        		foreach($v['name'] as $k=>$vl)
                        		{						
						$filename = JFile::makeSafe($vl);
						$dest = dirname(JPATH_BASE) . DS . "administrator/components/com_agenda/assets/upload/" . DS . $filename; //die($dest);
						$src = $_FILES[$this->var]['tmp_name'][$k];
						if ( in_array(strtolower(JFile::getExt($filename) ), $file_ext_allow[$k]) && JFile::upload($src, $dest)) {
							if (JFile::exists($dest))
								 $data->$k = $filename;// echo $dest;die();
						}                        		
                        		}
                        	}
                        }

			if($action=="edit"){
				$key_field = $this->pri_key;
				$data->$key_field = $id;

				if(count($_POST[$this->var]['delete_files'])>=1){
					foreach($_POST[$this->var]['delete_files'] as $k=>$v)
					{
						$data->$k = '';
					}
				}
				
				$db->updateRecord($this->getDataTbl(), $data, $this->pri_key);
				$db->query();
			}else{
				$db->insertRecord($this->getDataTbl(), $data);
			}

			if($task=="apply")
			{
				$url = array(
					'task'=>$_POST['action'],
					$this->pri_key=>$_POST[$this->pri_key],
				);
				$this->refresh($url);
			}
			else 
				$this->refresh();;
		}

		function showRecord()
		{
			global $db, $mainframe;
			jimport('joomla.html.pagination');

			JToolBarHelper::title( 'Show all records', 'generic.png' );
			JToolBarHelper::addNewX();
			JToolBarHelper::deleteList( "Are you sure? " , 'remove');

			$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
			$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
			$join_query	= array();
			$condition 	= array();
			$keywords = $_POST['keywords'];
			$find_keyword = '';
			
			if ($keywords!='') $find_keyword = "`value` like '%$keywords%'";
			
			if( count($_POST)>=1 ){
if($_POST[$this->var]['parent']>=1){
        $value = $_POST[$this->var]['parent'];        $join_query['select']  = "a.*"; 
        $join_query['table']  = "#__agenda_category a";
        $join_query['condition']  = "a.parent=#__agenda_category.id and a.parent = $value";}}

			$total = $db->loadRecords($this->getDataTbl(), $condition, 0, 0, true, $join_query, $find_keyword); //print_r($total);
			$paging = new JPagination($total, $limitstart, $limit);
			$data = $db->loadRecords($this->getDataTbl(), $condition, $limitstart, $limit, false, $join_query, $find_keyword);
			
			return Render_HTML::showRecord($data, $paging);
		}

		function removeRecord()
		{
			global $db;
			$db->removeRecords($this->getDataTbl(), array_values($_POST[$this->pri_key]));
			$this->refresh();
		}

		function refresh($params = array())
		{
			global $mainframe;
			$url = "index.php?option=".$this->option."&c=".$this->c;
			if(is_array($params) && count($params)>=1){
				foreach($params as $k=>$v){
					$p[] = "$k=$v";
				}
				$url .= '&'.implode('&', $p);
			}
			$mainframe->redirect($url);
		}

		function getDataTbl()
		{
			return $this->tbl;
		}
	}
?>
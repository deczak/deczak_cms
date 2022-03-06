<?php

class controllerDashboard extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelCategories();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}

	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;
	
		$logicDone = false;

		/*
		switch($controllerAction)
		{
		#	case 'view'		  : $logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete); break;
		#	case 'delete' : $logicDone = $this -> logicXHRDelete($_pDatabase); break;	

			case 'view'		: $logicDone = $this -> logicView(	$_pDatabase, $enableEdit, $enableDelete);	break;
			case 'xhr_edit'		: $logicDone = $this -> logicXHREdit(	$_pDatabase, $_xhrInfo);	break;
			case 'xhr_delete'	: $logicDone = $this -> logicXHRDelete($_pDatabase);	break;	
			case 'xhr_ping'		: $logicDone = $this -> logicXHRPing($_pDatabase);	break;	
			case 'xhr_index' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;		
			case 'create'	: $logicDone = $this -> logicCreate($_pDatabase);	break;
			case 'xhr_create'	: $logicDone = $this -> logicXHRCreate($_pDatabase);	break;
		}
		*/

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$dashboardInfo = CSession::get() -> sessionExtendedSettings -> dashboard ?? null;

		if(empty($dashboardInfo))
		{
			$dashboardInfo = json_decode('{"widgetList":[
				{"name":"widget_test", "size":"25"}
			]}');
		}

		foreach($dashboardInfo -> widgetList as &$item)
		{
			if(!file_exists(__DIR__.'/widgets/'.$item -> name.'.php'))
				continue;


			include_once __DIR__.'/widgets/'. $item -> name.'.php';

			$className = $item -> name;

			$item -> instance  = new $className;
			$item -> instance -> logic($_pDatabase, $item -> size);
		}

		$this -> setView(	
						'index',	
						'',
						[
							'dashboardInfo' => $dashboardInfo
						]
						);

		return true;
	}
}
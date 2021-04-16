<?php

class	CDirector
{
	private $viewList;

	public function
	__construct()
	{
		$this -> viewList = [];
	}

	public function
	register(string $_viewId = '')
	{
		if(empty($_viewId))
			$_viewId = strval(count($this -> viewList) + 1);

		if(!isset($this -> viewList[$_viewId]))
			$this -> viewList[$_viewId] = 	[
												"id" => $_viewId
											];

		return $_viewId;
	}

	public function
	view(string $_viewId = '', CPageRequest &$_pPageRequest, &$_pUserRights)
	{
		if($_pPageRequest -> isEditMode)
		{
			echo '<div class="cms-edit-content-container" data-view="'. $_viewId .'">';

			foreach($_pPageRequest -> objectsList as $_objectIndex =>  &$_object)
			{
				if($_object -> instance === NULL)
					continue; 

				if($_object -> content_id !== $_viewId)
					continue;

				$rightsString = json_encode($_pUserRights -> getModuleRights($_object -> module_id));
				$rightsString = str_replace('"', "", $rightsString);
				$rightsString = str_replace('[', "", $rightsString);
				$rightsString = str_replace(']', "", $rightsString);

				echo '<div class="cms-content-object" data-rights="'. $rightsString .'">';
				$_object -> instance -> view();
				echo '</div>';
			}

			echo '</div>';
		}
		else
		{
			if($_pPageRequest  -> objectsList === NULL) return;

			foreach($_pPageRequest  -> objectsList as $_objectIndex =>  &$_object)
			{
				if($_object -> instance === NULL)
					continue; 

				if($_object -> content_id !== $_viewId)
					continue;

				$_object -> instance -> view();
			}
		}
	}

}
?>
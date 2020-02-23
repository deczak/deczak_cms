<?php

class	CUserRights
{
	private	$m_rightsList;

	public function
	__construct()
	{
		$this -> m_rightsList = [];
	}

	public function
	loadUserRights(&$_sqlConnection, $_userId)
	{
		$sqlString		=	"	SELECT		tb_users_groups.*,
											tb_right_groups.*
								FROM		tb_users_groups
								LEFT JOIN	tb_right_groups ON tb_right_groups.group_id = tb_users_groups.group_id
								WHERE		tb_users_groups.user_id	= '". $_sqlConnection -> real_escape_string($_userId) ."'
							";



		$sqlURightsRes	= 	$_sqlConnection -> query($sqlString);		

		while($sqlURightsRes !== false && $sqlURightsItm = $sqlURightsRes -> fetch_assoc())
		{
			$sqlURightsItm['group_rights'] = json_decode($sqlURightsItm['group_rights'], true);
		
			if(!empty($sqlURightsItm['group_rights']))
			foreach($sqlURightsItm['group_rights'] as $_moduleRights  => $_rightsSet)
			{
				if(!isset($this -> m_rightsList[$_moduleRights]))
					$this -> m_rightsList[$_moduleRights] = new CUserRightsItm($_moduleRights);

				$this -> m_rightsList[$_moduleRights] -> addRights($_rightsSet);
			}										
		}
	}

	public function
	getModuleRights(int $_moduleId) : array
	{
		if(isset($this -> m_rightsList[$_moduleId]))
			return $this -> m_rightsList[$_moduleId] -> m_rights;

		return [];
	}

	public function
	existsRight(int $_moduleId, string $_rightId)
	{
		$moduleRight = $this -> getModuleRights($_moduleId);
		return in_array($_rightId, $moduleRight, true);
	}

	public function
	disableEditRights(bool $_disable)
	{
		if(!$_disable)
			return false;

		foreach($this -> m_rightsList as $rightsIndex => $rights)
		{
			foreach($rights -> m_rights as $rightIndex => $right)
			{
				if($right != 'index' && $right != 'view')
					unset($this -> m_rightsList[$rightsIndex] -> m_rights[$rightIndex]);
			}
		}
	}
}

class	CUserRightsItm
{
	public $m_rights;
	public $m_moduleId;

	public function
	__construct(int $_moduleId)
	{
		$this -> m_rights 	= [];
		$this -> m_moduleId	= $_moduleId;
	}

	public function
	addRights(array $_rights)
	{
		foreach($_rights as $right)
		{
			if(!in_array($right, $this -> m_rights, true))
				$this -> m_rights[] = $right;
		}
	}
}
?>
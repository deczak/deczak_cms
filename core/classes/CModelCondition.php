<?php

class CModelConditionStage
{
	public	$type;
	public	$column;
	public	$value;

	public function
	__construct($_type, $_column, $_value)
	{
		$this -> type 		= $_type;
		$this -> column 	= $_column;
		$this -> value 		= $_value;
	}
}

class CModelConditionOrder
{
	public	$column;
	public	$direction;

	public function
	__construct($_column, $_direction)
	{
		$this -> column 	= $_column;
		$this -> direction	= $_direction;
	}
}

class CModelConditionGroup
{
	public	$column;

	public function
	__construct($_column)
	{
		$this -> column 	= $_column;
	}
}

class CModelCondition
{
	public $conditionList;
	public $conditionLevel;
	public $oderByList;
	public $groupByList;
	public $limit;
	public $offset;

	public function
	__construct()
	{
		$this -> conditionList 		= [];
		$this -> conditionLevel 	= 0;
		$this -> groupByList 		= [];

		$this -> limit				= 0;
		$this -> offset				= 0;
	}

	public function
	where(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('=', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereNot(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('!=', $_columnName, $_columnValue);
		return $this;
	}

	public function
	or()
	{
		$this -> conditionLevel++;
		$this -> conditionList[] = [];
		return $this;
	}

	public function
	orderBy(string $_columnName, string $_direction = 'ASC')
	{
		$this -> oderByList[] = new CModelConditionOrder($_columnName, $_direction);
		return $this;
	}

	public function
	groupBy(string $_columnName)
	{
		$this -> groupByList[] = new CModelConditionGroup($_columnName);
		return $this;
	}

	public function
	limit(int $limit, int $offset = 0)
	{
		$this -> limit				= $limit;
		$this -> offset				= $offset;
	}

	public function
	isSet()
	{
		if(count($this -> conditionList[0]) === 0)
			return false;
		return true;
	}

	public function
	getConditions(&$_sqlConnection, &$_condition)
	{
		if($_condition !== NULL)
		{
			$_sqlString = '';
			if(count($_condition -> conditionList) != 0)
			{

				$_sqlString	= " WHERE ";

				$firstCondition = true;

				foreach($_condition -> conditionList as $condition)
				{
					if(!$firstCondition)
					{
						$_sqlString .= " OR ";
						$firstCondition = true;
					}

					foreach($condition as $conditionStage)
					{
						if($firstCondition)
						{
							$firstCondition = false;
						}
						else
						{
							$_sqlString .= " AND ";
						}

						$_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." '". $_sqlConnection -> real_escape_string($conditionStage -> value) . "' ";
					}
				}
			}
			
			if(count($_condition -> groupByList) != 0)
			{
				$arrayOrderBy = [];

				foreach($_condition -> groupByList as $oderBy)
					$arrayOrderBy[] = " `". $oderBy -> column ."` ";

				$_sqlString	.= " GROUP BY ". implode(',', $arrayOrderBy);
			}	

			if(count($_condition -> oderByList) != 0)
			{
				$arrayOrderBy = [];

				foreach($_condition -> oderByList as $oderBy)
					$arrayOrderBy[] = " `". $oderBy -> column ."` ". $oderBy -> direction;

				$_sqlString	.= " ORDER BY ". implode(',', $arrayOrderBy);
			}	

			if($this -> limit !== 0)
			{
				if($this -> offset !== 0)				
					$_sqlString	.= " LIMIT ". $this -> offset .", ". $this -> limit ." " ;				
				else	
					$_sqlString	.= " LIMIT ". $this -> limit ." " ;
			}
			
			return $_sqlString;
		}	
		return '';	
	}

	public function
	getConditionListValue($_columnName)
	{
		foreach($this -> conditionList as $conditionLevel)
		{
			foreach($conditionLevel as $conditionItem)
			{
				if($conditionItem -> column === $_columnName)
				{
					return $conditionItem -> value; 
				}
			}
		}

		return false;
	}
}

?>
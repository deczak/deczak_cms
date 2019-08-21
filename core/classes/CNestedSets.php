<?php

/*

	a)	Create a table, change the table name

		CREATE TABLE your_tabel_name (
		node_id    INT(12)      UNSIGNED NOT NULL AUTO_INCREMENT,
		node_name  VARCHAR(100)  NOT NULL,
		lft   INT(12)      UNSIGNED NOT NULL,
		rgt   INT(12)      UNSIGNED NOT NULL,
		PRIMARY KEY (node_id),
		key lft (lft),
		key rgt (rgt)
		)

	b)	Add the first initial node, you can add a name if you want

		INSERT INTO your_tabel_name (name,lft,rgt) VALUES ('',1,2)

	c)	Usage

		1) 	Make an object

		include 'CNestedSets.php';
		$_pNestedSet = new CNestedSets( $yourSQLInstance , 'my_awesome_table' );


		// Add a child to the initial node (id = 1)
		$_pNestedSet -> addChild(1, 'My node name');

		// Get all nodes in a sorted array
		$_nodeMap 	= [];
		$_pNestedS -> getNodeMap($_nodeMap);

*/

class CNestedSets
{
	private	$m_tableName;
	private	$m_sqlInstance;

	public function
	__construct(&$_sqlInstance, $_tableName)
	{
		if(empty($_tableName))
		{
			trigger_error('CNestedSets::__construct() - Table name is empty');
			return;
		}

		if(empty($_sqlInstance) || !property_exists($_sqlInstance, 'connect_errno') || $_sqlInstance -> connect_errno)
		{
			trigger_error('CNestedSets::__construct() - SQL Instance is not valid');
			return;
		}

		$this -> m_tableName 	= $_tableName;
		$this -> m_sqlInstance 	= &$_sqlInstance;
	}

	/**
	 * 	Rename a node
	 * 	@param 	int 	$_nodeID 		NodeID
	 * 	@param 	string 	$_nodeName 		New name for the node
	 */
	public function
	renameNode($_nodeID, $_nodeName)
	{
		if($this -> m_sqlInstance == NULL) return;

		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET node_name = '". $this -> m_sqlInstance -> real_escape_string($_nodeName) ."' WHERE node_id = '". $this -> m_sqlInstance -> real_escape_string($_nodeID) ."'");
	}

	/**
	 * 	Add a Child to the called node
	 * 	@param 	int 	$_parentNodeID 	NodeID
	 * 	@param 	string 	$_nodeName 		Name of the new node
	 */
	public function
	addChild($_parentNodeID, $_nodeName)
	{
		if($this -> m_sqlInstance == NULL) return;

		$_parentNode = $this -> getLR($_parentNodeID);
		if($_parentNode === false)
		{
			trigger_error('CNestedSets::addChild() - Parent node does not exists');
			return;
		}

		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET rgt=rgt+2 WHERE rgt >= ". $_parentNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft+2 WHERE lft >  ". $_parentNode['rgt']);
		$this -> m_sqlInstance -> query("INSERT INTO `". $this -> m_tableName ."` (node_name,lft,rgt) VALUES ('". $this -> m_sqlInstance -> real_escape_string($_nodeName) ."', ". $_parentNode['rgt'] .", ". ($_parentNode['rgt'] + 1) .")");
	}

	/**
	 * 	Add a sibling to the called node
	 * 	@param 	int 	$_nodeID 		NodeID
	 * 	@param 	string 	$_nodeName 		Name of the new node
	 */
	public function
	addSibling($_nodeID, $_nodeName)
	{
		if($this -> m_sqlInstance == NULL) return;

		$_siblinNode = $this -> getLR($_nodeID);
		if($_siblinNode === false)
		{
			trigger_error('CNestedSets::addSister() - Parent node does not exists');
			return;
		}

		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET rgt=rgt+2 WHERE rgt >= ". $_siblinNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft+2 WHERE lft >  ". $_siblinNode['rgt']);
		$this -> m_sqlInstance -> query("INSERT INTO `". $this -> m_tableName ."` (name,lft,rgt) VALUES ('". $this -> m_sqlInstance -> real_escape_string($_nodeName) ."', ". $_siblinNode['rgt'] .", ". ($_siblinNode['rgt'] + 1) .")");
	}

	/**
	 * 	Delete a node and all his children
	 * 	@param 	int 	$_nodeID 		NodeID
	 */
	public function
	deleteNodeTree($_nodeID)
	{
		if($this -> m_sqlInstance == NULL) return;

		$_parentNode = $this -> getLR($_nodeID);
		if($_parentNode === false)
		{
			trigger_error('CNestedSets::deleteNode() - Node does not exists');
			return;
		}

		$this -> m_sqlInstance -> query("DELETE FROM `". $this -> m_tableName ."` WHERE lft BETWEEN ". $_parentNode['lft'] ." AND ". $_parentNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft-ROUND(". ( $_parentNode['rgt'] - $_parentNode['lft'] + 1 ). ") WHERE lft > ". $_parentNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET rgt=rgt-ROUND(". ( $_parentNode['rgt'] - $_parentNode['lft'] + 1 ) .") WHERE rgt > ". $_parentNode['rgt']);
	}

	/**
	 * 	Delete a node and moves his children a level up
	 * 	@param 	int 	$_nodeID 		NodeID
	 */
	public function
	deleteNode($_nodeID)
	{
		if($this -> m_sqlInstance == NULL) return;

		$_parentNode = $this -> getLR($_nodeID);
		if($_parentNode === false)
		{
			trigger_error('CNestedSets::deleteNode() - Node does not exists');
			return;
		}

		$this -> m_sqlInstance -> query("DELETE FROM `". $this -> m_tableName ."` WHERE lft = ". $_parentNode['lft']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft-1, rgt=rgt-1 WHERE lft BETWEEN ". $_parentNode['lft'] ." AND ". $_parentNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft-2 WHERE lft > ". $_parentNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET rgt=rgt-2 WHERE rgt > ". $_parentNode['rgt']);
	}

	/**
	 * 	Returns an array with all parents of this node
	 * 	@param 	int 	$_nodeID 		NodeID
	 * 	@return array	Returns an array with all parents of this node
	 */
	public function
	getNodeParents($_nodeID)
	{
		if($this -> m_sqlInstance == NULL) return[];

		$_sqlString =	"	SELECT 		p.node_id, 
										p.node_name 
							FROM 		`". $this -> m_tableName ."` n,
										`". $this -> m_tableName ."` p
							WHERE 		n.lft
								BETWEEN p.lft 
									AND	p.rgt 
								AND 	n.node_id = '". $this -> m_sqlInstance -> real_escape_string($_nodeID) ."'
							ORDER BY 	n.lft
						";

		$_sqlNodeRes = $this -> m_sqlInstance -> query($_sqlString);

		if($_sqlNodeRes === false || !$_sqlNodeRes -> num_rows)
		{
			trigger_error('CNestedSets::getNodeParents() - Node does not exists');
			return [];
		}

		$_result 	    = [];
		while($_sqlNode = $_sqlNodeRes -> fetch_assoc())
			$_result[]  = $_sqlNode;
	
		return $_result;
	}

	/**
	 * 	Returns an array with all children of this node
	 * 	@param 	int 	$_nodeID 		NodeID
	 * 	@return array	Returns an array with all children of this node
	 */
	public function
	getNodeChilds($_nodeID)
	{
		if($this -> m_sqlInstance == NULL) return [];

		$_sqlString =	"	SELECT 		o.node_name,
										o.node_id,
										COUNT(p.node_id)-1 AS level
							FROM 		`". $this -> m_tableName ."` AS n,
										`". $this -> m_tableName ."` AS p,
										`". $this -> m_tableName ."` AS o
							WHERE 		o.lft BETWEEN p.lft AND p.rgt
							AND 		o.lft BETWEEN n.lft AND n.rgt
							AND 		n.node_id = '". $this -> m_sqlInstance -> real_escape_string($_nodeID) ."'
							GROUP BY	o.lft
							ORDER BY 	o.lft
						";

		$_sqlNodeRes = $this -> m_sqlInstance -> query($_sqlString);

		if($_sqlNodeRes === false || !$_sqlNodeRes -> num_rows)
		{
			trigger_error('CNestedSets::getNodeChilds() - Node does not exists');
			return [];
		}

		$_result 	    = [];
		while($_sqlNode = $_sqlNodeRes -> fetch_assoc())
			$_result[]  = $_sqlNode;
	
		return $_result;
	}

	/**
	 * 	Returns all nodes with his children
	 * 	@param 	array 	$_nodeMap 		Empty array for the nodeMap
	 */
	public function
	getNodeMap(&$_nodeMap)
	{
		if($this -> m_sqlInstance == NULL) return;

		$_sqlString =	"	SELECT 		n.node_name,
										n.node_id,
										COUNT(*)-1 AS level,
										ROUND ((n.rgt - n.lft - 1) / 2) AS offspring
							FROM 		`". $this -> m_tableName ."` AS n,
										`". $this -> m_tableName ."` AS p
							WHERE 		n.lft BETWEEN p.lft AND p.rgt
							GROUP BY 	n.lft
							ORDER BY 	n.lft;
						";

		$_sqlNodeRes = $this -> m_sqlInstance -> query($_sqlString);

		if($_sqlNodeRes === false || !$_sqlNodeRes -> num_rows)
		{
			trigger_error('CNestedSets::getNodeMap() - Node does not exists');
			return;
		}

		while($_sqlNode = $_sqlNodeRes -> fetch_assoc())
			$_nodeMap[]  = $_sqlNode;
	}

	/**
	 * 	Move a node with all of his children to another node
	 * 	@param 	int 	$_srcNodeID 	Node, that we will move to another node
	 * 	@param 	int 	$_targetNodeID 	Target node, that receives the source node as new child
	 */
	public function
	moveNodeTree($_srcNodeID, $_targetNodeID)
	{
		if($this -> m_sqlInstance == NULL) return;

		$_targetNode = $this -> getLR($_targetNodeID);
		if($_targetNode === false)
		{
			trigger_error('CNestedSets::moveNodeTree() - Target node does not exists');
			return;
		}

		$_sourceNode = $this -> getLR($_srcNodeID);
		if($_sourceNode === false)
		{
			trigger_error('CNestedSets::moveNodeTree() - Source node does not exists');
			return;
		}

		$_srcDistance 		= $_sourceNode['rgt'] - $_sourceNode['lft'] + 1;				// The size of the node with all of his childs
		$_targetDistance	= $_targetNode['rgt'] - $_sourceNode['rgt'];					// The distance between source node and target node
		$_targetDistance	= $_sourceNode['rgt'] - $_sourceNode['lft'] + $_targetDistance;
			
		// Make space for the source node                                
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET rgt=rgt+". $_srcDistance." WHERE rgt >= ". $_targetNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft+". $_srcDistance." WHERE lft >  ". $_targetNode['rgt'] );

		// Gathering sourceNode again, maybe it got changed
		$_sourceNode = $this -> getLR($_srcNodeID);	

		// Distance correction if we move it backward
		if($_targetDistance < 0)	
			$_targetDistance = $_targetDistance - $_srcDistance;
		
		// Move source node and his childs to the new space
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft+". $_targetDistance .", rgt=rgt+". $_targetDistance ." WHERE lft >= ". $_sourceNode['lft'] ." AND rgt <= ". $_sourceNode['rgt']);

		// Removing the old space from the source node
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET rgt=rgt-". $_srcDistance ." WHERE rgt >= ". $_sourceNode['rgt']);
		$this -> m_sqlInstance -> query("UPDATE `". $this -> m_tableName ."` SET lft=lft-". $_srcDistance ." WHERE lft >  ". $_sourceNode['rgt']);
	}

	/**
	 * 	Get the left and right value from a requested node
	 * 	@param 	int 		$_nodeID 	NodeID from requested node
	 * 	@return int/bool	Returns the lft and rgt value as array, otherwise false 
	 */
	private function
	getLR($_nodeID)
	{
		$_sqlParentNodeRes	= 	$this -> m_sqlInstance -> query("SELECT lft,rgt FROM `". $this -> m_tableName ."` WHERE node_id = '". $this -> m_sqlInstance -> real_escape_string($_nodeID) ."'");

		if($_sqlParentNodeRes === false || !$_sqlParentNodeRes -> num_rows)
			return false;

		$_sqlParentNode		=	$_sqlParentNodeRes -> fetch_assoc();
		return $_sqlParentNode;
	}
}

?>

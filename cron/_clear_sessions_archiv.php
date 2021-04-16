<?php

##	process session archiv and access data

	$statistic = [];

	$sessionTimeout = time() - CFG::GET() -> SESSION -> TIMEOUT;



	$dbAgentsRes 	 = $pDatabase	-> query(DB_SELECT) 
									-> table('tb_useragents') 
									-> selectColumns(['agent_name','agent_suffix'])
									-> exec();

	$dbSessionRes 	 = $pDatabase	-> query(DB_SELECT) 
									-> table('tb_sessions_archiv') 
									-> exec();

	#while($sqlSessionRes !== false && $dbSessionItm = $sqlSessionRes -> fetch_assoc())
	foreach($dbSessionRes as $dbSessionItm)
	{
		$isBotSession = false;

		#while($sqlAgents = $sqlAgentsRes -> fetch_assoc())
		foreach($dbAgentsRes as $dbAgentsItm)
		{
			if(strpos($dbSessionItm -> user_agent, $dbAgentsItm -> agent_suffix) !== false)
			{
				$isBotSession = true;
				break;
			}
		}	

		$dateStamp = strval( date('dmY', $dbSessionItm -> time_create) );

		if(!isset($statistic[$dateStamp]))
			$statistic[$dateStamp]['counter'] = 0;

		$statistic[$dateStamp]['counter'] 	= 	$statistic[$dateStamp]['counter'] + 1;
		$statistic[$dateStamp]['timestamp'] = 	$dbSessionItm -> time_create;
	


			$condSessAccArchiv		 = new CModelCondition();
			$condSessAccArchiv		-> where('session_id', $dbSessionItm -> session_id);

			$dbSessAccArchRes 		 = $pDatabase	-> query(DB_SELECT) 
													-> table('tb_sessions_access_archiv') 
													-> condition($condSessAccArchiv)
													-> exec();

		foreach($dbSessAccArchRes as $dbSessAccArchItm)
		{
			if($isBotSession)
			{
				if(!isset($statistic[$dateStamp]['pages']['all']['bot'][ $dbSessAccArchItm -> node_id ]['counter']))
					$statistic[$dateStamp]['pages']['all']['bot'][ $dbSessAccArchItm -> node_id ]['counter'] = 0;

				$statistic[$dateStamp]['pages']['all']['bot'][ $dbSessAccArchItm -> node_id ]['counter'] = $statistic[$dateStamp]['pages']['all']['bot'][ $dbSessAccArchItm -> node_id ]['counter'] + 1;
			} 
			else
			{
				if(!isset($statistic[$dateStamp]['pages']['all']['user'][ $dbSessAccArchItm -> node_id ]['counter']))
					$statistic[$dateStamp]['pages']['all']['user'][ $dbSessAccArchItm -> node_id ]['counter'] = 0;

				$statistic[$dateStamp]['pages']['all']['user'][ $dbSessAccArchItm -> node_id ]['counter'] = $statistic[$dateStamp]['pages']['all']['user'][ $dbSessAccArchItm -> node_id ]['counter'] + 1;
			}

			if($isBotSession)
			{
				$statistic[$dateStamp]['bot'][ $dbSessionItm -> user_ip ][] = $dbSessAccArchItm -> node_id;
			} 
			else
			{
				$statistic[$dateStamp]['user'][ $dbSessionItm -> user_ip ][] = $dbSessAccArchItm -> node_id;
			}

			if(!empty($dbSessAccArchItm -> referer) && strpos($dbSessAccArchItm -> referer, CMS_SERVER_URL) === false)
				$statistic[$dateStamp]['referer'][] = $dbSessAccArchItm -> referer;
		}


		$delSessAccCond	 = new CModelCondition();
		$delSessAccCond	-> where('session_id', $dbSessionItm -> session_id);

		$pDatabase		-> query(DB_DELETE) 
						-> table('tb_sessions_access_archiv') 
						-> condition($delSessAccCond)
						-> exec();

		// collect unique user requests

		if(isset($statistic[$dateStamp]['user'][ $dbSessionItm -> user_ip ]))
			$statistic[$dateStamp]['user'][ $dbSessionItm -> user_ip ] = array_unique($statistic[$dateStamp]['user'][ $dbSessionItm -> user_ip ]);
		else
			$statistic[$dateStamp]['user'][ $dbSessionItm -> user_ip ] = [];

		foreach($statistic[$dateStamp]['user'][ $dbSessionItm -> user_ip ] as $nodeId)
		{
			if(!isset($statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter']))
				$statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter'] = 0;

			$statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter'] = $statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter'] + 1;
		}

		// collect unique bot requests

		if(isset($statistic[$dateStamp]['bot'][ $dbSessionItm -> user_ip ]))
			$statistic[$dateStamp]['bot'][ $dbSessionItm -> user_ip ] = array_unique($statistic[$dateStamp]['bot'][ $dbSessionItm -> user_ip ]);
		else
			$statistic[$dateStamp]['bot'][ $dbSessionItm -> user_ip ] = [];
			
		foreach($statistic[$dateStamp]['bot'][ $dbSessionItm -> user_ip ] as $nodeId)
		{
			if(!isset($statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter']))
				$statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter'] = 0;

			$statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter'] = $statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter'] + 1;
		}

		// Referer dieser Session erfassen
		if(isset($statistic[$dateStamp]['referer']))
			$statistic[$dateStamp]['referer'] = array_unique($statistic[$dateStamp]['referer']);

		$delSessCond	 = new CModelCondition();
		$delSessCond	-> where('session_id', $dbSessionItm -> session_id);

		$pDatabase		-> query(DB_DELETE) 
						-> table('tb_sessions_archiv') 
						-> condition($delSessCond)
						-> exec();
	}

	if(CFG::GET() -> SESSION -> REPORT_WEEKLYACCESS)
	{
		$message 	= [];
		$message[] 	= "Weekly report summary";
		$message[] 	= "\r\n";

		foreach($statistic as $statsDay)
		{
			$message[] 	= date("Y/m/d", $statsDay['timestamp']);
			$message[] 	= "--------------------------------------------";

			if(isset($statsDay['user']))
			{
				if(!isset($statsDay['pages']['unique']['user']))
					$statsDay['pages']['unique']['user'] = [];

				$message[] 	= "Total Users           ". count($statsDay['user']);
				$message[] 	= "Pages (unique Users)  ". count($statsDay['pages']['unique']['user']);

				foreach($statsDay['pages']['unique']['user'] as $uniqueNode => $uniquSum)
				{
					$pageCondition	 = new CModelCondition();
					$pageCondition	-> where('node_id', $uniqueNode);

					$pageInfo 		 = $pDatabase	-> query(DB_SELECT) 
													-> table('tb_page_header') 
													-> selectColumns(['page_name'])
													-> condition($pageCondition)
													-> exec();

					if(count($pageInfo) != 0)
					{
						$sqlNodeData	=	$pageInfo[0];
						$message[] 		= "  - ". $uniquSum['counter'] ."x ". $sqlNodeData -> page_name;
					}
					else
					{
						$message[] 		= "  - ". $uniquSum['counter'] ."x ". $uniqueNode ." (unknown nodeId)";
					}
				}
			}

			if(isset($statsDay['bots']))
			{
				if(!isset($statsDay['pages']['unique']['bots']))
					$statsDay['pages']['unique']['bots'] = [];

				$message[] 	= "Total Bots            ". count($statsDay['bots']);
				$message[] 	= "Pages (unique Bots)   ". count($statsDay['pages']['unique']['bots']);

				foreach($statsDay['pages']['unique']['bots'] as $uniqueNode => $uniquSum)
				{
					$pageCondition	 = new CModelCondition();
					$pageCondition	-> where('node_id', $uniqueNode);

					$pageInfo 		 = $pDatabase	-> query(DB_SELECT) 
													-> table('tb_page_header') 
													-> selectColumns(['page_name'])
													-> condition($pageCondition)
													-> exec();

					if(count($pageInfo) != 0)
					{
						$sqlNodeData	=	$pageInfo[0];
						$message[] 	= "  - ". $uniquSum['counter'] ."x ". $sqlNodeData -> page_name;
					}
					else
					{
						$message[] 	= "  - ". $uniquSum['counter'] ."x ". $uniqueNode ." (unknown nodeId)";
					}
				}

			}

			if(isset($statsDay['referer']))
			{
				$message[] 	= "Referer";
				foreach($statsDay['referer'] as $refererValue)
				{
					$message[] 	= "  - ". $refererValue;
				}
			}

			$message[] 	= "\r\n";
		}

		CSysMailer::instance() 	-> sendMail('Weekly Access Reports', implode("\r\n", $message) );
	}
?>
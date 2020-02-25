<?php

##	process session archiv and access data

	$statistic = [];

	$sessionTimeout = time() - CFG::GET() -> SESSION -> TIMEOUT;




	$sqlAgentString	=	"	SELECT		tb_useragents.agent_name,
										tb_useragents.agent_suffix
							FROM		tb_useragents
						";

	$sqlAgentsRes		=	$sqlInstance -> query($sqlAgentString);



	$sqlString		=	"	SELECT 		tb_sessions_archiv.*
							FROM		tb_sessions_archiv
						";

	$sqlSessionRes	=	$sqlInstance -> query($sqlString);

	while($sqlSessionRes !== false && $sqlSessionData = $sqlSessionRes -> fetch_assoc())
	{


		$isBotSession = false;



		while($sqlAgents = $sqlAgentsRes -> fetch_assoc())
		{

			if(strpos($sqlSessionData['user_agent'], $sqlAgents['agent_suffix']) !== false)
			{

				$isBotSession = true;
				break;

			}





		}	$sqlAgentsRes -> data_seek(0);








		$dateStamp = strval( date('dmY', $sqlSessionData['time_create']) );




		if(!isset($statistic[$dateStamp]))
			$statistic[$dateStamp]['counter'] = 0;

		$statistic[$dateStamp]['counter'] 	= 	$statistic[$dateStamp]['counter'] + 1;
		$statistic[$dateStamp]['timestamp'] = 	$sqlSessionData['time_create'];
	

		$sqlString		=	"	SELECT 		tb_sessions_access_archiv.*
								FROM		tb_sessions_access_archiv
								WHERE		tb_sessions_access_archiv.session_id = '". $sqlSessionData['session_id'] ."'
							";

		$sqlArchivRes	=	$sqlInstance -> query($sqlString);


		while($sqlArchivRes !== false && $sqlArchivData = $sqlArchivRes -> fetch_assoc())
		{


			if($isBotSession)
			{
				if(!isset($statistic[$dateStamp]['pages']['all']['bot'][ $sqlArchivData['node_id'] ]['counter']))
					$statistic[$dateStamp]['pages']['all']['bot'][ $sqlArchivData['node_id'] ]['counter'] = 0;

				$statistic[$dateStamp]['pages']['all']['bot'][ $sqlArchivData['node_id'] ]['counter'] = $statistic[$dateStamp]['pages']['all']['bot'][ $sqlArchivData['node_id'] ]['counter'] + 1;
			} 
			else
			{
				if(!isset($statistic[$dateStamp]['pages']['all']['user'][ $sqlArchivData['node_id'] ]['counter']))
					$statistic[$dateStamp]['pages']['all']['user'][ $sqlArchivData['node_id'] ]['counter'] = 0;

				$statistic[$dateStamp]['pages']['all']['user'][ $sqlArchivData['node_id'] ]['counter'] = $statistic[$dateStamp]['pages']['all']['user'][ $sqlArchivData['node_id'] ]['counter'] + 1;
			}





			if($isBotSession)
			{

				$statistic[$dateStamp]['bot'][ $sqlSessionData['user_ip'] ][] = $sqlArchivData['node_id'];


			} 
			else
			{

				$statistic[$dateStamp]['user'][ $sqlSessionData['user_ip'] ][] = $sqlArchivData['node_id'];


			}





			if(!empty($sqlArchivData['referer']) && strpos($sqlArchivData['referer'],CMS_SERVER_URL) === false)
			$statistic[$dateStamp]['referer'][] = $sqlArchivData['referer'];



		}


		$sqlInstance -> query("DELETE FROM tb_sessions_access_archiv WHERE session_id = '". $sqlSessionData['session_id'] ."'");


		// collect unique user requests

		if(isset($statistic[$dateStamp]['user'][ $sqlSessionData['user_ip'] ]))
			$statistic[$dateStamp]['user'][ $sqlSessionData['user_ip'] ] = array_unique($statistic[$dateStamp]['user'][ $sqlSessionData['user_ip'] ]);
		else
			$statistic[$dateStamp]['user'][ $sqlSessionData['user_ip'] ] = [];

		foreach($statistic[$dateStamp]['user'][ $sqlSessionData['user_ip'] ] as $nodeId)
		{

			if(!isset($statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter']))
				$statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter'] = 0;

			$statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter'] = $statistic[$dateStamp]['pages']['unique']['user'][ $nodeId ]['counter'] + 1;

		}


		// collect unique bot requests

		if(isset($statistic[$dateStamp]['bot'][ $sqlSessionData['user_ip'] ]))
			$statistic[$dateStamp]['bot'][ $sqlSessionData['user_ip'] ] = array_unique($statistic[$dateStamp]['bot'][ $sqlSessionData['user_ip'] ]);
		else
			$statistic[$dateStamp]['bot'][ $sqlSessionData['user_ip'] ] = [];
			
		foreach($statistic[$dateStamp]['bot'][ $sqlSessionData['user_ip'] ] as $nodeId)
		{

			if(!isset($statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter']))
				$statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter'] = 0;

			$statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter'] = $statistic[$dateStamp]['pages']['unique']['bot'][ $nodeId ]['counter'] + 1;

		}



		// Referer dieser Session erfassen
		if(isset($statistic[$dateStamp]['referer']))
			$statistic[$dateStamp]['referer'] = array_unique($statistic[$dateStamp]['referer']);





		#tk::dbug($sqlSessionData);



		$sqlInstance -> query("DELETE FROM tb_sessions_archiv WHERE session_id = '". $sqlSessionData['session_id'] ."'");


	}

	#tk::dbug($statistic);


	if(CFG::GET() -> CRONJOB -> REPORT_WEEKLYACCESS)
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

				$message[] 	= "Total Users           ". count($statsDay['user']);
				$message[] 	= "Pages (unique Users)  ". count($statsDay['pages']['unique']['user']);

				foreach($statsDay['pages']['unique']['user'] as $uniqueNode => $uniquSum)
				{
					$sqlNodeString	=	"	SELECT		tb_page_header.page_name
											FROM		tb_page_header
											WHERE		tb_page_header.node_id = '". $uniqueNode ."'
										";

					$sqlNodeRes		=	$sqlInstance -> query($sqlNodeString);

					if($sqlNodeRes -> num_rows != 0)
					{
						$sqlNodeData	=	$sqlNodeRes -> fetch_array();
						$message[] 	= "  - ". $uniquSum['counter'] ."x ". $sqlNodeData['page_name'];
					}
					else
					{
						$message[] 	= "  - ". $uniquSum['counter'] ."x ". $uniqueNode ." (unknown nodeId)";
					}
				}

			}


			if(isset($statsDay['bots']))
			{

				$message[] 	= "Total Bots            ". count($statsDay['bots']);
				$message[] 	= "Pages (unique Bots)   ". count($statsDay['pages']['unique']['bots']);

				foreach($statsDay['pages']['unique']['bots'] as $uniqueNode => $uniquSum)
				{
					$sqlNodeString	=	"	SELECT		tb_page_header.page_name
											FROM		tb_page_header
											WHERE		tb_page_header.node_id = '". $uniqueNode ."'
										";

					$sqlNodeRes		=	$sqlInstance -> query($sqlNodeString);

					if($sqlNodeRes -> num_rows != 0)
					{
						$sqlNodeData	=	$sqlNodeRes -> fetch_array();
						$message[] 	= "  - ". $uniquSum['counter'] ."x ". $sqlNodeData['page_name'];
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
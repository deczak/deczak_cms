<?php

##	process session and access data

	$sessionTimeout = time() - CFG::GET() -> SESSION -> TIMEOUT;

	$sessionCond	 = new CModelCondition();
	$sessionCond	-> whereSmaller('time_update', $sessionTimeout);

	$dbSessionRes 	 = $pDatabase	-> query(DB_SELECT) 
									-> table('tb_sessions') 
									-> condition($sessionCond)
									-> exec();

	foreach($dbSessionRes as $dbSessionItm)
	{
		$pDatabase		-> query(DB_INSERT) 
						-> table('tb_sessions_archiv') 
						-> dtaObject($dbSessionItm)
						-> exec();


		$selSessAccCond		 = new CModelCondition();
		$selSessAccCond		-> where('session_id', $dbSessionItm -> session_id);

		$dbQuery 	= $pDatabase		-> query(DB_SELECT) 
										-> table('tb_sessions_access') 
										-> condition($selSessAccCond);

		$dbArchivRes = $dbQuery -> exec();

		foreach($dbArchivRes as $dbArchivItm)
		{
			$pDatabase		-> query(DB_INSERT) 
							-> table('tb_sessions_access_archiv') 
							-> dtaObject($dbArchivItm)
							-> exec();

			$delSessAccCond	 = new CModelCondition();
			$delSessAccCond	-> where('data_id', $dbArchivItm -> data_id);

			$pDatabase		-> query(DB_DELETE) 
							-> table('tb_sessions_access') 
							-> condition($delSessAccCond)
							-> exec();
		}
	}

	$pDatabase	-> query(DB_DELETE) 
				-> table('tb_sessions') 
				-> condition($sessionCond)
				-> exec();

?>
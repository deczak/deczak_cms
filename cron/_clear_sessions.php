<?php

##	process session and access data

	$sessionTimeout = time() - CFG::GET() -> SESSION -> TIMEOUT;

	/*
	$sqlString		=	"	SELECT 		tb_sessions.*
							FROM		tb_sessions
							WHERE		tb_sessions.time_update < $sessionTimeout
						";

	$sqlSessionRes	=	$sqlInstance -> query($sqlString);
	*/







	$sessionCond	 = new CModelCondition();
	$sessionCond	-> whereSmaller('time_update', $sessionTimeout);

	$dbSessionRes 	 = $pDatabase	-> query(DB_SELECT) 
									-> table('tb_sessions') 
									-> condition($sessionCond)
									-> exec();
















	foreach($dbSessionRes as $dbSessionItm)
	{
		#$sqlInstance -> query("INSERT INTO tb_sessions_archiv VALUES (". sqlImplosion($sqlSessionData) .")"); ## <-- da 



		$pDatabase		-> query(DB_INSERT) 
						-> table('tb_sessions_archiv') 
						-> dtaObject($dbSessionItm)
						-> exec();



		/*

		$sqlString		=	"	SELECT 		tb_sessions_access.*
								FROM		tb_sessions_access
								WHERE		tb_sessions_access.session_id = '". $dbSessionItm -> session_id ."'
							";

		$sqlArchivRes	=	$sqlInstance -> query($sqlString);
		*/





								$selSessAccCond		 = new CModelCondition();
								$selSessAccCond		-> where('session_id', $dbSessionItm -> session_id);



								$dbQuery 	= $sqlInstance		-> query(DB_SELECT) 
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



			#$sqlInstance -> query("INSERT INTO tb_sessions_access_archiv VALUES (". sqlImplosion($sqlArchivData) .")");
			#$sqlInstance -> query("DELETE FROM tb_sessions_access WHERE data_id = '". $sqlArchivData['data_id'] ."'");



		}
	}














	/*
	$sqlString		=	"	DELETE FROM	tb_sessions
							WHERE		tb_sessions.time_update < $sessionTimeout
						";

	$sqlInstance -> query($sqlString);
	*/


	$pDatabase	-> query(DB_DELETE) 
				-> table('tb_sessions') 
				-> condition($sessionCond)
				-> exec();







?>
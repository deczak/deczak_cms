<?php

##	process session and access data

	$sessionTimeout = time() - CFG::GET() -> SESSION -> TIMEOUT;

	$sqlString		=	"	SELECT 		tb_sessions.*
							FROM		tb_sessions
							WHERE		tb_sessions.time_update < $sessionTimeout
						";

	$sqlSessionRes	=	$sqlInstance -> query($sqlString);

	while($sqlSessionRes !== false && $sqlSessionData = $sqlSessionRes -> fetch_assoc())
	{
		$sqlInstance -> query("INSERT INTO tb_sessions_archiv VALUES (". sqlImplosion($sqlSessionData) .")");

		$sqlString		=	"	SELECT 		tb_sessions_access.*
								FROM		tb_sessions_access
								WHERE		tb_sessions_access.session_id = '". $sqlSessionData['session_id'] ."'
							";

		$sqlArchivRes	=	$sqlInstance -> query($sqlString);

		while($sqlArchivRes !== false && $sqlArchivData = $sqlArchivRes -> fetch_assoc())
		{
			$sqlInstance -> query("INSERT INTO tb_sessions_access_archiv VALUES (". sqlImplosion($sqlArchivData) .")");
			$sqlInstance -> query("DELETE FROM tb_sessions_access WHERE data_id = '". $sqlArchivData['data_id'] ."'");
		}
	}

	$sqlString		=	"	DELETE FROM	tb_sessions
							WHERE		tb_sessions.time_update < $sessionTimeout
						";


	$sqlInstance -> query($sqlString);

?>
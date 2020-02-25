<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModelCondition.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSheme.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModel.php';

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsers.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersBackend.php';	

$timestamp	= time();
$timeout 	= $timestamp - (CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> REVOKE_RIGHTS * (60 * 60 * 24));

$registerCondition	 = new CModelCondition();
$registerCondition 	-> whereNotNull('user_hash');
$registerCondition 	-> whereNot('user_hash','');
$registerCondition 	-> groupBy('user_id');
$modelUsersRegister	 = new modelUsersRegister();
$modelUsersRegister -> load($sqlInstance, $registerCondition);

$usergroupCondition	 = new CModelCondition();
$usergroupCondition -> whereNotNull('user_hash');
$usergroupCondition -> whereNot('user_hash','');
$usergroupCondition	-> groupBy('user_id');
$modelUserGroups	 = new modelUserGroups();
$modelUserGroups	-> load($sqlInstance, $usergroupCondition);

##	Collect avaiable remote users

$usersList = [];

foreach(CFG::GET() -> MYSQL -> DATABASE as $database)
{
	if($database['name'] === CFG::GET() -> MYSQL -> PRIMARY_DATABASE)
		continue;

	$remoteSQL = CSQLConnect::GET() -> getConnection($database['name']);

	##	condition

	$remoteCondition = new CModelCondition();
	$remoteCondition -> where('allow_remote', '1');
	$remoteCondition -> where('is_locked', '0');

	##	get users

	$modelUsers	  = new modelUsers();

	$modelUsers  -> load($remoteSQL, $remoteCondition);	
	$modelUsers  -> getDataInstance();

	foreach($modelUsers -> getDataInstance() as $user)
	{
		$id = hash('sha256', $database['name'] . $database['server'] . $user -> user_id . $user -> login_name);

		if(!empty($_userId) && $_userId !== $id)
			continue;

		$usersList[$id] = [
							"db_name"	=> $database['name'],
							"db_server"	=> $database['server'],
							"id" 		=> $id,
							"user"		=> $user
							];
	}

	## get backend users

	$modelUsersBackend	= new modelUsersBackend();
	$modelUsersBackend  -> load($remoteSQL, $remoteCondition);	
	$modelUsersBackend -> getDataInstance();

	foreach($modelUsersBackend -> getDataInstance() as $user)
	{
		$id = hash('sha256', $database['name'] . $database['server'] . $user -> user_id . $user -> login_name);

		if(!empty($_userId) && $_userId !== $id)
			continue;

		$usersList[$id] = [
							"db_name"	=> $database['name'],
							"db_server"	=> $database['server'],
							"id" 		=> $id,
							"user"		=> $user
							];
	}
}

##	loop through userRegisters

foreach($modelUsersRegister -> getDataInstance() as $user)
{
	if(!isset($usersList[$user -> user_hash]))
	{
		$removeCondition	 = new CModelCondition();
		$removeCondition 	-> where('user_hash', $user -> user_hash);
		$modelUsersRegister -> delete($sqlInstance, $removeCondition);
	}
}

##	loop through userGroups

foreach($modelUserGroups -> getDataInstance() as $user)
{
	if(!isset($usersList[$user -> user_hash]))
	{
		$removeCondition	 = new CModelCondition();
		$removeCondition 	-> where('user_hash', $user -> user_hash);
		$modelUserGroups	-> delete($sqlInstance, $removeCondition);

		continue;
	}

	##	check timeout for rights

	if($user -> update_time < $timeout && CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> REVOKE_RIGHTS != 0)
	{
		$reportMessage[] = "The rights were revoked for the following remote user after the time has expired:";
		$reportMessage[] = '';
		$reportMessage[] = 'User   : '. $usersList[$user -> user_hash]['user'] -> user_name_first .' '. $usersList[$user -> user_hash]['user'] -> user_name_last;
		$reportMessage[] = 'Origin : '. $usersList[$user -> user_hash]['db_name'] .' ('. $usersList[$user -> user_hash]['db_server'] .')';

		if(CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> REPORT_REVOKE)
			CSysMailer::instance() 	-> sendMail('Rights revoke for remote User', implode("\r\n", $reportMessage) );

		$removeCondition	 = new CModelCondition();
		$removeCondition 	-> where('user_hash', $user -> user_hash);
		$modelUserGroups 	-> delete($sqlInstance, $removeCondition);
	}
}

?>
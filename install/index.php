<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Install</title>
		<style>
			
			* { box-sizing:border-box; padding:0px; margin:0px; }
			body { background-color:rgb(45, 89, 134); position:relative; color:#fff; font-size:16px; font-family:sans-serif; height:100vh; }
			
			.inner-wrapper { max-width:1000px; margin:0 auto; position:relative; }
			
			.content-main { background-color:#fff; color:rgb(75,75,75); width:100vw; height:500px; position:absolute; top:50%; transform: translateY(-50%); left:0px; overflow:hidden; }			
			.content-main .content-positionier { position:relative; height:100%; width:100vw; }			
			.content-main .content-page { position:absolute; width:100%; height:100%; top:50%; transform:translateY(-50%); left:150vw; transition:left 1s; text-align:center; }						
			.content-main .content-page[data-page-id="1"] { left:0px; }
			
			h1 { margin-top: 50px; margin-bottom:25px;  font-size:1.5em; }
			p  { margin-bottom:15px; }
			
			.button-box { text-align:center; }
			button { padding:5px 10px; margin:15px 0px; }
			
			footer { position:absolute; bottom:25px; left:0px; width:100vw; font-size:0.8em; }
			
			table.install,
			table.form { margin:0 auto; width:500px; }
			table td { text-align:left; }
			table.form td:first-child { width:150px; }
			table.form td:last-child > input { width:100%; }

			table.install td { padding:10px; }
			table.install td:first-child { width:400px; }

			pre { text-align:left; margin:30px auto; line-height:1.5em; max-width:650px; }
						
		</style>

		<?php		
		$install_path = pathinfo(__FILE__, PATHINFO_DIRNAME);
		$project_path = substr($install_path,0, strrpos($install_path,'/')+1);
		$sub_path = substr($project_path,strlen($_SERVER['DOCUMENT_ROOT']));		
		
		if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
			$protocol = 'https://';
		else 
			$protocol = 'http://';
		?>

		<script src="../backend/js/toolkit.js"></script>
		
		<script>
		(function() {

			function
			onButton(button)
			{				
				var buttonAction 		= button.getAttribute('data-action');

				switch(buttonAction)
				{
					case 'next':
					case 'previous':
										switchPage(button);
										break;
										
					case 'install':		install();
										break;
				}
			}
				
			function
			switchPage(button)
			{
				var buttonAction 	= button.getAttribute('data-action');
				var	activePage 		= button.closest('.content-page');
				var	activePageID 	= activePage.getAttribute('data-page-id');
				var	pagesContainer 	= activePage.closest('.content-main');
				var	targetPageID	= activePageID;
								
				if(buttonAction === 'next')
					targetPageID++;
				else
					targetPageID--;	
												
				var	targetPage = pagesContainer.querySelector('.content-page[data-page-id="'+ targetPageID +'"]');
				
				if(buttonAction === 'next')
				{
					activePage.style.left = '-150vw';
					targetPage.style.left = '0px';				
				}					
				else
				{
					activePage.style.left = '150vw';
					targetPage.style.left = '0px';				
				}				
			}

			function
			install(step = 1)
			{
				var than = this;

				if(step == 1)
					document.querySelector('#install-process').innerHTML = '';

				switch(step)
				{
					case 1:		// create config file
								var	formData = new FormData();
									formData.append("server-root", document.querySelector('input[name="server-root"]').value);
									formData.append("server-url", document.querySelector('input[name="server-url"]').value);
									formData.append("server-subpath", document.querySelector('input[name="server-sub"]').value);
									formData.append("database-server", document.querySelector('input[name="database-server"]').value);
									formData.append("database-user", document.querySelector('input[name="database-user"]').value);
									formData.append("database-pass", document.querySelector('input[name="database-pass"]').value);
									formData.append("database-database", document.querySelector('input[name="database-database"]').value);
									formData.append("mail-name", document.querySelector('input[name="mail-name"]').value);
									formData.append("mail-mail", document.querySelector('input[name="mail-mail"]').value);
									formData.append("crypt-basekey", document.querySelector('input[name="crypt-basekey"]').value);

								reportInstallStep('Create configuration file ... ');
								cmstk.callXHR('<?php echo $protocol . $_SERVER['SERVER_NAME'] .''. $sub_path; ?>install/xhr-create-config.php', formData, onXHRInstallConfig, cmstk.onXHRError, than);
								break;

					case 2:		// create datebase structure
								var	formData = new FormData();
									formData.append("database-server", document.querySelector('input[name="database-server"]').value);
									formData.append("database-user", document.querySelector('input[name="database-user"]').value);
									formData.append("database-pass", document.querySelector('input[name="database-pass"]').value);
									formData.append("database-database", document.querySelector('input[name="database-database"]').value);

								reportInstallStep('Create database structure ... ');
								cmstk.callXHR('<?php echo $protocol. $_SERVER['SERVER_NAME'] .''. $sub_path; ?>install/xhr-create-database-structure.php', formData, onXHRInstallDatabaseStructure, cmstk.onXHRError, than);
								break;

					case 3:		// insert datebase initial data
								var	formData = new FormData();
									formData.append("database-server", document.querySelector('input[name="database-server"]').value);
									formData.append("database-user", document.querySelector('input[name="database-user"]').value);
									formData.append("database-pass", document.querySelector('input[name="database-pass"]').value);
									formData.append("database-database", document.querySelector('input[name="database-database"]').value);
									formData.append("user-user", document.querySelector('input[name="user-user"]').value);
									formData.append("user-pass", document.querySelector('input[name="user-pass"]').value);

								reportInstallStep('Insert database initial data ... ');
								cmstk.callXHR('<?php echo $protocol. $_SERVER['SERVER_NAME'] .''. $sub_path; ?>install/xhr-insert-database-data.php', formData, onXHRInstallDatabaseData, cmstk.onXHRError, than);
								break;

					case 5:		// create htaccess
								var	formData = new FormData();
									formData.append("server-subpath", document.querySelector('input[name="server-sub"]').value);
									formData.append("database-server", document.querySelector('input[name="database-server"]').value);
									formData.append("database-user", document.querySelector('input[name="database-user"]').value);
									formData.append("database-pass", document.querySelector('input[name="database-pass"]').value);
									formData.append("database-database", document.querySelector('input[name="database-database"]').value);

								reportInstallStep('Create HTAccess ... ');
								cmstk.callXHR('<?php echo $protocol. $_SERVER['SERVER_NAME'] .''. $sub_path; ?>install/xhr-create-htaccess.php', formData, onXHRInstallHTAccess, cmstk.onXHRError, than);
								break;
				}

			}


			function
			onXHRInstallConfig(response, instance)
			{
				if(response.state == 0)
				{
					reportInstallStep(response.msg + "\r\n");	
					install(2);
				}
				else
				{
					reportInstallStep(response.msg);	
				}
			}

			function
			onXHRInstallDatabaseStructure(response, instance)
			{
				if(response.state == 0)
				{
					reportInstallStep(response.msg + "\r\n");	
					install(3);
				}
				else
				{
					reportInstallStep(response.msg);	
				}
			}


			function
			onXHRInstallDatabaseData(response, instance)
			{
				if(response.state == 0)
				{
					reportInstallStep(response.msg + "\r\n");	
					install(5);
				}
				else
				{
					reportInstallStep(response.msg);	
				}
			}

			function
			onXHRInstallHTAccess(response, instance)
			{
				if(response.state == 0)
				{
					reportInstallStep(response.msg + "\r\n");	
					document.getElementById('install-done-url').innerHTML = document.querySelector('input[name="server-url"]').value;
					document.getElementById('install-done-url').setAttribute('href', document.querySelector('input[name="server-url"]').value);
					document.getElementById('install-done-url-backend').innerHTML = document.querySelector('input[name="server-url"]').value +'backend/';
					document.getElementById('install-done-url-backend').setAttribute('href', document.querySelector('input[name="server-url"]').value +'backend/');
					document.getElementById('install-done').style.display = 'block';
					//install(6);
				}
				else
				{
					reportInstallStep(response.msg);	
				}
			}

			function
			reportInstallStep(string)
			{
				var installProcess = document.querySelector('#install-process');
					installProcess.innerHTML = installProcess.innerHTML + string;
			}


			document.addEventListener('click', function(event) { var element = event.target; if(element !== null && element.tagName === 'BUTTON') onButton(element); }, false);
			
		}());	
		</script>			
		
	</head>
	<body>
		
		
		<header class="inner-wrapper">
		</header>

		
		<div class="content-main">
			<div class="content-positionier">
			
				<div class="inner-wrapper">

					<!-- page 1 --> 

					<div class="content-page" data-page-id="1">

						<h1>Installation of your new CMS</h1>
						
						<p>This install script leads you through the installation process of your new content managment system.</p>

						<p>If you are ready for the install, and also have all required data like MySQL (+ compatbile) beside you, click the Button.</p>
						
						
						<div class="button-box">
						
							<button type="button" data-action="next">Next</button>
						
						</div>
					</div>

					<!-- page 2 --> 

					<div class="content-page" data-page-id="2">
									
						<h1>Database Informationen</h1>

						<p>Please insert your MySQL compatible connection data to your Server. Stay sure the database exists and contains no tables. This CMS use for all CORE tables the prefix <b>tb_</b> </p>
						
						<table class="form">
							<tbody>
								<tr>
									<td>Server address</td>
									<td><input type="text" name="database-server" placeholder="e.g. localhost"></td>
								</tr>
								<tr>
									<td>Username</td>
									<td><input type="text" name="database-user" placeholder=""></td>
								</tr>
								<tr>
									<td>Password</td>
									<td><input type="password" name="database-pass" placeholder=""></td>
								</tr>
								<tr>
									<td>Database</td>
									<td><input type="text" name="database-database" placeholder=""></td>
								</tr>
							</tbody>
						</table>
						
						
						<div class="button-box">
							<button type="button" data-action="previous">Back</button> 
							<button type="button" data-action="next">Next</button>
						</div>
						
					</div>
					
					<!-- page 3 --> 
					
					<div class="content-page" data-page-id="3">
									
						<h1>Web server address and directory path</h1>

						<p>Correct the information if they differ. This CMS always ends all paths with a slash if no file is specifically called.</p>
			
						
						<?php		
						$install_path = pathinfo(__FILE__, PATHINFO_DIRNAME);
						$install_path = substr($install_path,0, strrpos($install_path,'/')+1);
						$sub_path = substr($install_path,strlen($_SERVER['DOCUMENT_ROOT']));		
						
						if(isset($_SERVER['HTTPS']) &&	($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
							$protocol = 'https://';
						else 
							$protocol = 'http://';
						?>
						
						<table class="form">
							<tbody>
								<tr>
									<td>Document root<span style="font-size:0.7em"><br>(with sub directory)</span></td>
									<td><input type="text" name="server-root" value="<?php echo $install_path; ?>"></td>
								</tr>
								<tr>
									<td>Sub directory<span style="font-size:0.7em"><br>(same as above)</span></td>
									<td><input type="text" name="server-sub" value="<?php echo substr($sub_path,1); ?>"></td>
								</tr>
								<tr>
									<td>URL address</td>
									<td><input type="text" name="server-url" value="<?php echo $protocol . $_SERVER['SERVER_NAME'] .''. $sub_path; ?>"></td>
								</tr>
							</tbody>
						</table>
						
						
						<div class="button-box">
							<button type="button" data-action="previous">Back</button> 
							<button type="button" data-action="next">Next</button>
						</div>
						
					</div>
					
					<!-- page 4 --> 
					
					<div class="content-page" data-page-id="4">
									
						<h1>Initial user account for administration.</h1>

						<p>Insert the data for the first administration user, you can change them later.</p>
			
						<table class="form">
							<tbody>
								<tr>
									<td>User name</td>
									<td><input type="text" name="user-user" placeholder=""></td>
								</tr>
								<tr>
									<td>Password</td>
									<td><input type="password" name="user-pass" placeholder=""></td>
								</tr>
							</tbody>
						</table>
						
						
						<div class="button-box">
							<button type="button" data-action="previous">Back</button> 
							<button type="button" data-action="next">Next</button>
						</div>
						
					</div>

					<!-- page 5 --> 
					
					<div class="content-page" data-page-id="5">
									
						<h1>System messages</h1>

						<p>This CMS sends you a mail if something important happens. Don't worry, there is a spam protection build in.</p>
			
						<table class="form">
							<tbody>
								<tr>
									<td>Receiver name</td>
									<td><input type="text" name="mail-name" placeholder=""></td>
								</tr>
								<tr>
									<td>Receiver address</td>
									<td><input type="text" name="mail-mail" placeholder=""></td>
								</tr>
							</tbody>
						</table>
						
						
						<div class="button-box">
							<button type="button" data-action="previous">Back</button> 
							<button type="button" data-action="next">Next</button>
						</div>
						
					</div>

					<!-- page 6 --> 
					
					<div class="content-page" data-page-id="6">
									
						<h1>Encyption Base Key</h1>

						<p>Certain data are stored in encrypted form in the database. A base key is required for encryption and can be set here.<br><br><b>This key cannot be changed afterwards.</b></p>
								
						<table class="form">
							<tbody>
								<tr>
									<td>Basekey</td>
									<td><input type="text" name="crypt-basekey" value="<?= hash('md5', time() . rand(999,9999999)); ?>"></td>
								</tr>
							</tbody>
						</table>
												
						<div class="button-box">
							<button type="button" data-action="previous">Back</button> 
							<button type="button" data-action="next">Next</button>
						</div>
											
					</div>	

					<!-- page 7 --> 
					
					<div class="content-page" data-page-id="7">
									
						<h1>Execute installation</h1>

						<p></p>
								
						<pre id="install-process"></pre>
					
						<button type="button" data-action="previous">Back</button> 
						<button type="button" data-action="install">Run install</button> 

						<br><br>

						<div id="install-done" style="display:none";>
							The installation is complete. Please check the result of your installation:<br><br>
							<a href="" id="install-done-url"></a><br>
							<a href="" id="install-done-url-backend"></a>

							<br><br>
							You can't reach this install address again until you removed the .htaccess file.
						</div>
											
					</div>					
					





					

				</div>
				
			</div>
			
		</div>
		
		
		<footer>

			<div class="inner-wrapper">		
				
				<p><b>Note on the Alpha version:</b><br>At the moment, there is no requirement check in this script. Please determine on your own whether your server meets the requirements. This script does not check if you have entered all the information, there is no validation. Missing or incorrect information leads to a faulty installation.</p>
				
			</div>	
			
		</footer>
		
		
	
		
		
	</body>
</html>
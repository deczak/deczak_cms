<?php

	$_aCrumbData  	= $imperator -> getCrumbPath();
	$_aSubSections  = $imperator -> getSubSection();

?>
<header>
	
	<div class="main-nav-container">
		<div class="wrapper-inside">
			<div class="left"><a href="<?php echo CMS_SERVER_URL_BACKEND; ?>"><?php echo strtoupper(CMS_BACKEND_STARTBUTTON); ?></a></div>
			<div class="right">
				<?php 
				if($session -> getSessionValue('IS_AUTH_OBJECT', LOGIN_OBJECT_BACKEND) !== false)
				{
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'right-groups/">Right Groups</a>';
					echo '&nbsp;&nbsp;&nbsp;';	
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'sites/">Sites</a>';
					echo '&nbsp;&nbsp;&nbsp;';				
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'backend-users/">Backend users</a>';				
				}
				?>
			</div>
		</div>
	</div>
	
	<div class="sub-nav-container">		
		<div class="wrapper-inside">			
			<div class="crumbs">
				<a class="darkblue" href="<?php echo CMS_SERVER_URL_BACKEND; ?>">Home</a> 
				<?php
				$_pathExtension = '';

				foreach($_aCrumbData as $_crumbKey => $_crumb)
				{
					if(!isset($_crumb['page_name'])) break;
					if($_crumb['page_path'] === '/') break;
					$_pathExtension .= $_crumb['page_path'];
					echo '<span class="crumb-delimeter">&rang;</span>';
					if(empty($_crumb['no_link']) || $_crumb['no_link'] === false)
						echo '<a class="darkblue" href="'. CMS_SERVER_URL_BACKEND . $_pathExtension .'">'. $_crumb['page_name'] .'</a>';
					else
						echo $_crumb['page_name'];
				}
				?> 
			</div>			
			<div class="sub-nav-items">				
				<?php
				foreach($_aSubSections as $_sub)
				{
					echo '<a class="darkblue" href="'. CMS_SERVER_URL_BACKEND . $page -> page_path .'/'. $_sub['page_path'] .'">'. $_sub['menu_name'] .'</a>';
				}
				?>
			</div>
		</div>	
	</div>

</header>
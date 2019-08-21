
<style>

	:root
	{
	--wrapper-inner-width: 1200px;
	--default-text-color : rgb(50,50,50);
	}

	* { box-sizing:border-box; }

	body { font-size:16px; padding:0px; margin:0px; background:rgb(245,245,245); color:rgb(50,50,50); overflow-y:scroll; }
	html {  padding:0px; margin:0px; }

	p { margin:10px 0px; }
	
	h1 { font-weight:300; font-size:30px; }
	h2 { font-weight:300; font-size:26px; }
	h3 { font-weight:600; font-size:22px; }
	h4 { font-weight:600; font-size:20px; }
	h5 { font-weight:600; font-size:18px; }
	h6 { font-weight:600; font-size:16px; }
		
	h1 ~ h4 { margin-top:-20px; }


/**	Wrappers
*/

	.outer-wrapper { }
	.inner-wrapper { margin:0 auto; width:100%; max-width:var(--wrapper-inner-width);  padding:0px 2%; }


/**	Header
*/

	header { padding:20px 0px; }
	header > div { display:flex; justify-content:space-between; align-items:center; position:relative; }

	header #page-headline { font-weight:300; font-size:2.5em; }

	header ul { padding:0px; margin:0px; list-style:none; }
	header #menu-stucture { position:absolute; top:50%; right: 2%; transform:translateY(-50%); z-index: 99999; }
	header #menu-stucture > li { position:relative; display:inline-block; }
	header #menu-stucture   li > a { display:block; margin:0px 5px; padding:5px 8px; text-decoration:none; border-bottom:2px solid rgba(255,0,0,0); font-size:1.1em; font-weight:300; color:var(--default-text-color); transition:all 0.15s;  }
	header #menu-stucture > li > a { text-transform: uppercase; }

	header #menu-stucture li > ul { display:none; top:100%;  }

	header #menu-stucture li > ul { display:none; position:absolute; top:100%;  }
	header #menu-stucture > li:hover > a {   border-bottom:2px solid rgba(255,0,0,1);  transition:all 0.15s; }
	header #menu-stucture > li:hover > ul { display:block; padding-top:2px; min-width:100%;}
	header #menu-stucture li > ul > li { white-space: nowrap; font-size:0.8em; min-width:100%;}
	header #menu-stucture li > ul > li:hover { background-color: rgba(255,255,255,0.8);}
	header #menu-stucture li > ul > li { white-space: nowrap; font-size:0.8em; min-width:100%;}

	header #crumb-path { margin-top:10px; }
	header #crumb-path > span.crumb-delimeter { display:inline-block; padding:0px 10px; font-weight:600; }
	header #crumb-path > span { white-space:nowrap;  }
	header #crumb-path > a { text-decoration:none; color:var(--default-text-color); }

	header #language-selection { position:absolute; bottom:100%; right:0px; }
	header #language-selection > a { display:inline-block; padding:3px 8px; margin:-3px 5px; text-decoration:none; text-transform:uppercase; }
	header #language-selection > a:hover { background-color:rgb(230,230,230); }


/**	Footer
*/

	.content { }

/**	Footer
*/

	footer { display:flex; justify-content:center;  }


/**	Messagebox
*/
	
	.message-container { max-width:1024px; margin:5px auto; padding:15px 11px; }
	.message-container:empty { display:none; }
	.message-container > p { margin: 4px 0; }
	.message-container.message-type-1 { border:1px solid red; }
	.message-container.message-type-2 { border:1px solid orange; }
	.message-container.message-type-3 { border:1px solid green; }
	.message-container.message-type-4 { border:1px solid blue; }
	
</style>

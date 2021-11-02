
class	cmsCodeEditor
{
	constructor()
	{
		this.aEditors 		= [];
	}

	init(className, nameAttribute)
	{
	//	var that = this;

		this.className 		= className;
	//	this.cmdFilelocation= cmdFilelocation;
		this.nameAttribute	= nameAttribute;
	
	//	cmstk.callXHR(this.cmdFilelocation, null, this.onXHRInit, cmstk.onXHRError, that);

	//	document.addEventListener('mousedown', function(event) { that.hideToolbars(event.target); }, false);
	}

	

	create(nFlag = 1)
	{
		

 		var targetElements = Array.prototype.slice.call(document.getElementsByClassName(this.className), 0);

		for(var nItem = 0; nItem < targetElements.length; nItem++)
		{
			if(this.editorExists(targetElements[nItem])) continue;
			this.replace(targetElements[nItem]);
		}
		
	//	document.execCommand("defaultParagraphSeparator", false, "br");
	}

	createOnDestNode(node)
	{
		var	targetElement = node.querySelector('.'+ this.className);
		if(targetElement != null)		
			this.replace(targetElement);
	}

	editorExists(targetElement)
	{
		var childNodes = targetElement.childNodes
		if(childNodes.length == 3 && childNodes[0].classList !== undefined && childNodes[0].classList.contains('rte-editbox'))
			return true;
		return false;
	}

	replace(srcElement)
	{

		var that 		= this;
		var	nEditorId 	= this.aEditors.length;

		var fontColor = cmstk.getVisibleBackgroundColor(srcElement);
			fontColor = cmstk.invertRGBA(fontColor);


		var oParent 			= document.createElement("div");
			oParent.classList.add(this.className);
			oParent.classList.add('editor-simple');
			oParent.id 			= srcElement.id || "rich-code-" + nEditorId;


		var oEditContainer					= document.createElement("div");
			oEditContainer.className			= "rte-editcont"
			oEditContainer.style.height 		= '0px';
			oEditContainer.style.transition 	= 'height 0.15s';
			oEditContainer.style.overflow 		= 'hidden';

		var oEditBox 			= document.createElement("textarea");
			oEditBox.className 	= "rte-editbox";
			oEditBox.id 		= "rte-editbox-" + nEditorId;
			oEditBox.innerHTML 	= srcElement.innerHTML;
			oEditBox.setAttribute('name', this.nameAttribute);
			oEditBox.setAttribute('wrap', 'off');
			oEditBox.oninput	= function() {
				var	textarea = this.parentNode.parentNode.querySelector('.rte-output');
					textarea.innerHTML = this.value;
			};
			oEditBox.onkeydown	= function(event) {
				//if(event.keyCode == 9) { // tab key
				if(event.key == 'Tab') { // tab key
					var text = "\t";
					if(this.selectionStart || this.selectionStart === 0)
					{
						var startPos = this.selectionStart;
						var endPos = this.selectionEnd;
						this.value = this.value.substring(0, startPos) + text + this.value.substring(endPos, this.value.length);
						this.selectionStart = startPos + text.length;
						this.selectionEnd = startPos + text.length;
					}
					else
					{
						this.value += text;
					}
					event.preventDefault();
				}

				
			};
		//	oEditBox.style.backgroundColor = 'transparent';
			oEditBox.style.color 		= fontColor;
			oEditBox.style.height 		= 'calc(100% - 20px)';
			oEditBox.style.width 		= '100%';
			oEditBox.style.fontFamily 	= 'monospace';
			oEditBox.style.borderWidth 	= '2px 0px 2px 0px';
			oEditBox.style.boxShadow 	= 'none';
			oEditBox.style.tabSize 		= '4';
			oEditBox.style.MozTabSize	= '4';
		//	oEditBox.style.whiteSpace 	= 'nowrap';
			oEditBox.style.overflow		= 'auto';
			oEditBox.style.resize		= 'none';
			oEditBox.style.margin		= '10px 0px';

		this.aEditors.push(oEditBox);

		oEditContainer.appendChild(oEditBox);

		var oOutputBox				= document.createElement("div");
			oOutputBox.className	= "rte-output"
			oOutputBox.id 			= "rte-output-" + nEditorId;
			oOutputBox.innerHTML 	= srcElement.innerHTML;

		var oToolsBar 			= document.createElement("div"); 
			oToolsBar.className = "rte-code-tools";
		//	oToolsBar.style.display = "none";
			oToolsBar.id 		= "rte-tools-" + nEditorId;



		var	oIconTag	  = document.createElement("i");
			oIconTag.classList.add('fas');
			oIconTag.classList.add('fa-code');

		var oButton 		  = document.createElement("button");
			oButton.className = "rte-button";
	//		oButton.id 		  = 'toggleSource-'+ nEditorId;
			oButton.title 	  = 'Hide/Show Source';
			oButton.onclick   = function() {

				var	textarea  = this.parentNode.parentNode.querySelector('.rte-editcont');

				if(textarea.style.height === '0px')
					textarea.style.height = '300px';
				else

					textarea.style.height = '0px';

			
			};
			oButton.setAttribute('type','button');
			oButton.style.color = fontColor;
			oButton.appendChild(oIconTag);
			oToolsBar.appendChild(oButton);


		oParent.appendChild(oToolsBar);
		oParent.appendChild(oEditContainer);
		oParent.appendChild(oOutputBox);

		srcElement.parentNode.replaceChild(oParent, srcElement);


	}

	
}
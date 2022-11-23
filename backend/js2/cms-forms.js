class cmsForms
{
	/**
	 * 	This function returns the valid input fields inside a fieldset as array
	 * 
	 * 	@param fieldsetNode Must be a FIELDSET or FORM DOM node!
	 * 	@return array An array with the fields, the name-attribute is the key.
	 */
	static collectFields(fieldsetNode)
	{
		let fieldList = [];
		for(var i = 0; i < fieldsetNode.elements.length; i++)
		{
			let field = fieldsetNode.elements[i];
			
			if(	!field.name 			   || 
			  	 field.disabled 		   || 
				 field.type === 'file'     || 
				 field.type === 'reset'    || 
				 field.type === 'submit'   || 
				 field.type === 'button'   || 
				(field.type === 'checkbox' && !field.checked) || 
				(field.type === 'radio'    && !field.checked)
			  )	 continue;

			fieldList[field.name] = field.value;
		}
		return fieldList;
	}

	/**
	 * 	This function returns the valid input fields inside a FormData object
	 * 
	 * 	@param fieldsetNode Must be a FIELDSET or FORM DOM node!
	 * 	@return FormData A FormData object with the fields
	 */
	static collectFormData(fieldsetNode)
	{
		let formData  = new FormData;
		let fieldList = cmsForms.collectFields(fieldsetNode);
		for(let i in fieldList)
		{
			if(typeof fieldList[i] === 'function')
				continue;
			formData.append(i, fieldList[i]);
		}
		return formData;
	}

	/*
		This function validates the input if this not covered bei vanilla HTML
	*/
	static validateInput(event)
	{
		let node = event.target;

		let correction = 0;	
		if(event.data !== null) // inputType: 'insertText'
		{

			switch(String(event.data).toLowerCase())
			{
				case 'ä':
				case 'ö':
				case 'ü':
				case 'ß':

					correction = 1;
			}
		}
		/*
			insertFromPaste
		*/

		if(node.tagName === 'INPUT')
		{
			if(node.hasAttribute('validate-filename'))
			{
				let position = node.selectionStart; 
				node.value = cmstk.validateFilename(node.value);
  				node.selectionEnd = position + correction;   
			}

			if(node.hasAttribute('validate-filepath'))
			{
				let position = node.selectionStart; 
				node.value = cmstk.validateFilepath(node.value);
  				node.selectionEnd = position + correction;   
			}
		}
	}
}


addEventListener("input", cmsForms.validateInput, false);
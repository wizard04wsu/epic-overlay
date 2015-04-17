function multiColumnSelect(separator, columnGapStr){
	
	var selects = document.getElementsByClassName("multiColumnSelect");
	
	for(s=0; s<selects.length; s++){
		if(selects[s].tagName == "SELECT"){
			(function (){
				var headings = selects[s].getElementsByTagName("optgroup")[0], headingsOption,
					options = Array.prototype.slice.call(selects[s].options, 0), o,
					optionColumns = [], columnText, c, maxLen = [];
				
				separator = separator || ";";
				columnGapStr = columnGapStr || "\u00a0";
				
				//get the maximum string length in each column
				
				if(headings){
					if(!headings.originalOptionText){
						//store the original text in case this script needs to be run again because of changes to the list
						headings.originalOptionText = headings.label;
					}
					
					//create a temporary option element for the headings
					headingsOption = document.createElement("option");
					headingsOption.innerHTML = headings.originalOptionText;
					
					//insert the option into the list
					options.unshift(headingsOption);
				}
				
				//for each option
				for(o=0; o<options.length; o++){
					
					if(!options[o].originalOptionText){
						//store the original text in case this script needs to be run again because of changes to the list
						options[o].originalOptionText = options[o].textContent || options[o].innerText;
					}
					
					//get the text of each column in this option
					columnText = options[o].originalOptionText.split(separator);
					
					//for each column
					for(c=0; c<columnText.length; c++){
						//remove extra spaces
						columnText[c] = columnText[c].replace(/\s+/g, " ").trim();
						//update the maximum string length in this column
						if(columnText[c].length > (maxLen[c] || 0)){
							maxLen[c] = columnText[c].length;
						}
					}
					
					optionColumns[o] = columnText;
					
				}
				
				//add spaces where necessary to align the columns
				
				//for each option
				for(o=0; o<options.length; o++){
					
					//get the text of each column in this option
					columnText = optionColumns[o];
					
					//for each column
					for(c=0; c<columnText.length; c++){
						//add required padding to the text in this column
						if(columnText[c].length < maxLen[c]){
							columnText[c] += Array(maxLen[c] - columnText[c].length + 1).join("\u00a0");
						}
						if(c < columnText.length-1){	//not the last column
							//add columnGapStr to this column
							columnText[c] += columnGapStr;
						}
					}
					
					//update the text of the option
					options[o].innerHTML = columnText.join("");
					
				}
				
				if(headings){
					headings.label = options[0].innerHTML.replace(/&nbsp;/g, "\u00a0");
				}
				
			})();
		}
	}
}


var currentInstance, iframe;

function initTemplates(templates){
	
	"use strict";
	
	var i, sel_templates = $("#templates"), option, currentTemplate,
		iTitle = $("#templateTitle")[0],
		iPath = $("#templatePath")[0],
		iConfig = $("#templateConfig")[0],
		sel_instances = $("#instances");
	
	if(!sel_templates || !templates) return;
	
	//add the template <option>s to the list
	for(i=0; i<templates.length; i++){
		option = document.createElement("option");
		option.value = templates[i].id;
		option.selected = i==0;
		option.innerHTML = templates[i].title;
		option.template = templates[i];
		sel_templates.append(option);
	}
	
	//event handler for when user clicks a different template in the list
	sel_templates.on("change", templateChange);
	templateChange();
	
	//event handlers for the buttons
	$("#templateRegister").on("click", registerTemplate);
	$("#templateRemove").on("click", removeTemplate);
	$("#templateSave").on("click", saveTemplate);
	$("#templateCancel").on("click", cancelTemplate);
	$("#instanceCreate").on("click", createInstance);
	
	//event handlers for changes to the text fields
	iTitle.addEventListener("input", updateSaveBtn, false);
	iTitle.addEventListener("change", updateSaveBtn, false);	//for IE
	iPath.addEventListener("input", updateSaveBtn, false);
	iPath.addEventListener("change", updateSaveBtn, false);	//for IE
	iConfig.addEventListener("input", updateSaveBtn, false);
	iConfig.addEventListener("change", updateSaveBtn, false);	//for IE
	updateSaveBtn();
	
	function templateChange(){
		currentTemplate = $("#templates option:selected")[0];
		if(currentTemplate){	//a template is selected
			currentTemplate = currentTemplate.template;
			//populate text fields
			$("#templateTitle")[0].value = currentTemplate.title;
			$("#templatePath")[0].value = currentTemplate.path;
			$("#templateConfig")[0].value = currentTemplate.config;
			//enable buttons
			$("#templateRemove")[0].disabled = $("#instanceCreate")[0].disabled = false;
		}
		else{	//no template selected (a new template is being registered)
			//clear text fields
			$("#templateTitle")[0].value = $("#templatePath")[0].value = $("#templateConfig")[0].value = "";
			//disable buttons
			$("#templateRemove")[0].disabled = $("#instanceCreate")[0].disabled = true;
		}
	}
	
	function registerTemplate(){
		//deselect the current template
		$("#templates option:selected").each(function (){ this.selected = false; });
		currentTemplate = null;
		templateChange();
		//put cursor in Title field
		$("#templateTitle")[0].focus();
	}
	
	function removeTemplate(){
		if(confirm("Are you sure you want to remove this template?\n\n"+HTMLToText(currentTemplate.title))){
			//request removal of the template
			$.ajax({
				url: 'set.php',
				method: 'POST',
				data: {
					template: currentTemplate.id,
					action: "removeTemplate"
				}
			}).done(function(content, message, xhr) {
				
				var remainingOptions;
				
				if (205 !== xhr.status) {	//error returned
					//display the error message
					alert("Failed to remove template:\n\n"+content);
					return;
				}
				
				//success; remove the template from the list
				$("#templates option:selected").remove();
				remainingOptions = $("#templates option");
				
				//select the first template in the list (if there is one)
				if(remainingOptions[0]) remainingOptions[0].selected = true;
				templateChange();
				
			}).fail(function(xhr, message, errorThrown) {
				//display a generic error message
				alert("Failed to remove template:\n\n"+message+"\n\n"+errorThrown);
			});
		}
	}
	
	function updateSaveBtn(){
		
		var btn_save = $("#templateSave")[0];
		
		if(currentTemplate && iTitle.value==currentTemplate.title && iPath.value==currentTemplate.path && iConfig.value==currentTemplate.config){
			//no changes have been made
			btn_save.disabled = true;
		}
		else if(!iTitle.value || !iPath.value || !iConfig.value){
			//one or more text fields are empty
			btn_save.disabled = true;
		}
		else{
			btn_save.disabled = false;
		}
		
	}
	
	function saveTemplate(){
		
		//disable the form fields & buttons
		iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = true;
		//TODO: display some "waiting" indicator
		
		//post the changes
		$.ajax({
			url: 'set.php',
			method: 'POST',
			data: {
				template: currentTemplate ? currentTemplate.id : "",
				action: currentTemplate ? "saveTemplate" : "registerTemplate",
				title: iTitle.value,
				path: iPath.value,
				config: iConfig.value
			}
		}).done(function(content, message, xhr) {
			
			var option, id, instances, i;
			
			if(!currentTemplate){	//template registered
				
				if (200 !== xhr.status) {	//error returned
					//display the error message
					alert("Failed to register template:\n\n"+content);
				}
				else{
	
					//get the new template ID
					id = 1*JSON.parse(content);
					
					if(id <= 0 || id !== Math.floor(id)){	//invalid ID
						alert("Invalid template ID: "+id);
					}
					else{
						
						//add the template <option> to the list
						option = document.createElement("option");
						option.value = id;
						option.innerHTML = iTitle.value;
						option.template = {id: id, title: iTitle.value, path: iPath.value, config: iConfig.value};
						sel_templates.append(option);
						
						//sort the template list
						$("#templates option").sortElements(templateComparator);
						
						//select the template
						option.selected = true;
						templateChange();
						
					}
					
				}
				
				//re-enable the form fields & buttons
				iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = false;
				updateSaveBtn();
				
			}
			else{	//template saved
				
				if (205 !== xhr.status) {	//error returned
					//display the error message
					alert("Failed to save settings:\n\n"+content);
				}
				else{
	
					//update the template <option>
					option = $("#templates option:selected")[0];
					option.innerHTML = iTitle.value;
					currentTemplate = option.template = {id: currentTemplate.id, title: iTitle.value, path: iPath.value, config: iConfig.value};
					$("#templates option").sortElements(templateComparator);
					
					//update the template objects and text in the instance list
					instances = $("#instances option");
					for(i=0; i<instances.length; i++){
						if(instances[i].instance.template.id == currentTemplate.id){
							instances[i].instance.template = currentTemplate;
							instances[i].innerHTML = instances[i].instance.title.replace(/\\/g, "&#92;")+"\\"+iTitle.value.replace(/\\/g, "&#92;");
							instances[i].originalOptionText = instances[i].innerHTML;
						}
					}
					
					//sort the instance list
					$("#instances option").sortElements(instanceComparator);
					multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
					
				}
				
				//re-enable the form fields & buttons
				iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = false;
				updateSaveBtn();
				
			}
			
		}).fail(function(xhr, message, errorThrown) {
			//display a generic error message
			if(currentTemplate){
				alert("Failed to save settings:\n\n"+message+"\n\n"+errorThrown);
			}
			else{
				alert("Failed to register template:\n\n"+message+"\n\n"+errorThrown);
			}
		})
		
	}
	
	function cancelTemplate(){
		
		if(currentTemplate){	//editing an existing template
			//reset the text fields to the existing values
			iTitle.value = currentTemplate.title;
			iPath.value = currentTemplate.path;
			iConfig.value = currentTemplate.config;
		}
		else{	//registering a new template
			//clear the text fields
			iTitle.value = iPath.value = iConfig.value = "";
		}
		
		updateSaveBtn();
		
	}
	
	function createInstance(){
		
		var template = currentTemplate;
		
		//request default template settings
		$.ajax({
			url: 'templates/'+template.config,
			method: 'POST',
			data: {
				getDefaults: "1"
			}
		}).done(function(content, message, xhr) {
			
			var title;
			
			if (200 !== xhr.status) {	//error returned
				//display the error message
				alert("Failed to retrieve default settings:\n\n"+content);
				return;
			}
			
			//success
			
			//have the user input a title
			title = prompt("Please enter the title.");
			while(title.trim() === ""){
				title = prompt("The title cannot be empty.\n\nPlease enter the title.");
			}
			if(title !== null){	//user didn't click Cancel
				
				title = textToHTML(title.trim());
				
				//add the instance to the database with the default settings
				$.ajax({
					url: 'set.php',
					method: 'POST',
					data: {
						action: "createInstance",
						title: title,
						template: template.id,
						settings: content
					}
				}).done(function(content, message, xhr) {
					
					var option, id;
					
					if (200 !== xhr.status) {	//error returned
						//display the error message
						alert("Failed to create the instance:\n\n"+content);
						return;
					}
					
					//get the new instance ID
					id = 1*JSON.parse(content);
					
					if(id <= 0 || id !== Math.floor(id)){	//invalid ID
						alert("Invalid instance ID: "+id);
					}
					else{
						
						//add the instance <option> to the list
						option = document.createElement("option");
						option.value = id;
						option.innerHTML = title.replace(/\\/g, "&#92;")+"\\"+template.title.replace(/\\/g, "&#92;");
						option.instance = {id: id, title: title, template: template};
						sel_instances.append(option);
						
						//sort the instance list
						$("#instances option").sortElements(instanceComparator);
						multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
						
						//select the new instance
						option.selected = true;
						instanceChange();
						
					}
					
				}).fail(function(xhr, message, errorThrown) {
					//display a generic error message
					alert("Failed to create the instance:\n\n"+message+"\n\n"+errorThrown);
				});
				
			}
			
			
		}).fail(function(xhr, message, errorThrown) {
			//display a generic error message
			alert("Failed to retrieve default settings:\n\n"+message+"\n\n"+errorThrown);
		});
		
	}
	
}


function initInstances(instances){
	
	"use strict";
	
	var i, sel_instances = $("#instances"), option;
	
	iframe = $("#settings")[0];
	
	if(!sel_instances || !instances) return;
	
	//add the instance <option>s to the list
	for(i=0; i<instances.length; i++){
		option = document.createElement("option");
		option.value = instances[i].id;
		option.selected = i==0;
		option.innerHTML = instances[i].title.replace(/\\/g, "&#92;")+"\\"+instances[i].template.title.replace(/\\/g, "&#92;");
		option.instance = instances[i];
		sel_instances.append(option);
	}
	
	multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
	
	//handlers to execute when the settings iframe loads
	iframe.addEventListener("load", updateIframeHeight, false);
	iframe.addEventListener("load", updateTitle, false);
	
	//event handler for when user selects a different instance in the list
	sel_instances.on("change", instanceChange);
	instanceChange();
	
	//event handlers for the buttons
	$("#instanceDelete").on("click", deleteInstance);
	
	function updateIframeHeight(){
		//set the height of the <iframe> to match that of the document it contains
		iframe.style.height = iframe.contentWindow.document.body.clientHeight + "px";
	}
	
	function updateTitle(){
		//when the settings page loads, get the title (from the instanceTitle variable in the settings page) and update the option in the instances list
		 
		var title = iframe.contentWindow.instanceTitle.trim(),
			option = $("#instances option:selected")[0];
		
		if(title !== currentInstance.title){	//the title has changed
			//update the title in the instances list
			currentInstance.title = title;
			option.innerHTML = title.replace(/\\/g, "&#92;")+"\\"+currentInstance.template.title.replace(/\\/g, "&#92;");
			option.originalOptionText = option.innerHTML;
			multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
			
			//sort the instances list
			$("#instances option").sortElements(instanceComparator);
		}
	}
	
	function deleteInstance(){
		if(confirm("Are you sure you want to delete this instance of the "+HTMLToText(currentInstance.template.title)+" template?\n\n"+HTMLToText(currentInstance.title))){
			//request deletion of the instance
			$.ajax({
				url: 'set.php',
				method: 'POST',
				data: {
					instance: currentInstance.id,
					action: "deleteInstance"
				}
			}).done(function(content, message, xhr) {
				
				var remainingOptions;
				
				if (205 !== xhr.status) {	//error returned
					//display the error message
					alert("Failed to delete instance:\n\n"+content);
					return;
				}
				
				//success; remove the instance from the list
				$("#instances option:selected").remove();
				remainingOptions = $("#instances option");
				
				//select the first instance in the list (if there is one)
				if(remainingOptions[0]) remainingOptions[0].selected = true;
				instanceChange();
				
			}).fail(function(xhr, message, errorThrown) {
				//display a generic error message
				alert("Failed to delete instance:\n\n"+message+"\n\n"+errorThrown);
			});
		}
	}
	
}


//comparator used to sort the template list
function templateComparator(a, b){
	return HTMLToText(a.template.title).localeCompare(HTMLToText(b.template.title));
}

//comparator used to sort the instances list
function instanceComparator(a, b){
	return HTMLToText(a.instance.title).localeCompare(HTMLToText(b.instance.title)) ||
		HTMLToText(a.instance.template.title).localeCompare(HTMLToText(b.instance.template.title));
}

function instanceChange(evt){
	currentInstance = $("#instances option:selected")[0];
	iframe = $("#settings")[0];
	if(currentInstance){	//an instance is selected
		currentInstance = currentInstance.instance;
		//show the link to open the instance in a new window
		$("#instanceLink")[0].style.visibility = "visible";
		$("#instanceLink")[0].href = "templates/"+currentInstance.template.path+"?instance="+currentInstance.id;
		//show the settings
		iframe.src = "templates/"+currentInstance.template.config+"?instance="+currentInstance.id;
	}
	else{	//no instance is selected (there are no instances)
		//hide the link
		$("#instanceLink")[0].style.visibility = "hidden";
		//clear the settings frame
		iframe.src = "";
	}
}

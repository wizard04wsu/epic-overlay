
function initTemplates(templates){
	
    "use strict";
    
	var i, sel_templates = $("#templates"), option, currentTemplate,
		iTitle = $("#templateTitle")[0],
		iPath = $("#templatePath")[0],
		iConfig = $("#templateConfig")[0];
	
	if(!sel_templates || !templates) return;
	
	//add the template <option>s to the list
	for(i=0; i<templates.length; i++){
		option = document.createElement("option");
		option.value = templates[i].id;
		option.selected = i==0;
		option.innerHTML = textToHtml(templates[i].title);
		option.template = templates[i];
		sel_templates.append(option);
	}
	
	sel_templates.on("change", templateChange);
	templateChange();
	$("#templateRegister").on("click", registerTemplate);
	$("#templateRemove").on("click", removeTemplate);
	$("#templateSave").on("click", saveTemplate);
	$("#templateCancel").on("click", cancelTemplate);
	$("#instanceCreate").on("click", createInstance);
	
	iTitle.addEventListener("input", updateSaveBtn, false);
	iTitle.addEventListener("change", updateSaveBtn, false);	//for IE
	iPath.addEventListener("input", updateSaveBtn, false);
	iPath.addEventListener("change", updateSaveBtn, false);	//for IE
	iConfig.addEventListener("input", updateSaveBtn, false);
	iConfig.addEventListener("change", updateSaveBtn, false);	//for IE
	updateSaveBtn();
	
	function templateChange(){
		currentTemplate = $("#templates option:selected")[0];
		if(currentTemplate){
			currentTemplate = currentTemplate.template;
			//populate fields
			$("#templateTitle")[0].value = currentTemplate.title;
			$("#templatePath")[0].value = currentTemplate.path;
			$("#templateConfig")[0].value = currentTemplate.config;
			//enable buttons
			$("#templateRemove")[0].disabled = $("#instanceCreate")[0].disabled = false;
		}
		else{	//no template selected
			//clear fields
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
		if(confirm("Are you sure you want to remove this template?\n\n"+currentTemplate.title)){
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
			btn_save.disabled = true;
		}
		else if(!iTitle.value || !iPath.value || !iConfig.value){
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
		
		//use jQuery to post the changes
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

					//re-enable the form fields & buttons
					iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = false;
					updateSaveBtn();

					return;
				}

				//get the ID
				id = 1*JSON.parse(content);
				if(id <= 0 || id !== Math.floor(id)){
					alert("Invalid instance ID: "+id);

					//re-enable the form fields & buttons
					iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = false;
					updateSaveBtn();

					return;
				}
				
				//add the template <option> to the list
				option = document.createElement("option");
				option.value = id;
				option.innerHTML = textToHtml(iTitle.value);
				option.template = {id: id, title: iTitle.value, path: iPath.value, config: iConfig.value};
				sel_templates.append(option);
				$("#templates option").sortElements(templateComparator);
				option.selected = true;
				templateChange();
				
				//re-enable the form fields & buttons
				iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = false;
				updateSaveBtn();

			}
			else{	//template saved
				
				if (205 !== xhr.status) {	//error returned
					//display the error message
					alert("Failed to save settings:\n\n"+content);

					//re-enable the form fields & buttons
					iTitle.disabled = iPath.disabled = iConfig.disabled = $("#templateSave").disabled = $("#templateCancel").disabled = false;
					updateSaveBtn();

					return;
				}

				//update the template <option>
				option = $("#templates option:selected")[0];
				option.innerHTML = textToHtml(iTitle.value);
				currentTemplate = option.template = {id: currentTemplate.id, title: iTitle.value, path: iPath.value, config: iConfig.value};
				$("#templates option").sortElements(templateComparator);
				
				//update the template names in the instance list
				instances = $("#instances option");
				for(i=0; i<instances.length; i++){
					if(instances[i].instance.template.id == currentTemplate.id){
						instances[i].instance.template = currentTemplate;
						instances[i].innerHTML = textToHtml(instances[i].instance.title).replace(/\\/g, "&#92;")+"\\"+textToHtml(iTitle.value).replace(/\\/g, "&#92;");
						instances[i].originalOptionText = instances[i].textContent || instances[i].innerText;
					}
				}
				$("#templates option").sortElements(instanceComparator);
				multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
				
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
		
		if(currentTemplate){
			iTitle.value = currentTemplate.title;
			iPath.value = currentTemplate.path;
			iConfig.value = currentTemplate.config;
		}
		else{
			iTitle.value = iPath.value = iConfig.value = "";
		}
		
		updateSaveBtn();
		
	}
	
	function createInstance(){
		
		var template = currentTemplate;
		
		//get default template settings
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
			if(title !== null){
				
				title = title.trim();
				
				//add the instance to the database
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
					
					//success; get the ID
					id = 1*JSON.parse(content);
					if(id <= 0 || id !== Math.floor(id)){
						alert("Invalid instance ID: "+id);
						return;
					}
					
					//add the instance <option> to the list
					option = document.createElement("option");
					option.value = id;
					option.innerHTML = textToHtml(title).replace(/\\/g, "&#92;")+"\\"+textToHtml(template.title).replace(/\\/g, "&#92;");
					option.instance = {id: id, title: title, template: template};
					sel_instances.append(option);
					$("#templates option").sortElements(instanceComparator);
					multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
					option.selected = true;
					instanceChange();
					
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
    
	var i, sel_instances = $("#instances"), iframe = $("#settings")[0], option, currentInstance;
	
	if(!sel_instances || !instances) return;
	
	//add the instance <option>s to the list
	for(i=0; i<instances.length; i++){
		option = document.createElement("option");
		option.value = instances[i].id;
		option.selected = i==0;
		option.innerHTML = textToHtml(instances[i].title).replace(/\\/g, "&#92;")+"\\"+textToHtml(instances[i].template.title).replace(/\\/g, "&#92;");
		option.instance = instances[i];
		sel_instances.append(option);
	}
	
	multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
	
	iframe.addEventListener("load", updateIframeHeight, false);
	sel_instances.on("change", instanceChange);
	instanceChange();
	$("#instanceDelete").on("click", deleteInstance);
	
	function instanceChange(evt){
		currentInstance = $("#instances option:selected")[0];
		if(currentInstance){
			currentInstance = currentInstance.instance;
			$("#instanceLink")[0].style.visibility = "visible";
			$("#instanceLink")[0].href = "templates/"+currentInstance.template.path+"?instance="+currentInstance.id;
			iframe.src = "templates/"+currentInstance.template.config+"?instance="+currentInstance.id;
		}
		else{
			$("#instanceLink")[0].style.visibility = "hidden";
			iframe.src = "";
		}
	}
	
	function updateIframeHeight(){
		iframe.style.height = iframe.contentWindow.document.body.clientHeight + "px";
	}
	
	function deleteInstance(){
		if(confirm("Are you sure you want to delete this instance of the "+currentInstance.template.title+" template?\n\n"+currentInstance.title)){
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
				if(remainingOptions[0]) remainingOptions[0].selected = true;
				instanceChange();
				
			}).fail(function(xhr, message, errorThrown) {
				//display a generic error message
				alert("Failed to delete instance:\n\n"+message+"\n\n"+errorThrown);
			});
		}
	}
	
}


function textToHtml(str){
	return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

function templateComparator(a, b){
	return a.template.title < b.template.title ? 1 : -1;
}

function instanceComparator(a, b){
	if(a.instance.title == b.instance.title){
		return a.instance.template.title < b.instance.template.title ? 1 : -1;
	}
	return a.instance.title < b.instance.title ? 1 : -1;
}

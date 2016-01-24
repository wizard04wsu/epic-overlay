function textToHTML(str){
	return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;")/*.replace(/'/g, "&#39;")*/.replace(/'/g, "&apos;");	//PHP uses &apos; instead of &#39;
}

//decode all HTML character entity references in the string (not just the reserved characters)
function HTMLToText(str){
	var tmp;
	tmp = document.createElement("div");
	tmp.innerHTML = str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
	return tmp.firstChild.nodeValue;
}

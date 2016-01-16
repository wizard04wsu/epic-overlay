//https://gist.github.com/wizard04wsu/8871791

//getDimensions([elem])
//setScrollPosition(elem, top, left)

(function (){
	
	"use strict";
	
	//**********************//
	//***** Dimensions *****//
	//**********************//
	
	//get current dimensions and positions of an element (or window/frame/document), plus those of the viewport, browser, and screen
	//if `elem` is not provided, `window` will be used
	//
	//returns an object containing:
	//	scroll: { width, height, left, top },
	//	inner: { width, height },
	//	outer: { width, height },	//includes scroll bars
	//	position: { left, top },	//position from top-left of document
	//
	//	viewport: {
	//		inner: { width, height },
	//		outer: { width, height } },	//includes scroll bars
	//	browser: { width, height, left, right },
	//	screen: { width, height, availWidth, availHeight, colorDepth }
	this.getDimensions = function getDimensions(elem){
		
		var win, doc, left, top, tmp, dims = {};
		
		elem = elem || window;
		doc = elem.ownerDocument || elem.document || elem;
		/*	  elem===element        elem===window    elem===document */
		win = doc.defaultView || doc.parentWindow || window;
		
		//****************************************************//
		//***** screen, browser, and viewport dimensions *****//
		//****************************************************//
		
		dims.screen = {
			width: screen.width,
			height: screen.height,
			//available screen dimensions; excludes taskbar, etc.
			availWidth: screen.availWidth,
			availHeight: screen.availHeight,
			colorDepth: screen.colorDepth
		};
		dims.browser = {
			width: win.outerWidth,	//undefined in IE, incorrect in Opera
			height: win.outerHeight,	//undefined in IE, incorrect in Opera
			left: win.screenX,		//undefined in IE, incorrect in Opera
			top: win.screenY			//undefined in IE, incorrect in Opera
		};
		dims.viewport = {
			outer: {	//includes scroll bars
				width: win.top.innerWidth,	//undefined in IE
				height: win.top.innerHeight	//undefined in IE
			},
			inner: {	//excludes scroll bars
				width: win.top.document.documentElement.clientWidth,
				height: win.top.document.documentElement.clientHeight
			}
		};
		
		if(elem === win || elem === win.document){
			
			//********************************************//
			//***** window/frame/document dimensions *****//
			//********************************************//
			
			//scroll position of document
			if(!isNaN(win.pageYOffset)){	//all except IE
				left = win.pageXOffset;
				top = win.pageYOffset;
			}
			else{	//IE
				//IE quirks mode
				left = win.document.body.scrollLeft;
				top = win.document.body.scrollTop;
				
				//IE standards compliance mode
				if(win.document.documentElement && win.document.documentElement.scrollTop){
					left = win.document.documentElement.scrollLeft;
					top = win.document.documentElement.scrollTop;
				}
			}
			left = left || 0;
			top = top || 0;
			
			dims.outer = {	//includes scroll bars
				width: win.innerWidth,	//undefined in IE
				height: win.innerHeight	//undefined in IE
			};
			dims.inner = {	//excludes scroll bars
				width: win.document.documentElement.clientWidth,
				height: win.document.documentElement.clientHeight	//incorrect in quirks mode (equals offsetHeight)
			};
			dims.scroll = {
				width: win.document.documentElement.scrollWidth,
				height: win.document.documentElement.scrollHeight,
				left: left,
				top: top
			};
			dims.position = { left: 0, top: 0 };
			
		}
		else{
			
			//*****************************//
			//***** element dimensions ****//
			//*****************************//
			
			//by "empty space", I mean the space surrounding (or in between) content & padding if they do not fill the element
			
			dims.outer = {	//everything (visible content & padding, empty space, scrollbar, border)
				width: elem.offsetWidth,
				height: elem.offsetHeight
			};
			dims.inner = {	//visible content & padding, and empty space; gives 0 for inline elements (you can use scrollWidth/Height if it's inline)
				width: elem.clientWidth,
				height: elem.clientHeight
			};
			dims.scroll = {
				//width & height of content & padding (visible or not), and empty space
				//accounts for IE not including the empty space
				//incorrect in Opera; it only includes the content
				width: elem.scrollWidth < elem.clientWidth ? elem.clientWidth : elem.scrollWidth,
				height: elem.scrollHeight < elem.clientHeight ? elem.clientHeight : elem.scrollHeight,
				
				//scroll position of content & padding
				left: elem.scrollLeft,
				top: elem.scrollTop
			};
			
			//position of element from the top-left corner of the document
			dims.position = {};
			tmp = elem;
			dims.position.left = dims.position.top = 0;
			while(tmp.offsetParent){
				dims.position.left += tmp.offsetLeft;
				dims.position.top += tmp.offsetTop;
				tmp = tmp.offsetParent;
			}
			
		}
		
		return dims;
		
	};
	
	
	//*********************//
	//***** Scrolling *****//
	//*********************//
	
	//set scroll position within an element or the document
	this.setScrollPosition = function setScrollPosition(elem, top, left){
		
		var doc, win;
		
		if(!elem) return;
		top = Number(top) || 0;
		left = Number(left) || 0;
		
		doc = elem.ownerDocument || elem.document || elem;
		/*	  elem===element        elem===window    elem===document */
		win = doc.defaultView || doc.parentWindow || window;
		
		if(elem === win || elem === doc || elem === doc.documentElement){	//scroll the document
			try{
				doc.documentElement.scrollTop = top;
				if(doc.documentElement.scrollTop !== top) doc.body.scrollTop = top;
			}catch(e){
				try{ doc.body.scrollTop = top; }catch(e){}
			}
			try{
				doc.documentElement.scrollLeft = left;
				if(doc.documentElement.scrollLeft !== left) doc.body.scrollLeft = left;
			}catch(e){
				try{ doc.body.scrollLeft = left; }catch(e){}
			}
		}
		else if(elem === doc.body){	//scroll the body
			try{ doc.body.scrollTop = top; }catch(e){ try{ doc.documentElement.scrollTop = top; }catch(e){} }
			try{ doc.body.scrollLeft = left; }catch(e){ try{ doc.documentElement.scrollLeft = left; }catch(e){} }
		}
		else{	//scroll the element
			elem.scrollLeft = left;
			elem.scrollTop = top;
		}
		
	};
	
}).call(this);

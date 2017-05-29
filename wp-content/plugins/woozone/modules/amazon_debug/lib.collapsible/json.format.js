// Initialization and events code for the app
WooZoneAmazonDebug = (function($) {
	"use strict";
	
	// public
	var debug_level = 0;
	
	// we need tabs as spaces and not CSS magin-left 
	// in order to ratain format when coping and pasing the code
	var SINGLE_TAB = "  ",
		lib_path = $id('WooZone-amzdbg-datael').getAttribute('alt'),
		ImgCollapsed = lib_path + "images/Collapsed.gif",
		ImgExpanded = lib_path + "images/Expanded.gif",
		QuoteKeys = true,
		_dateObj = new Date(),
		_regexpObj = new RegExp();
	var IsCollapsible, TAB;
	
	// init function, autoload
	function init() {
		// load the triggers
		$(document).ready(function() {
			triggers();
		});
	};
	
	function $id(id){ return document.getElementById(id); }
	
	function IsArray(obj) {
	  return obj && 
	          typeof obj === 'object' && 
	          typeof obj.length === 'number' &&
	          !(obj.propertyIsEnumerable('length'));
	}
	
	function Process(){
	  SetTab();
	  IsCollapsible = $id("CollapsibleView").checked;
	  var json = $id("RawJson").value;
	  var html = "";
	  try{
	    if(json == "") json = "\"\"";
	    var obj = eval("["+json+"]");
	    html = ProcessObject(obj[0], 0, false, false, false);
	    $id("Canvas").innerHTML = "<PRE class='CodeContainer'>"+html+"</PRE>";
	  }catch(e){
	    alert("JSON is not well formated:\n"+e.message);
	    $id("Canvas").innerHTML = "";
	  }
	}
	
	function ProcessObject(obj, indent, addComma, isArray, isPropertyContent){
	  var html = "";
	  var comma = (addComma) ? "<span class='Comma'>,</span> " : ""; 
	  var type = typeof obj;
	  var clpsHtml ="";
	  if(IsArray(obj)){
	    if(obj.length == 0){
	      html += GetRow(indent, "<span class='ArrayBrace'>[ ]</span>"+comma, isPropertyContent);
	    }else{
	      clpsHtml = IsCollapsible ? "<span><img src=\""+ImgExpanded+"\" class=\"ExpImgChoose\" /></span><span class='collapsible'>" : "";
	      html += GetRow(indent, "<span class='ArrayBrace'>[</span>"+clpsHtml, isPropertyContent);
	      for(var i = 0; i < obj.length; i++){
	        html += ProcessObject(obj[i], indent + 1, i < (obj.length - 1), true, false);
	      }
	      clpsHtml = IsCollapsible ? "</span>" : "";
	      html += GetRow(indent, clpsHtml+"<span class='ArrayBrace'>]</span>"+comma);
	    }
	  }else if(type == 'object'){
	    if (obj == null){
	        html += FormatLiteral("null", "", comma, indent, isArray, "Null");
	    }else if (obj.constructor == _dateObj.constructor) { 
	        html += FormatLiteral("new Date(" + obj.getTime() + ") /*" + obj.toLocaleString()+"*/", "", comma, indent, isArray, "Date"); 
	    }else if (obj.constructor == _regexpObj.constructor) {
	        html += FormatLiteral("new RegExp(" + obj + ")", "", comma, indent, isArray, "RegExp"); 
	    }else{
	      var numProps = 0;
	      for(var prop in obj) numProps++;
	      if(numProps == 0){
	        html += GetRow(indent, "<span class='ObjectBrace'>{ }</span>"+comma, isPropertyContent);
	      }else{
	        clpsHtml = IsCollapsible ? "<span><img src=\""+ImgExpanded+"\" class=\"ExpImgChoose\" /></span><span class='collapsible'>" : "";
	        html += GetRow(indent, "<span class='ObjectBrace'>{</span>"+clpsHtml, isPropertyContent);
	        var j = 0;
	        for(var prop in obj){
	          var quote = QuoteKeys ? "\"" : "";
	          html += GetRow(indent + 1, "<span class='PropertyName'>"+quote+prop+quote+"</span>: "+ProcessObject(obj[prop], indent + 1, ++j < numProps, false, true));
	        }
	        clpsHtml = IsCollapsible ? "</span>" : "";
	        html += GetRow(indent, clpsHtml+"<span class='ObjectBrace'>}</span>"+comma);
	      }
	    }
	  }else if(type == 'number'){
	    html += FormatLiteral(obj, "", comma, indent, isArray, "Number");
	  }else if(type == 'boolean'){
	    html += FormatLiteral(obj, "", comma, indent, isArray, "Boolean");
	  }else if(type == 'function'){
	    if (obj.constructor == _regexpObj.constructor) {
	        html += FormatLiteral("new RegExp(" + obj + ")", "", comma, indent, isArray, "RegExp"); 
	    }else{
	        obj = FormatFunction(indent, obj);
	        html += FormatLiteral(obj, "", comma, indent, isArray, "Function");
	    }
	  }else if(type == 'undefined'){
	    html += FormatLiteral("undefined", "", comma, indent, isArray, "Null");
	  }else{
	    html += FormatLiteral(obj.toString().split("\\").join("\\\\").split('"').join('\\"'), "\"", comma, indent, isArray, "String");
	  }
	  return html;
	}
	
	function FormatLiteral(literal, quote, comma, indent, isArray, style){
	  if(typeof literal == 'string')
	    literal = literal.split("<").join("&lt;").split(">").join("&gt;");
	  var str = "<span class='"+style+"'>"+quote+literal+quote+comma+"</span>";
	  if(isArray) str = GetRow(indent, str);
	  return str;
	}
	
	function FormatFunction(indent, obj){
	  var tabs = "";
	  for(var i = 0; i < indent; i++) tabs += TAB;
	  var funcStrArray = obj.toString().split("\n");
	  var str = "";
	  for(var i = 0; i < funcStrArray.length; i++){
	    str += ((i==0)?"":tabs) + funcStrArray[i] + "\n";
	  }
	  return str;
	}
	
	function GetRow(indent, data, isPropertyContent){
	  var tabs = "";
	  for(var i = 0; i < indent && !isPropertyContent; i++) tabs += TAB;
	  if(data != null && data.length > 0 && data.charAt(data.length-1) != "\n")
	    data = data+"\n";
	  return tabs+data;                       
	}
	
	function CollapsibleViewClicked(){
	  $id("CollapsibleViewDetail").style.visibility = $id("CollapsibleView").checked ? "visible" : "hidden";
	  Process();
	}
	
	function QuoteKeysClicked(){
	  QuoteKeys = $id("QuoteKeys").checked;
	  Process();
	}
	 
	function CollapseAllClicked(){
	  EnsureIsPopulated();
	  TraverseChildren($id("Canvas"), function(element){
	    if(element.className == 'collapsible'){
	      MakeContentVisible(element, false);
	    }
	  }, 0);
	}
	
	function ExpandAllClicked(){
	  EnsureIsPopulated();
	  TraverseChildren($id("Canvas"), function(element){
	    if(element.className == 'collapsible'){
	      MakeContentVisible(element, true);
	    }
	  }, 0);
	}
	
	function MakeContentVisible(element, visible){
	  var img = element.previousSibling.firstChild;
	  if(!!img.tagName && img.tagName.toLowerCase() == "img"){
	    element.style.display = visible ? 'inline' : 'none';
	    element.previousSibling.firstChild.src = visible ? ImgExpanded : ImgCollapsed;
	  }
	}
	
	function TraverseChildren(element, func, depth){
	  for(var i = 0; i < element.childNodes.length; i++){
	    TraverseChildren(element.childNodes[i], func, depth + 1);
	  }
	  func(element, depth);
	}
	
	function ExpImgClicked(img){
	  var container = img.parentNode.nextSibling;
	  if(!container) return;
	  var disp = "none";
	  var src = ImgCollapsed;
	  if(container.style.display == "none"){
	      disp = "inline";
	      src = ImgExpanded;
	  }
	  container.style.display = disp;
	  img.src = src;
	}
	
	function CollapseLevel(level){
	  EnsureIsPopulated();
	  TraverseChildren($id("Canvas"), function(element, depth){
	    if(element.className == 'collapsible'){
	      if(depth >= level){
	        MakeContentVisible(element, false);
	      }else{
	        MakeContentVisible(element, true);  
	      }
	    }
	  }, 0);
	}
	
	function TabSizeChanged(){
	  Process();
	}
	
	function SetTab(){
	  var select = $id("TabSize");
	  TAB = MultiplyString(parseInt(select.options[select.selectedIndex].value), SINGLE_TAB);
	}
	
	function EnsureIsPopulated(){
	  if(!$id("Canvas").innerHTML && !!$id("RawJson").value) Process();
	}
	
	function MultiplyString(num, str){
	  var sb =[];
	  for(var i = 0; i < num; i++){
	    sb.push(str);
	  }
	  return sb.join("");
	}
	
	function SelectAllClicked(){
	 
	  if(!!document.selection && !!document.selection.empty) {
	    document.selection.empty();
	  } else if(window.getSelection) {
	    var sel = getSelection();
	    if(sel.removeAllRanges) {
	      getSelection().removeAllRanges();
	    }
	  }
	 
	  var range = 
	      (!!document.body && !!document.body.createTextRange)
	          ? document.body.createTextRange()
	          : document.createRange();
	  
	  if(!!range.selectNode)
	    range.selectNode($id("Canvas"));
	  else if(range.moveToElementText)
	    range.moveToElementText($id("Canvas"));
	  
	  if(!!range.select)
	    range.select($id("Canvas"));
	  else
	    getSelection().addRange(range);
	}
	
	function LinkToJson(){
	  var val = $id("RawJson").value;
	  val = escape(val.split('/n').join(' ').split('/r').join(' '));
	  $id("InvisibleLinkUrl").value = val;
	  $id("InvisibleLink").submit();
	}

	function triggers() {
		// format json
		$('#WooZone-amzdbg-amazonResponse #GoFormatJson').click(function(e) {
			e.preventDefault();
			
			Process();
		});
		Process(); // default
		
		// change json display: space size between elements
		$('#WooZone-amzdbg-amazonResponse #TabSize').click(function(e) {
			e.preventDefault();
			
			TabSizeChanged();
		});
		
		// change json display: show quotes!
		$('#WooZone-amzdbg-amazonResponse #QuoteKeys').click(function(e) {
			//e.preventDefault();
			
			QuoteKeysClicked();
		});
		
		// change json display: collapse tree
		$('#WooZone-amzdbg-amazonResponse #CollapsibleView').click(function(e) {
			//e.preventDefault();
			
			CollapsibleViewClicked();
		});
		
		// change json display: select all tree elements
		$('#WooZone-amzdbg-amazonResponse #SelectAll').click(function(e) {
			e.preventDefault();
			
			SelectAllClicked();
		});
		
		// change json display: collapsible view expand all
		$('#WooZone-amzdbg-amazonResponse #CollapsibleViewDetail #CollapsibleViewExpandAll').click(function(e) {
			e.preventDefault();
			
			ExpandAllClicked();
		});
		// change json display: collapsible view collapse all
		$('#WooZone-amzdbg-amazonResponse #CollapsibleViewDetail #CollapsibleViewCollapseAll').click(function(e) {
			e.preventDefault();
			
			CollapseAllClicked();
		});
		// change json display: collapsible view level specific!
		$('#WooZone-amzdbg-amazonResponse #CollapsibleViewDetail .CollapsibleViewLevel').click(function(e) {
			e.preventDefault();
			
			var that = $(this);
			CollapseLevel( that.data('level') );
		});
		
		// json tree toogle
		$('#WooZone-amzdbg-amazonResponse').on('click', 'img.ExpImgChoose', function(e) {
			e.preventDefault();
			
			ExpImgClicked( this );
		});
		
		
	}

	function test() {
	}
	init();

	// external usage
	return {
		"test"			: test,
		"Process"		: Process
	};
})(jQuery);
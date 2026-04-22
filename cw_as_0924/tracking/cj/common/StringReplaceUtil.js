
function replaceAttackString(pValue){
	var str = "";
	if(pValue != null && "" != pValue) {
		str = pValue; 
		str = str.replace(/'/gi,"");
		str = str.replace(/`/gi,"");
		str = str.replace(/\\/gi,"");
		str = str.replace(/#/gi,"");
		str = str.replace(/;/gi,"");
		str = str.replace(/@/gi,"");
		str = str.replace(/=/gi,"");
		str = str.replace(/\//gi,"");
		str = str.replace(/\+/g, "");
		str = str.replace(/\(/gi,String.fromCodePoint(40));
		str = str.replace(/\)/gi,String.fromCodePoint(41));
		str = str.replace(/\</gi,"&lt;");
		str = str.replace(/\>/gi,"&gt;");
		str = str.replace(/--/gi,"");
	}
	return str;
}

function clearUsername(){
	if(document.getElementById("userForm").value == "username"){
		document.getElementById("userForm").value = "";
	}
}
function clearPassword(){
	if(document.getElementById("passForm").value == "password"){
		document.getElementById("passForm").value = "";
	}
}


//Help browsers submit with enter button scince the login button isn't techincally apart of the 
//forms DOM for styling purposes ;)
function pressedEnter(e){
	//e.KeyChar == (char)13
	if(e && e.keyCode == 13){
		document.forms[0].submit();
	}
	
}

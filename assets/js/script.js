window.toggleToast = ((show = true, message = '') => {
	var toast = document.getElementById('toast');
	var toastTxt = toast.children[1];

	toastTxt.innerHTML = message;

	if(show){
		toast.style.display = 'block';
	}else{
		toast.style.display = 'none';
	}
});

var toastCloseBtn = document.getElementById('toast').children[0];
toastCloseBtn.addEventListener('click', function(e){
	e.preventDefault();
	this.parentNode.style.display = 'none';
});

var tabElements = document.getElementsByClassName('tab');

for(i = 0; i < tabElements.length; i++){
	var liElements = tabElements[i].children;

	for(j = 0; j < liElements.length; j++){
		var linkElement = liElements[j].children[0];

		linkElement.addEventListener('click', function(e){
			e.preventDefault();
			var tabLink = this.getAttribute('href');

			var tabContent = document.getElementsByClassName('tab-pane');
			for(k = 0; k < tabContent.length; k++){
				if(tabContent[k].classList.contains('active')){
					tabContent[k].classList.remove('active');
				}
			}

			for(k = 0; k < liElements.length; k++){
				if(liElements[k].classList.contains('active')){
					liElements[k].classList.remove('active');
				}

				if(liElements[k].children[0].getAttribute('href') == tabLink){
					liElements[k].classList.add('active');
				}
			}

			// substr(1) - to remove hash(#)
			var tabPane = document.getElementById(tabLink.substr(1));
			tabPane.classList.add('active');
		});
	}
}

var togglePwdBtns = document.getElementsByClassName('toggle-pwd-fields');

for(i = 0; i < togglePwdBtns.length; i++){
	togglePwdBtns[i].addEventListener('click', function(e){
		var form = this.closest('form');
		var pwdFields = form.getElementsByClassName('password-field');

		for(j = 0; j < pwdFields.length; j++){
			if(this.checked){
				pwdFields[j].setAttribute('type', 'text');
			}else{
				pwdFields[j].setAttribute('type', 'password');
			}
		}
	});
}

// Check Page Visibility
var documentHidden, documentVisibilityChange;

if(typeof document.hidden !== "undefined"){
	// Opera 12.10 and Firefox 10 and later support
	documentHidden = "hidden";
	documentVisibilityChange = "visibilitychange";
}else if(typeof document.msHidden !== "undefined"){
	// IE
	documentHidden = "msHidden";
	documentVisibilityChange = "msvisibilitychange";
}else if(typeof document.webkitHidden !== "undefined"){
	// Chrome and latest browsers
	documentHidden = "webkitHidden";
	documentVisibilityChange = "webkitvisibilitychange";
}

function addPageFocusChangeListener(customFunction)
{
	if(documentHidden === undefined){
		console.error("This browser does not supports Page Visibility API");
	}else{
		document.addEventListener(documentVisibilityChange, function(){
			customFunction();
		});
	}
}

function showTab(id)
{
	if(id != null && id){
		var element = document.querySelector(id);

		if(element != null){
			if(element.classList.contains('tab-pane')){
				var tabLinkElement = document.querySelector('.tab-item > a[href="' + id + '"]');

				if(tabLinkElement != null){
					tabLinkElement.click();
				}
			}
		}
	}
}

function checkNotification()
{
	var canNotify = false;

	if(!'Notification' in window){
		console.error('This browser does not support desktop notification');
	}else if(Notification.permission === 'granted'){
		canNotify = true;
	}else if(Notification.permission === 'denied'){
		Notification.requestPermission().then(function(permission){
			window.canNotify = permission === 'granted';
		});
	}

	window.canNotify = canNotify;
}

function showNotification(content, callback)
{
	var title = 'VS Chat';
	var options = {
		icon: BASEURL + 'assets/logo.png',
		body: content
	};
	var currUrl = window.location.href;

	var notification = new Notification(title, options);
	notification.onclick = function(){
		if(callback === undefined){
			window.focus();
		}else{
			callback();
		}

		this.close();
	};
}

window.onhashchange = () => {
	var id = location.hash;
	showTab(id);
};

window.onload = () => {
	if(typeof(MSG_SHO_ON_LOAD) != 'undefined'){
		toggleToast(true, MSG_SHO_ON_LOAD);
	}

	checkNotification();

	showTab(location.hash);
};
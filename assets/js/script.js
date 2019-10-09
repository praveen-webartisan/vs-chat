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

window.onhashchange = () => {
	var id = location.hash;
	showTab(id);
};

window.onload = () => {
	if(typeof(MSG_SHO_ON_LOAD) != 'undefined'){
		toggleToast(true, MSG_SHO_ON_LOAD);
	}

	showTab(location.hash);
};
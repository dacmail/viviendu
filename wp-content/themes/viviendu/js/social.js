window.fbAsyncInit = function() {
		FB.init({
		  appId      : '1099906436728878',
		  xfbml      : true,
		  version    : 'v2.5'
		});
		};

		(function(d, s, id){
		 var js, fjs = d.getElementsByTagName(s)[0];
		 if (d.getElementById(id)) {return;}
		 js = d.createElement(s); js.id = id;
		 js.src = "//connect.facebook.net/es_ES/sdk.js";
		 fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
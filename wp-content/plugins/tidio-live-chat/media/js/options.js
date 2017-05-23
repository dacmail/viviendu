var TidioChatWP = {
    token: null,
    setRedirectLink: function(url){
        jQuery('a[href="admin.php?page=tidio-chat"]').attr('href', url).attr('target', '_blank');
        jQuery("#open-panel-link").attr('href', url);
    },
    renderProjects: function (data) {
        var select_project = jQuery('#select-tidio-project');
        for (var i in data.value) {
            var project = data.value[i];
            var value = {project_id:project.id, private_key:project.private_key, public_key:project.public_key};
            
            var option = jQuery('<option value="'+project.id+'">' + project.name + '</option>');
            option.data('value', value);
            select_project.append(option);
        }
        
        jQuery('#input-blocks').fadeOut('fast', function () {
            jQuery('#projects-selector').append(select_project);
            jQuery('#select-project').fadeIn('fast', function(){
                jQuery('#tidio-login-button').prop('disabled', false).text('Log in');
            });            
        });
    },
    getProjects: function (token) {
        jQuery.get("http://api-v2.tidio.co/project", {
            api_token: token
        }, function (response) {
            TidioChatWP.renderProjects(response);
        }, 'json');
    },
    showError: function (message) {
        jQuery('#tidio-wrapper .error').empty().append('<p>' + message + '</p>').show();
    },
    hideError: function () {
        jQuery('#tidio-wrapper .error').hide();
    },
    init: function () {
        
        
        var login_button = jQuery('#tidio-login-button');
        
        /* Login */
        login_button.click(function (e) {
            TidioChatWP.hideError();
            var error = false;
            e.preventDefault();
            var email = jQuery('#tidio-login-input').val();
            var password = jQuery('#tidio-password-input').val();

            if (email.length == 0 || password.length == 0) {
                TidioChatWP.showError('Please fill email and password fields.');
                error = true;
            } else if (email == '' || email.indexOf('@') == -1 || email.indexOf('.') == -1) {
                TidioChatWP.showError('Email is wrong');
                error = true;
            }

            if (error)
                return false;
            
            login_button.prop('disabled', true).text('Loading...');

            jQuery.get("http://api-v2.tidio.co/access/getUserToken", {
                email: email,
                password: password,
            }, function (data) {
                if (data.status == true && data.value != "ERR_DATA_INVALID") {
                    TidioChatWP.token = data.value;
                    TidioChatWP.getProjects(TidioChatWP.token);
                } else {
                    TidioChatWP.showError('Wrong email or password');
                    login_button.prop('disabled', false).text('Login');
                }
            }, 'json');
        });
        
        /* Load project details */
        jQuery('#get-tidio-project').click(function(e){
            e.preventDefault();
            jQuery('#get-tidio-project').prop('disabled', true).text('Loading...');
            var details = jQuery('#select-tidio-project option:selected').data('value');
            jQuery.extend( details,{'action': 'get_project_keys'});            
            jQuery.post(ajaxurl, details, function(response) {
                
                window.open(response, '_blank');
                TidioChatWP.setRedirectLink(response);
                jQuery('#welcome-text').fadeOut('fast', function(){
                    jQuery('#after-install-text').fadeIn('fast');
                });
                jQuery('#select-project').fadeOut('fast');
            });
            
        });        
        
        /* No account login */
        jQuery('#redirect-to-panel').click(function (e){
            e.preventDefault();
            jQuery('#redirect-to-panel').prop('disabled', true).text('Loading...');
            var details = {'action': 'get_private_key'};
            jQuery.post(ajaxurl, details, function(response) {
                if(response=='error'){
                    // load trought ajax url
                    TidioChatWP.accessTroughtXHR(function(response){
                        window.open(response, "_blank");
                        TidioChatWP.setRedirectLink(response);
                        jQuery('#welcome-text').fadeOut('fast', function(){
                            jQuery('#after-install-text').fadeIn('fast');
                        });
                        jQuery('#input-blocks').fadeOut('fast');
                    });
                    return false;    
                }
                //
                window.open(response, "_blank");
                TidioChatWP.setRedirectLink(response);
                jQuery('#welcome-text').fadeOut('fast', function(){
                    jQuery('#after-install-text').fadeIn('fast');
                });
                jQuery('#input-blocks').fadeOut('fast');
            });
        });
        
        /* Trigger on enter */
        jQuery('#tidio-login-input, #tidio-password-input').bind("keydown",function(e){
            if(e.keyCode == 13)
                login_button.trigger('click');
        });
        
        // Open panel
        
        // open-panel-link
    },
    
    accessTroughtXHR: function(_func){
        
        var xhr_url = '//www.tidio.co/external/create?url=' + location.protocol + '//' + location.host +  '&platform=wordpress';
        
        jQuery.getJSON(xhr_url, {}, function(r) {
            if(!r || !r.value){
                alert('Error occured while creating, please try again!');   
                return false;
            }
            _func('https://www.tidio.co/external/access?privateKey=' + r.value.private_key + '&app=chat');
            // save this in wordpress database
            jQuery.post(ajaxurl, {'action': 'tidio_chat_save_keys', 'public_key': r.value.public_key, 'private_key': r.value.private_key}, function(response) {

            });
                  
        }).fail(function(){
            alert('Error occured while creating, please try again!');    
        });
        
    }
};

jQuery(document).ready(function () {
    TidioChatWP.init();
});
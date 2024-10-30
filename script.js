jQuery(document).ready( function($) {
    bgc_open_pointer(0);
    function bgc_open_pointer(i) {
        pointer = bgcPointer.pointers[i]; 
        options = $.extend( pointer.options, {
            close: function() {
                $.post( ajaxurl, {
                    pointer: pointer.pointer_id,
                    action: 'dismiss-wp-pointer'
                });
            },
			buttons:function (event, t) {
                        button = jQuery('<a id="bgc-pointer-close" style="margin-left:5px" class="button-secondary">'+pointer.options.mailing_join_hide+'</a>');
                        button.bind('click.pointer', function () {
                            t.element.pointer('close');
                        });
                        return button;
                    } 
        });
 
        $(pointer.target).pointer( options ).pointer('open');
		 
                        jQuery('#bgc-pointer-close').after('<a id="bgc-pointer-primary" class="button-primary">' + pointer.options.mailing_join_btn + '</a>');
                         
           jQuery('#bgc-pointer-close').click(function () { 
                            $('#wp-admin-bar-bgc-days').pointer('close');
           });    
		   
		   jQuery('#bgc-pointer-primary').click(function () { 
                        $('#wp-admin-bar-bgc-days').pointer('close');
						var email =  jQuery("#bgc_email").val();
					   jQuery.ajax({
						type: "POST",
						url:  ajaxurl,
						data: {action: "bgc_mailing_list", email:email }
						}).done(function( html ) {
							
								   
					 }); 
		   });
    }
});
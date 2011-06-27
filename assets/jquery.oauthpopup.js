/*!
 * jQuery OAuth via popup window plugin
 *
 * @author  Nobu Funaki @zuzara
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
(function(jQuery){
    //  inspired by DISQUS
    jQuery.oauthpopup = function(options)
    {
        options.windowName = options.windowName ||  'ConnectWithOAuth'; // should not include space for IE
        options.windowOptions = options.windowOptions || 'location=0,status=0,width=800,height=400';
        options.callback = options.callback || function(){ window.location.reload(); };
        var that = this;

        that._oauthWindow = window.open(options.path, options.windowName, options.windowOptions);
        that._oauthInterval = window.setInterval(function(){
            if (that._oauthWindow.closed) {
                window.clearInterval(that._oauthInterval);
                options.callback();
            }
        }, 1000);
    };

})(jQuery);


	jQuery(document).ready(function(){
		jQuery('#twitter_connect').click(function(){
			jQuery.oauthpopup({
				"path": Symphony.Context.get('root')+"/extensions/twitternotifier/content/content.twitter_connect.php",
				"windowName": "authorize",
				"windowOptions": "scrollbars=yes",
				"callback": function(){
					window.location.reload();
				}
			});
		});
	});

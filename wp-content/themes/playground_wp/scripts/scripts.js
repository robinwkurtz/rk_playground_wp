var isMobile = {
    Android: function () {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function () {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function () {
        return navigator.userAgent.match(/iPhone|iPod|iPad/i);
    },
    Opera: function () {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function () {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function () {
        return (
            isMobile.Android() ||
                isMobile.BlackBerry() ||
                isMobile.iOS() ||
                isMobile.Opera() ||
                isMobile.Windows() );
    }
};

var handleClick = (isMobile.any() !== null) ? "touchstart" : "click";

$ = jQuery;
var functions = {
    emailReplace: function() {
        /** Use JavaScript to replace <a> with a mail link, to reduce potential spam**/
        var _varPre = "mailto:",
            _selector = ".js-replacer-text";

        if ($(_selector).length > 0) {
            $(_selector).each(function () {
                var _varUpdate = $(this).data('update'),
                    _varEnd = $(this).data('domain'),
                    _varMid = $(this).data('extra'),
                    _varText = $(this).data('text');
                $(this).attr('href', _varPre + _varMid + '@' + _varEnd);
                if (typeof _varUpdate == 'boolean' && _varUpdate != true) {


                } else {
                    if (typeof _varText !== 'undefined') {
                        $(this).html(_varText);
                    } else {
                        $(this).text(_varMid + '@' + _varEnd);
                    }
                }
            });
        }
    }
};

(function ($) {

    $(document).ready(function () {

        // Email Replace
        functions.emailReplace();

		// Burger Menu
    	$('.js-menu-button').on('click', function (event) {
    		event.preventDefault();
    		$('body').toggleClass('is-active');
    	});

		// Instagram Feed

		var feed_target = document.getElementById('instafeed');
		if (feed_target != null) {
			var feed = new Instafeed({
				// INSTRUCTIONS:
				// - userId from username -> https://smashballoon.com/instagram-feed/find-instagram-user-id/
				// - clientID from registered app -> https://www.instagram.com/developer/clients/manage/
				// - accessToken from following URL with filled in variables -> https://api.instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=token
				// DEV: robinwkurtz
				// userId: '145427418',
				// clientId: 'cef62a89e73c4f7c9e3c59fe48613508',
				// accessToken: '145427418.cef62a8.ae4cea243acf43f9998b694b7a566116',
				get: 'user',
				userId: '2130370132',
				clientId: '125232e625dc457fa64a35f45ae29c6b',
				accessToken: '2130370132.125232e.addc9fd534ef4d27be843fddd56e55af',
				template: '<li class="instagram-item {{type}}"><a href="{{link}}" target="_blank"><div class="instagram-img" style="background-image:url({{image}});">&nbsp;</div></a></li>',
				resolution: 'standard_resolution',
				limit: 4
				// filter: function(image) {
				// 	return image.tags.indexOf('oneplus') >= 0;
				// }
				// http://localhost/#access_token=145427418.cef62a8.ae4cea243acf43f9998b694b7a566116
			});
			feed.run();
		}

		// Google Maps JS
		// Set Map

		var map_target = document.getElementById('map');
		if (cms_variables.google.lat && cms_variables.google.long && map_target != null) {
			function initialize() {
				var myLatlng = new google.maps.LatLng(cms_variables.google.lat, cms_variables.google.long);
				var imagePath = cms_variables.google.pin
				var mapOptions = {
					zoom: 16,
					center: myLatlng,
					styles: [
						{"featureType":"landscape","elementType":"labels","stylers":[{"visibility":"off"}]},
						{"featureType":"transit","elementType":"labels","stylers":[{"visibility":"off"}]},
						{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},
						{"featureType":"water","elementType":"labels","stylers":[{"visibility":"off"}]},
						{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},
						{"stylers":[{"hue":"#000"},{"saturation":-100},{"gamma":2.15},{"lightness":12}]},
						{"featureType":"road","elementType":"labels.text.fill","stylers":[{"visibility":"on"},
						{"lightness":24}]},
						{"featureType":"road","elementType":"geometry","stylers":[{"lightness":57}]}
					],
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					mapTypeControlOptions: {
				    	mapTypeIds: 'roadmap'
				    }
				}

				var map = new google.maps.Map(document.getElementById('map'), mapOptions);
				//Callout Content
				var contentString = cms_variables.site.name;
				//Set window width + content
				var infowindow = new google.maps.InfoWindow({
					content: contentString,
					maxWidth: 500
				});

				// Add Marker
				var marker = new google.maps.Marker({
					position: myLatlng,
					map: map,
					icon: imagePath,
					title: cms_variables.site.name
				});

				// google.maps.event.addListener(marker, 'click', function() {
				// 	infowindow.open(map,marker);
				// });

				//Resize Function
				google.maps.event.addDomListener(window, "resize", function() {
					var center = map.getCenter();
					google.maps.event.trigger(map, "resize");
					map.setCenter(center);
				});
			}

			google.maps.event.addDomListener(window, 'load', initialize);
		}

    });

}(jQuery));

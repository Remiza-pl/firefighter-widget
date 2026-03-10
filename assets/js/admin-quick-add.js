/* global firefighterStatsQuickAddData */
/**
 * Admin Bar Quick Emergency Add
 *
 * Handles the quick-add click on the admin bar emergency menu items.
 * Data is passed via wp_localize_script as `firefighterStatsQuickAddData`.
 */
( function () {
	'use strict';

	/**
	 * Called from the onclick handler on each admin bar category item.
	 *
	 * @param {number} categoryId   Term ID of the emergency category.
	 * @param {string} categoryName Human-readable category name (for UI feedback).
	 */
	window.firefighterStatsQuickAdd = function ( categoryId, categoryName ) {
		var data = firefighterStatsQuickAddData;

		if ( ! confirm( data.i18n.confirm + ' "' + categoryName + '"?' ) ) {
			return;
		}

		var menuItem = document.querySelector(
			'#wp-admin-bar-firefighter-stats-quick-' + categoryId + ' .ab-item'
		);

		if ( ! menuItem ) {
			return;
		}

		var originalHTML     = menuItem.innerHTML;
		menuItem.innerHTML   = '⏳ ' + categoryName;
		menuItem.style.opacity = '0.6';

		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', data.ajaxUrl );
		xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

		xhr.onreadystatechange = function () {
			if ( xhr.readyState !== 4 ) {
				return;
			}

			menuItem.style.opacity = '1';

			if ( xhr.status === 200 ) {
				try {
					var response = JSON.parse( xhr.responseText );
					if ( response.success ) {
						menuItem.innerHTML = '✅ ' + categoryName;
						setTimeout( function () {
							menuItem.innerHTML = originalHTML;
						}, 2000 );
						// Only alert on the frontend; admin pages have wp.notices.
						if ( typeof wp === 'undefined' || ! wp.data ) {
							alert( data.i18n.success );
						}
					} else {
						menuItem.innerHTML = '❌ ' + categoryName;
						setTimeout( function () {
							menuItem.innerHTML = originalHTML;
						}, 2000 );
						alert( data.i18n.error );
					}
				} catch ( e ) {
					menuItem.innerHTML = originalHTML;
					alert( data.i18n.networkError );
				}
			} else {
				menuItem.innerHTML = originalHTML;
				alert( data.i18n.networkError );
			}
		};

		xhr.send(
			'action=firefighter_stats_quick_add' +
			'&category_id=' + encodeURIComponent( categoryId ) +
			'&nonce=' + encodeURIComponent( data.nonce )
		);
	};
}() );

/* global firefighterStatsCountsData */
/**
 * Emergency Counts Admin Page
 *
 * Fetches the current count for a selected category + date and populates
 * the display element on the Quick Counts admin page.
 * Data is passed via wp_localize_script as `firefighterStatsCountsData`.
 */
( function () {
	'use strict';

	function updateCurrentCount() {
		var categorySelect = document.getElementById( 'category_id' );
		var dateInput      = document.getElementById( 'date' );
		var display        = document.getElementById( 'current-count-display' );
		var countInput     = document.getElementById( 'count' );
		var data           = firefighterStatsCountsData;

		if ( ! categorySelect || ! dateInput || ! display || ! countInput ) {
			return;
		}

		if ( categorySelect.value && dateInput.value ) {
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', data.ajaxUrl );
			xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

			xhr.onreadystatechange = function () {
				if ( xhr.readyState !== 4 || xhr.status !== 200 ) {
					return;
				}
				try {
					var response = JSON.parse( xhr.responseText );
					var count    = response.success && response.data ? response.data.count : 0;
					display.innerHTML = data.i18n.totalCount + ' ' + count;
					countInput.value  = 1;
				} catch ( e ) {
					display.innerHTML = '';
				}
			};

			xhr.send(
				'action=firefighter_stats_get_count' +
				'&category_id=' + encodeURIComponent( categorySelect.value ) +
				'&date=' + encodeURIComponent( dateInput.value ) +
				'&nonce=' + encodeURIComponent( data.getCountNonce )
			);
		} else {
			display.innerHTML = '';
			countInput.value  = 0;
		}
	}

	// Expose globally so inline onchange="updateCurrentCount()" handlers work.
	window.updateCurrentCount = updateCurrentCount;
}() );

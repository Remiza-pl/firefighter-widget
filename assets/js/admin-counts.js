/* global firefighterStatsCountsData */
/**
 * Emergency Counts Admin Page — modal quick-add and year filter.
 *
 * Data is passed via wp_localize_script as `firefighterStatsCountsData`.
 */
( function () {
	'use strict';

	var _categoryId  = 0;
	var _selectedYear = 0;

	/**
	 * Open the add-count modal for a specific category.
	 *
	 * @param {number} categoryId   Term ID.
	 * @param {string} categoryName Display name.
	 * @param {number} selectedYear Currently selected year filter.
	 */
	function openModal( categoryId, categoryName, selectedYear ) {
		_categoryId   = categoryId;
		_selectedYear = selectedYear;

		var modal     = document.getElementById( 'fs-count-modal' );
		var catName   = document.getElementById( 'fs-modal-cat-name' );
		var countNote = document.getElementById( 'fs-modal-current-count' );
		var dateInput = document.getElementById( 'fs-modal-date' );
		var timeInput = document.getElementById( 'fs-modal-time' );
		var countIn   = document.getElementById( 'fs-modal-count' );

		if ( ! modal ) { return; }

		catName.textContent   = categoryName;
		countNote.textContent = '';
		countIn.value         = 1;
		timeInput.value       = '';

		// Set today's date as default.
		var today = new Date();
		var yyyy  = today.getFullYear();
		var mm    = String( today.getMonth() + 1 ).padStart( 2, '0' );
		var dd    = String( today.getDate() ).padStart( 2, '0' );
		dateInput.value = yyyy + '-' + mm + '-' + dd;

		modal.hidden = false;
		countIn.focus();
	}

	/**
	 * Close the modal.
	 */
	function closeModal() {
		var modal = document.getElementById( 'fs-count-modal' );
		if ( modal ) {
			modal.hidden = true;
		}
	}

	/**
	 * Submit the modal form via AJAX.
	 */
	function submitModal() {
		var data        = firefighterStatsCountsData;
		var submitBtn   = document.getElementById( 'fs-modal-submit' );
		var countInput  = document.getElementById( 'fs-modal-count' );
		var dateInput   = document.getElementById( 'fs-modal-date' );
		var timeInput   = document.getElementById( 'fs-modal-time' );
		var countNote   = document.getElementById( 'fs-modal-current-count' );

		var count = parseInt( countInput.value, 10 );
		var date  = dateInput.value;
		var time  = timeInput ? timeInput.value : '';

		if ( ! count || count < 1 ) {
			countInput.focus();
			return;
		}

		submitBtn.disabled    = true;
		submitBtn.textContent = data.i18n.adding;

		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', data.ajaxUrl );
		xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

		xhr.onreadystatechange = function () {
			if ( xhr.readyState !== 4 ) { return; }

			submitBtn.disabled = false;
			submitBtn.textContent = data.i18n.adding.replace( '...', '' ) || 'Add';

			if ( xhr.status !== 200 ) {
				countNote.textContent = data.i18n.errorMsg;
				return;
			}

			try {
				var response = JSON.parse( xhr.responseText );
				if ( response.success ) {
					// Update the card count.
					var cardCount = document.getElementById( 'fs-card-count-' + _categoryId );
					if ( cardCount && response.data && response.data.year_count !== undefined ) {
						cardCount.textContent = response.data.year_count;
					}

					// Update last entry on card.
					var cardLast = document.getElementById( 'fs-card-last-' + _categoryId );
					if ( cardLast && response.data && response.data.last_entry ) {
						var le    = response.data.last_entry;
						var label = le.date;
						if ( le.time ) { label += ' ' + le.time; }
						cardLast.textContent = label;
					}

					closeModal();
				} else {
					countNote.textContent = data.i18n.errorMsg;
				}
			} catch ( e ) {
				countNote.textContent = data.i18n.errorMsg;
			}
		};

		xhr.send(
			'action=firefighter_stats_add_count_ajax' +
			'&category_id=' + encodeURIComponent( _categoryId ) +
			'&count='        + encodeURIComponent( count ) +
			'&date='         + encodeURIComponent( date ) +
			'&time='         + encodeURIComponent( time ) +
			'&nonce='        + encodeURIComponent( data.addCountNonce )
		);
	}

	// Close on Escape key.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) { closeModal(); }
	} );

	/**
	 * Quick-add count from the widget settings panel.
	 * Reads nonce + ajax URL from hidden inputs embedded next to the button.
	 *
	 * @param {HTMLElement} btn The clicked button inside .fs-wqa-form.
	 */
	function widgetQuickAdd( btn ) {
		var container  = btn.closest( '.fs-widget-actions' );
		if ( ! container ) { return; }

		var catSelect  = container.querySelector( '.fs-wqa-cat' );
		var countInput = container.querySelector( '.fs-wqa-count' );
		var nonceInput = container.querySelector( '.fs-wqa-nonce' );
		var ajaxInput  = container.querySelector( '.fs-wqa-ajax' );
		var msgEl      = container.querySelector( '.fs-wqa-msg' );

		var categoryId = catSelect  ? catSelect.value               : '';
		var count      = countInput ? parseInt( countInput.value, 10 ) : 1;
		var nonce      = nonceInput ? nonceInput.value              : '';
		var ajaxUrl    = ajaxInput  ? ajaxInput.value               : '';

		if ( ! categoryId ) {
			if ( msgEl ) { msgEl.textContent = '⚠'; msgEl.className = 'fs-wqa-msg fs-wqa-msg--error'; }
			return;
		}
		if ( ! count || count < 1 ) { count = 1; }

		btn.disabled = true;

		// Today's date in Y-m-d format.
		var today = new Date();
		var date  = today.getFullYear() + '-' +
		            String( today.getMonth() + 1 ).padStart( 2, '0' ) + '-' +
		            String( today.getDate() ).padStart( 2, '0' );

		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', ajaxUrl );
		xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

		xhr.onreadystatechange = function () {
			if ( xhr.readyState !== 4 ) { return; }
			btn.disabled = false;
			try {
				var resp = JSON.parse( xhr.responseText );
				if ( resp.success ) {
					if ( msgEl ) { msgEl.textContent = '✓'; msgEl.className = 'fs-wqa-msg fs-wqa-msg--success'; }
					if ( countInput ) { countInput.value = 1; }
					setTimeout( function () {
						if ( msgEl ) { msgEl.textContent = ''; msgEl.className = 'fs-wqa-msg'; }
					}, 2500 );
				} else {
					if ( msgEl ) { msgEl.textContent = '✗ Error'; msgEl.className = 'fs-wqa-msg fs-wqa-msg--error'; }
				}
			} catch ( e ) {
				if ( msgEl ) { msgEl.textContent = '✗ Error'; msgEl.className = 'fs-wqa-msg fs-wqa-msg--error'; }
			}
		};

		xhr.send(
			'action=firefighter_stats_add_count_ajax' +
			'&category_id=' + encodeURIComponent( categoryId ) +
			'&count='       + encodeURIComponent( count ) +
			'&date='        + encodeURIComponent( date ) +
			'&nonce='       + encodeURIComponent( nonce )
		);
	}

	// Expose globally so inline onclick handlers work.
	window.fsOpenModal       = openModal;
	window.fsCloseModal      = closeModal;
	window.fsSubmitModal     = submitModal;
	window.fsWidgetQuickAdd  = widgetQuickAdd;

	// Legacy alias (admin bar quick-add date display).
	window.updateCurrentCount = function () {};
}() );

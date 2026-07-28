/**
 * Directorist Smart Badges – Admin JavaScript (vanilla, no jQuery).
 *
 * Responsibilities:
 *  - Badge list: native drag-reorder, toggle active, duplicate, delete, export/import.
 *  - Badge form: AJAX save via fetch()+FormData, validation with tab switching,
 *    native color inputs, wp.media image picker.
 *  - Condition repeater: add/remove/reorder, minimize/maximize, type- and
 *    compare-based field visibility.
 */
( function () {
	'use strict';

	var cfg = window.dsbAdmin || {};
	var S   = cfg.strings || {};

	function qs( sel, ctx )  { return ( ctx || document ).querySelector( sel ); }
	function qsa( sel, ctx ) { return Array.prototype.slice.call( ( ctx || document ).querySelectorAll( sel ) ); }

	var DSBAdmin = {

		conditionIndex: 0,

		// Bootstrap

		init: function () {
			this.conditionIndex = qsa( '#dsb-conditions-list .dsb-condition-item' ).length;
			this.bindEvents();
			this.initSortables();
			this.initColorFields();
			this.initExistingCompareStates();
			this.handleBadgeTypeChange();
		},

		// AJAX helper: POST FormData to admin-ajax.php, resolve JSON.

		post: function ( action, data ) {
			var body = data instanceof FormData ? data : new FormData();

			if ( data && ! ( data instanceof FormData ) ) {
				this.appendNested( body, '', data );
			}

			body.append( 'action', action );
			body.append( 'nonce', cfg.nonce );

			return fetch( cfg.ajaxUrl, {
				method      : 'POST',
				credentials : 'same-origin',
				body        : body
			} ).then( function ( res ) { return res.json(); } );
		},

		// Flatten nested objects/arrays into PHP-style bracket keys.
		appendNested: function ( fd, prefix, value ) {
			var self = this;

			if ( Array.isArray( value ) ) {
				value.forEach( function ( v, i ) {
					self.appendNested( fd, prefix + '[' + i + ']', v );
				} );
			} else if ( value && 'object' === typeof value ) {
				Object.keys( value ).forEach( function ( key ) {
					self.appendNested( fd, prefix ? prefix + '[' + key + ']' : key, value[ key ] );
				} );
			} else if ( prefix ) {
				fd.append( prefix, null == value ? '' : value );
			}
		},

		// Event binding (delegated)

		bindEvents: function () {
			var self = this;

			document.addEventListener( 'click', function ( e ) {
				var t = e.target.closest( 'button, a, label' );
				if ( ! t ) {
					return;
				}

				if ( t.classList.contains( 'dsb-delete-badge' ) )    { self.deleteBadge( t.dataset.badgeId ); }
				if ( t.classList.contains( 'dsb-duplicate-badge' ) ) { self.duplicateBadge( t.dataset.badgeId ); }
				if ( t.classList.contains( 'dsb-export-badges' ) )   { self.exportBadges(); }
				if ( t.classList.contains( 'dsb-add-condition' ) )   { self.addCondition(); }
				if ( t.classList.contains( 'dsb-color-clear' ) )     { self.clearColorField( t ); }

				if ( t.classList.contains( 'dsb-remove-condition' ) ) {
					t.closest( '.dsb-condition-item' ).remove();
					self.renumberConditions();
				}

				if ( t.classList.contains( 'dsb-toggle-condition' ) ) {
					self.toggleCondition( t.closest( '.dsb-condition-item' ) );
				}

				if ( t.classList.contains( 'dsb-upload-badge-image' ) ) {
					e.preventDefault();
					self.openBadgeImageFrame();
				}

				if ( t.classList.contains( 'dsb-remove-badge-image' ) ) {
					e.preventDefault();
					self.clearBadgeImage();
				}
			} );

			document.addEventListener( 'change', function ( e ) {
				var t = e.target;

				if ( t.classList.contains( 'dsb-toggle-active' ) )  { self.toggleBadge( t.dataset.badgeId ); }
				if ( t.classList.contains( 'dsb-condition-type' ) ) { self.handleConditionTypeChange( t ); }
				if ( t.classList.contains( 'dsb-compare-select' ) ) { self.handleCompareChange( t ); }
				if ( t.classList.contains( 'dsb-color-swatch' ) )   { self.syncColorFromSwatch( t ); }
				if ( 'dsb-badge-type' === t.id || 'dsb-display-type' === t.id ) { self.handleBadgeTypeChange(); }
				if ( 'dsb-import-file' === t.id ) { self.importBadges( t ); }
			} );

			document.addEventListener( 'input', function ( e ) {
				if ( e.target.classList.contains( 'dsb-color-input' ) ) {
					self.syncSwatchFromText( e.target );
				}
			} );

			var form = qs( '#dsb-badge-form' );
			if ( form ) {
				form.addEventListener( 'submit', function ( e ) {
					e.preventDefault();
					self.saveBadge( form );
				} );

				var idField = qs( '#dsb-badge-id-field' );
				if ( idField ) {
					idField.addEventListener( 'blur', function () {
						self.validateBadgeId( idField.value );
					} );
				}
			}
		},

		// Native drag & drop sorting

		initSortables: function () {
			var self = this;

			this.makeSortable( qs( '.dsb-badges-list' ), '.dsb-badge-row', '.dsb-drag-handle', function () {
				self.reorderBadges();
			} );

			this.makeSortable( qs( '#dsb-conditions-list' ), '.dsb-condition-item', '.dsb-condition-drag', function () {
				self.renumberConditions();
			} );
		},

		makeSortable: function ( container, itemSel, handleSel, onDrop ) {
			if ( ! container ) {
				return;
			}

			var dragging = null;

			// Only the handle arms the row for dragging.
			container.addEventListener( 'mousedown', function ( e ) {
				var handle = e.target.closest( handleSel );
				var item   = handle && handle.closest( itemSel );
				if ( item ) {
					item.setAttribute( 'draggable', 'true' );
				}
			} );

			container.addEventListener( 'dragstart', function ( e ) {
				dragging = e.target.closest( itemSel );
				if ( dragging ) {
					dragging.classList.add( 'is-dragging' );
					e.dataTransfer.effectAllowed = 'move';
					try { e.dataTransfer.setData( 'text/plain', '' ); } catch ( err ) {}
				}
			} );

			container.addEventListener( 'dragover', function ( e ) {
				if ( ! dragging ) {
					return;
				}
				e.preventDefault();

				var target = e.target.closest( itemSel );
				if ( ! target || target === dragging ) {
					return;
				}

				var rect   = target.getBoundingClientRect();
				var before = e.clientY < rect.top + rect.height / 2;
				target.parentNode.insertBefore( dragging, before ? target : target.nextSibling );
			} );

			container.addEventListener( 'dragend', function () {
				if ( ! dragging ) {
					return;
				}
				dragging.classList.remove( 'is-dragging' );
				dragging.removeAttribute( 'draggable' );
				dragging = null;
				onDrop();
			} );
		},

		// Native color fields: keep swatch and hex text in sync

		initColorFields: function () {
			var self = this;
			qsa( '.dsb-color-input' ).forEach( function ( input ) {
				self.syncSwatchFromText( input );
			} );
		},

		syncSwatchFromText: function ( input ) {
			var swatch = qs( '.dsb-color-swatch', input.closest( '.dsb-color-field' ) );
			if ( swatch && /^#[0-9a-fA-F]{6}$/.test( input.value ) ) {
				swatch.value = input.value;
			}
		},

		syncColorFromSwatch: function ( swatch ) {
			var input = qs( '.dsb-color-input', swatch.closest( '.dsb-color-field' ) );
			if ( input ) {
				input.value = swatch.value;
			}
		},

		clearColorField: function ( btn ) {
			var field  = btn.closest( '.dsb-color-field' );
			var input  = qs( '.dsb-color-input', field );
			var swatch = qs( '.dsb-color-swatch', field );
			if ( input )  { input.value = ''; }
			if ( swatch ) { swatch.value = '#ffffff'; }
		},

		// Badge type / display type: show or hide dependent fields

		handleBadgeTypeChange: function () {
			var typeEl    = qs( '#dsb-badge-type' );
			if ( ! typeEl ) {
				return;
			}

			var isTags    = 'tags' === typeEl.value;
			var displayEl = qs( '#dsb-display-type' );
			var isImage   = displayEl && 'image' === displayEl.value;

			this.toggleRows( '.dsb-maximum-tags-row', isTags );
			this.toggleRows( '.dsb-display-type-row', ! isTags );
			this.toggleRows( '.dsb-badge-image-row', ! isTags && isImage );
			this.toggleRows( '.dsb-label-display-row', ! isTags && ! isImage );
			this.toggleRows( '.dsb-badge-label-font-size-row', isTags || ! isImage );
			this.toggleRows( '.dsb-badge-icon-row, .dsb-badge-color-row, .dsb-badge-text-color-row', isTags || ! isImage );

			var label = qs( '#dsb-badge-label' );
			if ( label ) {
				label.required = ! isTags && ! isImage;
			}
		},

		toggleRows: function ( sel, show ) {
			qsa( sel ).forEach( function ( row ) {
				row.style.display = show ? '' : 'none';
			} );
		},

		// Badge image (wp.media)

		openBadgeImageFrame: function () {
			var self = this;

			if ( 'undefined' === typeof window.wp || ! window.wp.media ) {
				this.toast( S.error, 'error' );
				return;
			}

			var frame = window.wp.media( {
				title    : S.selectImage || 'Select Badge Image',
				button   : { text: S.useImage || 'Use this image' },
				multiple : false
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				if ( ! attachment || ! attachment.url ) {
					return;
				}
				qs( '#dsb-badge-image-id' ).value  = attachment.id || '';
				qs( '#dsb-badge-image-url' ).value = attachment.url;
				self.updateBadgeImagePreview( attachment.url );
			} );

			frame.open();
		},

		updateBadgeImagePreview: function ( url ) {
			var preview = qs( '.dsb-badge-image-preview' );
			var remove  = qs( '.dsb-remove-badge-image' );

			preview.textContent = '';

			if ( url ) {
				var img = document.createElement( 'img' );
				img.src = url;
				img.alt = '';
				preview.appendChild( img );
				remove.style.display = '';
			} else {
				remove.style.display = 'none';
			}
		},

		clearBadgeImage: function () {
			qs( '#dsb-badge-image-id' ).value  = '';
			qs( '#dsb-badge-image-url' ).value = '';
			this.updateBadgeImagePreview( '' );
		},

		// Condition repeater

		addCondition: function () {
			var template = qs( '#dsb-condition-template' ).innerHTML;
			var index    = this.conditionIndex++;
			var list     = qs( '#dsb-conditions-list' );

			list.insertAdjacentHTML( 'beforeend', template.replace( /\{\{index\}\}/g, index ) );

			var item = list.lastElementChild;
			this.handleConditionTypeChange( qs( '.dsb-condition-type', item ) );
			this.handleCompareChange( qs( '.dsb-compare-select', item ) );
			this.renumberConditions();
		},

		handleConditionTypeChange: function ( select ) {
			var item = select.closest( '.dsb-condition-item' );
			var meta = 'meta' === select.value;

			qs( '.dsb-meta-fields', item ).style.display         = meta ? '' : 'none';
			qs( '.dsb-pricing-plan-fields', item ).style.display = meta ? 'none' : '';
		},

		handleCompareChange: function ( select ) {
			var item = select.closest( '.dsb-condition-item' );
			var hide = 'EXISTS' === select.value || 'NOT EXISTS' === select.value;

			qs( '.dsb-meta-value-row', item ).style.display = hide ? 'none' : '';
			this.updateConditionSummary( item );
		},

		toggleCondition: function ( item ) {
			var collapsed = item.classList.toggle( 'dsb-collapsed' );
			var btn       = qs( '.dsb-toggle-condition', item );

			if ( collapsed ) {
				this.updateConditionSummary( item );
			}

			btn.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			btn.setAttribute( 'title', collapsed ? ( S.maximize || 'Maximize' ) : ( S.minimize || 'Minimize' ) );
		},

		updateConditionSummary: function ( item ) {
			var type    = qs( '.dsb-condition-type', item ).value;
			var summary = '';

			if ( 'meta' === type ) {
				var key = ( qs( '[name*="[meta_key]"]', item ) || {} ).value || '';
				var op  = ( qs( '.dsb-compare-select', item ) || {} ).value  || '=';
				var val = ( qs( 'input[name*="[meta_value]"]', item ) || {} ).value || '';
				summary = key + ' ' + op + ( 'EXISTS' === op || 'NOT EXISTS' === op ? '' : ' ' + val );
			} else if ( 'pricing_plan' === type ) {
				var status = ( qs( 'select[name*="[plan_status_condition]"]', item ) || {} ).value || '';
				summary    = status.replace( /_/g, ' ' );
			}

			qs( '.dsb-condition-summary', item ).textContent = summary;
		},

		renumberConditions: function () {
			var label = S.condition || 'Condition';

			qsa( '#dsb-conditions-list .dsb-condition-item' ).forEach( function ( item, i ) {
				item.setAttribute( 'data-condition-index', i );
				qs( '.dsb-condition-title', item ).textContent = label + ' #' + ( i + 1 );
			} );
		},

		initExistingCompareStates: function () {
			var self = this;
			qsa( '#dsb-conditions-list .dsb-compare-select' ).forEach( function ( select ) {
				self.handleCompareChange( select );
			} );
		},

		// Save badge (fetch + FormData)

		saveBadge: function ( form ) {
			var self = this;
			var btn  = qs( '.dsb-save-badge' );

			if ( ! this.validateForm() ) {
				return;
			}

			var fd = new FormData( form );

			// Unchecked checkboxes are absent from FormData; the server treats
			// a missing is_active as true, so always send an explicit value.
			var active = qs( '#dsb-badge-active' );
			fd.set( 'badge[is_active]', active && active.checked ? '1' : '0' );

			this.busy( btn, true );

			this.post( 'dsb_save_badge', fd )
				.then( function ( response ) {
					self.busy( btn, false );

					if ( ! response.success ) {
						self.toast( response.data.message, 'error' );
						return;
					}

					self.toast( response.data.message, 'success' );

					// Transition "add" → "edit" URL using the server-returned ID.
					var savedId = ( response.data.badge && response.data.badge.id ) || qs( '#dsb-badge-id' ).value;
					window.setTimeout( function () {
						window.location.href = self.getFormUrl( savedId );
					}, 800 );
				} )
				.catch( function () {
					self.busy( btn, false );
					self.toast( S.error, 'error' );
				} );
		},

		// Validation

		validateForm: function () {
			var valid   = true;
			var title   = qs( '#dsb-badge-title' );
			var badgeId = qs( '#dsb-badge-id-field' );
			var label   = qs( '#dsb-badge-label' );
			var type    = ( qs( '#dsb-badge-type' ) || {} ).value || 'custom';
			var display = ( qs( '#dsb-display-type' ) || {} ).value || 'label';
			var firstBad = null;

			qsa( '.dsb-field-error' ).forEach( function ( el ) { el.textContent = ''; } );

			if ( ! title.value.trim() ) {
				valid = false;
				title.classList.add( 'dsb-error' );
				firstBad = firstBad || title;
			} else {
				title.classList.remove( 'dsb-error' );
			}

			var idVal   = badgeId.value.trim();
			var idError = qs( '.dsb-field-error', badgeId.parentNode );

			if ( ! idVal || ! /^[a-z0-9-]+$/.test( idVal ) ) {
				valid = false;
				badgeId.classList.add( 'dsb-error' );
				idError.textContent = idVal ? S.invalidBadgeId : S.requiredField;
				firstBad = firstBad || badgeId;
			} else {
				badgeId.classList.remove( 'dsb-error' );
			}

			if ( 'custom' === type && 'label' === display && label && ! label.value.trim() ) {
				valid = false;
				label.classList.add( 'dsb-error' );
				firstBad = firstBad || label;
			} else if ( label ) {
				label.classList.remove( 'dsb-error' );
			}

			if ( ! valid ) {
				this.toast( S.requiredField, 'error' );
				this.revealField( firstBad );
			}

			return valid;
		},

		// Switch to the CSS tab containing a field, then focus it.
		revealField: function ( field ) {
			if ( ! field ) {
				return;
			}

			var panel = field.closest( '.dsb-tab-panel' );
			if ( panel ) {
				var key   = ( panel.className.match( /dsb-tab-panel--(\w+)/ ) || [] )[ 1 ];
				var radio = key && qs( '#dsb-tab-' + key );
				if ( radio ) {
					radio.checked = true;
				}
			}

			field.focus();
		},

		validateBadgeId: function ( value ) {
			var field = qs( '#dsb-badge-id-field' );
			var error = qs( '.dsb-field-error', field.parentNode );

			if ( ! value || ! /^[a-z0-9-]+$/.test( value ) ) {
				error.textContent = S.invalidBadgeId;
				field.classList.add( 'dsb-error' );
			} else {
				error.textContent = '';
				field.classList.remove( 'dsb-error' );
			}
		},

		// List actions

		deleteBadge: function ( badgeId ) {
			var self = this;

			if ( ! window.confirm( S.confirmDelete ) ) {
				return;
			}

			this.post( 'dsb_delete_badge', { id: badgeId } ).then( function ( response ) {
				self.toast( response.data.message, response.success ? 'success' : 'error' );
				if ( response.success ) {
					window.setTimeout( function () { window.location.reload(); }, 800 );
				}
			} ).catch( function () {
				self.toast( S.error, 'error' );
			} );
		},

		duplicateBadge: function ( badgeId ) {
			var self = this;

			this.post( 'dsb_duplicate_badge', { id: badgeId } ).then( function ( response ) {
				self.toast( response.data.message, response.success ? 'success' : 'error' );
				if ( response.success ) {
					window.setTimeout( function () { window.location.reload(); }, 800 );
				}
			} ).catch( function () {
				self.toast( S.error, 'error' );
			} );
		},

		toggleBadge: function ( badgeId ) {
			var self = this;

			this.post( 'dsb_toggle_badge', { id: badgeId } ).then( function ( response ) {
				if ( ! response.success ) {
					self.toast( response.data.message, 'error' );
					window.setTimeout( function () { window.location.reload(); }, 500 );
				}
			} ).catch( function () {
				self.toast( S.error, 'error' );
				window.setTimeout( function () { window.location.reload(); }, 500 );
			} );
		},

		reorderBadges: function () {
			var self  = this;
			var order = qsa( '.dsb-badge-row' ).map( function ( row ) {
				return row.dataset.badgeId;
			} );

			this.post( 'dsb_reorder_badges', { order: order } ).then( function ( response ) {
				if ( ! response.success ) {
					self.toast( response.data.message, 'error' );
				}
			} ).catch( function () {
				self.toast( S.error, 'error' );
			} );
		},

		// Export / import

		exportBadges: function () {
			var self = this;

			this.post( 'dsb_export_badges' ).then( function ( response ) {
				if ( ! response.success ) {
					self.toast( response.data.message, 'error' );
					return;
				}

				var blob = new Blob(
					[ JSON.stringify( response.data.badges, null, 2 ) ],
					{ type: 'application/json' }
				);
				var url  = URL.createObjectURL( blob );
				var link = document.createElement( 'a' );
				link.href     = url;
				link.download = 'directorist-smart-badges-' + Date.now() + '.json';
				link.click();
				URL.revokeObjectURL( url );
			} ).catch( function () {
				self.toast( S.error, 'error' );
			} );
		},

		importBadges: function ( input ) {
			var self = this;
			var file = input.files[ 0 ];

			if ( ! file ) {
				return;
			}

			var reader    = new FileReader();
			reader.onload = function ( e ) {
				input.value = '';

				var badges;
				try {
					badges = JSON.parse( e.target.result );
				} catch ( err ) {
					self.toast( S.parseError || S.error, 'error' );
					return;
				}

				if ( ! Array.isArray( badges ) ) {
					self.toast( S.invalidFile || S.error, 'error' );
					return;
				}

				var confirmMsg = ( S.importConfirm || 'Import %d badge(s)?' ).replace( '%d', badges.length );
				if ( ! window.confirm( confirmMsg ) ) {
					return;
				}

				self.post( 'dsb_import_badges', { badges: badges } ).then( function ( response ) {
					self.toast( response.data.message, response.success ? 'success' : 'error' );
					if ( response.success ) {
						window.setTimeout( function () { window.location.reload(); }, 800 );
					}
				} ).catch( function () {
					self.toast( S.error, 'error' );
				} );
			};
			reader.readAsText( file );
		},

		// UI helpers: toast + busy button

		toast: function ( message, type ) {
			var region = qs( '.dsb-toast-region' );

			if ( ! region ) {
				region = document.createElement( 'div' );
				region.className = 'dsb-toast-region';
				region.setAttribute( 'aria-live', 'polite' );
				document.body.appendChild( region );
			}

			var toast = document.createElement( 'div' );
			toast.className   = 'dsb-toast dsb-toast--' + ( type || 'success' );
			toast.textContent = message || '';
			region.appendChild( toast );

			window.requestAnimationFrame( function () {
				toast.classList.add( 'is-visible' );
			} );

			window.setTimeout( function () {
				toast.classList.remove( 'is-visible' );
				window.setTimeout( function () { toast.remove(); }, 200 );
			}, 4000 );
		},

		busy: function ( btn, on ) {
			if ( ! btn ) {
				return;
			}

			if ( on ) {
				btn.dataset.label = btn.textContent;
				btn.disabled      = true;
				btn.classList.add( 'is-busy' );
				btn.textContent   = S.saving || 'Saving…';
			} else {
				btn.disabled = false;
				btn.classList.remove( 'is-busy' );
				btn.textContent = btn.dataset.label || btn.textContent;
			}
		},

		// URL helpers

		getFormUrl: function ( badgeId ) {
			var url = cfg.ajaxUrl.replace( 'admin-ajax.php', 'admin.php' ) + '?page=directorist-smart-badges-form';
			return badgeId ? url + '&badge_id=' + encodeURIComponent( badgeId ) : url;
		}
	};

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { DSBAdmin.init(); } );
	} else {
		DSBAdmin.init();
	}

	window.DSBAdmin = DSBAdmin;

} )();

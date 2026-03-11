/**
 * Directorist Custom Badges – Admin JavaScript.
 *
 * Responsibilities:
 *  - Badge list: sortable rows, toggle active, duplicate, delete, export/import.
 *  - Badge form: save (AJAX), validate, color picker.
 *  - Condition repeater: add / remove, drag-to-reorder, minimize/maximize,
 *    type-based field visibility, compare-based Meta Value visibility.
 */

( function ( $ ) {
	'use strict';

	var DCBAdmin = {

		conditionIndex : 0,
		currentBadge   : null,

		// -----------------------------------------------------------------
		// Bootstrap
		// -----------------------------------------------------------------

		init: function () {
			this.bindEvents();
			this.initBadgeSortable();
			this.initConditionSortable();
			this.initColorPicker();
			this.initExistingCompareStates();
			this.initMetaKeySelect2();
		},

		// -----------------------------------------------------------------
		// Event binding
		// -----------------------------------------------------------------

		bindEvents: function () {
			var self = this;

			// Redirect to form page (add new).
			$( document ).on( 'click', '.dcb-add-badge-btn', function () {
				window.location.href = self.getFormUrl();
			} );

			// Save badge (form submit).
			$( document ).on( 'submit', '#dcb-badge-form', function ( e ) {
				e.preventDefault();
				self.saveBadge();
			} );

			// Edit badge → redirect to form.
			$( document ).on( 'click', '.dcb-edit-badge', function () {
				self.editBadge( $( this ).data( 'badge-id' ) );
			} );

			// Delete badge.
			$( document ).on( 'click', '.dcb-delete-badge', function () {
				self.deleteBadge( $( this ).data( 'badge-id' ) );
			} );

			// Duplicate badge.
			$( document ).on( 'click', '.dcb-duplicate-badge', function () {
				self.duplicateBadge( $( this ).data( 'badge-id' ) );
			} );

			// Toggle active status.
			$( document ).on( 'change', '.dcb-toggle-active', function () {
				self.toggleBadge( $( this ).data( 'badge-id' ) );
			} );

			// Add a new condition row.
			$( document ).on( 'click', '.dcb-add-condition', function () {
				self.addCondition();
			} );

			// Remove a condition row.
			$( document ).on( 'click', '.dcb-remove-condition', function () {
				$( this ).closest( '.dcb-condition-item' ).remove();
				self.renumberConditions();
			} );

			// Condition type switcher (Meta ↔ Pricing Plan).
			$( document ).on( 'change', '.dcb-condition-type', function () {
				self.handleConditionTypeChange( $( this ) );
			} );

			// Compare operator change → show/hide Meta Value row.
			$( document ).on( 'change', '.dcb-compare-select', function () {
				self.handleCompareChange( $( this ) );
			} );

			// Minimize / maximize condition body.
			$( document ).on( 'click', '.dcb-toggle-condition', function () {
				self.toggleCondition( $( this ).closest( '.dcb-condition-item' ) );
			} );

			// Badge ID live validation.
			$( document ).on( 'blur', '#dcb-badge-id-field', function () {
				self.validateBadgeId( $( this ).val() );
			} );

			// Export / import.
			$( document ).on( 'click', '.dcb-export-badges', function () {
				self.exportBadges();
			} );

			$( document ).on( 'change', '#dcb-import-file', function () {
				self.importBadges( this );
			} );
		},

		// -----------------------------------------------------------------
		// Sortable: badge list rows
		// -----------------------------------------------------------------

		initBadgeSortable: function () {
			var self = this;

			$( '.dcb-badges-list' ).sortable( {
				handle  : '.dcb-drag-handle',
				axis    : 'y',
				opacity : 0.6,
				cursor  : 'move',
				update  : function () {
					self.reorderBadges();
				}
			} );
		},

		// -----------------------------------------------------------------
		// Sortable: condition items within the repeater
		// -----------------------------------------------------------------

		initConditionSortable: function () {
			$( '.dcb-conditions-list' ).sortable( {
				handle      : '.dcb-condition-drag',
				axis        : 'y',
				opacity     : 0.7,
				cursor      : 'grabbing',
				placeholder : 'dcb-condition-placeholder',
				tolerance   : 'pointer',
				start: function ( event, ui ) {
					// Preserve height so placeholder matches item.
					ui.placeholder.height( ui.item.outerHeight() );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Color picker
		// -----------------------------------------------------------------

		initColorPicker: function () {
			if ( typeof $.fn.wpColorPicker !== 'undefined' ) {
				$( '.dcb-color-picker' ).wpColorPicker( {
					change: function () {},
					clear : function () {}
				} );
			}
		},

		// -----------------------------------------------------------------
		// URL helpers
		// -----------------------------------------------------------------

		getFormUrl: function ( badgeId ) {
			var url = dcbAdmin.ajaxUrl.replace( 'admin-ajax.php', 'admin.php' );
			url += '?page=directorist-custom-badges-form';
			if ( badgeId ) {
				url += '&badge_id=' + encodeURIComponent( badgeId );
			}
			return url;
		},

		getListUrl: function () {
			var url = dcbAdmin.ajaxUrl.replace( 'admin-ajax.php', 'admin.php' );
			return url + '?page=directorist-custom-badges';
		},

		// -----------------------------------------------------------------
		// Condition: add
		// -----------------------------------------------------------------

		addCondition: function () {
			var template = $( '#dcb-condition-template' ).html();
			var index    = this.conditionIndex++;
			var html     = template.replace( /\{\{index\}\}/g, index );

			$( '#dcb-conditions-list' ).append( html );

			// Wire up the new item.
			var $item = $( '#dcb-conditions-list .dcb-condition-item' ).last();
			this.handleConditionTypeChange( $item.find( '.dcb-condition-type' ) );
			this.handleCompareChange( $item.find( '.dcb-compare-select' ) );
			this.initMetaKeySelect2( $item );
			this.renumberConditions();
		},

		// -----------------------------------------------------------------
		// Meta Key: Select2 combobox (dropdown + type custom key)
		// -----------------------------------------------------------------

		initMetaKeySelect2: function ( $context ) {
			if ( typeof $.fn.select2 === 'undefined' ) {
				return;
			}

			var placeholder = ( dcbAdmin.strings && dcbAdmin.strings.metaKeyPlaceholder )
				? dcbAdmin.strings.metaKeyPlaceholder
				: 'Select or type a meta key…';

			var $targets = $context && $context.length
				? $context.find( '.dcb-meta-key-select' )
				: $( '.dcb-meta-key-select' );

			$targets.each( function () {
				var $el = $( this );
				if ( $el.data( 'select2' ) ) {
					return;
				}

				$el.select2( {
					width: '100%',
					placeholder: placeholder,
					allowClear: true,
					tags: true,
					createTag: function ( params ) {
						var term = $.trim( params.term );
						if ( term === '' ) {
							return null;
						}
						return {
							id: term,
							text: term,
							newTag: true
						};
					}
				} );
			} );
		},

		// -----------------------------------------------------------------
		// Condition: type change (Meta ↔ Pricing Plan)
		// -----------------------------------------------------------------

		handleConditionTypeChange: function ( $select ) {
			var type    = $select.val();
			var $item   = $select.closest( '.dcb-condition-item' );
			var $meta   = $item.find( '.dcb-meta-fields' );
			var $plan   = $item.find( '.dcb-pricing-plan-fields' );

			if ( 'meta' === type ) {
				$meta.show();
				$plan.hide();
			} else if ( 'pricing_plan' === type ) {
				$meta.hide();
				$plan.show();
			}
		},

		// -----------------------------------------------------------------
		// Condition: compare change → show / hide Meta Value row
		// -----------------------------------------------------------------

		handleCompareChange: function ( $select ) {
			var val     = $select.val();
			var $item   = $select.closest( '.dcb-condition-item' );
			var hideRow = ( 'EXISTS' === val || 'NOT EXISTS' === val );

			$item.find( '.dcb-meta-value-row' ).toggle( ! hideRow );

			// Update the collapsed summary if the item is already minimised.
			this.updateConditionSummary( $item );
		},

		// -----------------------------------------------------------------
		// Condition: minimize / maximize
		// -----------------------------------------------------------------

		toggleCondition: function ( $item ) {
			var $body      = $item.find( '.dcb-condition-body' );
			var $btn       = $item.find( '.dcb-toggle-condition' );
			var collapsed  = $item.hasClass( 'dcb-collapsed' );

			if ( collapsed ) {
				$item.removeClass( 'dcb-collapsed' );
				$body.slideDown( 180 );
				$btn
					.attr( 'title', dcbAdmin.strings.minimize || 'Minimize' )
					.attr( 'aria-expanded', 'true' )
					.find( '.dashicons' )
					.removeClass( 'dashicons-arrow-down-alt2' )
					.addClass( 'dashicons-arrow-up-alt2' );
			} else {
				this.updateConditionSummary( $item );
				$item.addClass( 'dcb-collapsed' );
				$body.slideUp( 180 );
				$btn
					.attr( 'title', dcbAdmin.strings.maximize || 'Maximize' )
					.attr( 'aria-expanded', 'false' )
					.find( '.dashicons' )
					.removeClass( 'dashicons-arrow-up-alt2' )
					.addClass( 'dashicons-arrow-down-alt2' );
			}
		},

		// -----------------------------------------------------------------
		// Condition: update the summary line shown when collapsed
		// -----------------------------------------------------------------

		updateConditionSummary: function ( $item ) {
			var type    = $item.find( '.dcb-condition-type' ).val();
			var summary = '';

			if ( 'meta' === type ) {
				var key = $item.find( '[name*="[meta_key]"]' ).val()   || '';
				var op  = $item.find( '.dcb-compare-select' ).val()         || '=';
				var val = $item.find( 'input[name*="[meta_value]"]' ).val() || '';
				summary = key + ' ' + op;
				if ( 'EXISTS' !== op && 'NOT EXISTS' !== op ) {
					summary += ' ' + val;
				}
			} else if ( 'pricing_plan' === type ) {
				var status = $item.find( 'select[name*="[plan_status_condition]"]' ).val() || '';
				summary    = status.replace( /_/g, ' ' );
			}

			$item.find( '.dcb-condition-summary' ).text( summary );
		},

		// -----------------------------------------------------------------
		// Condition: renumber visible #labels after add/remove
		// -----------------------------------------------------------------

		renumberConditions: function () {
			$( '#dcb-conditions-list .dcb-condition-item' ).each( function ( i ) {
				$( this )
					.attr( 'data-condition-index', i )
					.find( '.dcb-condition-title' )
					.text(
						( dcbAdmin.strings.condition || 'Condition' ) + ' #' + ( i + 1 )
					);
			} );
		},

		// -----------------------------------------------------------------
		// Condition: initialise compare states for pre-rendered conditions
		// -----------------------------------------------------------------

		initExistingCompareStates: function () {
			var self = this;
			$( '#dcb-conditions-list .dcb-condition-item' ).each( function () {
				self.handleCompareChange( $( this ).find( '.dcb-compare-select' ) );
			} );
		},

		// -----------------------------------------------------------------
		// Navigate to form page with badge ID
		// -----------------------------------------------------------------

		editBadge: function ( badgeId ) {
			window.location.href = this.getFormUrl( badgeId );
		},

		// -----------------------------------------------------------------
		// Populate form fields (used when editing via AJAX-loaded data)
		// -----------------------------------------------------------------

		populateForm: function ( badge ) {
			var self = this;
			this.currentBadge = badge;

			$( '#dcb-badge-id' ).val( badge.id || '' );
			$( '#dcb-badge-order' ).val( badge.order || '' );
			$( '#dcb-badge-title' ).val( badge.badge_title || '' );
			$( '#dcb-badge-icon' ).val( badge.badge_icon || '' );
			$( '#dcb-badge-id-field' ).val( badge.badge_id || '' );
			$( '#dcb-badge-label' ).val( badge.badge_label || '' );
			$( '#dcb-badge-class' ).val( badge.badge_class || '' );
			$( '#dcb-badge-color' ).val( badge.badge_color || '' );

			if ( typeof $.fn.wpColorPicker !== 'undefined' && $( '#dcb-badge-color' ).hasClass( 'wp-color-picker' ) ) {
				$( '#dcb-badge-color' ).wpColorPicker( 'color', badge.badge_color || '' );
			}

			$( '#dcb-condition-relation' ).val( badge.condition_relation || 'AND' );
			$( '#dcb-badge-active' ).prop(
				'checked',
				true === badge.is_active || '1' === badge.is_active || 1 === badge.is_active
			);

			// Rebuild condition rows.
			$( '#dcb-conditions-list' ).empty();
			this.conditionIndex = 0;

			if ( badge.conditions && badge.conditions.length > 0 ) {
				badge.conditions.forEach( function ( condition, idx ) {
					self.addCondition();

					var $item  = $( '#dcb-conditions-list .dcb-condition-item' ).last();
					var index  = self.conditionIndex - 1;
					var $type  = $item.find( '.dcb-condition-type' );

					$type.val( condition.type || 'meta' );
					$type.trigger( 'change' );

					// Set values after fields become visible.
					setTimeout( function () {
						if ( 'meta' === condition.type ) {
							var $meta = $item.find( '.dcb-meta-fields' );
							var $mk   = $meta.find( '[name="badge[conditions][' + index + '][meta_key]"]' );
							var mkVal = condition.meta_key || '';
							// Ensure option exists then set value (Select2 tags / custom keys).
							if ( mkVal ) {
								var hasOption = false;
								$mk.find( 'option' ).each( function () {
									if ( $( this ).val() === mkVal ) {
										hasOption = true;
										return false;
									}
								} );
								if ( ! hasOption ) {
									$mk.append( $( '<option></option>' ).val( mkVal ).text( mkVal ) );
								}
							}
							$mk.val( mkVal ).trigger( 'change' );
							$meta.find( 'input[name="badge[conditions][' + index + '][meta_value]"]' ).val( condition.meta_value || '' );
							$meta.find( 'select[name="badge[conditions][' + index + '][compare]"]' ).val( condition.compare || '=' );
							$meta.find( 'select[name="badge[conditions][' + index + '][type_cast]"]' ).val( condition.type_cast || 'CHAR' );
							// Apply visibility rules based on the loaded compare value.
							self.handleCompareChange( $meta.find( '.dcb-compare-select' ) );
							self.initMetaKeySelect2( $item );
						} else if ( 'pricing_plan' === condition.type ) {
							var $plan = $item.find( '.dcb-pricing-plan-fields' );
							$plan.find( 'select[name="badge[conditions][' + index + '][plan_status_condition]"]' ).val( condition.plan_status_condition || '' );
							$plan.find( 'input[name="badge[conditions][' + index + '][plan_id]"]' ).val( condition.plan_id || '' );
							$plan.find( 'select[name="badge[conditions][' + index + '][compare]"]' ).val( condition.compare || '=' );
						}
					}, 50 );
				} );
			}
		},

		// -----------------------------------------------------------------
		// Save badge (AJAX)
		// -----------------------------------------------------------------

		saveBadge: function () {
			var self      = this;
			var $btn      = $( '.dcb-save-badge' );
			var origText  = $btn.text();

			if ( ! this.validateForm() ) {
				return;
			}

			// Collect field values.
			var badgeData = {
				id               : $( '#dcb-badge-id' ).val()              || '',
				order            : $( '#dcb-badge-order' ).val()           || '',
				badge_title      : $( '#dcb-badge-title' ).val()           || '',
				badge_icon       : $( '#dcb-badge-icon' ).val()            || '',
				badge_id         : $( '#dcb-badge-id-field' ).val()        || '',
				badge_label      : $( '#dcb-badge-label' ).val()           || '',
				badge_class      : $( '#dcb-badge-class' ).val()           || '',
				badge_color      : $( '#dcb-badge-color' ).val()           || '',
				condition_relation: $( '#dcb-condition-relation' ).val()   || 'AND',
				is_active        : $( '#dcb-badge-active' ).is( ':checked' ) ? 1 : 0,
				conditions       : []
			};

			// Collect condition rows in DOM order (respects drag-reorder).
			$( '#dcb-conditions-list .dcb-condition-item' ).each( function () {
				var $item = $( this );
				var type  = $item.find( '.dcb-condition-type' ).val();

				if ( ! type ) {
					return;
				}

				var cond = { type: type };

				if ( 'meta' === type ) {
					var $meta        = $item.find( '.dcb-meta-fields' );
					cond.meta_key    = $meta.find( '[name*="[meta_key]"]' ).val()        || '';
					cond.meta_value  = $meta.find( 'input[name*="[meta_value]"]' ).val()      || '';
					cond.compare     = $meta.find( 'select[name*="[compare]"]' ).val()        || '=';
					cond.type_cast   = $meta.find( 'select[name*="[type_cast]"]' ).val()      || 'CHAR';
				} else if ( 'pricing_plan' === type ) {
					var $plan                  = $item.find( '.dcb-pricing-plan-fields' );
					cond.plan_status_condition = $plan.find( 'select[name*="[plan_status_condition]"]' ).val() || '';
					cond.plan_id               = $plan.find( 'input[name*="[plan_id]"]' ).val()                || '';
					cond.compare               = $plan.find( 'select[name*="[compare]"]' ).val()               || '=';
				}

				badgeData.conditions.push( cond );
			} );

			$btn.prop( 'disabled', true ).text( dcbAdmin.strings.saving );

			// Flatten nested data into a format WP admin-ajax.php can parse.
			var postData = {
				action : 'dcb_save_badge',
				nonce  : dcbAdmin.nonce,
				'badge[id]'                : badgeData.id,
				'badge[order]'             : badgeData.order,
				'badge[badge_title]'       : badgeData.badge_title,
				'badge[badge_icon]'        : badgeData.badge_icon,
				'badge[badge_id]'          : badgeData.badge_id,
				'badge[badge_label]'       : badgeData.badge_label,
				'badge[badge_class]'       : badgeData.badge_class,
				'badge[badge_color]'       : badgeData.badge_color,
				'badge[condition_relation]': badgeData.condition_relation,
				'badge[is_active]'         : badgeData.is_active
			};

			badgeData.conditions.forEach( function ( cond, i ) {
				postData[ 'badge[conditions][' + i + '][type]' ] = cond.type;

				if ( 'meta' === cond.type ) {
					postData[ 'badge[conditions][' + i + '][meta_key]'   ] = cond.meta_key   || '';
					postData[ 'badge[conditions][' + i + '][meta_value]' ] = cond.meta_value || '';
					postData[ 'badge[conditions][' + i + '][compare]'    ] = cond.compare    || '=';
					postData[ 'badge[conditions][' + i + '][type_cast]'  ] = cond.type_cast  || 'CHAR';
				} else if ( 'pricing_plan' === cond.type ) {
					postData[ 'badge[conditions][' + i + '][plan_status_condition]' ] = cond.plan_status_condition || '';
					postData[ 'badge[conditions][' + i + '][plan_id]'               ] = cond.plan_id               || '';
					postData[ 'badge[conditions][' + i + '][compare]'               ] = cond.compare               || '=';
				}
			} );

			$.ajax( {
				url     : dcbAdmin.ajaxUrl,
				type    : 'POST',
				data    : postData,
				success : function ( response ) {
					$btn.prop( 'disabled', false ).text( origText );

					if ( response.success ) {
						self.showNotice( response.data.message, 'success' );

						// Stay on the form page after saving.
						// For new badges use the server-returned ID so the URL
						// transitions from "add" to "edit" without losing the form.
						var savedId = response.data.badge && response.data.badge.id
							? response.data.badge.id
							: $( '#dcb-badge-id' ).val();

						setTimeout( function () {
							window.location.href = self.getFormUrl( savedId );
						}, 1000 );
					} else {
						self.showNotice( response.data.message, 'error' );
					}
				},
				error: function () {
					$btn.prop( 'disabled', false ).text( origText );
					self.showNotice( dcbAdmin.strings.error, 'error' );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Form validation
		// -----------------------------------------------------------------

		validateForm: function () {
			var isValid  = true;
			var $title   = $( '#dcb-badge-title' );
			var $badgeId = $( '#dcb-badge-id-field' );
			var $label   = $( '#dcb-badge-label' );

			$( '.dcb-field-error' ).text( '' );

			if ( ! $title.val().trim() ) {
				isValid = false;
				$title.addClass( 'dcb-error' );
			} else {
				$title.removeClass( 'dcb-error' );
			}

			var idVal = $badgeId.val().trim();
			if ( ! idVal ) {
				isValid = false;
				$badgeId.addClass( 'dcb-error' );
				$badgeId.siblings( '.dcb-field-error' ).text( dcbAdmin.strings.requiredField );
			} else if ( ! /^[a-z0-9-]+$/.test( idVal ) ) {
				isValid = false;
				$badgeId.addClass( 'dcb-error' );
				$badgeId.siblings( '.dcb-field-error' ).text( dcbAdmin.strings.invalidBadgeId );
			} else {
				$badgeId.removeClass( 'dcb-error' );
			}

			if ( ! $label.val().trim() ) {
				isValid = false;
				$label.addClass( 'dcb-error' );
			} else {
				$label.removeClass( 'dcb-error' );
			}

			if ( ! isValid ) {
				this.showNotice( dcbAdmin.strings.requiredField, 'error' );
			}

			return isValid;
		},

		validateBadgeId: function ( badgeId ) {
			var $field = $( '#dcb-badge-id-field' );
			var $error = $field.siblings( '.dcb-field-error' );

			if ( ! badgeId || ! /^[a-z0-9-]+$/.test( badgeId ) ) {
				$error.text( dcbAdmin.strings.invalidBadgeId );
				$field.addClass( 'dcb-error' );
			} else {
				$error.text( '' );
				$field.removeClass( 'dcb-error' );
			}
		},

		// -----------------------------------------------------------------
		// Delete badge
		// -----------------------------------------------------------------

		deleteBadge: function ( badgeId ) {
			var self = this;

			if ( ! confirm( dcbAdmin.strings.confirmDelete ) ) {
				return;
			}

			$.ajax( {
				url  : dcbAdmin.ajaxUrl,
				type : 'POST',
				data : { action: 'dcb_delete_badge', id: badgeId, nonce: dcbAdmin.nonce },
				success: function ( response ) {
					if ( response.success ) {
						self.showNotice( response.data.message, 'success' );
						setTimeout( function () { location.reload(); }, 1000 );
					} else {
						self.showNotice( response.data.message, 'error' );
					}
				},
				error: function () {
					self.showNotice( dcbAdmin.strings.error, 'error' );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Duplicate badge
		// -----------------------------------------------------------------

		duplicateBadge: function ( badgeId ) {
			var self = this;

			$.ajax( {
				url  : dcbAdmin.ajaxUrl,
				type : 'POST',
				data : { action: 'dcb_duplicate_badge', id: badgeId, nonce: dcbAdmin.nonce },
				success: function ( response ) {
					if ( response.success ) {
						self.showNotice( response.data.message, 'success' );
						setTimeout( function () { location.reload(); }, 1000 );
					} else {
						self.showNotice( response.data.message, 'error' );
					}
				},
				error: function () {
					self.showNotice( dcbAdmin.strings.error, 'error' );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Toggle badge active status
		// -----------------------------------------------------------------

		toggleBadge: function ( badgeId ) {
			var self = this;

			$.ajax( {
				url  : dcbAdmin.ajaxUrl,
				type : 'POST',
				data : { action: 'dcb_toggle_badge', id: badgeId, nonce: dcbAdmin.nonce },
				success: function ( response ) {
					if ( ! response.success ) {
						self.showNotice( response.data.message, 'error' );
						setTimeout( function () { location.reload(); }, 500 );
					}
				},
				error: function () {
					self.showNotice( dcbAdmin.strings.error, 'error' );
					setTimeout( function () { location.reload(); }, 500 );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Reorder badges (after drag-drop on list page)
		// -----------------------------------------------------------------

		reorderBadges: function () {
			var self  = this;
			var order = [];

			$( '.dcb-badge-row' ).each( function () {
				order.push( $( this ).data( 'badge-id' ) );
			} );

			$.ajax( {
				url  : dcbAdmin.ajaxUrl,
				type : 'POST',
				data : { action: 'dcb_reorder_badges', order: order, nonce: dcbAdmin.nonce },
				success: function ( response ) {
					if ( ! response.success ) {
						self.showNotice( response.data.message, 'error' );
					}
				},
				error: function () {
					self.showNotice( dcbAdmin.strings.error, 'error' );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Export badges to JSON file
		// -----------------------------------------------------------------

		exportBadges: function () {
			var self = this;

			$.ajax( {
				url  : dcbAdmin.ajaxUrl,
				type : 'POST',
				data : { action: 'dcb_export_badges', nonce: dcbAdmin.nonce },
				success: function ( response ) {
					if ( response.success ) {
						var blob = new Blob(
							[ JSON.stringify( response.data.badges, null, 2 ) ],
							{ type: 'application/json' }
						);
						var url  = URL.createObjectURL( blob );
						var link = document.createElement( 'a' );
						link.href     = url;
						link.download = 'directorist-custom-badges-' + Date.now() + '.json';
						link.click();
						URL.revokeObjectURL( url );
					} else {
						self.showNotice( response.data.message, 'error' );
					}
				},
				error: function () {
					self.showNotice( dcbAdmin.strings.error, 'error' );
				}
			} );
		},

		// -----------------------------------------------------------------
		// Import badges from JSON file
		// -----------------------------------------------------------------

		importBadges: function ( input ) {
			var self = this;
			var file = input.files[ 0 ];

			if ( ! file ) {
				return;
			}

			var reader    = new FileReader();
			reader.onload = function ( e ) {
				try {
					var badges = JSON.parse( e.target.result );

					if ( ! Array.isArray( badges ) ) {
						self.showNotice( 'Invalid file format.', 'error' );
						return;
					}

					if ( ! confirm( 'Import ' + badges.length + ' badge(s)?' ) ) {
						return;
					}

					$.ajax( {
						url  : dcbAdmin.ajaxUrl,
						type : 'POST',
						data : { action: 'dcb_import_badges', badges: badges, nonce: dcbAdmin.nonce },
						success: function ( response ) {
							if ( response.success ) {
								self.showNotice( response.data.message, 'success' );
								setTimeout( function () { location.reload(); }, 1000 );
							} else {
								self.showNotice( response.data.message, 'error' );
							}
						},
						error: function () {
							self.showNotice( dcbAdmin.strings.error, 'error' );
						}
					} );
				} catch ( err ) {
					self.showNotice( 'Error parsing JSON file.', 'error' );
				}
			};
			reader.readAsText( file );
		},

		// -----------------------------------------------------------------
		// Inline notice banner
		// -----------------------------------------------------------------

		showNotice: function ( message, type ) {
			type = type || 'info';
			var $notice = $( '<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>' );
			$( '.dcb-notices' ).html( $notice );

			setTimeout( function () {
				$notice.fadeOut( 400, function () { $( this ).remove(); } );
			}, 5000 );
		}
	};

	// Boot on DOM ready.
	$( document ).ready( function () {
		DCBAdmin.init();
		// Expose globally so the inline PHP script in the form template can set conditionIndex.
		window.DCBAdmin = DCBAdmin;
	} );

} )( jQuery );

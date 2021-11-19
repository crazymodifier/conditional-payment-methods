/**
 * Conditional Payment Methods for WooCommerce JavaScript
 *
 * @package conditional-payment-methods-for-woocommerce/assets/js
 */

jQuery(
	function($) {

		var cpmData               = '',
		cpmToggle                 = '',
		cpmMetaboxWrapper         = '',
		cpmBoarding               = '',
		cpmToolbar                = '',
		cpmMetaboxCount           = '',
		cpmAvailableRuleFields    = '',
		cpmAvailableRuleOperators = '',
		cpmConditionCount         = 0,
		cpmRuleCount              = 0,
		cpmToggleEnabledClass	  = 'woocommerce-input-toggle--enabled',
		cpmToggleDisabledClass	  = 'woocommerce-input-toggle--disabled',
		errorMessage              = 'Conditional Payment Methods For WooCommerce Settings';

		// Function to handle all scripts that need to be iniitialized on document change.
		var cpmScripts = function() {
			try {
				$( '#wc_cpm_data' ).find( '.cpm-select2' ).select2();

				$( '.cpm-rules' ).each(
					function() {
						$( this ).find( '.plus' ).not( ':last' ).hide();
						$( this ).find( '.trash:first' ).hide();
						$( this ).find( '.rule-and:first' ).hide();
						$( this ).find( '.rule-field:first' ).css( 'margin-left', '3.7em' );
					}
				);

			} catch ( e ) {
				console.log( errorMessage, e );
			}
		}

		var cpmRuleValueOptionsHtml = function( params = {} ) {

			try {
				let settingsRuleData = ( params.hasOwnProperty( 'settingsRuleData' ) ) ? params.settingsRuleData : {},
				conditionIndex       = ( params.hasOwnProperty( 'conditionIndex' ) ) ? params.conditionIndex : 0,
				ruleIndex            = ( params.hasOwnProperty( 'ruleIndex' ) ) ? params.ruleIndex : 0;

				let settingsData = { field: settingsRuleData.hasOwnProperty( 'field' ) ? settingsRuleData.field : '',
					operator: settingsRuleData.hasOwnProperty( 'operator' ) ? settingsRuleData.operator : '',
					value: settingsRuleData.hasOwnProperty( 'value' ) ? settingsRuleData.value : '' },
				fieldType        = ( cpmAvailableRuleFields[settingsData.field].hasOwnProperty( 'type' ) ) ? cpmAvailableRuleFields[settingsData.field].type : 'string',
				fieldValues      = ( cpmAvailableRuleFields[settingsData.field].hasOwnProperty( 'values' ) ) ? cpmAvailableRuleFields[settingsData.field].values : [],
				ruleOptions      = '',
				ruleValueHtml    = '';

				// Code to prepare field value options.
				if ( 'string' === fieldType && Object.entries( fieldValues ).length > 0 ) {

					settingsData.value = ( settingsData.value ) ? settingsData.value : [];

					Object.entries( fieldValues ).forEach(
						([key, value]) => { ruleOptions += '<option value="' + key + '" ' + ( ( settingsData.value.indexOf( key ) != -1 ) ? 'selected' : '' ) + '>' + value + '</option>'; }
					);
				}

				if ( ruleOptions != '' ) { // for select2.
					ruleValueHtml = '<div class="rule-value select-field">' +
									'<select name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][rules][' + params.ruleIndex + '][value][]" class="multiselect cpm-select2" multiple="multiple" data-placeholder="' + ( ( 'billing_country' === settingsData.field ) ? cpmSettingsParams.localizedStrings.selectBillingCountries : cpmSettingsParams.localizedStrings.selectShippingCountries ) + '">' +
											ruleOptions +
									'</select>' +
								'</div>';
				} else if ( 'number' === fieldType ) { // for number fields.

					ruleValueHtml = '<div class="rule-value number-field">' +
									'<input type="number" class="wc_input_price short" name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][rules][' + params.ruleIndex + '][value]" value="' + settingsData.value + '" placeholder="' + cpmSettingsParams.localizedStrings.enterNumber + '" step="any" min="0"/>' +
								'</div>'
				} else { // for string fields.
					ruleValueHtml = '<div class="rule-value">' +
									'<input type="text"  name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][rules][' + params.ruleIndex + '][value]" value="' + ( Array.isArray( settingsData.value ) ? settingsData.value.join( ',' ) : settingsData.value ) + '" placeholder="' + cpmSettingsParams.localizedStrings.enterMultipleValuesSeparatedByComma + '" />' +
								'</div>';
				}

				return ruleValueHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}

		}

		var cpmRuleOperatorOptionsHtml = function( params = {} ) {

			try {
				let settingsRuleData = ( params.hasOwnProperty( 'settingsRuleData' ) ) ? params.settingsRuleData : {},
				conditionIndex       = ( params.hasOwnProperty( 'conditionIndex' ) ) ? params.conditionIndex : 0,
				ruleIndex            = ( params.hasOwnProperty( 'ruleIndex' ) ) ? params.ruleIndex : 0;

				let settingsData    = { field: settingsRuleData.hasOwnProperty( 'field' ) ? settingsRuleData.field : '',
					operator: settingsRuleData.hasOwnProperty( 'operator' ) ? settingsRuleData.operator : '',
					value: settingsRuleData.hasOwnProperty( 'value' ) ? settingsRuleData.value : '' },
				fieldType           = ( cpmAvailableRuleFields[settingsData.field].hasOwnProperty( 'type' ) ) ? cpmAvailableRuleFields[settingsData.field].type : 'string',
				ruleOperatorOptions = '',
				ruleOperatorHtml    = '';

				// Code to prepare operator options.
				Object.entries( cpmAvailableRuleOperators[fieldType] ).forEach(
					([key, value]) => { ruleOperatorOptions += '<option value="' + key + '" ' + ( ( key === settingsData.operator ) ? 'selected' : '' ) + '>' + value + '</option>'; }
				);

				ruleOperatorHtml = '<div class="rule-operator select-field">' +
									'<select name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][rules][' + params.ruleIndex + '][operator]" data-placeholder="' + cpmSettingsParams.localizedStrings.selectOperator + '">' +
										ruleOperatorOptions +
									'</select>' +
								'</div>';

				return ruleOperatorHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}

		}

		var cpmConditionRuleHtml = function( params = {} ) {

			try {
				let ruleOptions  = { field: '', operator: '', value: '' },
				settingsRuleData = ( params.hasOwnProperty( 'ruleData' ) ) ? params.ruleData : [],
				ruleHtml         = '';

				let ruleSettings = { field: ( settingsRuleData.hasOwnProperty( 'field' ) ) ? settingsRuleData.field : 'billing_country',
					operator: ( settingsRuleData.hasOwnProperty( 'operator' ) ) ? settingsRuleData.operator : '',
					value: ( settingsRuleData.hasOwnProperty( 'value' ) ) ? settingsRuleData.value : ''
				};

				let ruleParams = { settingsRuleData: ruleSettings, conditionIndex: params.conditionIndex, ruleIndex: params.ruleIndex, conditionStatus: params.conditionStatus };

				// Code to prepare field options.
				Object.entries( cpmAvailableRuleFields ).forEach(
					([key, obj]) => { ruleOptions.field += '<option value="' + key + '" ' + ( ( key === ruleSettings.field ) ? 'selected' : '' ) + '>' + obj.title + '</option>'; }
				);

				ruleHtml = '<div class="rule-row" data-rule_id="' + params.ruleIndex + '">' +
								'<div class="rule-and"><i>' + cpmSettingsParams.localizedStrings.andSmall + '</i></div>' +
								'<div class="rule-field select-field">' +
									'<select name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][rules][' + params.ruleIndex + '][field]" data-placeholder="' + cpmSettingsParams.localizedStrings.selectField + '">' +
										ruleOptions.field +
									'</select>' +
								'</div>' +
								cpmRuleOperatorOptionsHtml( ruleParams ) +
								cpmRuleValueOptionsHtml( ruleParams ) +
								'<div class="wc-cpm rule-actions column-wc_actions">' +
									'<a href="#" data-tip="' + cpmSettingsParams.localizedStrings.addRule + '" class="button wc-action-button plus help_tip"></a>' +
									'<a href="#" data-tip="' + cpmSettingsParams.localizedStrings.removeRule + '" class="button wc-action-button trash help_tip"></a>' +
								'</div>' +
							'</div>';

				return ruleHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}

		}

		var cpmConditionRuleGroupHtml = function( params = {} ) {

			try {
				let conditionParams = ( params.hasOwnProperty( 'condition' ) ) ? params.condition : {},
				ruleGroupHtml       = '';
				ruleGroupHtml      += '<div class="cpm_condition_rules_list widefat">';

				if ( conditionParams.hasOwnProperty( 'rules' ) && Object.entries( conditionParams.rules ).length > 0 ) {
					Object.entries( conditionParams.rules ).forEach(
						([key, obj]) => {
							if ( obj.hasOwnProperty( 'field' ) ) { // phpcs:ignore
								params.ruleIndex = key; // phpcs:ignore
								params.ruleData  = obj; // phpcs:ignore
								ruleGroupHtml   += cpmConditionRuleHtml( params ); // phpcs:ignore
							} // phpcs:ignore
						}
					);
				} else {
					params.ruleIndex = ++cpmRuleCount
					ruleGroupHtml   += cpmConditionRuleHtml( params );
				}

				ruleGroupHtml += '</div>';

				return ruleGroupHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}
		}

		var cpmConditionFieldsHtml = function( params = {} ) {

			try {
				let paymentGatewayOptions = '';
				let conditionParams       = ( params.hasOwnProperty( 'condition' ) ) ? params.condition : {},
				fieldHtml                 = '';

				let settingsPaymentMethods = ( conditionParams.hasOwnProperty( 'payment_methods' ) ) ? conditionParams.payment_methods : [];

				Object.entries( cpmSettingsParams.dataParams.availablePaymentMethods ).forEach(
					([key, title]) => { paymentGatewayOptions += '<option value="' + key + '" ' + ( ( settingsPaymentMethods.indexOf( key ) != -1 ) ? 'selected' : '' ) + '>' + title + '</option>'; }
				);

				fieldHtml += '<div class="cpm-form">' +
								'<div class="cpm-form-field cpm-title">' +
									'<label>' + cpmSettingsParams.localizedStrings.title + '</label>' +
									'<div class="cpm-form-content">' +
										'<input type="text" class="title" name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][title]" id="condition_' + params.conditionIndex + '_title" placeholder="' + cpmSettingsParams.localizedStrings.titlePlaceholder + '" value="' + ( ( conditionParams.hasOwnProperty( 'title' ) ? conditionParams.title : '' ) ) + '"/>' +
									'</div>' +
								'</div>' +
								'<div class="cpm-form-field" style="height:100%;width:100%;margin-top:1em;">' +
									'<div class="cpm-field">' +
										'<div class="cpm-form-field">' +
											'<div class="cpm-form-content">' +
												'<select class="cpm-show-hide" name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][exclude]">' +
													'<option value=0 ' + ( ( 0 === parseInt( conditionParams.exclude ) ) ? 'selected' : '' ) + '>' + cpmSettingsParams.localizedStrings.include + '</option>' +
													'<option value=1 ' + ( ( 1 === parseInt( conditionParams.exclude ) ) ? 'selected' : '' ) + '>' + cpmSettingsParams.localizedStrings.exclude + '</option>' +
												'</select>' +
												'<select name="condition[' + params.conditionStatus + '][' + params.conditionIndex + '][payment_methods][]" class="multiselect cpm-select2" data-wrap="yes" multiple="multiple" data-placeholder="' + cpmSettingsParams.localizedStrings.selectPaymentMethod + '">' +
													paymentGatewayOptions +
												'</select>' +
												'<div class="wc-cpm-notice">' +
													'<i>' +
														cpmSettingsParams.localizedStrings.onlyWhen +
													'</i>' +
												'</div>' +
											'</div>' +
										'</div>' +
									'</div>' +
									'<div class="cpm-rules">' +
										cpmConditionRuleGroupHtml( params ) +
									'</div>' +
								'</div>' +
						'</div>';
				return fieldHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}
		}

		var cpmConditionHtml = function( params = {} ) {

			try {
				let toggleClass = ( 'yes' === params.enabled ) ? cpmToggleEnabledClass : cpmToggleDisabledClass,
				conditionParams = ( params.hasOwnProperty( 'condition' ) ) ? params.condition : {};

				params.conditionStatus = ( 'yes' === params.enabled ) ? 'enabled' : 'disabled';
				params.state           = ( ! params.hasOwnProperty( 'state' ) ) ? '' : params.state;

				let conditionHtml = '<div class="wc_cpm wc_cpm_' + params.conditionIndex + ' wc-metabox ' + params.state + '" data-condition_id="' + params.conditionIndex + '">' +
									'<h3>' +
										'<div class="condition_title">' +
											'<span class="wp-cpm-active-toggle woocommerce-input-toggle ' + toggleClass + '"></span>' +
											'<span class="condition_title_index_container">#<span class="condition_title_index">' + params.conditionIndex + '</span></span> <span class="condition_title_inner">' + ( ( conditionParams.hasOwnProperty( 'title' ) ? conditionParams.title : '' ) ) + '</span>' +
										'</div>' +
										'<div class="handle">' +
											'<div class="handle-item toggle-item" aria-label="' + cpmSettingsParams.localizedStrings.clickToToggle + '"></div>' +
											'<a href="#" class="remove_row delete">' + cpmSettingsParams.localizedStrings.remove + '</a>' +
										'</div>' +
									'</h3>' +
									'<div class="wp_cpm_data wc-metabox-content" ' + (( 'open' === params.state ) ? '' : 'style="display:none;"' ) + ' >' +
										cpmConditionFieldsHtml( params ) +
									'</div>' +
								'</div>';

				return conditionHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}

		}

		var cpmSettingsHtml = function() {

			try {
				let settingsHtml = '<div class="inside">' +
									'<p class="toolbar">' +
										'<span class="wc_cpm_bulk_toggle_wrapper  ' + ( ( Object.entries( cpmSettingsParams.settingsData ).length > 0 ) ? '' : 'disabled' ) + '">' +
											'<a href="#" class="expand_all">' + cpmSettingsParams.localizedStrings.expandAll + '</a>' +
											'<a href="#" class="close_all">' + cpmSettingsParams.localizedStrings.closeAll + '</a>' +
										'</span>' +
									'</p>' +
									'<div class="wc_cpm wc-metaboxes ui-sortable">';

				if ( Object.entries( cpmSettingsParams.settingsData ).length > 0 ) {

					let enabledConditions = ( cpmSettingsParams.settingsData.hasOwnProperty( 'enabled' ) ) ? cpmSettingsParams.settingsData.enabled : [],
					disabledConditions    = ( cpmSettingsParams.settingsData.hasOwnProperty( 'disabled' ) ) ? cpmSettingsParams.settingsData.disabled : [];

					Object.entries( enabledConditions ).forEach(
						([index, conditions]) => {
							let params    = { enabled: 'yes', conditionIndex: ++cpmConditionCount, condition: conditions }; // phpcs:ignore
							settingsHtml += cpmConditionHtml( params ); // phpcs:ignore
						}
					);

					Object.entries( disabledConditions ).forEach(
						([index, conditions]) => { // phpcs:ignore
							let params    = { enabled: 'no', conditionIndex: ++cpmConditionCount, condition: conditions }; // phpcs:ignore
							settingsHtml += cpmConditionHtml( params ); // phpcs:ignore
						} // phpcs:ignore
					);

				} else { // Boarding message.
					settingsHtml += '<div class="wc_cpm_boarding">' +
									'<div class="wc_cpm_boarding_message">' +
										'<h3>' + cpmSettingsParams.localizedStrings.paymentMethodConditions + '</h3>' +
										'<p>' + cpmSettingsParams.localizedStrings.noConditionsFound + '</p>' +
									'</div>' +
								'</div>';
				}

				settingsHtml += '</div>' +
							'<div class="toolbar toolbar--footer borderless ' + ( ( Object.entries( cpmSettingsParams.settingsData ).length > 0 ) ? '' : 'wc_cpm_data--empty' ) + '">' +
								'<button id="wc_cpm_add" type="button" class="button button-secondary wc_cpm_add_condition">' + cpmSettingsParams.localizedStrings.addCondition + '</button>' +
							'</div>' +
						'</div>';

				return settingsHtml;
			} catch ( e ) {
				console.log( errorMessage, e );
			}

		}

		var cpmSettingsInit = function() {

			try {
				cpmAvailableRuleFields    = cpmSettingsParams.dataParams.availableRuleFields;
				cpmAvailableRuleOperators = cpmSettingsParams.dataParams.availableRuleFieldOperators;
				cpmConditionCount         = 0;
				cpmRuleCount              = 0;

				$( '#wc_cpm_data' ).html( cpmSettingsHtml() );
				cpmScripts();

				cpmData           = $( '#wc_cpm_data' );
				cpmToggle         = cpmData.find( '.wc_cpm_bulk_toggle_wrapper' );
				cpmMetaboxWrapper = cpmData.find( '.wc-metaboxes' );
				cpmBoarding       = cpmMetaboxWrapper.find( '.wc_cpm_boarding' );
				cpmToolbar        = cpmData.find( '.toolbar' );
				cpmMetaboxCount   = cpmMetaboxWrapper.find( '.wp_cpm_condition' ).length;
			} catch ( e ) {
				console.log( errorMessage, e );
			}

		}

		$(
			function() {
				cpmSettingsInit();

				// Meta-Boxes - Open/close.
				cpmData.on(
					'click',
					'.wc-metabox > h3',
					function( event ) {
						try {
							if ( ! event.target.classList.contains( 'wp-cpm-active-toggle' ) ) {
								$( this ).parent( '.wc-metabox' ).toggleClass( 'closed' ).toggleClass( 'open' );
								$( this ).next( '.wc-metabox-content' ).stop().slideToggle( 300 );
								cpmScripts();
							}
						} catch ( e ) {
							console.log( errorMessage, e );
						}

					}
				);
			}
		)

		.on(
			'click',
			'.wc_cpm_add_condition',
			function() {

				try {
					let params = {
						state: 'open',
						enabled: 'yes',
						conditionIndex: ++cpmConditionCount
					};

					let conditionHtml = cpmConditionHtml( params );
					cpmData.trigger( 'cpm_before_condition_add', conditionHtml );
					cpmMetaboxWrapper.append( conditionHtml );
					cpmScripts();

					cpmToggle.removeClass( 'disabled' );
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		.on(
			'cpm_before_condition_add',
			function() {
				// Hide default boarding if exists.
				try {
					if ( cpmBoarding.length ) {
						cpmBoarding.hide();
						cpmToolbar.removeClass( 'wc_cpm_data--empty' );
					}
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		// Condition Active toggle.
		.on(
			'click',
			'.wp-cpm-active-toggle',
			function() {
				try {

					let searchString = '',
					replaceString    = '';

					if ( $( this ).hasClass( cpmToggleDisabledClass ) ) {
						$( this ).removeClass( cpmToggleDisabledClass ).addClass( cpmToggleEnabledClass );
						searchString  = 'disabled';
						replaceString = 'enabled';
					} else if ( $( this ).hasClass( cpmToggleEnabledClass ) ) {
						$( this ).removeClass( cpmToggleEnabledClass ).addClass( cpmToggleDisabledClass );
						searchString  = 'enabled';
						replaceString = 'disabled';
					}

					$( this ).parents( '.wc_cpm.wc-metabox' ).find( "[name^='condition[" + searchString + "]']" ).each(
						function(){
							let name = $( this ).attr( 'name' );
							name     = name.replace( searchString,replaceString );
							$( this ).attr( 'name',name );
						}
					)

				} catch ( e ) {
					console.log( errorMessage, e );
				}
			}
		)

		// condition Remove.
		.on(
			'click',
			'.wc_cpm .remove_row',
			function( e ) {

				try {
					e.preventDefault();

					var $parent = $( this ).closest( '.wc-metabox' );

					$parent.find( '*' ).off();
					$parent.remove();
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		// Condiion Rule Add.
		.on(
			'click',
			'.rule-actions .plus',
			function( e ) {

				try {
					e.preventDefault();

					let currentRuleIndex       = parseInt( $( this ).parents( '.rule-row' ).attr( 'data-rule_id' ) ),
						currentConditionIndex  = parseInt( $( this ).parents( '.wc_cpm' ).attr( 'data-condition_id' ) ),
						currentConditionStatus = ( $( this ).parents( '.wc_cpm.wc-metabox' ).find( '.wp-cpm-active-toggle' ).hasClass( cpmToggleEnabledClass ) ) ? 'enabled' : 'disabled';
					let conditionRuleHtml      = cpmConditionRuleHtml( { conditionIndex: currentConditionIndex, ruleIndex: ++currentRuleIndex, conditionStatus: currentConditionStatus } );
					$( this ).hide();
					$( this ).parents( '.cpm_condition_rules_list' ).append( conditionRuleHtml );
					cpmScripts();
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		// Condition Rule Remove.
		.on(
			'click',
			'.rule-actions .trash',
			function( e ) {

				try {
					e.preventDefault();
					$( this ).parents( '.rule-row' ).remove();
					$( '.cpm_condition_rules_list' ).find( '.rule-row:last-child' ).find( '.rule-actions .plus' ).show();
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		.on(
			'change',
			'.rule-field',
			function() {

				try {
					let ruleParams = { field: ( $( this ).hasClass( 'select-field' ) ? $( this ).find( 'select' ).val() : $( this ).find( 'input' ).val() ) },
					params         = { settingsRuleData: ruleParams, conditionStatus: 'enabled', conditionIndex: $( this ).parents( '.wc_cpm' ).attr( 'data-condition_id' ), ruleIndex: $( this ).parents( '.rule-row' ).attr( 'data-rule_id' ) };

					let ruleOperatorValueOptions = cpmRuleOperatorOptionsHtml( params );
					ruleOperatorValueOptions    += cpmRuleValueOptionsHtml( params );

					$( this ).parents( '.rule-row' ).find( '.rule-operator' ).remove();
					$( this ).parents( '.rule-row' ).find( '.rule-value' ).remove();
					$( ruleOperatorValueOptions ).insertAfter( $( this ) );
					cpmScripts();
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		.on(
			'keyup',
			'input.title',
			function() {
				try {
					$( this ).closest( '.wc_cpm' ).find( 'h3 .condition_title_inner' ).text( $( this ).val() );
				} catch ( e ) {
					console.log( errorMessage, e );
				}
			}
		)

		// Condition Expand.
		.on(
			'click',
			'.wc_cpm_bulk_toggle_wrapper .expand_all',
			function(e) {

				try {
					e.preventDefault();

					cpmMetaboxWrapper.find( '.wc-metabox' ).each(
						function() {
							$( this ).find( '.wc-metabox-content' ).show();
							$( this ).addClass( 'open' ).removeClass( 'closed' );
						}
					);
				} catch ( e ) {
					console.log( errorMessage, e );
				}

			}
		)

		// Condition Close.
		.on(
			'click',
			'.wc_cpm_bulk_toggle_wrapper .close_all',
			function(e) {

				try {
					e.preventDefault();

					cpmMetaboxWrapper.find( '.wc-metabox' ).each(
						function() {
							$( this ).find( '.wc-metabox-content' ).hide();
							$( this ).addClass( 'closed' ).removeClass( 'open' );
						}
					);
				} catch ( e ) {
					console.log( errorMessage, e );
				}
			}
		);

	}
);

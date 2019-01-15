/* global UltimatumCustomFieldsModuleL10 */
/* global UltimatumSEO */
/* global _ */
( function() {
	/**
	 * The Ultimatum Custom Fields Module adds the custom fields to the content that were defined in the titles and meta's
	 * section of the Ultimatum SEO settings when those fields are available.
	 *
	 * @constructor
	 * @property {Array} customFieldNames
	 * @property {Object} customFields
	 */
	var UltimatumCustomFieldsModule = function() {
		UltimatumSEO.app.registerModule( "UltimatumCustomFieldsModule", { status: "loading" } );

		this.customFields = {};

		this.updateCustomFields();
		this.declareReady();
	};

	/**
	 * Declares ready with UltimatumSEO.
	 *
	 * @returns {void}
	 */
	UltimatumCustomFieldsModule.prototype.declareReady = function() {
		UltimatumSEO.app.moduleReady( "UltimatumCustomFieldsModule" );
		UltimatumSEO.app.registerModification( "content", this.addCustomFields.bind( this ), "UltimatumCustomFieldsModule" );
	};

	/**
	 * Declares reloaded with UltimatumSEO.
	 *
	 * @returns {void}
	 */
	UltimatumCustomFieldsModule.prototype.declareReloaded = function() {
		UltimatumSEO.app.moduleReloaded( "UltimatumCustomFieldsModule" );
	};

	/**
	 * The callback used to add the custom fields to the content to be analyzed by UltimatumSEO.js.
	 *
	 * @param {String} content The content for adding the custom fields to.
	 * @returns {String} The content.
	 */
	UltimatumCustomFieldsModule.prototype.addCustomFields = function( content ) {
		for( var fieldName in this.customFields ) {
			content += " ";
			content += this.customFields[ fieldName ];
		}
		return content;
	};

	/**
	 * Fetches the relevant custom fields from the form and saves them in a property.
	 * Then declares reloaded and rebinds the custom fields form.
	 *
	 * @returns {void}
	 */
	UltimatumCustomFieldsModule.prototype.updateCustomFields = function() {
		var customFields = {};
		jQuery( "#the-list > tr:visible" ).each(
			function( i, el ) {
				var customFieldName = jQuery( "#" + el.id + "-key" ).val();
				if ( UltimatumCustomFieldsModuleL10.custom_field_names.indexOf( customFieldName ) !== -1 ) {
					customFields[ customFieldName ] = jQuery( "#" + el.id + "-value" ).val();
				}
			}
		);
		this.customFields = customFields;
		this.declareReloaded();
		this.bindCustomFields();
	};

	/**
	 * Adds the necessary event bindings for monitoring which custom fields are added/removed/updated.
	 *
	 * @returns {void}
	 */
	UltimatumCustomFieldsModule.prototype.bindCustomFields = function() {
		var callback = _.debounce( this.updateCustomFields.bind( this ), 500, true );

		jQuery( "#the-list .button + .update_meta" ).off( "click.mcmsseoCustomFields" ).on( "click.mcmsseoCustomFields", callback );
		jQuery( "#the-list" ).off( "mcmsListDelEnd.mcmsseoCustomFields" ).on( "mcmsListDelEnd.mcmsseoCustomFields", callback );
		jQuery( "#the-list" ).off( "mcmsListAddEnd.mcmsseoCustomFields" ).on( "mcmsListAddEnd.mcmsseoCustomFields", callback );
		jQuery( "#the-list textarea" ).off( "input.mcmsseoCustomFields" ).on( "input.mcmsseoCustomFields", callback );
	};

	if ( typeof UltimatumSEO !== "undefined" && typeof UltimatumSEO.app !== "undefined" ) {
		new UltimatumCustomFieldsModule();
	} else {
		jQuery( window ).on(
			"UltimatumSEO:ready",
			function() {
				new UltimatumCustomFieldsModule();
			}
		);
	}
}() );

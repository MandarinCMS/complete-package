/* global _mcmsMySkinSettings, confirm */
window.mcms = window.mcms || {};

( function($) {

// Set up our namespace...
var myskins, l10n;
myskins = mcms.myskins = mcms.myskins || {};

// Store the myskin data and settings for organized and quick access
// myskins.data.settings, myskins.data.myskins, myskins.data.l10n
myskins.data = _mcmsMySkinSettings;
l10n = myskins.data.l10n;

// Shortcut for isInstall check
myskins.isInstall = !! myskins.data.settings.isInstall;

// Setup app structure
_.extend( myskins, { model: {}, view: {}, routes: {}, router: {}, template: mcms.template });

myskins.Model = Backbone.Model.extend({
	// Adds attributes to the default data coming through the .org myskins api
	// Map `id` to `slug` for shared code
	initialize: function() {
		var description;

		// If myskin is already installed, set an attribute.
		if ( _.indexOf( myskins.data.installedMySkins, this.get( 'slug' ) ) !== -1 ) {
			this.set({ installed: true });
		}

		// Set the attributes
		this.set({
			// slug is for installation, id is for existing.
			id: this.get( 'slug' ) || this.get( 'id' )
		});

		// Map `section.description` to `description`
		// as the API sometimes returns it differently
		if ( this.has( 'sections' ) ) {
			description = this.get( 'sections' ).description;
			this.set({ description: description });
		}
	}
});

// Main view controller for myskins.php
// Unifies and renders all available views
myskins.view.Dexign = mcms.Backbone.View.extend({

	el: '#mcmsbody-content .wrap .myskin-browser',

	window: $( window ),
	// Pagination instance
	page: 0,

	// Sets up a throttler for binding to 'scroll'
	initialize: function( options ) {
		// Scroller checks how far the scroll position is
		_.bindAll( this, 'scroller' );

		this.SearchView = options.SearchView ? options.SearchView : myskins.view.Search;
		// Bind to the scroll event and throttle
		// the results from this.scroller
		this.window.bind( 'scroll', _.throttle( this.scroller, 300 ) );
	},

	// Main render control
	render: function() {
		// Setup the main myskin view
		// with the current myskin collection
		this.view = new myskins.view.MySkins({
			collection: this.collection,
			parent: this
		});

		// Render search form.
		this.search();

		this.$el.removeClass( 'search-loading' );

		// Render and append
		this.view.render();
		this.$el.empty().append( this.view.el ).addClass( 'rendered' );
	},

	// Defines search element container
	searchContainer: $( '.search-form' ),

	// Search input and view
	// for current myskin collection
	search: function() {
		var view,
			self = this;

		// Don't render the search if there is only one myskin
		if ( myskins.data.myskins.length === 1 ) {
			return;
		}

		view = new this.SearchView({
			collection: self.collection,
			parent: this
		});
		self.SearchView = view;

		// Render and append after screen title
		view.render();
		this.searchContainer
			.append( $.parseHTML( '<label class="screen-reader-text" for="mcms-filter-search-input">' + l10n.search + '</label>' ) )
			.append( view.el )
			.on( 'submit', function( event ) {
				event.preventDefault();
			});
	},

	// Checks when the user gets close to the bottom
	// of the mage and triggers a myskin:scroll event
	scroller: function() {
		var self = this,
			bottom, threshold;

		bottom = this.window.scrollTop() + self.window.height();
		threshold = self.$el.offset().top + self.$el.outerHeight( false ) - self.window.height();
		threshold = Math.round( threshold * 0.9 );

		if ( bottom > threshold ) {
			this.trigger( 'myskin:scroll' );
		}
	}
});

// Set up the Collection for our myskin data
// @has 'id' 'name' 'screenshot' 'author' 'authorURI' 'version' 'active' ...
myskins.Collection = Backbone.Collection.extend({

	model: myskins.Model,

	// Search terms
	terms: '',

	// Controls searching on the current myskin collection
	// and triggers an update event
	doSearch: function( value ) {

		// Don't do anything if we've already done this search
		// Useful because the Search handler fires multiple times per keystroke
		if ( this.terms === value ) {
			return;
		}

		// Updates terms with the value passed
		this.terms = value;

		// If we have terms, run a search...
		if ( this.terms.length > 0 ) {
			this.search( this.terms );
		}

		// If search is blank, show all myskins
		// Useful for resetting the views when you clean the input
		if ( this.terms === '' ) {
			this.reset( myskins.data.myskins );
			$( 'body' ).removeClass( 'no-results' );
		}

		// Trigger a 'myskins:update' event
		this.trigger( 'myskins:update' );
	},

	// Performs a search within the collection
	// @uses RegExp
	search: function( term ) {
		var match, results, haystack, name, description, author;

		// Start with a full collection
		this.reset( myskins.data.myskins, { silent: true } );

		// Escape the term string for RegExp meta characters
		term = term.replace( /[-\/\\^$*+?.()|[\]{}]/g, '\\$&' );

		// Consider spaces as word delimiters and match the whole string
		// so matching terms can be combined
		term = term.replace( / /g, ')(?=.*' );
		match = new RegExp( '^(?=.*' + term + ').+', 'i' );

		// Find results
		// _.filter and .test
		results = this.filter( function( data ) {
			name        = data.get( 'name' ).replace( /(<([^>]+)>)/ig, '' );
			description = data.get( 'description' ).replace( /(<([^>]+)>)/ig, '' );
			author      = data.get( 'author' ).replace( /(<([^>]+)>)/ig, '' );

			haystack = _.union( [ name, data.get( 'id' ), description, author, data.get( 'tags' ) ] );

			if ( match.test( data.get( 'author' ) ) && term.length > 2 ) {
				data.set( 'displayAuthor', true );
			}

			return match.test( haystack );
		});

		if ( results.length === 0 ) {
			this.trigger( 'query:empty' );
		} else {
			$( 'body' ).removeClass( 'no-results' );
		}

		this.reset( results );
	},

	// Paginates the collection with a helper method
	// that slices the collection
	paginate: function( instance ) {
		var collection = this;
		instance = instance || 0;

		// MySkins per instance are set at 20
		collection = _( collection.rest( 20 * instance ) );
		collection = _( collection.first( 20 ) );

		return collection;
	},

	count: false,

	// Handles requests for more myskins
	// and caches results
	//
	// When we are missing a cache object we fire an apiCall()
	// which triggers events of `query:success` or `query:fail`
	query: function( request ) {
		/**
		 * @static
		 * @type Array
		 */
		var queries = this.queries,
			self = this,
			query, isPaginated, count;

		// Store current query request args
		// for later use with the event `myskin:end`
		this.currentQuery.request = request;

		// Search the query cache for matches.
		query = _.find( queries, function( query ) {
			return _.isEqual( query.request, request );
		});

		// If the request matches the stored currentQuery.request
		// it means we have a paginated request.
		isPaginated = _.has( request, 'page' );

		// Reset the internal api page counter for non paginated queries.
		if ( ! isPaginated ) {
			this.currentQuery.page = 1;
		}

		// Otherwise, send a new API call and add it to the cache.
		if ( ! query && ! isPaginated ) {
			query = this.apiCall( request ).done( function( data ) {

				// Update the collection with the queried data.
				if ( data.myskins ) {
					self.reset( data.myskins );
					count = data.info.results;
					// Store the results and the query request
					queries.push( { myskins: data.myskins, request: request, total: count } );
				}

				// Trigger a collection refresh event
				// and a `query:success` event with a `count` argument.
				self.trigger( 'myskins:update' );
				self.trigger( 'query:success', count );

				if ( data.myskins && data.myskins.length === 0 ) {
					self.trigger( 'query:empty' );
				}

			}).fail( function() {
				self.trigger( 'query:fail' );
			});
		} else {
			// If it's a paginated request we need to fetch more myskins...
			if ( isPaginated ) {
				return this.apiCall( request, isPaginated ).done( function( data ) {
					// Add the new myskins to the current collection
					// @todo update counter
					self.add( data.myskins );
					self.trigger( 'query:success' );

					// We are done loading myskins for now.
					self.loadingMySkins = false;

				}).fail( function() {
					self.trigger( 'query:fail' );
				});
			}

			if ( query.myskins.length === 0 ) {
				self.trigger( 'query:empty' );
			} else {
				$( 'body' ).removeClass( 'no-results' );
			}

			// Only trigger an update event since we already have the myskins
			// on our cached object
			if ( _.isNumber( query.total ) ) {
				this.count = query.total;
			}

			this.reset( query.myskins );
			if ( ! query.total ) {
				this.count = this.length;
			}

			this.trigger( 'myskins:update' );
			this.trigger( 'query:success', this.count );
		}
	},

	// Local cache array for API queries
	queries: [],

	// Keep track of current query so we can handle pagination
	currentQuery: {
		page: 1,
		request: {}
	},

	// Send request to api.mandarincms.com/myskins
	apiCall: function( request, paginated ) {
		return mcms.ajax.send( 'query-myskins', {
			data: {
			// Request data
				request: _.extend({
					per_page: 100,
					fields: {
						description: true,
						tested: true,
						requires: true,
						rating: true,
						downloaded: true,
						downloadLink: true,
						last_updated: true,
						homepage: true,
						num_ratings: true
					}
				}, request)
			},

			beforeSend: function() {
				if ( ! paginated ) {
					// Spin it
					$( 'body' ).addClass( 'loading-content' ).removeClass( 'no-results' );
				}
			}
		});
	},

	// Static status controller for when we are loading myskins.
	loadingMySkins: false
});

// This is the view that controls each myskin item
// that will be displayed on the screen
myskins.view.MySkin = mcms.Backbone.View.extend({

	// Wrap myskin data on a div.myskin element
	className: 'myskin',

	// Reflects which myskin view we have
	// 'grid' (default) or 'detail'
	state: 'grid',

	// The HTML template for each element to be rendered
	html: myskins.template( 'myskin' ),

	events: {
		'click': myskins.isInstall ? 'preview': 'expand',
		'keydown': myskins.isInstall ? 'preview': 'expand',
		'touchend': myskins.isInstall ? 'preview': 'expand',
		'keyup': 'addFocus',
		'touchmove': 'preventExpand',
		'click .myskin-install': 'installMySkin',
		'click .update-message': 'updateMySkin'
	},

	touchDrag: false,

	initialize: function() {
		this.model.on( 'change', this.render, this );
	},

	render: function() {
		var data = this.model.toJSON();

		// Render myskins using the html template
		this.$el.html( this.html( data ) ).attr({
			tabindex: 0,
			'aria-describedby' : data.id + '-action ' + data.id + '-name',
			'data-slug': data.id
		});

		// Renders active myskin styles
		this.activeMySkin();

		if ( this.model.get( 'displayAuthor' ) ) {
			this.$el.addClass( 'display-author' );
		}
	},

	// Adds a class to the currently active myskin
	// and to the overlay in detailed view mode
	activeMySkin: function() {
		if ( this.model.get( 'active' ) ) {
			this.$el.addClass( 'active' );
		}
	},

	// Add class of focus to the myskin we are focused on.
	addFocus: function() {
		var $myskinToFocus = ( $( ':focus' ).hasClass( 'myskin' ) ) ? $( ':focus' ) : $(':focus').parents('.myskin');

		$('.myskin.focus').removeClass('focus');
		$myskinToFocus.addClass('focus');
	},

	// Single myskin overlay screen
	// It's shown when clicking a myskin
	expand: function( event ) {
		var self = this;

		event = event || window.event;

		// 'enter' and 'space' keys expand the details view when a myskin is :focused
		if ( event.type === 'keydown' && ( event.which !== 13 && event.which !== 32 ) ) {
			return;
		}

		// Bail if the user scrolled on a touch device
		if ( this.touchDrag === true ) {
			return this.touchDrag = false;
		}

		// Prevent the modal from showing when the user clicks
		// one of the direct action buttons
		if ( $( event.target ).is( '.myskin-actions a' ) ) {
			return;
		}

		// Prevent the modal from showing when the user clicks one of the direct action buttons.
		if ( $( event.target ).is( '.myskin-actions a, .update-message, .button-link, .notice-dismiss' ) ) {
			return;
		}

		// Set focused myskin to current element
		myskins.focusedMySkin = this.$el;

		this.trigger( 'myskin:expand', self.model.cid );
	},

	preventExpand: function() {
		this.touchDrag = true;
	},

	preview: function( event ) {
		var self = this,
			current, preview;

		event = event || window.event;

		// Bail if the user scrolled on a touch device
		if ( this.touchDrag === true ) {
			return this.touchDrag = false;
		}

		// Allow direct link path to installing a myskin.
		if ( $( event.target ).not( '.install-myskin-preview' ).parents( '.myskin-actions' ).length ) {
			return;
		}

		// 'enter' and 'space' keys expand the details view when a myskin is :focused
		if ( event.type === 'keydown' && ( event.which !== 13 && event.which !== 32 ) ) {
			return;
		}

		// pressing enter while focused on the buttons shouldn't open the preview
		if ( event.type === 'keydown' && event.which !== 13 && $( ':focus' ).hasClass( 'button' ) ) {
			return;
		}

		event.preventDefault();

		event = event || window.event;

		// Set focus to current myskin.
		myskins.focusedMySkin = this.$el;

		// Construct a new Preview view.
		myskins.preview = preview = new myskins.view.Preview({
			model: this.model
		});

		// Render the view and append it.
		preview.render();
		this.setNavButtonsState();

		// Hide previous/next navigation if there is only one myskin
		if ( this.model.collection.length === 1 ) {
			preview.$el.addClass( 'no-navigation' );
		} else {
			preview.$el.removeClass( 'no-navigation' );
		}

		// Append preview
		$( 'div.wrap' ).append( preview.el );

		// Listen to our preview object
		// for `myskin:next` and `myskin:previous` events.
		this.listenTo( preview, 'myskin:next', function() {

			// Keep local track of current myskin model.
			current = self.model;

			// If we have ventured away from current model update the current model position.
			if ( ! _.isUndefined( self.current ) ) {
				current = self.current;
			}

			// Get next myskin model.
			self.current = self.model.collection.at( self.model.collection.indexOf( current ) + 1 );

			// If we have no more myskins, bail.
			if ( _.isUndefined( self.current ) ) {
				self.options.parent.parent.trigger( 'myskin:end' );
				return self.current = current;
			}

			preview.model = self.current;

			// Render and append.
			preview.render();
			this.setNavButtonsState();
			$( '.next-myskin' ).focus();
		})
		.listenTo( preview, 'myskin:previous', function() {

			// Keep track of current myskin model.
			current = self.model;

			// Bail early if we are at the beginning of the collection
			if ( self.model.collection.indexOf( self.current ) === 0 ) {
				return;
			}

			// If we have ventured away from current model update the current model position.
			if ( ! _.isUndefined( self.current ) ) {
				current = self.current;
			}

			// Get previous myskin model.
			self.current = self.model.collection.at( self.model.collection.indexOf( current ) - 1 );

			// If we have no more myskins, bail.
			if ( _.isUndefined( self.current ) ) {
				return;
			}

			preview.model = self.current;

			// Render and append.
			preview.render();
			this.setNavButtonsState();
			$( '.previous-myskin' ).focus();
		});

		this.listenTo( preview, 'preview:close', function() {
			self.current = self.model;
		});

	},

	// Handles .disabled classes for previous/next buttons in myskin installer preview
	setNavButtonsState: function() {
		var $myskinInstaller = $( '.myskin-install-overlay' ),
			current = _.isUndefined( this.current ) ? this.model : this.current;

		// Disable previous at the zero position
		if ( 0 === this.model.collection.indexOf( current ) ) {
			$myskinInstaller.find( '.previous-myskin' ).addClass( 'disabled' );
		}

		// Disable next if the next model is undefined
		if ( _.isUndefined( this.model.collection.at( this.model.collection.indexOf( current ) + 1 ) ) ) {
			$myskinInstaller.find( '.next-myskin' ).addClass( 'disabled' );
		}
	},

	installMySkin: function( event ) {
		var _this = this;

		event.preventDefault();

		mcms.updates.maybeRequestFilesystemCredentials( event );

		$( document ).on( 'mcms-myskin-install-success', function( event, response ) {
			if ( _this.model.get( 'id' ) === response.slug ) {
				_this.model.set( { 'installed': true } );
			}
		} );

		mcms.updates.installMySkin( {
			slug: $( event.target ).data( 'slug' )
		} );
	},

	updateMySkin: function( event ) {
		var _this = this;

		if ( ! this.model.get( 'hasPackage' ) ) {
			return;
		}

		event.preventDefault();

		mcms.updates.maybeRequestFilesystemCredentials( event );

		$( document ).on( 'mcms-myskin-update-success', function( event, response ) {
			_this.model.off( 'change', _this.render, _this );
			if ( _this.model.get( 'id' ) === response.slug ) {
				_this.model.set( {
					hasUpdate: false,
					version: response.newVersion
				} );
			}
			_this.model.on( 'change', _this.render, _this );
		} );

		mcms.updates.updateMySkin( {
			slug: $( event.target ).parents( 'div.myskin' ).first().data( 'slug' )
		} );
	}
});

// MySkin Details view
// Set ups a modal overlay with the expanded myskin data
myskins.view.Details = mcms.Backbone.View.extend({

	// Wrap myskin data on a div.myskin element
	className: 'myskin-overlay',

	events: {
		'click': 'collapse',
		'click .delete-myskin': 'deleteMySkin',
		'click .left': 'previousMySkin',
		'click .right': 'nextMySkin',
		'click #update-myskin': 'updateMySkin'
	},

	// The HTML template for the myskin overlay
	html: myskins.template( 'myskin-single' ),

	render: function() {
		var data = this.model.toJSON();
		this.$el.html( this.html( data ) );
		// Renders active myskin styles
		this.activeMySkin();
		// Set up navigation events
		this.navigation();
		// Checks screenshot size
		this.screenshotCheck( this.$el );
		// Contain "tabbing" inside the overlay
		this.containFocus( this.$el );
	},

	// Adds a class to the currently active myskin
	// and to the overlay in detailed view mode
	activeMySkin: function() {
		// Check the model has the active property
		this.$el.toggleClass( 'active', this.model.get( 'active' ) );
	},

	// Set initial focus and constrain tabbing within the myskin browser modal.
	containFocus: function( $el ) {

		// Set initial focus on the primary action control.
		_.delay( function() {
			$( '.myskin-overlay' ).focus();
		}, 100 );

		// Constrain tabbing within the modal.
		$el.on( 'keydown.mcms-myskins', function( event ) {
			var $firstFocusable = $el.find( '.myskin-header button:not(.disabled)' ).first(),
				$lastFocusable = $el.find( '.myskin-actions a:visible' ).last();

			// Check for the Tab key.
			if ( 9 === event.which ) {
				if ( $firstFocusable[0] === event.target && event.shiftKey ) {
					$lastFocusable.focus();
					event.preventDefault();
				} else if ( $lastFocusable[0] === event.target && ! event.shiftKey ) {
					$firstFocusable.focus();
					event.preventDefault();
				}
			}
		});
	},

	// Single myskin overlay screen
	// It's shown when clicking a myskin
	collapse: function( event ) {
		var self = this,
			scroll;

		event = event || window.event;

		// Prevent collapsing detailed view when there is only one myskin available
		if ( myskins.data.myskins.length === 1 ) {
			return;
		}

		// Detect if the click is inside the overlay
		// and don't close it unless the target was
		// the div.back button
		if ( $( event.target ).is( '.myskin-backdrop' ) || $( event.target ).is( '.close' ) || event.keyCode === 27 ) {

			// Add a temporary closing class while overlay fades out
			$( 'body' ).addClass( 'closing-overlay' );

			// With a quick fade out animation
			this.$el.fadeOut( 130, function() {
				// Clicking outside the modal box closes the overlay
				$( 'body' ).removeClass( 'closing-overlay' );
				// Handle event cleanup
				self.closeOverlay();

				// Get scroll position to avoid jumping to the top
				scroll = document.body.scrollTop;

				// Clean the url structure
				myskins.router.navigate( myskins.router.baseUrl( '' ) );

				// Restore scroll position
				document.body.scrollTop = scroll;

				// Return focus to the myskin div
				if ( myskins.focusedMySkin ) {
					myskins.focusedMySkin.focus();
				}
			});
		}
	},

	// Handles .disabled classes for next/previous buttons
	navigation: function() {

		// Disable Left/Right when at the start or end of the collection
		if ( this.model.cid === this.model.collection.at(0).cid ) {
			this.$el.find( '.left' )
				.addClass( 'disabled' )
				.prop( 'disabled', true );
		}
		if ( this.model.cid === this.model.collection.at( this.model.collection.length - 1 ).cid ) {
			this.$el.find( '.right' )
				.addClass( 'disabled' )
				.prop( 'disabled', true );
		}
	},

	// Performs the actions to effectively close
	// the myskin details overlay
	closeOverlay: function() {
		$( 'body' ).removeClass( 'modal-open' );
		this.remove();
		this.unbind();
		this.trigger( 'myskin:collapse' );
	},

	updateMySkin: function( event ) {
		var _this = this;
		event.preventDefault();

		mcms.updates.maybeRequestFilesystemCredentials( event );

		$( document ).on( 'mcms-myskin-update-success', function( event, response ) {
			if ( _this.model.get( 'id' ) === response.slug ) {
				_this.model.set( {
					hasUpdate: false,
					version: response.newVersion
				} );
			}
			_this.render();
		} );

		mcms.updates.updateMySkin( {
			slug: $( event.target ).data( 'slug' )
		} );
	},

	deleteMySkin: function( event ) {
		var _this = this,
		    _collection = _this.model.collection,
		    _myskins = myskins;
		event.preventDefault();

		// Confirmation dialog for deleting a myskin.
		if ( ! window.confirm( mcms.myskins.data.settings.confirmDelete ) ) {
			return;
		}

		mcms.updates.maybeRequestFilesystemCredentials( event );

		$( document ).one( 'mcms-myskin-delete-success', function( event, response ) {
			_this.$el.find( '.close' ).trigger( 'click' );
			$( '[data-slug="' + response.slug + '"]' ).css( { backgroundColor:'#faafaa' } ).fadeOut( 350, function() {
				$( this ).remove();
				_myskins.data.myskins = _.without( _myskins.data.myskins, _.findWhere( _myskins.data.myskins, { id: response.slug } ) );

				$( '.mcms-filter-search' ).val( '' );
				_collection.doSearch( '' );
				_collection.remove( _this.model );
				_collection.trigger( 'myskins:update' );
			} );
		} );

		mcms.updates.deleteMySkin( {
			slug: this.model.get( 'id' )
		} );
	},

	nextMySkin: function() {
		var self = this;
		self.trigger( 'myskin:next', self.model.cid );
		return false;
	},

	previousMySkin: function() {
		var self = this;
		self.trigger( 'myskin:previous', self.model.cid );
		return false;
	},

	// Checks if the myskin screenshot is the old 300px width version
	// and adds a corresponding class if it's true
	screenshotCheck: function( el ) {
		var screenshot, image;

		screenshot = el.find( '.screenshot img' );
		image = new Image();
		image.src = screenshot.attr( 'src' );

		// Width check
		if ( image.width && image.width <= 300 ) {
			el.addClass( 'small-screenshot' );
		}
	}
});

// MySkin Preview view
// Set ups a modal overlay with the expanded myskin data
myskins.view.Preview = myskins.view.Details.extend({

	className: 'mcms-full-overlay expanded',
	el: '.myskin-install-overlay',

	events: {
		'click .close-full-overlay': 'close',
		'click .collapse-sidebar': 'collapse',
		'click .devices button': 'previewDevice',
		'click .previous-myskin': 'previousMySkin',
		'click .next-myskin': 'nextMySkin',
		'keyup': 'keyEvent',
		'click .myskin-install': 'installMySkin'
	},

	// The HTML template for the myskin preview
	html: myskins.template( 'myskin-preview' ),

	render: function() {
		var self = this,
			currentPreviewDevice,
			data = this.model.toJSON(),
			$body = $( document.body );

		$body.attr( 'aria-busy', 'true' );

		this.$el.removeClass( 'iframe-ready' ).html( this.html( data ) );

		currentPreviewDevice = this.$el.data( 'current-preview-device' );
		if ( currentPreviewDevice ) {
			self.tooglePreviewDeviceButtons( currentPreviewDevice );
		}

		myskins.router.navigate( myskins.router.baseUrl( myskins.router.myskinPath + this.model.get( 'id' ) ), { replace: false } );

		this.$el.fadeIn( 200, function() {
			$body.addClass( 'myskin-installer-active full-overlay-active' );
		});

		this.$el.find( 'iframe' ).one( 'load', function() {
			self.iframeLoaded();
		});
	},

	iframeLoaded: function() {
		this.$el.addClass( 'iframe-ready' );
		$( document.body ).attr( 'aria-busy', 'false' );
	},

	close: function() {
		this.$el.fadeOut( 200, function() {
			$( 'body' ).removeClass( 'myskin-installer-active full-overlay-active' );

			// Return focus to the myskin div
			if ( myskins.focusedMySkin ) {
				myskins.focusedMySkin.focus();
			}
		}).removeClass( 'iframe-ready' );

		// Restore the previous browse tab if available.
		if ( myskins.router.selectedTab ) {
			myskins.router.navigate( myskins.router.baseUrl( '?browse=' + myskins.router.selectedTab ) );
			myskins.router.selectedTab = false;
		} else {
			myskins.router.navigate( myskins.router.baseUrl( '' ) );
		}
		this.trigger( 'preview:close' );
		this.undelegateEvents();
		this.unbind();
		return false;
	},

	collapse: function( event ) {
		var $button = $( event.currentTarget );
		if ( 'true' === $button.attr( 'aria-expanded' ) ) {
			$button.attr({ 'aria-expanded': 'false', 'aria-label': l10n.expandSidebar });
		} else {
			$button.attr({ 'aria-expanded': 'true', 'aria-label': l10n.collapseSidebar });
		}

		this.$el.toggleClass( 'collapsed' ).toggleClass( 'expanded' );
		return false;
	},

	previewDevice: function( event ) {
		var device = $( event.currentTarget ).data( 'device' );

		this.$el
			.removeClass( 'preview-desktop preview-tablet preview-mobile' )
			.addClass( 'preview-' + device )
			.data( 'current-preview-device', device );

		this.tooglePreviewDeviceButtons( device );
	},

	tooglePreviewDeviceButtons: function( newDevice ) {
		var $devices = $( '.mcms-full-overlay-footer .devices' );

		$devices.find( 'button' )
			.removeClass( 'active' )
			.attr( 'aria-pressed', false );

		$devices.find( 'button.preview-' + newDevice )
			.addClass( 'active' )
			.attr( 'aria-pressed', true );
	},

	keyEvent: function( event ) {
		// The escape key closes the preview
		if ( event.keyCode === 27 ) {
			this.undelegateEvents();
			this.close();
		}
		// The right arrow key, next myskin
		if ( event.keyCode === 39 ) {
			_.once( this.nextMySkin() );
		}

		// The left arrow key, previous myskin
		if ( event.keyCode === 37 ) {
			this.previousMySkin();
		}
	},

	installMySkin: function( event ) {
		var _this   = this,
		    $target = $( event.target );
		event.preventDefault();

		if ( $target.hasClass( 'disabled' ) ) {
			return;
		}

		mcms.updates.maybeRequestFilesystemCredentials( event );

		$( document ).on( 'mcms-myskin-install-success', function() {
			_this.model.set( { 'installed': true } );
		} );

		mcms.updates.installMySkin( {
			slug: $target.data( 'slug' )
		} );
	}
});

// Controls the rendering of div.myskins,
// a wrapper that will hold all the myskin elements
myskins.view.MySkins = mcms.Backbone.View.extend({

	className: 'myskins mcms-clearfix',
	$overlay: $( 'div.myskin-overlay' ),

	// Number to keep track of scroll position
	// while in myskin-overlay mode
	index: 0,

	// The myskin count element
	count: $( '.wrap .myskin-count' ),

	// The live myskins count
	liveMySkinCount: 0,

	initialize: function( options ) {
		var self = this;

		// Set up parent
		this.parent = options.parent;

		// Set current view to [grid]
		this.setView( 'grid' );

		// Move the active myskin to the beginning of the collection
		self.currentMySkin();

		// When the collection is updated by user input...
		this.listenTo( self.collection, 'myskins:update', function() {
			self.parent.page = 0;
			self.currentMySkin();
			self.render( this );
		} );

		// Update myskin count to full result set when available.
		this.listenTo( self.collection, 'query:success', function( count ) {
			if ( _.isNumber( count ) ) {
				self.count.text( count );
				self.announceSearchResults( count );
			} else {
				self.count.text( self.collection.length );
				self.announceSearchResults( self.collection.length );
			}
		});

		this.listenTo( self.collection, 'query:empty', function() {
			$( 'body' ).addClass( 'no-results' );
		});

		this.listenTo( this.parent, 'myskin:scroll', function() {
			self.renderMySkins( self.parent.page );
		});

		this.listenTo( this.parent, 'myskin:close', function() {
			if ( self.overlay ) {
				self.overlay.closeOverlay();
			}
		} );

		// Bind keyboard events.
		$( 'body' ).on( 'keyup', function( event ) {
			if ( ! self.overlay ) {
				return;
			}

			// Bail if the filesystem credentials dialog is shown.
			if ( $( '#request-filesystem-credentials-dialog' ).is( ':visible' ) ) {
				return;
			}

			// Pressing the right arrow key fires a myskin:next event
			if ( event.keyCode === 39 ) {
				self.overlay.nextMySkin();
			}

			// Pressing the left arrow key fires a myskin:previous event
			if ( event.keyCode === 37 ) {
				self.overlay.previousMySkin();
			}

			// Pressing the escape key fires a myskin:collapse event
			if ( event.keyCode === 27 ) {
				self.overlay.collapse( event );
			}
		});
	},

	// Manages rendering of myskin pages
	// and keeping myskin count in sync
	render: function() {
		// Clear the DOM, please
		this.$el.empty();

		// If the user doesn't have switch capabilities
		// or there is only one myskin in the collection
		// render the detailed view of the active myskin
		if ( myskins.data.myskins.length === 1 ) {

			// Constructs the view
			this.singleMySkin = new myskins.view.Details({
				model: this.collection.models[0]
			});

			// Render and apply a 'single-myskin' class to our container
			this.singleMySkin.render();
			this.$el.addClass( 'single-myskin' );
			this.$el.append( this.singleMySkin.el );
		}

		// Generate the myskins
		// Using page instance
		// While checking the collection has items
		if ( this.options.collection.size() > 0 ) {
			this.renderMySkins( this.parent.page );
		}

		// Display a live myskin count for the collection
		this.liveMySkinCount = this.collection.count ? this.collection.count : this.collection.length;
		this.count.text( this.liveMySkinCount );

		/*
		 * In the myskin installer the myskins count is already announced
		 * because `announceSearchResults` is called on `query:success`.
		 */
		if ( ! myskins.isInstall ) {
			this.announceSearchResults( this.liveMySkinCount );
		}
	},

	// Iterates through each instance of the collection
	// and renders each myskin module
	renderMySkins: function( page ) {
		var self = this;

		self.instance = self.collection.paginate( page );

		// If we have no more myskins bail
		if ( self.instance.size() === 0 ) {
			// Fire a no-more-myskins event.
			this.parent.trigger( 'myskin:end' );
			return;
		}

		// Make sure the add-new stays at the end
		if ( ! myskins.isInstall && page >= 1 ) {
			$( '.add-new-myskin' ).remove();
		}

		// Loop through the myskins and setup each myskin view
		self.instance.each( function( myskin ) {
			self.myskin = new myskins.view.MySkin({
				model: myskin,
				parent: self
			});

			// Render the views...
			self.myskin.render();
			// and append them to div.myskins
			self.$el.append( self.myskin.el );

			// Binds to myskin:expand to show the modal box
			// with the myskin details
			self.listenTo( self.myskin, 'myskin:expand', self.expand, self );
		});

		// 'Add new myskin' element shown at the end of the grid
		if ( ! myskins.isInstall && myskins.data.settings.canInstall ) {
			this.$el.append( '<div class="myskin add-new-myskin"><a href="' + myskins.data.settings.installURI + '"><div class="myskin-screenshot"><span></span></div><h2 class="myskin-name">' + l10n.addNew + '</h2></a></div>' );
		}

		this.parent.page++;
	},

	// Grabs current myskin and puts it at the beginning of the collection
	currentMySkin: function() {
		var self = this,
			current;

		current = self.collection.findWhere({ active: true });

		// Move the active myskin to the beginning of the collection
		if ( current ) {
			self.collection.remove( current );
			self.collection.add( current, { at:0 } );
		}
	},

	// Sets current view
	setView: function( view ) {
		return view;
	},

	// Renders the overlay with the MySkinDetails view
	// Uses the current model data
	expand: function( id ) {
		var self = this, $card, $modal;

		// Set the current myskin model
		this.model = self.collection.get( id );

		// Trigger a route update for the current model
		myskins.router.navigate( myskins.router.baseUrl( myskins.router.myskinPath + this.model.id ) );

		// Sets this.view to 'detail'
		this.setView( 'detail' );
		$( 'body' ).addClass( 'modal-open' );

		// Set up the myskin details view
		this.overlay = new myskins.view.Details({
			model: self.model
		});

		this.overlay.render();

		if ( this.model.get( 'hasUpdate' ) ) {
			$card  = $( '[data-slug="' + this.model.id + '"]' );
			$modal = $( this.overlay.el );

			if ( $card.find( '.updating-message' ).length ) {
				$modal.find( '.notice-warning h3' ).remove();
				$modal.find( '.notice-warning' )
					.removeClass( 'notice-large' )
					.addClass( 'updating-message' )
					.find( 'p' ).text( mcms.updates.l10n.updating );
			} else if ( $card.find( '.notice-error' ).length ) {
				$modal.find( '.notice-warning' ).remove();
			}
		}

		this.$overlay.html( this.overlay.el );

		// Bind to myskin:next and myskin:previous
		// triggered by the arrow keys
		//
		// Keep track of the current model so we
		// can infer an index position
		this.listenTo( this.overlay, 'myskin:next', function() {
			// Renders the next myskin on the overlay
			self.next( [ self.model.cid ] );

		})
		.listenTo( this.overlay, 'myskin:previous', function() {
			// Renders the previous myskin on the overlay
			self.previous( [ self.model.cid ] );
		});
	},

	// This method renders the next myskin on the overlay modal
	// based on the current position in the collection
	// @params [model cid]
	next: function( args ) {
		var self = this,
			model, nextModel;

		// Get the current myskin
		model = self.collection.get( args[0] );
		// Find the next model within the collection
		nextModel = self.collection.at( self.collection.indexOf( model ) + 1 );

		// Sanity check which also serves as a boundary test
		if ( nextModel !== undefined ) {

			// We have a new myskin...
			// Close the overlay
			this.overlay.closeOverlay();

			// Trigger a route update for the current model
			self.myskin.trigger( 'myskin:expand', nextModel.cid );

		}
	},

	// This method renders the previous myskin on the overlay modal
	// based on the current position in the collection
	// @params [model cid]
	previous: function( args ) {
		var self = this,
			model, previousModel;

		// Get the current myskin
		model = self.collection.get( args[0] );
		// Find the previous model within the collection
		previousModel = self.collection.at( self.collection.indexOf( model ) - 1 );

		if ( previousModel !== undefined ) {

			// We have a new myskin...
			// Close the overlay
			this.overlay.closeOverlay();

			// Trigger a route update for the current model
			self.myskin.trigger( 'myskin:expand', previousModel.cid );

		}
	},

	// Dispatch audible search results feedback message
	announceSearchResults: function( count ) {
		if ( 0 === count ) {
			mcms.a11y.speak( l10n.noMySkinsFound );
		} else {
			mcms.a11y.speak( l10n.myskinsFound.replace( '%d', count ) );
		}
	}
});

// Search input view controller.
myskins.view.Search = mcms.Backbone.View.extend({

	tagName: 'input',
	className: 'mcms-filter-search',
	id: 'mcms-filter-search-input',
	searching: false,

	attributes: {
		placeholder: l10n.searchPlaceholder,
		type: 'search',
		'aria-describedby': 'live-search-desc'
	},

	events: {
		'input': 'search',
		'keyup': 'search',
		'blur': 'pushState'
	},

	initialize: function( options ) {

		this.parent = options.parent;

		this.listenTo( this.parent, 'myskin:close', function() {
			this.searching = false;
		} );

	},

	search: function( event ) {
		// Clear on escape.
		if ( event.type === 'keyup' && event.which === 27 ) {
			event.target.value = '';
		}

		// Since doSearch is debounced, it will only run when user input comes to a rest.
		this.doSearch( event );
	},

	// Runs a search on the myskin collection.
	doSearch: function( event ) {
		var options = {};

		this.collection.doSearch( event.target.value.replace( /\+/g, ' ' ) );

		// if search is initiated and key is not return
		if ( this.searching && event.which !== 13 ) {
			options.replace = true;
		} else {
			this.searching = true;
		}

		// Update the URL hash
		if ( event.target.value ) {
			myskins.router.navigate( myskins.router.baseUrl( myskins.router.searchPath + event.target.value ), options );
		} else {
			myskins.router.navigate( myskins.router.baseUrl( '' ) );
		}
	},

	pushState: function( event ) {
		var url = myskins.router.baseUrl( '' );

		if ( event.target.value ) {
			url = myskins.router.baseUrl( myskins.router.searchPath + encodeURIComponent( event.target.value ) );
		}

		this.searching = false;
		myskins.router.navigate( url );

	}
});

/**
 * Navigate router.
 *
 * @since 4.9.0
 *
 * @param {string} url - URL to navigate to.
 * @param {object} state - State.
 * @returns {void}
 */
function navigateRouter( url, state ) {
	var router = this;
	if ( Backbone.history._hasPushState ) {
		Backbone.Router.prototype.navigate.call( router, url, state );
	}
}

// Sets up the routes events for relevant url queries
// Listens to [myskin] and [search] params
myskins.Router = Backbone.Router.extend({

	routes: {
		'myskins.php?myskin=:slug': 'myskin',
		'myskins.php?search=:query': 'search',
		'myskins.php?s=:query': 'search',
		'myskins.php': 'myskins',
		'': 'myskins'
	},

	baseUrl: function( url ) {
		return 'myskins.php' + url;
	},

	myskinPath: '?myskin=',
	searchPath: '?search=',

	search: function( query ) {
		$( '.mcms-filter-search' ).val( query.replace( /\+/g, ' ' ) );
	},

	myskins: function() {
		$( '.mcms-filter-search' ).val( '' );
	},

	navigate: navigateRouter

});

// Execute and setup the application
myskins.Run = {
	init: function() {
		// Initializes the blog's myskin library view
		// Create a new collection with data
		this.myskins = new myskins.Collection( myskins.data.myskins );

		// Set up the view
		this.view = new myskins.view.Dexign({
			collection: this.myskins
		});

		this.render();

		// Start debouncing user searches after Backbone.history.start().
		this.view.SearchView.doSearch = _.debounce( this.view.SearchView.doSearch, 500 );
	},

	render: function() {

		// Render results
		this.view.render();
		this.routes();

		if ( Backbone.History.started ) {
			Backbone.history.stop();
		}
		Backbone.history.start({
			root: myskins.data.settings.adminUrl,
			pushState: true,
			hashChange: false
		});
	},

	routes: function() {
		var self = this;
		// Bind to our global thx object
		// so that the object is available to sub-views
		myskins.router = new myskins.Router();

		// Handles myskin details route event
		myskins.router.on( 'route:myskin', function( slug ) {
			self.view.view.expand( slug );
		});

		myskins.router.on( 'route:myskins', function() {
			self.myskins.doSearch( '' );
			self.view.trigger( 'myskin:close' );
		});

		// Handles search route event
		myskins.router.on( 'route:search', function() {
			$( '.mcms-filter-search' ).trigger( 'keyup' );
		});

		this.extraRoutes();
	},

	extraRoutes: function() {
		return false;
	}
};

// Extend the main Search view
myskins.view.InstallerSearch =  myskins.view.Search.extend({

	events: {
		'input': 'search',
		'keyup': 'search'
	},

	terms: '',

	// Handles Ajax request for searching through myskins in public repo
	search: function( event ) {

		// Tabbing or reverse tabbing into the search input shouldn't trigger a search
		if ( event.type === 'keyup' && ( event.which === 9 || event.which === 16 ) ) {
			return;
		}

		this.collection = this.options.parent.view.collection;

		// Clear on escape.
		if ( event.type === 'keyup' && event.which === 27 ) {
			event.target.value = '';
		}

		this.doSearch( event.target.value );
	},

	doSearch: function( value ) {
		var request = {};

		// Don't do anything if the search terms haven't changed.
		if ( this.terms === value ) {
			return;
		}

		// Updates terms with the value passed.
		this.terms = value;

		request.search = value;

		// Intercept an [author] search.
		//
		// If input value starts with `author:` send a request
		// for `author` instead of a regular `search`
		if ( value.substring( 0, 7 ) === 'author:' ) {
			request.search = '';
			request.author = value.slice( 7 );
		}

		// Intercept a [tag] search.
		//
		// If input value starts with `tag:` send a request
		// for `tag` instead of a regular `search`
		if ( value.substring( 0, 4 ) === 'tag:' ) {
			request.search = '';
			request.tag = [ value.slice( 4 ) ];
		}

		$( '.filter-links li > a.current' )
			.removeClass( 'current' )
			.removeAttr( 'aria-current' );

		$( 'body' ).removeClass( 'show-filters filters-applied show-favorites-form' );
		$( '.drawer-toggle' ).attr( 'aria-expanded', 'false' );

		// Get the myskins by sending Ajax POST request to api.mandarincms.com/myskins
		// or searching the local cache
		this.collection.query( request );

		// Set route
		myskins.router.navigate( myskins.router.baseUrl( myskins.router.searchPath + encodeURIComponent( value ) ), { replace: true } );
	}
});

myskins.view.Installer = myskins.view.Dexign.extend({

	el: '#mcmsbody-content .wrap',

	// Register events for sorting and filters in myskin-navigation
	events: {
		'click .filter-links li > a': 'onSort',
		'click .myskin-filter': 'onFilter',
		'click .drawer-toggle': 'moreFilters',
		'click .filter-drawer .apply-filters': 'applyFilters',
		'click .filter-group [type="checkbox"]': 'addFilter',
		'click .filter-drawer .clear-filters': 'clearFilters',
		'click .edit-filters': 'backToFilters',
		'click .favorites-form-submit' : 'saveUsername',
		'keyup #mcmsorg-username-input': 'saveUsername'
	},

	// Initial render method
	render: function() {
		var self = this;

		this.search();
		this.uploader();

		this.collection = new myskins.Collection();

		// Bump `collection.currentQuery.page` and request more myskins if we hit the end of the page.
		this.listenTo( this, 'myskin:end', function() {

			// Make sure we are not already loading
			if ( self.collection.loadingMySkins ) {
				return;
			}

			// Set loadingMySkins to true and bump page instance of currentQuery.
			self.collection.loadingMySkins = true;
			self.collection.currentQuery.page++;

			// Use currentQuery.page to build the myskins request.
			_.extend( self.collection.currentQuery.request, { page: self.collection.currentQuery.page } );
			self.collection.query( self.collection.currentQuery.request );
		});

		this.listenTo( this.collection, 'query:success', function() {
			$( 'body' ).removeClass( 'loading-content' );
			$( '.myskin-browser' ).find( 'div.error' ).remove();
		});

		this.listenTo( this.collection, 'query:fail', function() {
			$( 'body' ).removeClass( 'loading-content' );
			$( '.myskin-browser' ).find( 'div.error' ).remove();
			$( '.myskin-browser' ).find( 'div.myskins' ).before( '<div class="error"><p>' + l10n.error + '</p><p><button class="button try-again">' + l10n.tryAgain + '</button></p></div>' );
			$( '.myskin-browser .error .try-again' ).on( 'click', function( e ) {
				e.preventDefault();
				$( 'input.mcms-filter-search' ).trigger( 'input' );
			} );
		});

		if ( this.view ) {
			this.view.remove();
		}

		// Set ups the view and passes the section argument
		this.view = new myskins.view.MySkins({
			collection: this.collection,
			parent: this
		});

		// Reset pagination every time the install view handler is run
		this.page = 0;

		// Render and append
		this.$el.find( '.myskins' ).remove();
		this.view.render();
		this.$el.find( '.myskin-browser' ).append( this.view.el ).addClass( 'rendered' );
	},

	// Handles all the rendering of the public myskin directory
	browse: function( section ) {
		// Create a new collection with the proper myskin data
		// for each section
		this.collection.query( { browse: section } );
	},

	// Sorting navigation
	onSort: function( event ) {
		var $el = $( event.target ),
			sort = $el.data( 'sort' );

		event.preventDefault();

		$( 'body' ).removeClass( 'filters-applied show-filters' );
		$( '.drawer-toggle' ).attr( 'aria-expanded', 'false' );

		// Bail if this is already active
		if ( $el.hasClass( this.activeClass ) ) {
			return;
		}

		this.sort( sort );

		// Trigger a router.naviagte update
		myskins.router.navigate( myskins.router.baseUrl( myskins.router.browsePath + sort ) );
	},

	sort: function( sort ) {
		this.clearSearch();

		// Track sorting so we can restore the correct tab when closing preview.
		myskins.router.selectedTab = sort;

		$( '.filter-links li > a, .myskin-filter' )
			.removeClass( this.activeClass )
			.removeAttr( 'aria-current' );

		$( '[data-sort="' + sort + '"]' )
			.addClass( this.activeClass )
			.attr( 'aria-current', 'page' );

		if ( 'favorites' === sort ) {
			$( 'body' ).addClass( 'show-favorites-form' );
		} else {
			$( 'body' ).removeClass( 'show-favorites-form' );
		}

		this.browse( sort );
	},

	// Filters and Tags
	onFilter: function( event ) {
		var request,
			$el = $( event.target ),
			filter = $el.data( 'filter' );

		// Bail if this is already active
		if ( $el.hasClass( this.activeClass ) ) {
			return;
		}

		$( '.filter-links li > a, .myskin-section' )
			.removeClass( this.activeClass )
			.removeAttr( 'aria-current' );
		$el
			.addClass( this.activeClass )
			.attr( 'aria-current', 'page' );

		if ( ! filter ) {
			return;
		}

		// Construct the filter request
		// using the default values
		filter = _.union( [ filter, this.filtersChecked() ] );
		request = { tag: [ filter ] };

		// Get the myskins by sending Ajax POST request to api.mandarincms.com/myskins
		// or searching the local cache
		this.collection.query( request );
	},

	// Clicking on a checkbox to add another filter to the request
	addFilter: function() {
		this.filtersChecked();
	},

	// Applying filters triggers a tag request
	applyFilters: function( event ) {
		var name,
			tags = this.filtersChecked(),
			request = { tag: tags },
			filteringBy = $( '.filtered-by .tags' );

		if ( event ) {
			event.preventDefault();
		}

		if ( ! tags ) {
			mcms.a11y.speak( l10n.selectFeatureFilter );
			return;
		}

		$( 'body' ).addClass( 'filters-applied' );
		$( '.filter-links li > a.current' )
			.removeClass( 'current' )
			.removeAttr( 'aria-current' );

		filteringBy.empty();

		_.each( tags, function( tag ) {
			name = $( 'label[for="filter-id-' + tag + '"]' ).text();
			filteringBy.append( '<span class="tag">' + name + '</span>' );
		});

		// Get the myskins by sending Ajax POST request to api.mandarincms.com/myskins
		// or searching the local cache
		this.collection.query( request );
	},

	// Save the user's MandarinCMS.org username and get his favorite myskins.
	saveUsername: function ( event ) {
		var username = $( '#mcmsorg-username-input' ).val(),
			nonce = $( '#mcmsorg-username-nonce' ).val(),
			request = { browse: 'favorites', user: username },
			that = this;

		if ( event ) {
			event.preventDefault();
		}

		// save username on enter
		if ( event.type === 'keyup' && event.which !== 13 ) {
			return;
		}

		return mcms.ajax.send( 'save-mcmsorg-username', {
			data: {
				_mcmsnonce: nonce,
				username: username
			},
			success: function () {
				// Get the myskins by sending Ajax POST request to api.mandarincms.com/myskins
				// or searching the local cache
				that.collection.query( request );
			}
		} );
	},

	// Get the checked filters
	// @return {array} of tags or false
	filtersChecked: function() {
		var items = $( '.filter-group' ).find( ':checkbox' ),
			tags = [];

		_.each( items.filter( ':checked' ), function( item ) {
			tags.push( $( item ).prop( 'value' ) );
		});

		// When no filters are checked, restore initial state and return
		if ( tags.length === 0 ) {
			$( '.filter-drawer .apply-filters' ).find( 'span' ).text( '' );
			$( '.filter-drawer .clear-filters' ).hide();
			$( 'body' ).removeClass( 'filters-applied' );
			return false;
		}

		$( '.filter-drawer .apply-filters' ).find( 'span' ).text( tags.length );
		$( '.filter-drawer .clear-filters' ).css( 'display', 'inline-block' );

		return tags;
	},

	activeClass: 'current',

	/*
	 * When users press the "Upload MySkin" button, show the upload form in place.
	 */
	uploader: function() {
		var uploadViewToggle = $( '.upload-view-toggle' ),
			$body = $( document.body );

		uploadViewToggle.on( 'click', function() {
			// Toggle the upload view.
			$body.toggleClass( 'show-upload-view' );
			// Toggle the `aria-expanded` button attribute.
			uploadViewToggle.attr( 'aria-expanded', $body.hasClass( 'show-upload-view' ) );
		});
	},

	// Toggle the full filters navigation
	moreFilters: function( event ) {
		var $body = $( 'body' ),
			$toggleButton = $( '.drawer-toggle' );

		event.preventDefault();

		if ( $body.hasClass( 'filters-applied' ) ) {
			return this.backToFilters();
		}

		this.clearSearch();

		myskins.router.navigate( myskins.router.baseUrl( '' ) );
		// Toggle the feature filters view.
		$body.toggleClass( 'show-filters' );
		// Toggle the `aria-expanded` button attribute.
		$toggleButton.attr( 'aria-expanded', $body.hasClass( 'show-filters' ) );
	},

	// Clears all the checked filters
	// @uses filtersChecked()
	clearFilters: function( event ) {
		var items = $( '.filter-group' ).find( ':checkbox' ),
			self = this;

		event.preventDefault();

		_.each( items.filter( ':checked' ), function( item ) {
			$( item ).prop( 'checked', false );
			return self.filtersChecked();
		});
	},

	backToFilters: function( event ) {
		if ( event ) {
			event.preventDefault();
		}

		$( 'body' ).removeClass( 'filters-applied' );
	},

	clearSearch: function() {
		$( '#mcms-filter-search-input').val( '' );
	}
});

myskins.InstallerRouter = Backbone.Router.extend({
	routes: {
		'myskin-install.php?myskin=:slug': 'preview',
		'myskin-install.php?browse=:sort': 'sort',
		'myskin-install.php?search=:query': 'search',
		'myskin-install.php': 'sort'
	},

	baseUrl: function( url ) {
		return 'myskin-install.php' + url;
	},

	myskinPath: '?myskin=',
	browsePath: '?browse=',
	searchPath: '?search=',

	search: function( query ) {
		$( '.mcms-filter-search' ).val( query.replace( /\+/g, ' ' ) );
	},

	navigate: navigateRouter
});


myskins.RunInstaller = {

	init: function() {
		// Set up the view
		// Passes the default 'section' as an option
		this.view = new myskins.view.Installer({
			section: 'featured',
			SearchView: myskins.view.InstallerSearch
		});

		// Render results
		this.render();

		// Start debouncing user searches after Backbone.history.start().
		this.view.SearchView.doSearch = _.debounce( this.view.SearchView.doSearch, 500 );
	},

	render: function() {

		// Render results
		this.view.render();
		this.routes();

		if ( Backbone.History.started ) {
			Backbone.history.stop();
		}
		Backbone.history.start({
			root: myskins.data.settings.adminUrl,
			pushState: true,
			hashChange: false
		});
	},

	routes: function() {
		var self = this,
			request = {};

		// Bind to our global `mcms.myskins` object
		// so that the router is available to sub-views
		myskins.router = new myskins.InstallerRouter();

		// Handles `myskin` route event
		// Queries the API for the passed myskin slug
		myskins.router.on( 'route:preview', function( slug ) {

			// Remove existing handlers.
			if ( myskins.preview ) {
				myskins.preview.undelegateEvents();
				myskins.preview.unbind();
			}

			// If the myskin preview is active, set the current myskin.
			if ( self.view.view.myskin && self.view.view.myskin.preview ) {
				self.view.view.myskin.model = self.view.collection.findWhere( { 'slug': slug } );
				self.view.view.myskin.preview();
			} else {

				// Select the myskin by slug.
				request.myskin = slug;
				self.view.collection.query( request );
				self.view.collection.trigger( 'update' );

				// Open the myskin preview.
				self.view.collection.once( 'query:success', function() {
					$( 'div[data-slug="' + slug + '"]' ).trigger( 'click' );
				});

			}
		});

		// Handles sorting / browsing routes
		// Also handles the root URL triggering a sort request
		// for `featured`, the default view
		myskins.router.on( 'route:sort', function( sort ) {
			if ( ! sort ) {
				sort = 'featured';
				myskins.router.navigate( myskins.router.baseUrl( '?browse=featured' ), { replace: true } );
			}
			self.view.sort( sort );

			// Close the preview if open.
			if ( myskins.preview ) {
				myskins.preview.close();
			}
		});

		// The `search` route event. The router populates the input field.
		myskins.router.on( 'route:search', function() {
			$( '.mcms-filter-search' ).focus().trigger( 'keyup' );
		});

		this.extraRoutes();
	},

	extraRoutes: function() {
		return false;
	}
};

// Ready...
$( document ).ready(function() {
	if ( myskins.isInstall ) {
		myskins.RunInstaller.init();
	} else {
		myskins.Run.init();
	}

	// Update the return param just in time.
	$( document.body ).on( 'click', '.load-customize', function() {
		var link = $( this ), urlParser = document.createElement( 'a' );
		urlParser.href = link.prop( 'href' );
		urlParser.search = $.param( _.extend(
			mcms.customize.utils.parseQueryString( urlParser.search.substr( 1 ) ),
			{
				'return': window.location.href
			}
		) );
		link.prop( 'href', urlParser.href );
	});

	$( '.broken-myskins .delete-myskin' ).on( 'click', function() {
		return confirm( _mcmsMySkinSettings.settings.confirmDelete );
	});
});

})( jQuery );

// Align myskin browser thickbox
var tb_position;
jQuery(document).ready( function($) {
	tb_position = function() {
		var tbWindow = $('#TB_window'),
			width = $(window).width(),
			H = $(window).height(),
			W = ( 1040 < width ) ? 1040 : width,
			adminbar_height = 0;

		if ( $('#mcmsadminbar').length ) {
			adminbar_height = parseInt( $('#mcmsadminbar').css('height'), 10 );
		}

		if ( tbWindow.size() ) {
			tbWindow.width( W - 50 ).height( H - 45 - adminbar_height );
			$('#TB_iframeContent').width( W - 50 ).height( H - 75 - adminbar_height );
			tbWindow.css({'margin-left': '-' + parseInt( ( ( W - 50 ) / 2 ), 10 ) + 'px'});
			if ( typeof document.body.style.maxWidth !== 'undefined' ) {
				tbWindow.css({'top': 20 + adminbar_height + 'px', 'margin-top': '0'});
			}
		}
	};

	$(window).resize(function(){ tb_position(); });
});

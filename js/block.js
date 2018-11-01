( function( wp ) {
	/**
	 * Registers a new block provided a unique name and an object defining its behavior.
	 * @see https://github.com/WordPress/gutenberg/tree/master/blocks#api
	 */
	var registerBlockType = wp.blocks.registerBlockType;
	/**
	 * Returns a new element of given type. Element is an abstraction layer atop React.
	 * @see https://github.com/WordPress/gutenberg/tree/master/element#element
	 */
	var el = wp.element.createElement,
		TextControl = wp.components.TextControl,
		ToggleControl = wp.components.ToggleControl,
		ServerSideRender = wp.components.ServerSideRender,
		InspectorControls = wp.editor.InspectorControls,
		__ = wp.i18n.__;

	/**
	 * Retrieves the translation of text.
	 * @see https://github.com/WordPress/gutenberg/tree/master/i18n#api
	 */
	var __ = wp.i18n.__;

	/**
	 * Every block starts by registering a new block type definition.
	 * @see https://wordpress.org/gutenberg/handbook/block-api/
	 */
	registerBlockType( 'doubleclick-for-wp/dfw-ad-unit', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __( 'DoubleClick Ad Unit' ),

		/**
		 * An icon property should be specified to make it easier to identify a block.
		 * These can be any of WordPress’ Dashicons, or a custom svg element.
		 */
		icon: 'format-image',

		/**
		 * Blocks are grouped into categories to help users browse and discover them.
		 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
		 */
		category: 'embed',

		/**
		 * Optional block extended support features.
		 */
		supports: {
			// Removes support for an HTML mode.
			html: false,
			align: true,
			alignwide: true,
			anchor: false,
			customClassName: false,
			className: true,
			inserter: true,
			multuple: true,
		},

		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 * @see https://wordpress.org/gutenberg/handbook/block-edit-save/#edit
		 *
		 * Makes use of the window.dfw variable, created in function dfw_block_init().
		 *
		 * @param {Object} [props] Properties passed from the editor.
		 * @return {Element}       Element to render.
		 */
		edit: function( props ) {

			/**
			 * Closure generator for edit function's size TextControl onchange callback
			 */
			function onchangecallbackGenerator( key ) {
				return function( value ) {
					/*
					console.log( {
						value: value,
						key: key,
						props: props,
						event: event
					} );
					*/

					new_sizes = props.attributes.sizes;

					// what if there aren't any sizes saved yet?
					if ( typeof new_sizes !== 'object' ) {
						new_sizes = {};
					}

					console.log( value );
					// add this given size to the size array
					new_sizes[event.target.attributes['data-key'].value] = value;
					//console.log( new_sizes );

					props.setAttributes( { sizes: new_sizes } );
					//console.log( props.attributes );
				};
			}

			var breakpoint_forms = [];
			for ( var key in dfw.breakpoints ) {
				var value = 'silly';
				console.log( typeof props.attributes.sizes, props.attributes.sizes );
				if ( typeof props.attributes.sizes === 'object' ) {
					if ( Object.keys(props.attributes.sizes).length > 0 ) {
						if ( typeof props.attributes.sizes[key] === 'string' ) {
							value = props.attributes.sizes[key];
						} else {
							value = '';
						}
					}
				}
				breakpoint_forms.push(
					el(
						TextControl,
						{
							'data-key': key,
							key: key,
							label: [
								key,
								el(
									'i',
									{},
									' (',
									dfw.breakpoints[key]['min_width'],
									'+)'
								),
							],
							value: value,
							// this does not work; while the "key" variable is now kept, the props aren't updated.
							// I think this is because the local props in the generated callback is not the props outside the closure?
							onChange: onchangecallbackGenerator( key )
						}
					)
				);
			}

			// add some instructional text, to match what we put in the widget.
			if ( breakpoint_forms.length > 0 ) {
				breakpoint_forms.unshift(
					el(
						'a',
						{
							href: 'https://github.com/INN/doubleclick-for-wp/blob/master/docs/readme.md#1-via-reusable-widget',
							target: '_blank'
						},
						__( '(Help?)' )
					)
				);
				breakpoint_forms.unshift(
					' '
				);
				breakpoint_forms.unshift(
					__( 'Configure ad unit sizes to be displayed for each breakpoint' )
				);
			}


			return [
				el(
					'div',
					{
						className: props.className
					},
					el(
						TextControl,
						{
							label: __( 'Identifier/Ad code' ),
							value: props.attributes.identifier,
							onChange: function ( value ) { props.setAttributes( { identifier: value } ) },
							help: __( 'This is the Google Ad Manager ad unit identifier for the ad code that will be output by this block.' ),
						}
					)
				),
				el(
					InspectorControls,
					{},
					el(
						TextControl,
						{
							label: __( 'Identifier/Ad code' ),
							value: props.attributes.identifier,
							onChange: function ( value ) { props.setAttributes( { identifier: value } ) },
							help: __( 'This is the Google Ad Manager ad unit identifier for the ad code that will be output by this block.' ),
							className: 'dfw-component-margin',
						}
					),
					el(
						ToggleControl,
						{
							checked: props.attributes.lazyLoad,
							onChange: function ( value ) { props.setAttributes( { lazyLoad: value }  ) },
							label: __( 'Lazy load?' ),
							help: __( 'Only load ad once it comes into view on screen.' ),
							className: 'dfw-component-margin',
						}
					),
					el(
						'div',
						{
							className: 'dfw-component-margin',
						},
						breakpoint_forms
					)
				)
			]
		},

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into `post_content`.
		 * @see https://wordpress.org/gutenberg/handbook/block-edit-save/#save
		 *
		 * Returns a Comment because of reasons documented at
		 * https://github.com/INN/super-cool-ad-inserter-plugin/blob/v0.2/blocks/scaip-sidebar/block.js#L145-L166
		 *
		 * @return {Element}       Element to render.
		 */
		save: function( props ) {
			//console.log( props.attributes );
			return document.createComment( props );
		}
	} );
} )(
	window.wp
);

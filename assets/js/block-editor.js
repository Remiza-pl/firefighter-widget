/**
 * Emergency Statistics — Gutenberg Block Editor Script
 *
 * No-build approach: plain ES5 JavaScript using globally available
 * wp.* packages. Registered as the editor script for the
 * firefighter-stats/emergency-list-widget block.
 *
 * Dependencies declared in PHP: wp-blocks, wp-block-editor, wp-components,
 * wp-i18n, wp-element, wp-server-side-render.
 */
( function ( blocks, blockEditor, components, i18n, element, serverSideRender ) {
	'use strict';

	var el                = element.createElement;
	var __                = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody         = components.PanelBody;
	var TextControl       = components.TextControl;
	var ToggleControl     = components.ToggleControl;
	var SelectControl     = components.SelectControl;
	var RangeControl      = components.RangeControl;
	var ServerSideRender  = serverSideRender;

	blocks.registerBlockType( 'firefighter-stats/emergency-list-widget', {

		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;

			return [
				el(
					InspectorControls,
					{ key: 'inspector' },
					el(
						PanelBody,
						{
							title: __( 'General', 'firefighter-stats' ),
							initialOpen: true,
						},
						el( TextControl, {
							label:    __( 'Title', 'firefighter-stats' ),
							value:    attributes.title,
							onChange: function ( val ) { setAttributes( { title: val } ); },
						} )
					),
					el(
						PanelBody,
						{
							title:       __( 'Category Summary', 'firefighter-stats' ),
							initialOpen: true,
						},
						el( ToggleControl, {
							label:    __( 'Show Category Summary', 'firefighter-stats' ),
							checked:  attributes.showCategorySummary,
							onChange: function ( val ) { setAttributes( { showCategorySummary: val } ); },
						} ),
						el( SelectControl, {
							label:    __( 'Count Period', 'firefighter-stats' ),
							value:    attributes.categoryTimePeriod,
							options:  [
								{ label: __( 'All Time', 'firefighter-stats' ), value: 'all' },
								{ label: __( 'This Year', 'firefighter-stats' ), value: 'year' },
								{ label: __( 'This Month', 'firefighter-stats' ), value: 'month' },
							],
							onChange: function ( val ) { setAttributes( { categoryTimePeriod: val } ); },
						} ),
						el( SelectControl, {
							label:    __( 'Sort Categories By', 'firefighter-stats' ),
							value:    attributes.categorySort,
							options:  [
								{ label: __( 'Alphabetical', 'firefighter-stats' ), value: 'alphabet' },
								{ label: __( 'Count (High to Low)', 'firefighter-stats' ), value: 'count_desc' },
								{ label: __( 'Count (Low to High)', 'firefighter-stats' ), value: 'count_asc' },
							],
							onChange: function ( val ) { setAttributes( { categorySort: val } ); },
						} ),
						el( ToggleControl, {
							label:    __( 'Show Categories with Zero Count', 'firefighter-stats' ),
							checked:  attributes.showZeroCategories,
							onChange: function ( val ) { setAttributes( { showZeroCategories: val } ); },
						} )
					),
					el(
						PanelBody,
						{
							title:       __( 'Recent Emergencies List', 'firefighter-stats' ),
							initialOpen: false,
						},
						el( ToggleControl, {
							label:    __( 'Show Posts List', 'firefighter-stats' ),
							checked:  attributes.showPostsList,
							onChange: function ( val ) { setAttributes( { showPostsList: val } ); },
						} ),
						el( RangeControl, {
							label:    __( 'Number of Posts', 'firefighter-stats' ),
							value:    attributes.limit,
							min:      1,
							max:      20,
							onChange: function ( val ) { setAttributes( { limit: val } ); },
						} ),
						el( SelectControl, {
							label:    __( 'Post Order', 'firefighter-stats' ),
							value:    attributes.order,
							options:  [
								{ label: __( 'Default', 'firefighter-stats' ), value: 'default' },
								{ label: __( 'By date, newest first', 'firefighter-stats' ), value: 'date_desc' },
								{ label: __( 'By date, oldest first', 'firefighter-stats' ), value: 'date_asc' },
								{ label: __( 'By title, ascending', 'firefighter-stats' ), value: 'title_asc' },
								{ label: __( 'By title, descending', 'firefighter-stats' ), value: 'title_desc' },
								{ label: __( 'Random', 'firefighter-stats' ), value: 'random' },
							],
							onChange: function ( val ) { setAttributes( { order: val } ); },
						} ),
						el( ToggleControl, {
							label:    __( 'Show Date', 'firefighter-stats' ),
							checked:  attributes.showDate,
							onChange: function ( val ) { setAttributes( { showDate: val } ); },
						} ),
						el( ToggleControl, {
							label:    __( 'Show Category', 'firefighter-stats' ),
							checked:  attributes.showCategory,
							onChange: function ( val ) { setAttributes( { showCategory: val } ); },
						} )
					)
				),
				el(
					ServerSideRender,
					{
						key:        'preview',
						block:      'firefighter-stats/emergency-list-widget',
						attributes: attributes,
					}
				),
			];
		},

		// Server-side rendered — save returns null.
		save: function () {
			return null;
		},
	} );
}(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element,
	window.wp.serverSideRender
) );

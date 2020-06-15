import React from 'react';
import { Component, render } from '@wordpress/element';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import allLocales from '@fullcalendar/core/locales-all';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import TaxonomySelect from './TaxonomySelect';

export default class GbFullCalendar extends Component {

	constructor( props ) {
		super( props );
		this.calendarRef = React.createRef();
		this.filterParams = {};

		/**
		 * The FullCalendar options
		 * @link https://fullcalendar.io/docs
		 */
		this.fc = props.fc;

		/**
		 * Additional options for Gutenberg, Wordpress and EventsManager
		 * @type {{ajaxUrl: string, month: string, year: string}}
		 */
		this.fcExtra = props.fcExtra;
	}

	getExtraParams() {
		return {
			action: 'WP_FullCalendar',
			type: 'event',
			month: this.fcExtra.month,
			year: this.fcExtra.year,
			...this.filterParams,
		};
	}

	onSelectTaxonomy( taxonomy, value ) {
		this.filterParams[taxonomy] = value;

		let calendarApi = this.calendarRef.current.getApi();
		calendarApi.refetchEvents();
	}

	render() {
		const _onSelectTax = ( ...props ) => this.onSelectTaxonomy( ...props );
		const plugins = [ dayGridPlugin, timeGridPlugin, listPlugin ];
		if (this.fc.themeSystem === 'bootstrap') {
			plugins.push( bootstrapPlugin );
		}
		const fcOptions = {
			eventSources: [
				// WP Events manager source
				{
					url: this.fcExtra.ajaxUrl,
					method: 'POST',
					extraParams: () => this.getExtraParams(),
					failure: function( err ) {
						alert( 'there was an error while fetching events!' + JSON.stringify( err ) );
					},
				},
			],
			viewDidMount: ( { view, el } ) => {
				const calendarWrapper = el.parentElement.parentElement;
				// Only add filter, if not already done, as viewDidMount is called more often.
				if (calendarWrapper.getElementsByClassName( 'fc-filter-toolbar' ).length === 0) {
					const fcHeaderToolbars = calendarWrapper.getElementsByClassName( 'fc-header-toolbar' );

					// Create filter toolbar
					const fcFilterToolbar = document.createElement( 'div' );
					fcFilterToolbar.classList.add( 'fc-toolbar', 'fc-filter-toolbar' );
					fcFilterToolbar.style.marginBottom = '1.5em';

					const taxonomyDropdowns = (
						<div className='fc-toolbar-chunk'>
							{
								this.fcExtra.taxonomyNodes.map( ( tNode ) => {
									return ( <TaxonomySelect onSelectTaxonomy={ _onSelectTax } { ...tNode } /> );
								} )
							}
						</div>
					);
					render( taxonomyDropdowns, fcFilterToolbar );

					for (let fcHeaderToolbar of fcHeaderToolbars) {
						fcHeaderToolbar.style.marginBottom = 0;
						fcHeaderToolbar.after( fcFilterToolbar );
					}
				}
			},
			eventDataTransform: ( eventData ) => {
				// Text color is now handled by fc to get best contrast in different modes
				// Can be removed, if em doesn't send text color anymore.
				if (this.fc.eventDisplay === 'block' && eventData.color !== '#FFFFFF') {
					// TODO workaround for white background, should be handled in lib
					delete eventData.textColor;
				}
				return eventData;
			},
			...this.fc,
		};
		return (
			<div>
				<FullCalendar
					ref={ this.calendarRef }
					locales={ allLocales }
					plugins={ plugins }
					{ ...fcOptions }
				/>
			</div>
		);
	}
}

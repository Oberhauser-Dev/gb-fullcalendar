import { Component, render, useImperativeHandle } from '@wordpress/element';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import allLocales from '@fullcalendar/core/locales-all';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import TaxonomySelect from './TaxonomySelect';
import CircularProgress from '@material-ui/core/CircularProgress';

/**
 * @typedef {{ajaxUrl: string, taxonomyNodes: TaxonomyNode[], initialTaxonomies: [], month: string, year: string}} FcExtra
 * @typedef {{echo: boolean, class: string, selected: int[], name: string, slug: string, show_option_all: string, items: [], is_empty: boolean}} TaxonomyNode
 * @typedef {{ fc: {import('@fullcalendar/common').CalendarOptions}, fcExtra: FcExtra }} GbFcPrefs
 */

export default class GbFullCalendar extends Component {

	/**
	 * @param props {GbFcPrefs}
	 */
	constructor( props ) {
		super( props );
		this.calendarRef = React.createRef();
		this.loadingComponent = React.createRef();

		/**
		 * The FullCalendar options
		 * @link https://fullcalendar.io/docs
		 */
		this.fc = props.fc;

		/**
		 * Additional options for Gutenberg, Wordpress and EventsManager
		 */
		this.fcExtra = props.fcExtra;

		/**
		 * The filter parameters while fetching events from the source.
		 */
		this.filterParams = {};

		// Preprocess taxonomies
		this.fcExtra.taxonomyNodes = this.fcExtra.taxonomyNodes.filter( ( tNode => ! tNode.is_empty ) ).map( ( tNode ) => {
			tNode.items = tNode.items.filter( term => term.count > 0 );
			let selected = this.fcExtra.initialTaxonomies[tNode.slug];
			tNode.selected = selected ?? tNode.selected;
			if (! Array.isArray( tNode.selected )) {
				tNode.selected = [ tNode.selected ];
			}
			tNode.selected = tNode.selected.map( termId => parseInt( termId ) );

			// Init filter params
			if (tNode.selected && tNode.selected.length > 0) {
				this.filterParams[tNode.taxonomy] = tNode.selected;
			}
			return tNode;
		} );

		// Calendar options
		const _onSelectTax = ( ...props ) => this.onSelectTaxonomy( ...props );
		const plugins = [ dayGridPlugin, timeGridPlugin, listPlugin ];
		if (this.fc.themeSystem === 'bootstrap') {
			plugins.push( bootstrapPlugin );
		}

		/**
		 * @type {import('@fullcalendar/common').CalendarOptions}
		 */
		this.fcOptions = {
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
			loading: ( isLoading ) => {
				if (this.loadingComponent.current) {
					this.loadingComponent.current.loading( isLoading );
				}
			},
			locales: allLocales,
			plugins: plugins,
			...this.fc,
		};
	}

	getExtraParams() {
		return {
			action: 'WP_FullCalendar',
			type: 'event',
			...this.filterParams,
		};
	}

	onSelectTaxonomy( taxonomy, value ) {
		if (value.includes( 0 )) {
			delete this.filterParams[taxonomy];
		} else {
			this.filterParams[taxonomy] = value;
		}

		let calendarApi = this.calendarRef.current.getApi();
		calendarApi.refetchEvents();
	}

	render() {
		return (
			<div style={ { position: 'relative' } }>
				<FullCalendar ref={ this.calendarRef } { ...this.fcOptions }/>
				<LoadingComponent ref={ this.loadingComponent }/>
			</div>
		);
	}
}

const LoadingComponent = React.forwardRef( ( props, ref ) => {
	const [ loadingIndicator, setLoadingIndicator ] = React.useState( true );

	useImperativeHandle( ref, () => ( {

		loading( isLoading ) {
			if (isLoading !== loadingIndicator) {
				setLoadingIndicator( isLoading );
			}
		},

	} ) );

	return (
		<>
			{ loadingIndicator &&
			<div style={ {
				...styles.absoluteFill,
				backgroundColor: 'rgba(255, 255, 255, 0.6)',
				zIndex: 999,
			} }>
				<CircularProgress style={ {
					...styles.absoluteFill,
					margin: 'auto',
				} }/>
			</div> }
		</>
	);

} );

const styles = {
	absoluteFill: {
		position: 'absolute',
		top: 0,
		right: 0,
		left: 0,
		bottom: 0,
	},
};

/**
 *
 * @param attributes
 * @param gbFcPrefs {GbFcPrefs}
 * @returns {GbFcPrefs}
 */
export function attributesToGbfcOptions( attributes, gbFcPrefs = { fc: {}, fcExtra: {} } ) {
	// Set fc preferences
	const { initialView } = attributes;
	if (initialView) {
		gbFcPrefs.fc.initialView = initialView;
	}

	// Set fcExtra preferences
	let { initialTaxonomies } = attributes;
	if (initialTaxonomies) {
		gbFcPrefs.fcExtra.initialTaxonomies = initialTaxonomies;
	}
	return gbFcPrefs;
}

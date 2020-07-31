import { Component, render, useImperativeHandle, useEffect } from '@wordpress/element';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import allLocales from '@fullcalendar/core/locales-all';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import TaxonomySelect from './TaxonomySelect';
import CircularProgress from '@material-ui/core/CircularProgress';
import { ThemeProvider, createMuiTheme } from '@material-ui/core/styles';
import { Tooltip } from '@material-ui/core';
import Typography from '@material-ui/core/Typography';

/**
 * @typedef {{ajaxUrl: string, eventAction: string, taxonomyNodes: TaxonomyNode[], initialTaxonomies: [], htmlFontSize: number, tooltips: boolean, tooltipAction:string, tooltipPlacement: string}} FcExtra
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

		this.muiTheme = createMuiTheme( {
			typography: {
				// You might want to change the <html> element default font size. For instance, when using the 10px simplification
				htmlFontSize: this.fcExtra.htmlFontSize,
			},
		} );

		/**
		 * @type {import('@fullcalendar/common').CalendarOptions}
		 */
		this.fcOptions = {
			eventContent: ( arg ) => {
				if (this.fcExtra.tooltips) {
					// TODO change in favor of more simple (built-in) solution
					const data = this.calendarRef.current.getApi().getCurrentData();
					const viewSpec = data.viewSpecs[arg.view.type];
					let innerContent;
					if (viewSpec.component.name === 'ListView') {
						// ListView has other content than regular views.
						// See: https://github.com/fullcalendar/fullcalendar-react/issues/12#issuecomment-665807912
						innerContent = this.renderListInnerContent( arg );
					} else {
						innerContent = this.renderInnerContent( arg );
					}
					return (
						<TooltipComponent url={ this.fcExtra.ajaxUrl }
										  action={ this.fcExtra.tooltipAction }
										  placement={ this.fcExtra.tooltipPlacement }
										  { ...arg }>
							{ innerContent }
						</TooltipComponent>
					);
				}
				// If nothing is returned, it renders regular content.
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
						<ThemeProvider theme={ this.muiTheme }>
							<div className='fc-toolbar-chunk'>
								{
									this.fcExtra.taxonomyNodes.map( ( tNode ) => {
										return ( <TaxonomySelect onSelectTaxonomy={ _onSelectTax } { ...tNode } /> );
									} )
								}
							</div>
						</ThemeProvider>
					);
					render( taxonomyDropdowns, fcFilterToolbar );

					for (let fcHeaderToolbar of fcHeaderToolbars) {
						fcHeaderToolbar.style.marginBottom = 0;

						// TODO replace with fcHeaderToolbar.after( fcFilterToolbar ); when IE is deprecated.
						fcHeaderToolbar.parentNode.insertBefore( fcFilterToolbar, fcHeaderToolbar.nextSibling );
					}
				}
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
			action: this.fcExtra.eventAction,
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

	/**
	 * https://github.com/fullcalendar/fullcalendar/blob/495d925436e533db2fd591e09a0c887adca77053/packages/common/src/common/StandardEvent.tsx#L79
	 */
	renderInnerContent( innerProps ) {
		return (
			<div className='fc-event-main-frame'>
				{ innerProps.timeText &&
				<div className='fc-event-time'>{ innerProps.timeText }</div>
				}
				<div className='fc-event-title-container'>
					<div className='fc-event-title fc-sticky'>
						{ innerProps.event.title || <Fragment>&nbsp;</Fragment> }
					</div>
				</div>
			</div>
		);
	}

	/**
	 * https://github.com/fullcalendar/fullcalendar/blob/495d925436e533db2fd591e09a0c887adca77053/packages/list/src/ListViewEventRow.tsx#L55
	 */
	renderListInnerContent( props ) {
		let { event } = props;
		let url = event.url;
		let anchorAttrs = url ? { href: url } : {};

		return (
			<a { ...anchorAttrs }>
				{ event.title }
			</a>
		);
	}

	render() {
		return (
			<div style={ { position: 'relative' } }>
				<ThemeProvider theme={ this.muiTheme }>
					<FullCalendar ref={ this.calendarRef } { ...this.fcOptions }/>
					<LoadingComponent ref={ this.loadingComponent }/>
				</ThemeProvider>
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

const TooltipComponent = ( { url, action, placement, event, ...props } ) => {
	const [ tooltipContent, setTooltipContent ] = React.useState( false );
	const event_data = {
		action: action,
		post_id: event.extendedProps.post_id,
		event_id: event.extendedProps.event_id,
	};

	async function handleOpen() {
		if (! tooltipContent) {
			const result = await fetch(
				url,
				{
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					method: 'POST',
					body: new URLSearchParams( event_data ),
				},
			);
			const content = await result.json();
			setTooltipContent( ( content && content.excerpt ) ?
				( <>
					{ content.imageUrl &&
					<img src={ content.imageUrl }
						 style={ {
							 maxWidth: content.imageDimensions[0],
							 maxHeight: content.imageDimensions[1],
							 marginLeft: '4px',
							 marginBottom: '2px',
							 marginTop: '2px',
							 float: 'right',
						 } }/> }
					<div dangerouslySetInnerHTML={ { __html: content.excerpt } }/>
				</> ) :
				'No information available',
			);
		}
	}

	return (
		<Tooltip title={ <Typography>{ tooltipContent || 'Loading...' }</Typography> }
				 arrow
				 placement={ placement }
				 onOpen={ handleOpen }>
			{ props.children }
		</Tooltip>
	);

};

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

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import GbFullCalendar, { attributesToGbfcOptions } from './GbFullCalendar';
import {
	SelectControl,
	PanelBody,
	PanelRow,
} from '@wordpress/components';
import {
	InspectorControls,
} from '@wordpress/editor';
import fcOptions from './FullCalendarOptions.json';
import GbFullCalendarWrapper from './GbFullCalendarWrapper';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object} [props] Properties passed from the editor.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {

	const gbFcPrefs = attributesToGbfcOptions( attributes, GbFcGlobal );
	const initialTaxonomies = gbFcPrefs.fcExtra.initialTaxonomies;

	function onChangeInputField( fieldName, newValue ) {
		setAttributes( { [fieldName]: newValue } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title="View settings" initialOpen={ true }>
					<PanelRow>
						<SelectControl
							label="Initial View"
							value={ gbFcPrefs.fc.initialView }
							options={ fcOptions.initialView }
							onChange={ ( value ) => onChangeInputField( 'initialView', value ) }
						/>
					</PanelRow>
				</PanelBody>
				<PanelBody title="Taxonomy settings">
					{
						gbFcPrefs.fcExtra.taxonomyNodes.map( ( tNode ) => {
							const items = Object.values( tNode.items ).map( ( term ) => {
								return {
									label: term.name,
									value: term.term_id,
								};
							} );

							// Add all option at the beginning
							items.unshift( {
								label: tNode.show_option_all,
								value: 0,
							} );
							const initialVal = initialTaxonomies[tNode.slug] ?? null;

							return (
								<PanelRow>
									<SelectControl
										multiple={ true }
										label={ 'Default for ' + tNode.name }
										value={ initialVal }
										options={ items }
										onChange={ ( value ) => {
											initialTaxonomies[tNode.slug] = value;
											// Clone object in order to trigger React updating view
											onChangeInputField( 'initialTaxonomies', Object.assign( {}, initialTaxonomies ) );
										} }
									/>
								</PanelRow>
							);
						} )
					}
				</PanelBody>
			</InspectorControls>

			{/* Only to test wrapper */ }
			{/*<GbFullCalendarWrapper gbFcLocal={gbFcPrefs}/>*/ }

			{/* The real calendar */ }
			<GbFullCalendar { ...gbFcPrefs } />
		</>
	);
}

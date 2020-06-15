/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import GbFullCalendar from './GbFullCalendar';

import {
	CheckboxControl,
	RadioControl,
	TextControl,
	ToggleControl,
	SelectControl,
	PanelBody,
	PanelRow,
} from '@wordpress/components';
import {
	InspectorControls,
} from '@wordpress/editor';
import fcOptions from './FullCalendarOptions.json'

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
	const {
		content,
		checkboxField,
		radioField,
		textField,
		toggleField,
		initialView
	} = attributes;

	const gbFcPrefs = {
		fc: GbFcGlobal.fc,
		fcExtra: GbFcGlobal.fcExtra,
	}
	if (attributes) {
		gbFcPrefs.fc = Object.assign( gbFcPrefs.fc, attributes );
		//gbFcPrefs.fcExtra = Object.assign( gbFcPrefs.fcExtra, GbFcLocal.fcExtra );
	}

	function onChangeInputField( fieldName, newValue ) {
		setAttributes( { [fieldName]: newValue } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody
					title="Most awesome settings ever"
					initialOpen={ true }
				>
					<PanelRow>
						<CheckboxControl
							heading="Checkbox Field"
							label="Tick Me"
							help="Additional help text"
							checked={ checkboxField }
							onChange={ ( value ) => onChangeInputField( 'checkbox', value ) }
						/>
					</PanelRow>
					<PanelRow>
						<RadioControl
							label="Radio Field"
							selected={ radioField }
							options={
								[
									{ label: 'Yes', value: 'yes' },
									{ label: 'No', value: 'no' },
								]
							}
							onChange={ ( value ) => onChangeInputField( 'radio', value ) }
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							label="Text Field"
							help="Additional help text"
							value={ textField }
							onChange={ ( value ) => onChangeInputField( 'text', value ) }
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label="Toggle Field"
							checked={ toggleField }
							onChange={ ( value ) => onChangeInputField( 'toggle', value ) }
						/>
					</PanelRow>
					<PanelRow>
						<SelectControl
							label="Initial View"
							value={ initialView }
							options={ fcOptions.initialView }
							onChange={ ( value ) => onChangeInputField( 'initialView', value ) }
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<GbFullCalendar {...gbFcPrefs} />
		</>
	);
}

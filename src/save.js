/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
import { attributesToGbfcOptions } from './GbFullCalendar';

export default function save( { attributes } ) {
	const gbFcLocal = attributesToGbfcOptions(attributes)

	return (
		<>
			<div className="fullcalendar-wrapper"></div>
			<script>
				var GbFcLocal = { JSON.stringify( gbFcLocal ) }
			</script>
		</>
	);
}

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { initFullcalendars } from './client';

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
export default function Edit( { className } ) {
	return (
		<div>
			<p className={ className }>
				{ __( 'GB Fullcalendar – hello from the editor!', 'create-block' ) }
			</p>
			<div className="fullcalendarWrapper"></div>
		</div>
	);
}

/**
 * Observe whole backend DOM, to dynamically add fullcalendar.
  */

// const targetNode = document.getElementById('wpbody');
const targetNode = document;
if (targetNode) {
	const config = { attributes: false, childList: true, subtree: true };

	const callback = function( mutationsList ) {
		for (let mutation of mutationsList) {
			if (mutation.type === 'childList' && mutation.addedNodes) {
				for (let i = 0; i < mutation.addedNodes.length; i++) {
					const domNode = mutation.addedNodes.item( i );
					if (domNode instanceof Element) {
						initFullcalendars( domNode );
					}
				}
			}
		}

		// Stop observing, if needed
		// observer.disconnect();
	};

	const observer = new MutationObserver( callback );
	observer.observe( targetNode, config );
}

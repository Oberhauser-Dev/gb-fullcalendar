import { render } from '@wordpress/element';
import GbFullCalendar from './GbFullCalendar';

window.addEventListener('DOMContentLoaded', (event) => {
	// Does only work on front-end as in backend they are generated dynamically (without wrapper).
	const wrappers = document.getElementsByClassName(`gbfc-wrapper`);

	for(let wrapper of wrappers) {
		const gbFcPrefs = Object.assign({}, GbFcGlobal)

		// Overwrite global preferences with block preferences for each wrapper.
		const gbFcLocal = window[`GbFcLocal_${ wrapper.getAttribute('data-value') }`]
		if (typeof gbFcLocal !== 'undefined') {
			gbFcPrefs.fc = Object.assign( gbFcPrefs.fc, gbFcLocal.fc );
			gbFcPrefs.fcExtra = Object.assign( gbFcPrefs.fcExtra, gbFcLocal.fcExtra );
		}

		render(<GbFullCalendar {...gbFcPrefs} />, wrapper);
	}
});

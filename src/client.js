import { render } from '@wordpress/element';
import GbFullCalendar from './GbFullCalendar';

window.addEventListener('DOMContentLoaded', (event) => {
	// Does only work on front-end as in backend they are generated dynamically (without wrapper).
	const wrappers = document.getElementsByClassName(`fullcalendar-wrapper`);

	const gbFcPrefs = {
		fc: GbFcGlobal.fc,
		fcExtra: GbFcGlobal.fcExtra,
	}
	if (typeof GbFcLocal !== 'undefined') {
		gbFcPrefs.fc = Object.assign( gbFcPrefs.fc, GbFcLocal.fc );
		gbFcPrefs.fcExtra = Object.assign( gbFcPrefs.fcExtra, GbFcLocal.fcExtra );
	}

	for(let wrapper of wrappers) {
		render(<GbFullCalendar {...gbFcPrefs} />, wrapper);
	}
});

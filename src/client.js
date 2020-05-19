import { render } from '@wordpress/element';
import GbFullCalendar from './GbFullCalendar';

window.addEventListener('DOMContentLoaded', (event) => {
	// Does only work on front-end as in backend they are generated dynamically (without wrapper).
	const wrappers = document.getElementsByClassName(`fullcalendar-wrapper`);
	for(let wrapper of wrappers) {
		render(<GbFullCalendar />, wrapper);
	}
});

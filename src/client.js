import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

import '@fullcalendar/core/main.min.css';
import '@fullcalendar/daygrid/main.min.css';

export function initFullcalendars( domEl ) {
	const calendars = domEl.getElementsByClassName( 'fullcalendarWrapper' );
	for (let i = 0; i < calendars.length; i++) {
		const calendarEl = calendars.item( i );
		if (! calendarEl.classList.contains( 'fc' )) {

			const calendar = new Calendar( calendarEl, {
				plugins: [ dayGridPlugin ],
			} );
			calendar.render();
		}
	}
}

document.addEventListener( 'DOMContentLoaded', function() {
	initFullcalendars( document );
} );

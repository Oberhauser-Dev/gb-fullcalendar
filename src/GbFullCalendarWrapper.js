// import { useInstanceId } from '@wordpress/compose';
Object.defineProperty(String.prototype, 'hashCode', {
	value: function() {
		var hash = 0, i, chr;
		for (i = 0; i < this.length; i++) {
			chr   = this.charCodeAt(i);
			hash  = ((hash << 5) - hash) + chr;
			hash |= 0; // Convert to 32bit integer
		}
		return hash;
	}
});

export default function GbFullCalendarWrapper( { gbFcLocal } ) {
	// const instanceId = useInstanceId( GbFullCalendarWrapper );
	const gbFcLocalJSON = JSON.stringify( gbFcLocal )
	// Workaround to create unique id, which remains the same over two sessions.
	// TODO use "useInstanceId" instead, if possible.
	const instanceId = Math.abs(gbFcLocalJSON.hashCode()).toString(16);
	return (
		<>
			<div id={ `gbfc-wrapper-${ instanceId }` } data-value={ instanceId } className="gbfc-wrapper">

			</div>
			<script>
				{ `var GbFcLocal_${ instanceId } = ${gbFcLocalJSON}` }
			</script>
		</>
	);
}

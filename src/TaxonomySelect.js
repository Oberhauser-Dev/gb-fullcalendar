import { makeStyles } from '@material-ui/core/styles';
import InputLabel from '@material-ui/core/InputLabel';
import MenuItem from '@material-ui/core/MenuItem';
import FormControl from '@material-ui/core/FormControl';
import Select from '@material-ui/core/Select';
import Checkbox from '@material-ui/core/Checkbox';
import ListItemText from '@material-ui/core/ListItemText';

const useStyles = makeStyles( ( theme ) => ( {
	formControl: {
		margin: theme.spacing( 1 ),
		minWidth: 120,
	},
	selectEmpty: {
		marginTop: theme.spacing( 2 ),
	},
} ) );

/**
 *
 * @param props {TaxonomyNode}
 * @returns {*}
 */
export default function TaxonomySelect( props ) {
	const classes = useStyles();
	const { onSelectTaxonomy, taxonomy, name, show_option_all, selected } = props;
	const [ termIds, setTermIds ] = React.useState( selected ?? [] );

	// Add reset button for all selects.
	const items = [ ...props.items ];
	items.unshift( {
		name: show_option_all,
		term_id: 0,
	} );

	// Sort by hierarchy
	const hierarchicItems = hierarchy( Object.values( items ), { idKey: 'term_id', parentKey: 'parent' } );

	const handleChange = ( event ) => {

		let values = event.target.value;
		if (values.includes( 0 )) {
			if (termIds.includes( 0 ) && values.length > termIds.length && values.length !== items.length) {
				// If another checkbox than all is enabled, but not disabled, then uncheck "All"
				// But don't uncheck, if all items are about to be enabled.
				values.splice( values.indexOf( 0 ), 1 );
			}
		} else if (values.length === 0) {
			// Select "all" if nothing is selected
			values = [ 0 ];
		}
		// else if (values.length === ( items.length - 1 ) && ! termIds.includes( 0 )) {
		// 	// If all are checked besides "All", then check it too
		// 	// Actually there exist events with no checkbox in a taxonomy, too
		// 	//values.unshift( 0 );
		// }
		setTermIds( values );
		onSelectTaxonomy( taxonomy, values );
	};

	return (
		<FormControl className={ classes.formControl }>
			<InputLabel id="demo-simple-select-label">{ name }</InputLabel>
			<Select
				multiple
				value={ termIds }
				onChange={ handleChange }
				renderValue={ ( selected ) => {
					if (selected.includes( 0 )) {
						return items.find( term => term.term_id === 0 ).name;
					} else {
						return selected.map( termId => {
							const term = items.find( term => term.term_id === parseInt( termId ) );
							return term ? term.name : null;
						} ).filter( Boolean ).join( ', ' );
					}
				} }
			>
				{ flattenHierarchy( hierarchicItems, 0, termIds ) }
			</Select>
		</FormControl>
	);
}

function flattenHierarchy( items, depth, selected = [] ) {
	return items.map( ( term ) => {
		// Color dot, if needed
		// let colorEl = '';
		// if (term.color) {
		// 	colorEl = <span style={ { color: term.color, fontSize: '1.5em' } }>● </span>;
		// }
		let space = '';
		for (let i = 0; i < depth; i++) {
			space += ' ';
		}
		const customColor = ! selected.includes( term.term_id ) ? 'grey' : term.color;
		let res = [
			<MenuItem key={ term.term_id } value={ term.term_id }>
				<Checkbox style={ { color: customColor } }
						  checked={ selected.includes( term.term_id ) || selected.includes( 0 ) }/>
				<ListItemText primary={ space + term.name }/>
			</MenuItem>,
		];
		if (term.children && term.children.length > 0) {
			res = res.concat( flattenHierarchy( term.children, depth + 1, selected ) );
		}
		return res;
	} );
}

/**
 * Sort hierarchically
 */
const hierarchy = ( data = [], { idKey = 'id', parentKey = 'parentId', childrenKey = 'children' } = {} ) => {
	const tree = [];
	const childrenOf = {};
	data.forEach( ( item ) => {
		const { [idKey]: id, [parentKey]: parentId = 0 } = item;
		childrenOf[id] = childrenOf[id] || [];
		item[childrenKey] = childrenOf[id];
		parentId ? (
				childrenOf[parentId] = childrenOf[parentId] || []
			).push( item )
			: tree.push( item );
	} );
	return tree;
};

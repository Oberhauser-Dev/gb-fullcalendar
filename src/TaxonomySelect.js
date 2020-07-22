import { makeStyles } from '@material-ui/core/styles';
import InputLabel from '@material-ui/core/InputLabel';
import MenuItem from '@material-ui/core/MenuItem';
import FormControl from '@material-ui/core/FormControl';
import Select from '@material-ui/core/Select';
import { ThemeProvider, createMuiTheme } from '@material-ui/core/styles';

const useStyles = makeStyles( ( theme ) => ( {
	formControl: {
		margin: theme.spacing( 1 ),
		minWidth: 120,
	},
	selectEmpty: {
		marginTop: theme.spacing( 2 ),
	},
} ) );

const theme = createMuiTheme( {
	typography: {
		// Informiere die Material-UI über die Schriftgröße des HTML-Elements.
		// TODO check if this works in all themes... https://material-ui.com/de/customization/typography/
		htmlFontSize: 10,
	},
} );

// interface TaxonomySelectProps {
// 	'class': string;
// 	echo: false;
// 	hide_empty: boolean;
// 	hierarchical: boolean;
// 	name: string;
// 	selected: boolean;
// 	show_option_all: string;
// 	taxonomy: string;
//  items: [{
// 		count: 8
// 		description: "Sonderveranstaltung während der Corona-Pandemie"
// 		filter: "raw"
// 		name: "Corona-Five"
// 		parent: 0
// 		slug: "event_type_corona"
// 		taxonomy: "event_type"
// 		term_group: 0
// 		term_id: 2
// 		term_taxonomy_id: 2
//  }]
// }

/**
 *
 * @param props {TaxonomyNode}
 * @returns {*}
 */
export default function TaxonomySelect( props ) {
	const classes = useStyles();
	const { onSelectTaxonomy, taxonomy, name, show_option_all, selected } = props;
	const [ termId, setTermId ] = React.useState( selected );

	// Sort by hierarchy
	const items = hierarchy( Object.values( props.items ), { idKey: 'term_id', parentKey: 'parent' } );
	// Add reset button for all selects.
	items.unshift( {
		name: show_option_all,
		term_id: 0,
	} );

	const handleChange = ( event ) => {
		setTermId( event.target.value );
		onSelectTaxonomy( taxonomy, event.target.value );
	};

	return (
		<ThemeProvider theme={ theme }>
			<FormControl className={ classes.formControl }>
				<InputLabel id="demo-simple-select-label">{ name }</InputLabel>
				<Select
					labelId="demo-simple-select-label"
					id="demo-simple-select"
					value={ termId }
					onChange={ handleChange }
				>
					{ flattenHierarchy( items, 0 ) }
				</Select>
			</FormControl>
		</ThemeProvider>
	);
}

function flattenHierarchy( items, depth ) {

	return items.map( ( term ) => {
		let colorEl = '';
		if (term.color) {
			colorEl = <span style={ { color: term.color, fontSize: '1.5em' } }>● </span>;
		}
		let space = '';
		for (let i = 0; i < depth; i++) {
			space += ' ';
		}
		let res = [ <MenuItem value={ term.term_id }>{ colorEl }{ space + term.name }</MenuItem> ];
		if (term.children && term.children.length > 0) {
			res = res.concat( flattenHierarchy( term.children, depth + 1 ) );
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

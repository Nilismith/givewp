/**
 * WordPress dependencies
 */
const { ServerSideRender } = wp.components;

/**
 * Internal dependencies
 */
import SelectForm from '../../components/select-form';
import Inspector from './inspector';
import Controls from './controls';

/**
 * Render Block UI For Editor
 */

const GiveForm = ( props ) => {
	const { attributes, isSelected, className } = props;
	const { id } = attributes;

	// Render block UI
	let blockUI;

	if ( ! id ) {
		blockUI = <SelectForm { ... { ...props } } />;
	} else {
		blockUI = (
			<div id="donation-form-preview-block">
				<Inspector { ... { ...props } } />
				<Controls { ... { ...props } } />
				<ServerSideRender block="give/donation-form" attributes={ attributes } />
			</div>
		);
	}

	return (
		<div className={ !! isSelected ? `${ className } isSelected` : className } >
			{ blockUI }
		</div>
	);
};

export default GiveForm;

// Vendor dependencies
import axios from 'axios';
import { useState, useEffect, Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import Chart from '../chart';
import SkeletonChart from '../skeleton-chart';

// Store-related dependencies
import { useStoreValue } from '../../store';

const RESTChart = ( { title, type, aspectRatio, endpoint, showLegend } ) => {
	// Use period from store
	const [ { period }, dispatch ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	// Use to manage loading state
	const [ loaded, setLoaded ] = useState( false );

	// Fetch new data and update Chart when period changes
	useEffect( () => {
		if ( period.startDate && period.endDate ) {
			setLoaded( false );
			axios.get( wpApiSettings.root + 'give-api/v2/reports/' + endpoint, {
				params: {
					start: period.startDate.format( 'YYYY-MM-DD-HH' ),
					end: period.endDate.format( 'YYYY-MM-DD-HH' ),
				},
				headers: {
					'X-WP-Nonce': wpApiSettings.nonce,
				},
			} )
				.then( function( response ) {
					setLoaded( true );
					setFetched( response.data.data );

					if ( endpoint === 'income' ) {
						const found = response.data.data.datasets[ 0 ].data.reduce( ( a, b ) => a + b, 0 ) > 0 ? true : false;
						dispatch( {
							type: 'SET_DONATIONS_FOUND',
							payload: found,
						} );
						dispatch( {
							type: 'SET_PAGE_LOADED',
							payload: true,
						} );
					}
				} );
		}
	}, [ period, endpoint ] );

	return (
		<Fragment>
			{ title && (
				<div className="givewp-chart-title">
					{ title }
					{ ! loaded && (
						<span className="spinner is-active" />
					) }
				</div>
			) }
			{ fetched ? (
				<Chart
					type={ type }
					aspectRatio={ aspectRatio }
					data={ fetched }
					showLegend={ showLegend }
				/>
			) : (
				<SkeletonChart
					type={ type }
					aspectRatio={ aspectRatio }
					showLegend={ showLegend }
				/>
			) }
		</Fragment>
	);
};

RESTChart.propTypes = {
	// Chart type (ex: line)
	type: PropTypes.string.isRequired,
	// Chart aspect ratio
	aspectRatio: PropTypes.number,
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
	// Display Chart with Legend
	showLegend: PropTypes.bool,
};

RESTChart.defaultProps = {
	type: null,
	aspectRatio: 0.6,
	endpoint: null,
	showLegend: false,
};

export default RESTChart;

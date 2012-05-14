<?php
	
	exit( 'This method is deprecated since it is incorrect as of right now.');

	require( 'route.php' );

	// Set-up variables
	$num_wp = count( $input_route['waypoints'] );
	$resulting_routes = array();

	if ( $num_wp > 8 )
		exit( "Maximum of 8 waypoints please!\n" );

	/**
	 * Main loop. Loop through every possible set of waypoints.
	 */
	for ( $i = 0; $i < $num_wp; ++$i )
	{
		echo "Calculating route " . ($i+1) . " out of $num_wp...";

		// Create the set
		// TODO: This is wrong, we need to find all possible permutations of the group of waypoints. Deprecate the file until solved.
		for ( $j = 0; $j < $num_wp; ++$j )
			$set[$j] = $input_route['waypoints'][ ( ($i+$j) > $num_wp-1 ? ($i+$j)%($num_wp) : ($i+$j) ) ];

		// Setup URL parameters
		$args = array(
			'origin' => $input_route['start'],
			'destination' => $input_route['end'],
			'waypoints' => '',
			'sensor' => 'false',
			);

		// Insert waypoints
		foreach ( $set as $waypoint )
			$args['waypoints'] .= $waypoint . '|';

		// Create URL
		$url = 'http://maps.googleapis.com/maps/api/directions/json?' . http_build_query( $args );

		if ( strlen( $url ) > 2048 )
			exit( "URL length exceeded!\n" );

		// Query URL
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_REFERER, "douglasstridsberg.com" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$body = curl_exec( $ch );
		curl_close( $ch );

		// Decode the response
		$results = json_decode( $body );

		if ( $results->status != "OK" )
		{
			echo "ERROR! " . $results->status;
			exit;
		}

		// Copy relevant results to array
		$resulting_routes[$i] = array( 'distance' => '', 'waypoints' => array() );
		foreach( $results->routes as $route )
		{
			foreach( $route->legs as $leg )
				$resulting_routes[$i]['distance'] += $leg->distance->value;
		}
		$resulting_routes[$i]['waypoints'] = $set;

		echo " done! \n";
	}

	// Sort result array
	foreach ( $resulting_routes as $key => $row )
	{
		$distance[$key] = $row['distance'];
		$waypoints[$key] = $row['waypoints'];
	}
	array_multisort( $distance, SORT_ASC, $resulting_routes );

	echo "\n\n===========\n";
	echo "RESULTS";
	echo "\n===========\n";

	echo "\nFastest route (from " . $input_route['start'] . " to " . $input_route['end'] . ") is the following:\n";
	foreach ( $resulting_routes[0]['waypoints'] as $num => $wp )
		echo "\t" . ($num+1) . " $wp\n";
	echo "\nTOTAL DISTANCE: " . ($resulting_routes[0]['distance']/1000) . "km\n\n";

?>
<?php

	require( 'route.php' );

	// Add start and end to array
	$waypoints = $input_route['waypoints'];
	array_unshift( $waypoints, $input_route['start'] );
	array_push( $waypoints, $input_route['end'] );

	// Set-up variables
	$num_wp = count( $waypoints );
	// [from][to] = distance
	$distances = array();

	/**
	 * Main loop. Loop through every possible set of waypoints.
	 */
	for ( $i = 0; $i < $num_wp; ++$i )
	{
		echo "Getting all possible distance combinations for " . ($i+1) . " out of $num_wp...";

		// Initialize array
		$distances[$i] = array();

		// Loop through all other waypoints
		for ( $j = 0; $j < $num_wp; ++$j )
		{
			// Skip querying if we're looking at the same waypoints
			if ( $j == $i )
			{
				$distances[$i][$j] = 0;
				continue;
			}

			// Skip querying if there already exists such a distance
			if ( isset( $distances[$j][$i] ) && $distances[$j][$i] > 0 )
			{
				$distances[$i][$j] = $distances[$j][$i];
				continue;
			}

			// Setup URL parameters
			$args = array(
				'origins' => $waypoints[$i],
				'destinations' => $waypoints[$j],
				'sensor' => 'false',
				);

			// Create URL
			$url = 'http://maps.googleapis.com/maps/api/distancematrix/json?' . http_build_query( $args );

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
			$distances[$i][$j] = $results->rows[0]->elements[0]->distance->value;
		}

		echo " done! \n";

		// Sleep to prevent query limit
		sleep(2);
	}

	/**
	 * We now have the distances between any two points. We will now BRUTEFORCE THAT FUCKER!
	 */
	// Initializing array positions
	$_s = 0;
	$_e = $num_wp - 1;

	// Require functions
	require( 'func.php' );

	// Init variables
	$numbers = array();
	$permutations = array();
	$results = array();

	// Fill an array with all the numbers of the waypoints
	for ( $i = $_s+1; $i < $_e; ++$i )
		$numbers[] = $i;

	// Find all permutations of waypoint orders and store them in an array
	getpermutations( '0', $numbers, $permutations );

	// Loop through each permutation
	foreach ( $permutations as $permutation )
	{
		// Explode each permutation into an array of waypoints
		$perm_array = explode( ',', $permutation );

		// Add end (start is already there as prefix to permutations function)
		$perm_array[] = $_e;

		// Generate string key
		$permutation = implode( ',', $perm_array );

		// Init array
		$results[$permutation] = 0;

		// Loop through each entry in the permutation
		foreach ( $perm_array as $pos => $point )
		{
			// Store distance unless $point is at the end
			if ( $point != $_e )
				$results[$permutation] += $distances[$point][$perm_array[$pos+1]];
		}
	}

	// Sort results
	asort( $results );

	// Get first element
	list( $shortest_combination ) = array_keys( $results );
	$shortest_distance = $results[$shortest_combination];

	echo "TEST1:";
	print_r( $shortest_combination );

	echo "\nTEST2:";
	print_r( $shortest_distance );

	echo "\n\n===========\n";
	echo "RESULTS";
	echo "\n===========\n";

	echo "\nFastest route (from " . $input_route['start'] . " to " . $input_route['end'] . ") is the following:\n";
	foreach ( explode( ',', $shortest_combination ) as $num => $pos )
		echo "\t" . ($num+1) . " " . $waypoints[$pos] . "\n";
	echo "\nTOTAL DISTANCE: " . ($shortest_distance/1000) . "km\n\n";

?>
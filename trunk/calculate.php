<?php

	// Debugging on or off?
	define( 'DEBUG', true );

	// Let's get the route
	$_input = file_get_contents( "waypoints.txt" );
	$pois = explode( "\n", $_input );

	// Parse
	if ( count( $pois ) > 2 )
	{
		$input_route['start'] = array_shift( $pois );
		$input_route['end'] = array_pop( $pois );

		// What's left is are the waypoints
		$input_route['waypoints'] = $pois;

		if ( count( $pois ) >= 10 )
			echo "WARNING! You have entered more than 10 waypoints.\nThe calculation phase may take a long time!\n\n";
	}
	else
		exit( "Please enter at least a starting position and an ending position!\n" );

	// Add start and end to array
	$waypoints = $input_route['waypoints'];
	array_unshift( $waypoints, $input_route['start'] );
	array_push( $waypoints, $input_route['end'] );

	// Set-up variables
	$num_wp = count( $waypoints );
	// [from][to] = distance
	$distances = array();

	echo "Calculating table of distances...\n";

	/**
	 * Main loop. Loop through every possible set of waypoints.
	 */
	for ( $i = 0; $i < $num_wp; ++$i )
	{
		echo "\t" . ($i+1) . " of $num_wp...";

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
			if ( isset( $distances[$j][$i] ) )
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

		// Only sleep if we are not at the end
		if ( $i != $num_wp )
			sleep(2);

		echo " done! \n";
	}

	echo "\nBeginning calculation phase...\nThis may take a long time depending on how many waypoints you have!";

	/**
	 * We now have the distances between any two points. Bruteforce is not nice :( .
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

	if ( DEBUG )
		$_permutation_start = set_time_marker();

	// Find all permutations of waypoint orders and store them in an array
	find_shortest_distance( '0', $numbers, $_e, $distances, $results );

	if ( DEBUG )
		$_permutation_end = set_time_marker(); 

	/**
	 * Let's print the results, shall we?
	 */
	echo "\n\n===========\n";
	echo "RESULTS";
	echo "\n===========\n";

	echo "\nFastest route is the following:\n";
	foreach ( explode( ',', $results['waypoints'] ) as $num => $pos )
		echo "\t" . ($num+1) . ". " . $waypoints[$pos] . "\n";
	echo "\nTOTAL DISTANCE: " . ($results['distance']/1000) . "km\n";
	if ( DEBUG )
		echo "CALCULATION TIME: " . number_format( ( $_permutation_end - $_permutation_start ), 2 ) . "s\n\n";

?>
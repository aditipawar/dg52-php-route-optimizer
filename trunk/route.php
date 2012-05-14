<?php

	/**
	 * Input the waypoints here, separated by a newline. First line is starting point, last line is destination.
	 */
	$_input = file_get_contents( "waypoints.txt" );
	$pois = explode( "\n", $_input );

	// Parse
	if ( count( $pois ) > 2 )
	{
		$input_route['start'] = array_shift( $pois );
		$input_route['end'] = array_pop( $pois );

		// What's left is are the waypoints
		$input_route['waypoints'] = $pois;
	}
	else
		exit( "Please enter at least a starting position and an ending position!\n" );


?>
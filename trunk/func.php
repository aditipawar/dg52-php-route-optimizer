<?php

	/**
	 * find_shorest_distance()
	 *
	 * @abstract The heavy lifter, finds the shortest distance by permuting the waypoints inputted
	 * and calculating the shortest route out of them all.
	 * Does this recursively since it needs to find the permutations.
	 *
	 * @param string $prefix The prefix to add to all permutations.
	 * @param array $waypoints The ID's of all the waypoints.
	 * @param int $end The ID of the ending waypoint.
	 * @param array &$distances The table of distances between any two ID's.
	 * @param array &$results The result array that the function will write to.
	 *
	 * @return void
	 */
	function find_shortest_distance( $prefix, $waypoints, $end, &$distances, &$results )
	{
		// If we have a full permutation
		if ( count( $waypoints ) == 1 ) {
			// Store the permutation
			$permutation = $prefix . ',' . array_pop($waypoints);

			// Explode the permutation into an array of waypoints
			$perm_array = explode( ',', $permutation );

			// Add end to array
			$perm_array[] = $end;

			// Init array
			$temp_result = 0;

			// Loop through each entry in the permutation
			foreach ( $perm_array as $pos => $point )
			{
				// Store distance unless $point is the end
				if ( $point != $end )
					$temp_result += $distances[$point][$perm_array[$pos+1]];
			}

			// Only store if it is smaller than the previous best. If nothing has been stored yet, store the first value.
			if ( !isset( $results['distance'] ) || $temp_result < $results['distance'] )
			{
				$results['distance'] = $temp_result;
				$results['waypoints'] = $permutation . "," . $end;
			}
		} else {
			for ( $i = 0, $limit = count( $waypoints ); $i < $limit; $i++ ) {
				$tmp = $waypoints;
				unset( $tmp[$i] );

				find_shortest_distance( $prefix . ',' . $waypoints[$i], array_values($tmp), $end, $distances, $results );
			}
		}
	}

	/**
	 * set_time_marker()
	 *
	 * @abstract Stores a marker for a specific point in time, to be used in debugging and such.
	 * Source taken from http://www.tipsntutorials.com/tips/PHP/74.
	 *
	 * @return void
	 */
	function set_time_marker()
	{
		$a = explode( ' ', microtime() );
		return(double) $a[0] + $a[1];
	}

?>
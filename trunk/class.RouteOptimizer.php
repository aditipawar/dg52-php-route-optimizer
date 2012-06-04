<?php

	/**
	 * dG52 PHP Route Optimizer
	 *
	 * @author: Douglas Stridsberg
	 * @email: doggie52@gmail.com
	 * @url: www.douglasstridsberg.com
	 *
	 * Main class file.
	 */

	/**
	 * RouteOptimizer
	 *
	 * @todo Add a property to store the results.
	 * Allow caching of already fetched distances.
	 * Manage screen output.
	 *
	 * @package RouteOptimizer
	 */
	class RouteOptimizer
	{

		/**
		 * Defines whether or not to display additional debug information.
		 *
		 * @var bool
		 */
		const DEBUG = true;

		/**
		 * Table of distances between any two points.
		 * Stored as [from][to] = distance.
		 *
		 * @var array
		 */
		public $distances;

		/**
		 * RouteOptimizer()
		 *
		 * @abstract Constructor, runs the class and calculates the shortest route.
		 *
		 * @param string $start The starting location.
		 * @param array $waypoints The array of waypoints to cover.
		 * @param string $end The ending location.
		 *
		 * @todo Add a return value.
		 */
		public function RouteOptimizer( $start, $waypoints, $end )
		{
			// Possible warning
			if ( count( $waypoints ) >= 10 )
				echo "WARNING! You have entered more than 10 waypoints.\nThe calculation phase may take a long time!\n\n";

			// Add start and end to array
			array_unshift( $waypoints, $start );
			array_push( $waypoints, $end );

			// Set-up variables
			$num_wp = count( $waypoints );
			$this->distances = array();

			echo "Calculating table of distances...\n";

			/**
			 * Main loop. Loop through every possible set of waypoints.
			 */
			for ( $i = 0; $i < $num_wp; ++$i ) {
				echo "\t" . ($i+1) . " of $num_wp...";

				// Initialize array
				$this->distances[$i] = array();

				// Loop through all other waypoints
				for ( $j = 0; $j < $num_wp; ++$j ) {
					// Skip querying if we're looking at the same waypoints
					if ( $j == $i ) {
						$this->distances[$i][$j] = 0;
						continue;
					}

					// Skip querying if there already exists such a distance
					if ( isset( $this->distances[$j][$i] ) ) {
						$this->distances[$i][$j] = $this->distances[$j][$i];
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

					if ( $results->status != "OK" ) {
						echo "ERROR! " . $results->status;
						exit;
					}

					// Copy relevant results to array
					$this->distances[$i][$j] = $results->rows[0]->elements[0]->distance->value;
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

			// Init variables
			$numbers = array();
			$permutations = array();
			$results = array();

			// Fill an array with all the numbers of the waypoints
			for ( $i = $_s+1; $i < $_e; ++$i )
				$numbers[] = $i;

			if ( self::DEBUG )
				$_permutation_start = $this->set_time_marker();

			// Find all permutations of waypoint orders and store them in an array
			$this->find_shortest_distance( '0', $numbers, $_e, $this->distances, $results );

			if ( self::DEBUG )
				$_permutation_end = $this->set_time_marker();

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
			if ( self::DEBUG )
				echo "CALCULATION TIME: " . number_format( ( $_permutation_end - $_permutation_start ), 2 ) . "s\n\n";
		}

		/**
		 * find_shortest_distance()
		 *
		 * @abstract The heavy lifter, finds the shortest distance by permuting the waypoints inputted
		 * and calculating the shortest route out of them all.
		 * Does this recursively since it needs to find the permutations.
		 *
		 * @access private
		 *
		 * @param string $prefix The prefix to add to all permutations.
		 * @param array $waypoints The ID's of all the waypoints.
		 * @param int $end The ID of the ending waypoint.
		 * @param array &$distances The table of distances between any two ID's.
		 * @param array &$results The result array that the function will write to.
		 *
		 * @return void
		 */
		private function find_shortest_distance( $prefix, $waypoints, $end, &$distances, &$results )
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
				foreach ( $perm_array as $pos => $point ) {
					// Store distance unless $point is the end
					if ( $point != $end )
						$temp_result += $distances[$point][$perm_array[$pos+1]];
				}

				// Only store if it is smaller than the previous best. If nothing has been stored yet, store the first value.
				if ( !isset( $results['distance'] ) || $temp_result < $results['distance'] ) {
					$results['distance'] = $temp_result;
					$results['waypoints'] = $permutation . "," . $end;
				}
			} else {
				for ( $i = 0, $limit = count( $waypoints ); $i < $limit; $i++ ) {
					$tmp = $waypoints;
					unset( $tmp[$i] );

					$this->find_shortest_distance( $prefix . ',' . $waypoints[$i], array_values($tmp), $end, $distances, $results );
				}
			}
		}

		/**
		 * set_time_marker()
		 *
		 * @abstract Stores a marker for a specific point in time, to be used in debugging and such.
		 * Source taken from http://www.tipsntutorials.com/tips/PHP/74.
		 *
		 * @access private
		 *
		 * @return double The time marker.
		 */
		private function set_time_marker()
		{
			$a = explode( ' ', microtime() );
			return(double) $a[0] + $a[1];
		}

	}

?>
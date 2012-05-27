<?php

	/**
	 * dG52 PHP Route Optimizer
	 *
	 * @author: Douglas Stridsberg
	 * @email: doggie52@gmail.com
	 * @url: www.douglasstridsberg.com
	 *
	 * Runs the route optimizer.
	 */

	require( "class.RouteOptimizer.php" );

	$obj = new RouteOptimizer( "Sweden",
								array( "Germany", "Monaco", "Switzerland", "France", "United Kingdom" ),
								"Norway"
			);

?>
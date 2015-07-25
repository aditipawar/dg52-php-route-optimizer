# dG52 PHP Route Optimizer by Doggie52 #
### A PHP class to optimize a route between two locations via any number of waypoints ###

Using **dG52's PHP Route Optimizer** you can find the shortest distance between any two locations via any number of waypoints. It is presently rather inefficient and will need to be optimized before it can optimize routes with more than 8-9 waypoints!

## Usage ##
Check out the example usage in the `example.php`-file included in the source!

## Notes ##
Please report any issues in our [Issue Tracker](http://code.google.com/p/dg52-php-route-optimizer/issues/list)! Thank you in advance. The class uses [Google's Distance Matrix API](https://developers.google.com/maps/documentation/distancematrix/) to calculate the distances between any two points.

### Technical stuff ###
The class currently requires `n + (n-1) + (n-2) + ... + 1` requests to be sent to the API and `n!` calculations to be performed, where `n` is the number of waypoints. Use with moderation!
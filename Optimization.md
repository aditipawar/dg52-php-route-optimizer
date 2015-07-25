# Introduction #

On this page we discuss possible improvements to, and optimizations of the algorithm and the script.


# Current limitations #

As of [revision 5](https://code.google.com/p/dg52-php-route-optimizer/source/detail?r=5), the algorithm has the following bottlenecks, in order of severity:
  1. **The `getpermutations()` function (in `func.php`).** It is called to get all possible permutations of a range of numbers and store this as an array, which is both CPU- and memory intensive (`n!` different rows of numbers). For only 10 numbers, this function stores somewhere around **500MB** of data which is plain absurd.
  1. **The Google Maps API Distance Matrix call (in `calculate.php`).** It is called `n^2` times to get the distance between any two waypoints and must be slowed down (`sleep(2)`) to not go over the limits imposed by Google.

## Suggested improvements ##

Here are a number of suggested improvements for each of the above limitations:
  1. 
    * To solve the problem of storing enormous amounts of data on the memory, one could have the function calculate the total distance of each route it finds, directly. If the found distance is not less than the current shortest route it can simply discard it. If it is, it can simply overwrite it.
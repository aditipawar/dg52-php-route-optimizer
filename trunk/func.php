<?php

	function getpermutations($prefix, $characters, &$permutations)
	{
	    if (count($characters) == 1)
	        $permutations[] = $prefix . ',' . array_pop($characters);
	    else
	    {
	        for ($i = 0; $i < count($characters); $i++)
	        {
	            $tmp = $characters;
	            unset($tmp[$i]);

	            getpermutations($prefix . ',' . $characters[$i], array_values($tmp), $permutations);
	        }
	    }
	}

?>
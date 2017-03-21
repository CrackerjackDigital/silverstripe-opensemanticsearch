<?php
namespace OpenSemanticSearch;

trait array_bitfield_map {
	/**
	 * Given an array returns a bitfield with bits set where
	 * values in the array are present and truthish or match a value given in $match
	 *
	 * @param array $array e.g. [ 'yes' => true, 'fred' => 'dagg', 'no' => false ]
	 * @param array $mapBits e.g. [ 'yes' => 0b01010, 'fred' => 16, => 'no' => 4 ]
	 * @param array $match e.g. [ 'fred' => 'dagg' ]
	 *
	 * @return int e.g. 10
	 * @internal param array $map of array key in $array => bitfield mask to set (via or)
	 *
	 */
	public function arr_to_btf( array $array, array $mapBits, array $match = [] ) {
		$btf = 0;
		foreach ($array as $key => $value) {
			if ($value || (array_key_exists($key, $match) && ($match == $value))) {
				if (isset($mapBits[$key])) {
					$btf |= $mapBits[$key];
               }
			}
		}
		return $btf;
	}

	/**
	 * Given a bit field to test and a map of bit values to keys returns an array with those keys
	 * and values of either true or a matching value in the $match array if the bits are all set
	 * in the bitfield for that key. If bits are not set in the bitfield the array entry is
	 * added with the key bit a value of false.
	 *
	 * @param int   $bitfield e.g. 0b11010
	 * @param array $map      e.g. [ 0b01010 => 'yes', 0b10000 => 'fred', 4 => 'no' ]
	 * @param array $match    e.g. [ 'fred' => 'dagg' ]
	 *
	 * @return array e.g. [ 'yes' => true, 'fred' => 'dagg', 'no' => false ]
	 */
	public function btf_to_arr( $bitfield, array $map, array $match = [] ) {
		$array = [];
		foreach ($map as $bits => $key) {
			if (($bitfield & $bits) === $bits) {
				$array[$key] = array_key_exists($key, $match) ? $match[$key] : true;
			} else {
				$array[$key] = false;
			}
		}
		return $array;
	}
}
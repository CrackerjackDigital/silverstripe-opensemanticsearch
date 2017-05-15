<?php

namespace OpenSemanticSearch\Traits;

trait map_key_transform {
	/**
	 * Given a map replaces the keys in it with the key map provided, optionally unsetting the original key.
	 *
	 * @param array $map            e.g. [ 'limit' => 100 ]
	 * @param array $replaceKeys    e.g. [ 'limit' => 'rows' ]
	 * @param bool  $removeReplaced wether to unset the key if found in the map
	 *
	 * @return array e.g. [ 'rows' => 100 ] (limit removed by removeReplaced option)
	 */
	public function map_key_transform( $map, array $replaceKeys = [], $removeReplaced = true ) {
		if (is_array($map)) {
			foreach ( $map as $key => $value ) {
				if ( array_key_exists( $key, $replaceKeys ) ) {
					$map[ $replaceKeys[ $key ] ] = $value;
					if ( $removeReplaced ) {
						unset( $map[ $key ] );
					}
				}
			}

		}
		return $map;
	}
}
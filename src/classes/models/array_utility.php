<?php
/**
 * WP_Framework_Common Classes Models Array Utility
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

use /** @noinspection PhpUndefinedClassInspection */
	JsonSerializable;
use stdClass;
use Traversable;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Array_Utility
 * @package WP_Framework_Common\Classes\Models
 */
class Array_Utility implements \WP_Framework_Core\Interfaces\Singleton {

	use Singleton, Package;

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @param mixed $obj
	 * @param bool $ignore_value
	 *
	 * @return array
	 */
	public function to_array( $obj, $ignore_value = true ) {
		if ( $obj instanceof stdClass ) {
			$obj = get_object_vars( $obj );
		} /** @noinspection PhpUndefinedClassInspection */ elseif ( $obj instanceof JsonSerializable ) {
			$obj = (array) $obj->jsonSerialize();
		} elseif ( $obj instanceof Traversable ) {
			$obj = iterator_to_array( $obj );
		} elseif ( ! is_array( $obj ) ) {
			if ( method_exists( $obj, 'to_array' ) ) {
				$obj = (array) $obj->to_array();
			} elseif ( method_exists( $obj, 'toArray' ) ) {
				$obj = (array) $obj->toArray();
			} elseif ( method_exists( $obj, 'toJson' ) ) {
				$obj = json_decode( $obj->toJson(), true );
			} elseif ( ! $ignore_value && ( ! empty( $obj ) || '' !== (string) $obj ) ) {
				$obj = [ $obj ];
			}
		}
		if ( ! is_array( $obj ) || empty( $obj ) ) {
			return [];
		}

		return $obj;
	}

	/**
	 * @param array|object $array
	 * @param bool $preserve_keys
	 *
	 * @return array
	 */
	public function flatten( $array, $preserve_keys = false ) {
		$array  = $this->to_array( $array );
		$return = [];
		array_walk_recursive( $array, function ( $value, $key ) use ( &$return, $preserve_keys ) {
			if ( $preserve_keys ) {
				$return[ $key ] = $value;
			} else {
				$return[] = $value;
			}
		} );

		return $return;
	}

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function wrap( $value ) {
		if ( is_null( $value ) ) {
			return [];
		}

		return is_array( $value ) ? $value : [ $value ];
	}

	/**
	 * @param array|object $array
	 * @param string|int|array $key
	 *
	 * @return bool
	 */
	public function exists( $array, $key ) {
		$array = $this->to_array( $array );

		if ( is_string( $key ) || is_int( $key ) ) {
			if ( array_key_exists( $key, $array ) ) {
				return true;
			}

			if ( is_int( $key ) || strpos( $key, '.' ) === false ) {
				return false;
			}
			$keys = explode( '.', $key );
		} else {
			$keys = $this->to_array( $key );
		}

		foreach ( $keys as $segment ) {
			$a = $this->to_array( $array );
			if ( array_key_exists( $segment, $a ) ) {
				$array = $a[ $segment ];
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array|object $array
	 * @param mixed $value
	 * @param bool $strict
	 *
	 * @return mixed
	 */
	public function search_key( $array, $value, $strict = false ) {
		return array_search( $value, $this->to_array( $array ), $strict ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	}

	/**
	 * @param array|object $array
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $array, $key, $default = null ) {
		$array = $this->to_array( $array );

		if ( is_null( $key ) ) {
			return $array;
		}

		if ( is_string( $key ) || is_int( $key ) ) {
			if ( array_key_exists( $key, $array ) ) {
				return $array[ $key ];
			}

			if ( is_int( $key ) || strpos( $key, '.' ) === false ) {
				return $this->app->utility->value( $default );
			}
			$keys = explode( '.', $key );
		} else {
			$keys = $this->to_array( $key );
		}

		foreach ( $keys as $segment ) {
			$a = $this->to_array( $array );
			if ( array_key_exists( $segment, $a ) ) {
				$array = $a[ $segment ];
			} else {
				return $this->app->utility->value( $default );
			}
		}

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed ...$keys
	 *
	 * @return mixed
	 */
	public function search( $array, $key, ...$keys ) {
		$array = $this->to_array( $array );
		if ( count( $keys ) > 0 ) {
			$default = array_pop( $keys );
		} else {
			$default = null;
		}

		array_unshift( $keys, $key );
		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				return $array[ $key ];
			}
		}

		return $this->app->utility->value( $default );
	}

	/**
	 * @param array|object $array
	 * @param string|array|null $key
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function set( $array, $key, $value ) {
		$array = $this->to_array( $array );

		if ( is_null( $key ) ) {
			return $array;
		}

		$keys = is_array( $key ) ? $key : explode( '.', $key );
		if ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );
			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = [];
			}
			$array[ $key ] = $this->set( $array[ $key ], $keys, $value );

			return $array;
		}

		$array[ $keys[0] ] = $value;

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string|array|null $key
	 *
	 * @return array
	 */
	public function delete( $array, $key ) {
		$array = $this->to_array( $array );

		if ( is_null( $key ) ) {
			return $array;
		}

		$keys = is_array( $key ) ? $key : explode( '.', $key );
		if ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );
			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = [];
			}
			$array[ $key ] = $this->delete( $array[ $key ], $keys );

			return $array;
		}

		unset( $array[ $keys[0] ] );

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed $default
	 * @param bool $filter
	 *
	 * @return array
	 */
	public function pluck( $array, $key, $default = null, $filter = false ) {
		$array = $this->to_array( $array );

		return array_map(
			function ( $data ) use ( $key, $default ) {
				if ( is_object( $data ) ) {
					$data = (array) $data;
				}

				return is_array( $data ) && array_key_exists( $key, $data ) ? $data[ $key ] : $this->app->utility->value( $default );
			},
			$filter ? array_filter( $array, function ( $data ) use ( $key ) {
				if ( is_object( $data ) ) {
					$data = (array) $data;
				}

				return is_array( $data ) && array_key_exists( $key, $data );
			} ) : $array
		);
	}

	/**
	 * @param array|object $array
	 * @param string|callable $callback
	 *
	 * @return array
	 */
	public function map( $array, $callback ) {
		$array = $this->to_array( $array );

		foreach ( $array as $key => $value ) {
			$array[ $key ] = $this->is_closure( $callback ) ? $this->call_closure( $callback, $value, $key ) : ( is_string( $callback ) && method_exists( $value, $callback ) ? $value->$callback( $key ) : null );
		}

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string|callable $callback
	 *
	 * @return array
	 */
	public function filter( $array, $callback ) {
		$array = $this->to_array( $array );

		foreach ( $array as $key => $value ) {
			if ( ! ( $this->is_closure( $callback ) ? $this->call_closure( $callback, $value, $key ) : ( is_string( $callback ) && method_exists( $value, $callback ) ? $value->$callback( $key ) : true ) ) ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string|callable $callback
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function first( $array, $callback = null, $default = null ) {
		$array = $this->to_array( $array );

		if ( is_null( $callback ) ) {
			if ( empty( $array ) ) {
				return $this->app->utility->value( $default );
			}
			foreach ( $array as $value ) {
				return $value;
			}
		}

		foreach ( $array as $key => $value ) {
			if ( $this->is_closure( $callback ) ? $this->call_closure( $callback, $value, $key ) : ( is_string( $callback ) && method_exists( $value, $callback ) ? $value->$callback( $key ) : false ) ) {
				return $value;
			}
		}

		return $this->app->utility->value( $default );
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 *
	 * @return array
	 */
	public function pluck_unique( $array, $key ) {
		return array_values( array_unique( $this->pluck( $array, $key, null, true ) ) );
	}

	/**
	 * @param array|object $array
	 * @param string|null $key
	 * @param string|null $value
	 *
	 * @return array
	 */
	public function combine( $array, $key, $value = null ) {
		$array = $this->to_array( $array );
		if ( isset( $key ) ) {
			$keys   = $this->pluck( $array, $key );
			$values = is_null( $value ) ? $array : $this->pluck( $array, $value );
		} else {
			$keys   = array_unique( $array );
			$values = $keys;
		}

		return array_combine( $keys, $values );
	}

	/**
	 * @param array|object $array
	 * @param callable $callback
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function aggregate( $array, $callback, $extractor ) {
		$array = $this->to_array( $array );
		$now   = $extractor( array_shift( $array ) );
		foreach ( $array as $item ) {
			$now = $callback( $now, $extractor( $item ) );
		}

		return $now;
	}

	/**
	 * @param array|object $array
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function sum( $array, $extractor ) {
		return $this->aggregate( $array, function ( $now, $next ) {
			return $now + $next;
		}, $extractor );
	}

	/**
	 * @param array|object $array
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function mul( $array, $extractor ) {
		return $this->aggregate( $array, function ( $now, $next ) {
			return $now * $next;
		}, $extractor );
	}

	/**
	 * @param array|object $array
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function max( $array, $extractor ) {
		return $this->aggregate( $array, function ( $now, $next ) {
			return $next > $now ? $next : $now;
		}, $extractor );
	}

	/**
	 * @param array|object $array
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function min( $array, $extractor ) {
		return $this->aggregate( $array, function ( $now, $next ) {
			return $next < $now ? $next : $now;
		}, $extractor );
	}

	/**
	 * @param array|object $array
	 * @param callable $extractor
	 *
	 * @return float
	 */
	public function ave( $array, $extractor ) {
		$array = $this->to_array( $array );
		if ( empty( $array ) ) {
			return 0;
		}

		return (float) $this->sum( $array, $extractor ) / count( $array );
	}
}

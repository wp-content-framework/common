<?php
/**
 * WP_Framework_Common Classes Models Collection
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use WP_Framework;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Collection
 * @package WP_Framework_Common\Classes\Models
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate {

	/**
	 * @var WP_Framework $app
	 */
	private $app;

	/**
	 * @var array $items
	 */
	private $items;

	/**
	 * Collection constructor.
	 *
	 * @param WP_Framework $app
	 * @param mixed $items
	 */
	public function __construct( WP_Framework $app, $items = [] ) {
		$this->app   = $app;
		$this->items = $this->app->array->to_array( $items, false );
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return $this->items;
	}

	/**
	 * @return ArrayIterator|Traversable
	 */
	public function getIterator() {
		return new ArrayIterator( $this->items );
	}

	/**
	 * @param bool $preserve_keys
	 *
	 * @return $this
	 */
	public function flatten( $preserve_keys = false ) {
		return new static( $this->app, $this->app->array->flatten( $this->items, $preserve_keys ) );
	}

	/**
	 * @param string|array|null $key
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set( $key, $value ) {
		$this->items = $this->app->array->set( $this->items, $key, $value );

		return $this;
	}

	/**
	 * @param mixed $item
	 *
	 * @return $this
	 */
	public function add( $item ) {
		$this->items[] = $item;

		return $this;
	}


	/**
	 * @param string|array|null $key
	 *
	 * @return $this
	 */
	public function delete( $key ) {
		$this->items = $this->app->array->delete( $this->items, $key );

		return $this;
	}

	/**
	 * @param mixed $items
	 *
	 * @return $this
	 */
	public function merge( $items ) {
		return new static( $this->app, array_merge( $this->items, $this->app->array->to_array( $items, false ) ) );
	}

	/**
	 * @param string|callable $callback
	 *
	 * @return $this
	 */
	public function map( $callback ) {
		return new static( $this->app, $this->app->array->map( $this->items, $callback ) );
	}

	/**
	 * @param string|callable $callback
	 *
	 * @return $this
	 */
	public function filter( $callback ) {
		return new static( $this->app, $this->app->array->filter( $this->items, $callback ) );
	}

	/**
	 * @return $this
	 */
	public function unique() {
		return new static( $this->app, array_unique( $this->items ) );
	}

	/**
	 * @return $this
	 */
	public function values() {
		return new static( $this->app, array_values( $this->items ) );
	}

	/**
	 * @return $this
	 */
	public function keys() {
		return new static( $this->app, array_keys( $this->items ) );
	}

	/**
	 * @param int $offset
	 * @param int $length
	 *
	 * @return $this
	 */
	public function slice( $offset, $length = null ) {
		return new static( $this->app, array_slice( $this->items, $offset, $length, true ) );
	}

	/**
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function take( $limit ) {
		if ( $limit < 0 ) {
			return $this->slice( $limit, abs( $limit ) );
		}

		return $this->slice( 0, $limit );
	}

	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function chunk( $size ) {
		if ( $size <= 0 ) {
			return new static( $this->app );
		}

		$chunks = [];
		foreach ( array_chunk( $this->items, $size, true ) as $chunk ) {
			$chunks[] = new static( $this->app, $chunk );
		}

		return new static( $this->app, $chunks );
	}

	/**
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function sort( $callback = null ) {
		$items = $this->items;
		if ( $callback && is_callable( $callback ) ) {
			uasort( $items, $callback );
		} else {
			asort( $items, $callback );
		}

		return new static( $this->app, $items );
	}

	/**
	 * @return $this
	 */
	public function reverse() {
		return new static( $this->app, array_reverse( $this->items, true ) );
	}

	/**
	 * @return int
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->items );
	}

	/**
	 * @param mixed $value
	 * @param bool $strict
	 *
	 * @return bool
	 */
	public function exists( $value, $strict = false ) {
		return false !== $this->app->array->search_key( $this->items, $value, $strict );
	}

	/**
	 * @param string|int|array $key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return $this->app->array->exists( $this->items, $key );
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		return $this->app->array->get( $this->items, $key, $default );
	}

	/**
	 * @param callable $callback
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function first( $callback = null, $default = null ) {
		return $this->app->array->first( $this->items, $callback, $default );
	}

	/**
	 * @param string $key
	 * @param mixed ...$keys
	 *
	 * @return mixed
	 */
	public function search( $key, ...$keys ) {
		return $this->app->array->search( $this->items, $key, ...$keys );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @param bool $filter
	 *
	 * @return array
	 */
	public function pluck( $key, $default = null, $filter = false ) {
		return $this->app->array->pluck( $this->items, $key, $default, $filter );
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function pluck_unique( $key ) {
		return $this->app->array->pluck_unique( $this->items, $key );
	}

	/**
	 * @param string|null $key
	 * @param string|null $value
	 *
	 * @return array
	 */
	public function combine( $key, $value = null ) {
		return $this->app->array->combine( $this->items, $key, $value );
	}

	/**
	 * @param callable $callback
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function aggregate( $callback, $extractor ) {
		return $this->app->array->aggregate( $this->items, $callback, $extractor );
	}

	/**
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function sum( $extractor ) {
		return $this->app->array->sum( $this->items, $extractor );
	}

	/**
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function mul( $extractor ) {
		return $this->app->array->mul( $this->items, $extractor );
	}

	/**
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function max( $extractor ) {
		return $this->app->array->max( $this->items, $extractor );
	}

	/**
	 * @param callable $extractor
	 *
	 * @return mixed
	 */
	public function min( $extractor ) {
		return $this->app->array->min( $this->items, $extractor );
	}

	/**
	 * @param callable $extractor
	 *
	 * @return float
	 */
	public function ave( $extractor ) {
		return $this->app->array->ave( $this->items, $extractor );
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return array_key_exists( $offset, $this->items );
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->items[ $offset ];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->items[] = $value;
		} else {
			$this->items[ $offset ] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->items[ $offset ] );
	}
}

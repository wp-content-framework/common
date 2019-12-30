<?php
/**
 * WP_Framework_Common Models Collection Test
 *
 * @version 0.0.49
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

use WP_Framework_Common\Classes\Models\Collection;
use WP_Framework_Common\Tests\TestCase;

/**
 * Class CollectionTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CollectionTest extends TestCase {

	/**
	 * @var Collection $collection1
	 */
	private $collection1;

	/**
	 * @var Collection $collection2
	 */
	private $collection2;

	protected function setUp() {
		$this->collection1 = new Collection( static::$app, [
			1,
			'test2',
			3  => [ 'test3', 'test4' ],
			10 => '10',
		] );
		$this->collection2 = new Collection( static::$app, [
			[ 1, 10 ],
			[ 2, 20 ],
			[ 3, 30 ],
		] );
	}

	public function test_to_array() {
		$this->assertEquals( [
			0  => 1,
			1  => 'test2',
			3  => [ 'test3', 'test4' ],
			10 => '10',
		], $this->collection1->to_array() );
	}

	public function test_flatten() {
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			2 => 'test3',
			3 => 'test4',
			4 => '10',
		], $this->collection1->flatten()->to_array() );
		$this->assertEquals( [
			0  => 'test3',
			1  => 'test4',
			10 => '10',
		], $this->collection1->flatten( true )->to_array() );
	}

	public function test_set() {
		$this->assertEquals( [
			0  => 1,
			1  => 'test2',
			3  => [ 'test3', 'test4' ],
			10 => '10',
			11 => 11,
		], $this->collection1->set( 11, 11 )->to_array() );
		$this->assertEquals( [
			0  => 1,
			1  => 'test2',
			3  => [ 'test3', 'test4' ],
			10 => 10,
			11 => 11,
		], $this->collection1->set( 10, 10 )->to_array() );
	}

	public function test_add() {
		$this->assertEquals( [
			0  => 1,
			1  => 'test2',
			3  => [ 'test3', 'test4' ],
			10 => '10',
			11 => 11,
		], $this->collection1->add( 11 )->to_array() );
	}

	public function test_merge() {
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			2 => [ 'test3', 'test4' ],
			3 => '10',
			4 => 11,
		], $this->collection1->merge( 11 )->to_array() );
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			2 => [ 'test3', 'test4' ],
			3 => '10',
			4 => 'merge1',
			5 => 'merge2',
		], $this->collection1->merge( [ 'merge1', 'merge2' ] )->to_array() );
	}

	public function test_delete() {
		$this->assertEquals( [
			1  => 'test2',
			3  => [ 'test3', 'test4' ],
			10 => '10',
		], $this->collection1->delete( 0 )->to_array() );
		$this->assertEquals( [
			1  => 'test2',
			3  => [ 'test3', 'test4' ],
			10 => '10',
		], $this->collection1->delete( 100 )->to_array() );
	}

	public function test_map() {
		$this->assertEquals(
			[
				0 => '1',
				1 => 'test2',
				2 => 'test3',
				3 => 'test4',
				4 => '10',
			],
			$this->collection1->flatten()->map(
				function ( $item ) {
					return strval( $item );
				}
			)->to_array()
		);
	}

	public function test_filter() {
		$this->assertEquals(
			[
				1 => 'test2',
				2 => 'test3',
				3 => 'test4',
				4 => '10',
			],
			$this->collection1->flatten()->filter(
				function ( $item ) {
					return is_string( $item );
				}
			)->to_array()
		);
	}

	public function test_unique() {
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			2 => 'test3',
			3 => 'test4',
			4 => '10',
		], $this->collection1->flatten()->merge( [ 1, 'test2', 1 ] )->unique()->to_array() );
	}

	public function test_values() {
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			2 => [ 'test3', 'test4' ],
			3 => '10',
		], $this->collection1->values()->to_array() );
	}

	public function test_keys() {
		$this->assertEquals( [
			0,
			1,
			3,
			10,
		], $this->collection1->keys()->to_array() );
	}

	public function test_slice() {
		$this->assertEquals( [
			1 => 'test2',
			3 => [ 'test3', 'test4' ],
		], $this->collection1->slice( 1, 2 )->to_array() );
	}

	public function test_take() {
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			3 => [ 'test3', 'test4' ],
		], $this->collection1->take( 3 )->to_array() );
		$this->assertEquals( [
			10 => '10',
		], $this->collection1->take( -1 )->to_array() );
	}

	public function test_chunk() {
		$chunk = $this->collection1->chunk( 3 )->to_array();
		$this->assertCount( 2, $chunk );
		$this->assertEquals( [
			0 => 1,
			1 => 'test2',
			3 => [ 'test3', 'test4' ],
		], $chunk[0]->to_array() );
		$this->assertEquals( [
			10 => '10',
		], $chunk[1]->to_array() );
	}

	public function test_sort() {
		$this->assertEquals( [
			0 => 'test2',
			1 => 'test3',
			2 => 'test4',
			3 => 1,
			4 => '10',
		], $this->collection1->flatten()->sort()->values()->to_array() );
	}

	public function test_reverse() {
		$this->assertEquals( [
			4 => 'test2',
			3 => 'test3',
			2 => 'test4',
			1 => 1,
			0 => '10',
		], $this->collection1->flatten()->sort()->reverse()->values()->to_array() );
	}

	public function test_count() {
		$this->assertEquals( 4, $this->collection1->count() );
		$this->assertEquals( 5, $this->collection1->flatten()->count() );
	}

	public function test_is_empty() {
		$this->assertEquals( false, $this->collection1->is_empty() );
		$this->assertEquals( true, $this->collection1->filter( function () {
			return false;
		} )->is_empty() );
	}

	public function test_exists() {
		$this->assertEquals( true, $this->collection1->exists( '10' ) );
		$this->assertEquals( true, $this->collection1->exists( 10 ) );
		$this->assertEquals( false, $this->collection1->exists( '11' ) );
		$this->assertEquals( false, $this->collection1->exists( 11 ) );
	}

	public function test_get() {
		$this->assertEquals( '10', $this->collection1->get( '10' ) );
		$this->assertEquals( '10', $this->collection1->get( 10 ) );
		$this->assertEquals( null, $this->collection1->get( '11' ) );
		$this->assertEquals( null, $this->collection1->get( 11 ) );
	}

	public function test_search() {
		$this->assertEquals( '10', $this->collection1->search( 10 ) );
		$this->assertEquals( '10', $this->collection1->search( 11, 12, 10, 'test' ) );
		$this->assertEquals( 'test', $this->collection1->search( 11, 12, 13, 'test' ) );
	}

	public function test_pluck() {
		$this->assertEquals( [
			0  => null,
			1  => null,
			3  => 'test4',
			10 => null,
		], $this->collection1->pluck( 1 ) );
		$this->assertEquals( [
			0  => 'test',
			1  => 'test',
			3  => 'test4',
			10 => 'test',
		], $this->collection1->pluck( 1, 'test' ) );
		$this->assertEquals( [
			3 => 'test4',
		], $this->collection1->pluck( 1, null, true ) );
	}

	public function test_pluck_unique() {
		$this->assertEquals( [
			'test4',
			'test5',
		], $this->collection1->merge( [ [ 'test', 'test4' ], [ 'test', 'test5' ] ] )->pluck_unique( 1 ) );
	}

	public function test_combine() {
		$this->assertEquals( [
			10 => [ 1, 10 ],
			20 => [ 2, 20 ],
			30 => [ 3, 30 ],
		], $this->collection2->combine( 1 ) );
		$this->assertEquals( [
			10 => 1,
			20 => 2,
			30 => 3,
		], $this->collection2->combine( 1, 0 ) );
	}

	public function test_aggregate() {
		$this->assertEquals( 60, $this->collection2->aggregate(
			function ( $now, $next ) {
				return $now + $next;
			},
			function ( $item ) {
				return $item[1];
			}
		) );
	}

	public function test_sum() {
		$this->assertEquals( 60, $this->collection2->sum( function ( $item ) {
			return $item[1];
		} ) );
	}

	public function test_mul() {
		$this->assertEquals( 6000, $this->collection2->mul( function ( $item ) {
			return $item[1];
		} ) );
	}

	public function test_max() {
		$this->assertEquals( 30, $this->collection2->max( function ( $item ) {
			return $item[1];
		} ) );
	}

	public function test_min() {
		$this->assertEquals( 10, $this->collection2->min( function ( $item ) {
			return $item[1];
		} ) );
	}

	public function test_ave() {
		$this->assertEquals( 20, $this->collection2->ave( function ( $item ) {
			return $item[1];
		} ) );
	}
}

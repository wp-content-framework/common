<?php
/**
 * WP_Framework_Common Models Utility Test
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

use Exception;
use WP_Framework_Common\Classes\Models\Array_Utility;
use WP_Framework_Common\Classes\Models\File_Utility;
use WP_Framework_Common\Classes\Models\String_Utility;
use WP_Framework_Common\Classes\Models\Utility;
use WP_Framework_Common\Tests\TestCase;

require_once __DIR__ . DS . 'misc' . DS . 'collection.php';
require_once __DIR__ . DS . 'misc' . DS . 'data.php';

/**
 * Class UtilityTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UtilityTest extends TestCase {

	/**
	 * @var Utility $utility
	 */
	private static $utility;

	/**
	 * @var Array_Utility $array
	 */
	private static $array;

	/**
	 * @var File_Utility $file
	 */
	private static $file;

	/**
	 * @var String_Utility $string
	 */
	private static $string;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$utility = Utility::get_instance( static::$app );
		static::$array   = Array_Utility::get_instance( static::$app );
		static::$file    = File_Utility::get_instance( static::$app );
		static::$string  = String_Utility::get_instance( static::$app );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$file->delete_upload_dir( static::$app );
	}

	/**
	 * @dataProvider provider_test_flatten
	 *
	 * @param array $array
	 * @param bool $preserve_keys
	 * @param array $expected
	 */
	public function test_flatten( $array, $preserve_keys, $expected ) {
		$this->assertEquals( $expected, static::$utility->flatten( $array, $preserve_keys ) );
		$this->assertEquals( $expected, static::$array->flatten( $array, $preserve_keys ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_flatten() {
		return [
			[
				[],
				false,
				[],
			],
			[
				[
					[ 'test1', 'test2' ],
					[ 'test3', 'test4' ],
				],
				false,
				[ 'test1', 'test2', 'test3', 'test4' ],
			],
			[
				[
					[
						'a' => 'test1',
						'b' => 'test2',
					],
					[
						'c' => 'test3',
						'd' => 'test4',
					],
				],
				false,
				[ 'test1', 'test2', 'test3', 'test4' ],
			],
			[
				[
					[
						'a' => 'test1',
						'b' => 'test2',
					],
					[
						'c' => 'test3',
						'd' => 'test4',
					],
				],
				true,
				[
					'a' => 'test1',
					'b' => 'test2',
					'c' => 'test3',
					'd' => 'test4',
				],
			],
			[
				[
					[
						'a' => 'test1',
						'b' => 'test2',
					],
					[
						'a' => 'test3',
						'b' => 'test4',
					],
				],
				true,
				[
					'a' => 'test3',
					'b' => 'test4',
				],
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_value
	 *
	 * @param array $expected
	 * @param mixed $obj
	 * @param bool $ignore_value
	 */
	public function test_get_array_value( $expected, $obj, $ignore_value = true ) {
		$this->assertEquals( $expected, static::$utility->get_array_value( $obj, $ignore_value ) );
		$this->assertEquals( $expected, static::$array->to_array( $obj, $ignore_value ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_value() {
		return [
			[
				[],
				'test',
			],
			[
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				(object) [
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
			],
			[
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				new Misc\Collection( [
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				] ),
			],
			[
				[ 'test' ],
				'test',
				false,
			],
			[
				[ '0' ],
				'0',
				false,
			],
			[
				[ 0 ],
				0,
				false,
			],
			[
				[],
				false,
				false,
			],
			[
				[],
				null,
				false,
			],
			[
				[],
				'',
				false,
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_wrap
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_array_wrap( $value, $expected ) {
		$this->assertEquals( $expected, static::$utility->array_wrap( $value ) );
		$this->assertEquals( $expected, static::$array->wrap( $value ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_wrap() {
		return [
			[
				null,
				[],
			],
			[
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
			],
			[
				'test',
				[ 'test' ],
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_exists
	 *
	 * @param bool $expected
	 * @param array $array
	 * @param string $key
	 */
	public function test_array_exists( $expected, $array, $key ) {
		$this->assertEquals( $expected, static::$array->exists( $array, $key ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_exists() {
		return [
			[
				false,
				[],
				'test1',
			],
			[
				true,
				[ 'test2' => 2 ],
				'test2',
			],
			[
				false,
				[ 'test3_1' => [ 'test3_2' => 3 ] ],
				'test3_1.test_3',
			],
			[
				true,
				[ 'test4_1' => [ 'test4_2' => 4 ] ],
				'test4_1',
			],
			[
				true,
				[ 'test5_1' => [ 'test5_2' => 5 ] ],
				'test5_1.test5_2',
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_search_key
	 *
	 * @param mixed $expected
	 * @param array $array
	 * @param mixed $value
	 * @param bool $strict
	 */
	public function test_array_search_key( $expected, $array, $value, $strict ) {
		$this->assertEquals( $expected, static::$array->search_key( $array, $value, $strict ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_search_key() {
		return [
			[
				false,
				[],
				'test1',
				false,
			],
			[
				false,
				[],
				'test2',
				true,
			],
			[
				'test3',
				[ 'test3' => 3 ],
				3,
				true,
			],
			[
				false,
				[ 4 => '4' ],
				4,
				true,
			],
			[
				4,
				[ 4 => '4' ],
				4,
				false,
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_get
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @param mixed $expected
	 */
	public function test_array_get( $array, $key, $default, $expected ) {
		$this->assertEquals( $expected, static::$utility->array_get( $array, $key, $default ) );
		$this->assertEquals( $expected, static::$array->get( $array, $key, $default ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_get() {
		return [
			[
				[],
				'test',
				null,
				null,
			],
			[
				[
					'test1' => true,
					'test2' => 100,
				],
				'test2',
				false,
				100,
			],
			[
				[
					'test1' => true,
					'test2' => 100,
				],
				'test3',
				'default',
				'default',
			],
			[
				[
					'test1' => [
						'test1-1' => true,
						'test1-2' => 100,
					],
					'test2' => 200,
				],
				'test1.test1-2',
				null,
				100,
			],
			[
				[
					'test1' => [
						'test1-1' => true,
						'test1-2' => 100,
					],
					'test2' => 200,
				],
				'test2.test2-1',
				function () {
					return 'default';
				},
				'default',
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_search
	 *
	 * @param mixed $expected
	 * @param array $array
	 * @param string $key
	 * @param array $keys
	 */
	public function test_array_search( $expected, $array, $key, $keys = [] ) {
		$this->assertEquals( $expected, static::$utility->array_search( $array, $key, ...$keys ) );
		$this->assertEquals( $expected, static::$array->search( $array, $key, ...$keys ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_search() {
		return [
			[
				1,
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				'test1',
			],
			[
				2,
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				'test10',
				[
					'test2',
					'test3',
					'default',
				],
			],
			[
				null,
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				'test10',
			],
			[
				'default',
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				'test10',
				[
					'test20',
					'test30',
					'default',
				],
			],
			[
				'default',
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				'test10',
				[
					'test20',
					'test30',
					function () {
						return 'default';
					},
				],
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_set
	 * @depends      test_array_get
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_array_set( $array, $key, $value ) {
		$array2 = $array;
		$array  = static::$utility->array_set( $array, $key, $value );
		$this->assertEquals( $value, static::$utility->array_get( $array, $key ) );
		$array2 = static::$array->set( $array2, $key, $value );
		$this->assertEquals( $value, static::$array->get( $array2, $key ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_array_set() {
		return [
			[
				[],
				'test',
				null,
			],
			[
				[
					'test' => true,
				],
				'test',
				100,
			],
			[
				[
					'test' => true,
				],
				'test2',
				false,
			],
			[
				[
					'test' => true,
				],
				'test',
				[
					'test1' => true,
				],
			],
			[
				[
					'test' => true,
				],
				'test2.test3',
				[
					'test4' => 'test5',
				],
			],
		];
	}

	/**
	 * @dataProvider provider_test_array_delete
	 *
	 * @param array $array
	 * @param string $key
	 * @param string|null $key2
	 */
	public function test_array_delete( $array, $key, $key2 = null ) {
		$array = static::$array->delete( $array, $key );
		$this->assertNull( static::$array->get( $array, $key, null ) );
		if ( isset( $key2 ) ) {
			$this->assertNotNull( static::$array->get( $array, $key2, null ) );
		}
	}

	/**
	 * @return array
	 */
	public function provider_test_array_delete() {
		return [
			[
				[],
				'test1',
			],
			[
				[
					'test1' => 'test1',
					'test2' => 'test2',
				],
				'test1',
				'test2',
			],
			[
				[
					'test1' => [
						'test2' => 'test3',
						'test4' => 'test5',
					],
				],
				'test1.test2',
				'test1.test4',
			],
		];
	}

	/**
	 * @dataProvider provider_test_pluck
	 *
	 * @param $expected
	 * @param $array
	 * @param $key
	 * @param $default
	 * @param $filter
	 */
	public function test_pluck( $expected, $array, $key, $default, $filter ) {
		$this->assertEquals( $expected, static::$array->pluck( $array, $key, $default, $filter ) );
	}

	/**
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function provider_test_pluck() {
		return [
			[
				[],
				[],
				'test',
				null,
				false,
			],
			[
				[ null, null, null, null ],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test',
				null,
				false,
			],
			[
				[],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test',
				null,
				true,
			],
			[
				[ 1, 2, null, 4 ],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[ 'test2' => 30 ],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test1',
				null,
				false,
			],
			[
				[
					0 => 1,
					1 => 2,
					3 => 4,
				],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[ 'test2' => 30 ],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test1',
				null,
				true,
			],
		];
	}

	/**
	 * @dataProvider provider_test_map
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_map( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->map( $array, $callback ) );
	}

	/**
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function provider_test_map() {
		return [
			[
				[],
				[],
				function ( $value ) {
					return $value;
				},
			],
			[
				[ 1, 2, 3, 4 ],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				function ( $value ) {
					return $value['test1'];
				},
			],
			[
				[
					100 => 1,
					200 => 2,
					300 => 3,
					400 => 4,
				],
				[
					100 => [
						'test1' => 1,
						'test2' => 10,
					],
					200 => [
						'test1' => 2,
						'test2' => 20,
					],
					300 => [
						'test1' => 3,
						'test2' => 30,
					],
					400 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				function ( $value ) {
					return $value['test1'];
				},
			],
			[
				[
					100 => '1/100',
					200 => '2/200',
					300 => '3/300',
					400 => '4/400',
				],
				[
					100 => [
						'test1' => 1,
						'test2' => 10,
					],
					200 => [
						'test1' => 2,
						'test2' => 20,
					],
					300 => [
						'test1' => 3,
						'test2' => 30,
					],
					400 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				function ( $value, $key ) {
					return $value['test1'] . '/' . $key;
				},
			],
			[
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
				[
					'test1' => new Misc\Data( 1 ),
					'test2' => new Misc\Data( 2 ),
					'test3' => new Misc\Data( 3 ),
				],
				'map_test',
			],
		];
	}

	/**
	 * @dataProvider provider_test_filter
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_filter( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->filter( $array, $callback ) );
	}

	/**
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function provider_test_filter() {
		$tmp = [
			'test1' => new Misc\Data( 1 ),
			'test2' => new Misc\Data( 2 ),
			'test3' => new Misc\Data( 3 ),
		];

		return [
			[
				[],
				[],
				function () {
					return false;
				},
			],
			[
				[
					1 => [
						'test1' => 2,
						'test2' => 20,
					],
					3 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				function ( $value ) {
					return 0 === $value['test1'] % 2;
				},
			],
			[
				[
					200 => [
						'test1' => 2,
						'test2' => 20,
					],
					400 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				[
					100 => [
						'test1' => 1,
						'test2' => 10,
					],
					200 => [
						'test1' => 2,
						'test2' => 20,
					],
					300 => [
						'test1' => 3,
						'test2' => 30,
					],
					400 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				function ( $value ) {
					return 0 === $value['test1'] % 2;
				},
			],
			[
				[
					400 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				[
					100 => [
						'test1' => 1,
						'test2' => 10,
					],
					200 => [
						'test1' => 2,
						'test2' => 20,
					],
					300 => [
						'test1' => 3,
						'test2' => 30,
					],
					400 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				function ( $value, $key ) {
					return 0 === $value['test1'] % 2 && 200 < $key;
				},
			],
			[
				[
					'test1' => $tmp['test1'],
					'test3' => $tmp['test3'],
				],
				$tmp,
				'map_filter',
			],
		];
	}

	/**
	 * @dataProvider provider_test_first
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 * @param $default
	 */
	public function test_first( $expected, $array, $callback, $default ) {
		$this->assertEquals( $expected, static::$array->first( $array, $callback, $default ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_first() {
		return [
			[
				null,
				[],
				function () {
					return false;
				},
				null,
			],
			[
				null,
				[ 1, 2, 3, 4 ],
				function () {
					return false;
				},
				null,
			],
			[
				2,
				[ 1, 2, 3, 4 ],
				function ( $value ) {
					return 0 === $value % 2;
				},
				null,
			],
			[
				1,
				[ 1, 2, 3, 4 ],
				null,
				null,
			],
		];
	}

	/**
	 * @dataProvider provider_test_pluck_unique
	 *
	 * @param $expected
	 * @param $array
	 * @param $key
	 */
	public function test_pluck_unique( $expected, $array, $key ) {
		$this->assertEquals( $expected, static::$array->pluck_unique( $array, $key ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_pluck_unique() {
		return [
			[
				[ 1, 2, 3, 4 ],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test1',
			],
			[
				[
					0 => 1,
					1 => 2,
					2 => 4,
				],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 2,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test1',
			],
		];
	}

	/**
	 * @dataProvider provider_test_combine
	 *
	 * @param $expected
	 * @param $array
	 * @param $key
	 * @param $value
	 */
	public function test_combine( $expected, $array, $key, $value ) {
		$this->assertEquals( $expected, static::$array->combine( $array, $key, $value ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_combine() {
		return [
			[
				[
					1 => 10,
					2 => 20,
					3 => 30,
					4 => 40,
				],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test1',
				'test2',
			],
			[
				[
					1 => [
						'test1' => 1,
						'test2' => 10,
					],
					2 => [
						'test1' => 2,
						'test2' => 20,
					],
					3 => [
						'test1' => 3,
						'test2' => 30,
					],
					4 => [
						'test1' => 4,
						'test2' => 40,
					],
				],
				[
					[
						'test1' => 1,
						'test2' => 10,
					],
					[
						'test1' => 2,
						'test2' => 20,
					],
					[
						'test1' => 3,
						'test2' => 30,
					],
					[
						'test1' => 4,
						'test2' => 40,
					],
				],
				'test1',
				null,
			],
			[
				[
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
				],
				[
					1,
					2,
					3,
					4,
				],
				null,
				null,
			],
		];
	}

	/**
	 * @dataProvider provider_test_replace
	 *
	 * @param string $string
	 * @param array $data
	 * @param string $expected
	 */
	public function test_replace( $string, $data, $expected ) {
		$this->assertEquals( $expected, static::$utility->replace( $string, $data ) );
		$this->assertEquals( $expected, static::$string->replace( $string, $data ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_replace() {
		return [
			[
				'test',
				[ 'a' => 'b' ],
				'test',
			],
			[
				'test1${a}test2',
				[ 'a' => 'b' ],
				'test1btest2',
			],
			[
				'test1${test}test2',
				[ 'test' => '' ],
				'test1test2',
			],
		];
	}

	/**
	 * @dataProvider provider_test_explode
	 *
	 * @param string $string
	 * @param string|array $delimiter
	 * @param array $expected
	 */
	public function test_explode( $string, $delimiter, $expected ) {
		$this->assertEquals( $expected, static::$string->explode( $string, $delimiter ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_explode() {
		return [
			[
				'test',
				',',
				[ 'test' ],
			],
			[
				'test1,test2',
				',',
				[ 'test1', 'test2' ],
			],
			[
				'test1,test2 , test1 ',
				',',
				[ 'test1', 'test2' ],
			],
			[
				'test1|test2 , test1 ',
				[ ',', '|' ],
				[ 'test1', 'test2' ],
			],
		];
	}

	/**
	 * @dataProvider provider_test_starts_with
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $expected
	 */
	public function test_starts_with( $haystack, $needle, $expected ) {
		$this->assertEquals( $expected, static::$utility->starts_with( $haystack, $needle ) );
		$this->assertEquals( $expected, static::$string->starts_with( $haystack, $needle ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_starts_with() {
		return [
			[
				'abc',
				'a',
				true,
			],
			[
				'abc',
				'ab',
				true,
			],
			[
				'abc',
				'abc',
				true,
			],
			[
				'abc',
				'abcd',
				false,
			],
			[
				'abc',
				'b',
				false,
			],
			[
				'abc',
				'bcd',
				false,
			],
			[
				'',
				'',
				false,
			],
			[
				'',
				'a',
				false,
			],
			[
				'a',
				'',
				false,
			],
		];
	}

	/**
	 * @dataProvider provider_test_ends_with
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $expected
	 */
	public function test_ends_with( $haystack, $needle, $expected ) {
		$this->assertEquals( $expected, static::$utility->ends_with( $haystack, $needle ) );
		$this->assertEquals( $expected, static::$string->ends_with( $haystack, $needle ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_ends_with() {
		return [
			[
				'abc',
				'c',
				true,
			],
			[
				'abc',
				'bc',
				true,
			],
			[
				'abc',
				'abc',
				true,
			],
			[
				'abc',
				'0abc',
				false,
			],
			[
				'abc',
				'b',
				false,
			],
			[
				'abc',
				'0ba',
				false,
			],
			[
				'',
				'',
				false,
			],
			[
				'',
				'a',
				false,
			],
			[
				'a',
				'',
				false,
			],
		];
	}

	/**
	 * @dataProvider provider_test_snake
	 *
	 * @param $expected
	 * @param $value
	 * @param string $delimiter
	 */
	public function test_snake( $expected, $value, $delimiter = '_' ) {
		$this->assertEquals( $expected, static::$utility->snake( $value, $delimiter ) );
		$this->assertEquals( $expected, static::$string->snake( $value, $delimiter ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_snake() {
		return [
			[
				'snake_case',
				'snakeCase',
			],
			[
				'kebab-case',
				'kebabCase',
				'-',
			],
		];
	}

	/**
	 * @dataProvider provider_test_camel
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_camel( $value, $expected ) {
		$this->assertEquals( $expected, static::$utility->camel( $value ) );
		$this->assertEquals( $expected, static::$string->camel( $value ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_camel() {
		return [
			[
				'camel-test',
				'camelTest',
			],
			[
				'camel_test',
				'camelTest',
			],
		];
	}

	/**
	 * @dataProvider provider_test_studly
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_studly( $value, $expected ) {
		$this->assertEquals( $expected, static::$utility->studly( $value ) );
		$this->assertEquals( $expected, static::$string->studly( $value ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_studly() {
		return [
			[
				'studly-test',
				'StudlyTest',
			],
			[
				'studly_test',
				'StudlyTest',
			],
		];
	}

	public function test_lock_process() {
		$test1 = 0;
		$test2 = 0;
		$this->assertTrue( static::$utility->lock_process( static::$app, 'test_lock_process1', function () use ( &$test1, &$test2 ) {
			$test1 = 1;
			static::$utility->lock_process( static::$app, 'test_lock_process2', function () use ( &$test2 ) {
				$test2 = 2;
			} );
			static::$utility->lock_process( static::$app, 'test_lock_process1', function () use ( &$test1 ) {
				$test1 = 10;
			} );
		} ) );
		$this->assertEquals( 1, $test1 );
		$this->assertEquals( 2, $test2 );
	}

	public function test_upload_file_not_exists() {
		$this->assertFalse( static::$utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertFalse( static::$file->upload_file_exists( static::$app, 'test/file1.txt' ) );
	}

	/**
	 * @depends test_upload_file_not_exists
	 * @throws Exception
	 */
	public function test_create_upload_file() {
		static::$utility->create_upload_file( static::$app, 'test/file1.txt', 'file test' );
		static::$file->create_upload_file( static::$app, 'test/file10.txt', 'file test' );
	}

	/**
	 * @depends test_create_upload_file
	 */
	public function test_upload_file_exists() {
		$this->assertTrue( static::$utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertTrue( static::$file->upload_file_exists( static::$app, 'test/file10.txt' ) );
	}

	/**
	 * @depends test_upload_file_exists
	 */
	public function test_delete_upload_file() {
		$this->assertTrue( static::$utility->delete_upload_file( static::$app, 'test/file1.txt' ) );
		$this->assertTrue( static::$file->delete_upload_file( static::$app, 'test/file10.txt' ) );
	}

	/**
	 * @depends test_delete_upload_file
	 */
	public function test_upload_file_not_exists2() {
		$this->assertFalse( static::$utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertFalse( static::$file->upload_file_exists( static::$app, 'test/file10.txt' ) );
	}

	/**
	 * @depends test_upload_file_not_exists2
	 */
	public function test_get_upload_file_contents() {
		$this->assertFalse( static::$utility->get_upload_file_contents( static::$app, 'test/file1.txt' ) );
		$this->assertEquals( 'create test', static::$utility->get_upload_file_contents( static::$app, 'test/file2.txt', function () {
			return 'create test';
		} ) );
		$this->assertEquals( 'create test', static::$utility->get_upload_file_contents( static::$app, 'test/file2.txt' ) );
		$this->assertFalse( static::$utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertTrue( static::$utility->upload_file_exists( static::$app, 'test/file2.txt' ) );

		$this->assertFalse( static::$file->get_upload_file_contents( static::$app, 'test/file10.txt' ) );
		$this->assertEquals( 'create test', static::$file->get_upload_file_contents( static::$app, 'test/file20.txt', function () {
			return 'create test';
		} ) );
		$this->assertEquals( 'create test', static::$file->get_upload_file_contents( static::$app, 'test/file20.txt' ) );
		$this->assertFalse( static::$file->upload_file_exists( static::$app, 'test/file10.txt' ) );
		$this->assertTrue( static::$file->upload_file_exists( static::$app, 'test/file20.txt' ) );
	}

	/**
	 * @depends test_get_upload_file_contents
	 */
	public function test_get_upload_file_url() {
		$this->assertFalse( static::$utility->get_upload_file_url( static::$app, 'test/file1.txt' ) );
		$this->assertEquals( static::$app->define->upload_url . '/test/file3.txt', static::$utility->get_upload_file_url( static::$app, 'test/file3.txt', function () {
			return 'create test';
		} ) );
		$this->assertEquals( static::$app->define->upload_url . '/test/file3.txt', static::$utility->get_upload_file_url( static::$app, 'test/file3.txt' ) );
		$this->assertFalse( static::$utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertTrue( static::$utility->upload_file_exists( static::$app, 'test/file3.txt' ) );

		$this->assertFalse( static::$file->get_upload_file_url( static::$app, 'test/file10.txt' ) );
		$this->assertEquals( static::$app->define->upload_url . '/test/file30.txt', static::$file->get_upload_file_url( static::$app, 'test/file30.txt', function () {
			return 'create test';
		} ) );
		$this->assertEquals( static::$app->define->upload_url . '/test/file30.txt', static::$file->get_upload_file_url( static::$app, 'test/file30.txt' ) );
		$this->assertFalse( static::$file->upload_file_exists( static::$app, 'test/file10.txt' ) );
		$this->assertTrue( static::$file->upload_file_exists( static::$app, 'test/file30.txt' ) );
	}

	/**
	 * @depends test_get_upload_file_url
	 */
	public function test_delete_upload_dir() {
		$this->assertTrue( static::$utility->delete_upload_dir( static::$app ) );
		$this->assertFalse( static::$utility->upload_file_exists( static::$app, '' ) );
	}

	/**
	 * @dataProvider provider_test_sum
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_sum( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->sum( $array, $callback ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_sum() {
		return [
			[
				6,
				[
					[
						'a' => 1,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['a'];
				},
			],
			[
				3,
				[
					[
						'a' => 1,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['b'];
				},
			],
		];
	}

	/**
	 * @dataProvider provider_test_mul
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_mul( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->mul( $array, $callback ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_mul() {
		return [
			[
				6,
				[
					[
						'a' => 1,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['a'];
				},
			],
			[
				0,
				[
					[
						'a' => 1,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['b'];
				},
			],
		];
	}

	/**
	 * @dataProvider provider_test_max
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_max( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->max( $array, $callback ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_max() {
		return [
			[
				10,
				[
					[
						'a' => 10,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['a'];
				},
			],
			[
				21,
				[
					[
						'a' => 1,
						'b' => 20,
					],
					[
						'a' => 2,
						'b' => 21,
					],
					[
						'a' => 3,
						'b' => 12,
					],
				],
				function ( $item ) {
					return $item['b'];
				},
			],
		];
	}

	/**
	 * @dataProvider provider_test_min
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_min( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->min( $array, $callback ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_min() {
		return [
			[
				2,
				[
					[
						'a' => 10,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['a'];
				},
			],
			[
				12,
				[
					[
						'a' => 1,
						'b' => 20,
					],
					[
						'a' => 2,
						'b' => 21,
					],
					[
						'a' => 3,
						'b' => 12,
					],
				],
				function ( $item ) {
					return $item['b'];
				},
			],
		];
	}

	/**
	 * @dataProvider provider_test_ave
	 *
	 * @param $expected
	 * @param $array
	 * @param $callback
	 */
	public function test_ave( $expected, $array, $callback ) {
		$this->assertEquals( $expected, static::$array->ave( $array, $callback ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_ave() {
		return [
			[
				2,
				[
					[
						'a' => 1,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['a'];
				},
			],
			[
				1,
				[
					[
						'a' => 1,
						'b' => 0,
					],
					[
						'a' => 2,
						'b' => 1,
					],
					[
						'a' => 3,
						'b' => 2,
					],
				],
				function ( $item ) {
					return $item['b'];
				},
			],
		];
	}
}

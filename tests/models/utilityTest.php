<?php
/**
 * WP_Framework_Common Models Utility Test
 *
 * @version 0.0.19
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

require_once __DIR__ . DS . 'misc' . DS . 'collection.php';

/**
 * Class UtilityTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 */
class UtilityTest extends \WP_Framework_Common\Tests\TestCase {

	/**
	 * @var \WP_Framework_Common\Classes\Models\Utility $_utility
	 */
	private static $_utility;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_utility = \WP_Framework_Common\Classes\Models\Utility::get_instance( static::$app );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$_utility->delete_upload_dir( static::$app );
	}

	/**
	 * @dataProvider _test_flatten_provider
	 *
	 * @param array $array
	 * @param bool $preserve_keys
	 * @param array $expected
	 */
	public function test_flatten( $array, $preserve_keys, $expected ) {
		$this->assertEquals( $expected, static::$_utility->flatten( $array, $preserve_keys ) );
	}

	/**
	 * @return array
	 */
	public function _test_flatten_provider() {
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
					[ 'a' => 'test1', 'b' => 'test2' ],
					[ 'c' => 'test3', 'd' => 'test4' ],
				],
				false,
				[ 'test1', 'test2', 'test3', 'test4' ],
			],
			[
				[
					[ 'a' => 'test1', 'b' => 'test2' ],
					[ 'c' => 'test3', 'd' => 'test4' ],
				],
				true,
				[ 'a' => 'test1', 'b' => 'test2', 'c' => 'test3', 'd' => 'test4' ],
			],
			[
				[
					[ 'a' => 'test1', 'b' => 'test2' ],
					[ 'a' => 'test3', 'b' => 'test4' ],
				],
				true,
				[ 'a' => 'test3', 'b' => 'test4' ],
			],
		];
	}

	/**
	 * @dataProvider _test_get_array_value_provider
	 *
	 * @param mixed $obj
	 * @param array $expected
	 */
	public function test_get_array_value( $obj, $expected ) {
		$this->assertEquals( $expected, static::$_utility->get_array_value( $obj ) );
	}

	/**
	 * @return array
	 */
	public function _test_get_array_value_provider() {
		return [
			[
				'test',
				[],
			],
			[
				(object) [
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
				new Misc\Collection( [
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				] ),
				[
					'test1' => 1,
					'test2' => 2,
					'test3' => 3,
				],
			],
		];
	}

	/**
	 * @dataProvider _test_array_wrap_provider
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_array_wrap( $value, $expected ) {
		$this->assertEquals( $expected, static::$_utility->array_wrap( $value ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_wrap_provider() {
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
	 * @dataProvider _test_array_get_provider
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @param mixed $expected
	 */
	public function test_array_get( $array, $key, $default, $expected ) {
		$this->assertEquals( $expected, static::$_utility->array_get( $array, $key, $default ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_get_provider() {
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
				false,
				false,
			],
		];
	}

	/**
	 * @dataProvider _test_array_search_provider
	 *
	 * @param mixed $expected
	 * @param array $array
	 * @param string $key
	 * @param array $keys
	 */
	public function test_array_search( $expected, $array, $key, $keys = [] ) {
		$this->assertEquals( $expected, static::$_utility->array_search( $array, $key, ...$keys ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_search_provider() {
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
		];
	}

	/**
	 * @dataProvider _test_array_set_provider
	 * @depends      test_array_get
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_array_set( $array, $key, $value ) {
		static::$_utility->array_set( $array, $key, $value );
		$this->assertEquals( $value, static::$_utility->array_get( $array, $key ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_set_provider() {
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
		];
	}

	/**
	 * @dataProvider _test_replace_provider
	 *
	 * @param string $string
	 * @param array $data
	 * @param string $expected
	 */
	public function test_replace( $string, $data, $expected ) {
		$this->assertEquals( $expected, static::$_utility->replace( $string, $data ) );
	}

	/**
	 * @return array
	 */
	public function _test_replace_provider() {
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
	 * @dataProvider _test_starts_with_provider
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $expected
	 */
	public function test_starts_with( $haystack, $needle, $expected ) {
		$this->assertEquals( $expected, static::$_utility->starts_with( $haystack, $needle ) );
	}

	/**
	 * @return array
	 */
	public function _test_starts_with_provider() {
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
	 * @dataProvider _test_ends_with_provider
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $expected
	 */
	public function test_ends_with( $haystack, $needle, $expected ) {
		$this->assertEquals( $expected, static::$_utility->ends_with( $haystack, $needle ) );
	}

	/**
	 * @return array
	 */
	public function _test_ends_with_provider() {
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
	 * @dataProvider _test_snake_provider
	 *
	 * @param $expected
	 * @param $value
	 * @param string $delimiter
	 */
	public function test_snake( $expected, $value, $delimiter = '_' ) {
		$this->assertEquals( $expected, static::$_utility->snake( $value, $delimiter ) );
	}

	/**
	 * @return array
	 */
	public function _test_snake_provider() {
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
	 * @dataProvider _test_camel_provider
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_camel( $value, $expected ) {
		$this->assertEquals( $expected, static::$_utility->camel( $value ) );
	}

	/**
	 * @return array
	 */
	public function _test_camel_provider() {
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
	 * @dataProvider _test_studly_provider
	 *
	 * @param $value
	 * @param $expected
	 */
	public function test_studly( $value, $expected ) {
		$this->assertEquals( $expected, static::$_utility->studly( $value ) );
	}

	/**
	 * @return array
	 */
	public function _test_studly_provider() {
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
		$this->assertTrue( static::$_utility->lock_process( static::$app, 'test_lock_process1', function () use ( &$test1, &$test2 ) {
			$test1 = 1;
			static::$_utility->lock_process( static::$app, 'test_lock_process2', function () use ( &$test2 ) {
				$test2 = 2;
			} );
			static::$_utility->lock_process( static::$app, 'test_lock_process1', function () use ( &$test1 ) {
				$test1 = 10;
			} );
		} ) );
		$this->assertEquals( 1, $test1 );
		$this->assertEquals( 2, $test2 );
	}

	public function test_upload_file_not_exists() {
		$this->assertFalse( static::$_utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
	}

	/**
	 * @depends test_upload_file_not_exists
	 * @throws \Exception
	 */
	public function test_create_upload_file() {
		static::$_utility->create_upload_file( static::$app, 'test/file1.txt', 'file test' );
	}

	/**
	 * @depends test_create_upload_file
	 */
	public function test_upload_file_exists() {
		$this->assertTrue( static::$_utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
	}

	/**
	 * @depends test_upload_file_exists
	 */
	public function test_delete_upload_file() {
		$this->assertTrue( static::$_utility->delete_upload_file( static::$app, 'test/file1.txt' ) );
	}

	/**
	 * @depends test_delete_upload_file
	 */
	public function test_upload_file_not_exists2() {
		$this->assertFalse( static::$_utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
	}

	/**
	 * @depends test_upload_file_not_exists2
	 */
	public function test_get_upload_file_contents() {
		$this->assertFalse( static::$_utility->get_upload_file_contents( static::$app, 'test/file1.txt' ) );
		$this->assertEquals( 'create test', static::$_utility->get_upload_file_contents( static::$app, 'test/file2.txt', function () {
			return 'create test';
		} ) );
		$this->assertEquals( 'create test', static::$_utility->get_upload_file_contents( static::$app, 'test/file2.txt' ) );
		$this->assertFalse( static::$_utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertTrue( static::$_utility->upload_file_exists( static::$app, 'test/file2.txt' ) );
	}

	/**
	 * @depends test_get_upload_file_contents
	 */
	public function test_get_upload_file_url() {
		$this->assertFalse( static::$_utility->get_upload_file_url( static::$app, 'test/file1.txt' ) );
		$this->assertEquals( static::$app->define->upload_url . '/test/file3.txt', static::$_utility->get_upload_file_url( static::$app, 'test/file3.txt', function () {
			return 'create test';
		} ) );
		$this->assertEquals( static::$app->define->upload_url . '/test/file3.txt', static::$_utility->get_upload_file_url( static::$app, 'test/file3.txt' ) );
		$this->assertFalse( static::$_utility->upload_file_exists( static::$app, 'test/file1.txt' ) );
		$this->assertTrue( static::$_utility->upload_file_exists( static::$app, 'test/file3.txt' ) );
	}

	/**
	 * @depends test_get_upload_file_url
	 */
	public function test_delete_upload_dir() {
		$this->assertTrue( static::$_utility->delete_upload_dir( static::$app ) );
		$this->assertFalse( static::$_utility->upload_file_exists( static::$app, '' ) );
	}
}
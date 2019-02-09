<?php
/**
 * WP_Framework_Common Models Utility Test
 *
 * @version 0.0.18
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

/**
 * Class UtilityTest
 * @package WP_Framework_Common\Tests\Models
 * @group technote
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
<?php
/**
 * WP_Framework_Common Models Option Test
 *
 * @version 0.0.38
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

/**
 * Class OptionTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 */
class OptionTest extends \WP_Framework_Common\Tests\TestCase {

	/**
	 * @var \WP_Framework_Common\Classes\Models\Option $_option
	 */
	private static $_option;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_option = \WP_Framework_Common\Classes\Models\Option::get_instance( static::$app );
		foreach ( static::get_test_value() as $value ) {
			static::$_option->delete( $value[0] );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$_option->uninstall();
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set( $key, $value ) {
		$this->assertEquals( true, static::$_option->set( $key, $value ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 * @depends      test_set
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get( $key, $value ) {
		$this->assertEquals( $value, static::$_option->get( $key ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 * @depends      test_get
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_delete(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$_option->delete( $key ) );
		$this->assertEquals( 'test', static::$_option->get( $key, 'test' ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 * @depends      test_delete
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set2( $key, $value ) {
		$this->assertEquals( true, static::$_option->set( $key, $value, true ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 * @depends      test_set2
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get2( $key, $value ) {
		$this->assertEquals( $value, static::$_option->get( $key, '', true ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 * @depends      test_get2
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_delete2(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$_option->delete( $key, true ) );
		$this->assertEquals( 'test', static::$_option->get( $key, 'test', true ) );
	}

	/**
	 * @return array
	 */
	private static function get_test_value() {
		return [
			[ 'technote_test_option_bool', true ],
			[ 'technote_test_option_int', 123 ],
			[ 'technote_test_option_float', 0.987 ],
			[ 'technote_test_option_string', 'test' ],
			[
				'technote_test_option_array',
				[
					'test1' => 'test1',
					'test2' => 2,
					'test3' => false,
				],
			],
			[ 'technote_test_option_null', null ],
		];
	}

	/**
	 * @return array
	 */
	public function _test_value_provider() {
		return static::get_test_value();
	}
}
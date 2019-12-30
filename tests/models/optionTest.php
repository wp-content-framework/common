<?php
/**
 * WP_Framework_Common Models Option Test
 *
 * @version 0.0.49
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

use WP_Framework_Common\Classes\Models\Option;
use WP_Framework_Common\Tests\TestCase;

/**
 * Class OptionTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 */
class OptionTest extends TestCase {

	/**
	 * @var Option $option
	 */
	private static $option;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$option = Option::get_instance( static::$app );
		foreach ( static::get_test_value() as $value ) {
			static::$option->delete( $value[0] );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$option->uninstall();
	}

	/**
	 * @dataProvider provider_test_value
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set( $key, $value ) {
		$this->assertEquals( true, static::$option->set( $key, $value ) );
	}

	/**
	 * @dataProvider provider_test_value
	 * @depends      test_set
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get( $key, $value ) {
		$this->assertEquals( $value, static::$option->get( $key ) );
	}

	/**
	 * @dataProvider provider_test_value
	 * @depends      test_get
	 *
	 * @param string $key
	 * @param mixed $value
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function test_delete(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$option->delete( $key ) );
		$this->assertEquals( 'test', static::$option->get( $key, 'test' ) );
	}

	/**
	 * @dataProvider provider_test_value
	 * @depends      test_delete
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set2( $key, $value ) {
		$this->assertEquals( true, static::$option->set( $key, $value, true ) );
	}

	/**
	 * @dataProvider provider_test_value
	 * @depends      test_set2
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get2( $key, $value ) {
		$this->assertEquals( $value, static::$option->get( $key, '', true ) );
	}

	/**
	 * @dataProvider provider_test_value
	 * @depends      test_get2
	 *
	 * @param string $key
	 * @param mixed $value
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function test_delete2(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$option->delete( $key, true ) );
		$this->assertEquals( 'test', static::$option->get( $key, 'test', true ) );
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
	public function provider_test_value() {
		return static::get_test_value();
	}
}

<?php
/**
 * WP_Framework_Common Models User Test
 *
 * @version 0.0.49
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

use WP_Framework_Common\Classes\Models\User;
use WP_Framework_Common\Tests\TestCase;

/**
 * Class UserTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 */
class UserTest extends TestCase {

	/**
	 * @var User $user
	 */
	private static $user;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$user = User::get_instance( static::$app );
		foreach ( static::get_test_value() as $value ) {
			static::$user->delete( $value[0], 1 );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		foreach ( static::get_test_value() as $value ) {
			static::$user->delete( $value[0], 1 );
		}
	}

	/**
	 * @dataProvider provider_test_value
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set( $key, $value ) {
		$this->assertEquals( true, static::$user->set( $key, $value, 1 ) );
	}

	/**
	 * @dataProvider provider_test_value
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get( $key, $value ) {
		$this->assertEquals( $value, static::$user->get( $key, 1 ) );
	}

	/**
	 * @dataProvider provider_test_value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function test_delete(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$user->delete( $key, 1 ) );
		$this->assertEquals( '', static::$user->get( $key, 1 ) );
	}

	/**
	 * @return array
	 */
	private static function get_test_value() {
		return [
			[ 'technote_test_user_bool', true ],
			[ 'technote_test_user_int', 123 ],
			[ 'technote_test_user_float', 0.987 ],
			[ 'technote_test_user_string', 'test' ],
			[
				'technote_test_user_array',
				[
					'test1' => 'test1',
					'test2' => 2,
					'test3' => false,
				],
			],
			[ 'technote_test_user_null', null ],
		];
	}

	/**
	 * @return array
	 */
	public function provider_test_value() {
		return static::get_test_value();
	}
}

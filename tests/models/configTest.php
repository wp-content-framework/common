<?php
/**
 * WP_Framework_Common Models Config Test
 *
 * @version 0.0.49
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

use Phake;
use WP_Framework_Common\Classes\Models\Config;
use WP_Framework_Common\Tests\TestCase;

/**
 * Class ConfigTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 */
class ConfigTest extends TestCase {

	/**
	 * @var Config $config
	 */
	private static $config;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$package = Phake::mock( '\WP_Framework\Package_Core' );
		Phake::when( $package )->get_config( 'test_config' )->thenReturn( [
			'test1' => 'test1',
			'test2' => 'test2',
		] );
		Phake::when( static::$app )->get_packages()->thenReturn( [ $package ] );
		static::$config = Config::get_instance( static::$app );

		if ( ! file_exists( static::$app->define->plugin_configs_dir ) ) {
			mkdir( static::$app->define->plugin_configs_dir, true );
		}
		touch( static::$app->define->plugin_configs_dir . DS . 'test_config.php' );
		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			static::$app->define->plugin_configs_dir . DS . 'test_config.php',
			<<< EOS
<?php

return array(

	'test2' => 'test3',
	'test4' => 'test4',

);

EOS
		);
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		if ( file_exists( static::$app->define->plugin_configs_dir . DS . 'test_config.php' ) ) {
			unlink( static::$app->define->plugin_configs_dir . DS . 'test_config.php' );
		}
	}

	public function test_get_only_framework_config() {
		$this->assertEquals( 'test1', static::$config->get( 'test_config', 'test1' ) );
	}

	public function test_overwrite_config() {
		$this->assertEquals( 'test3', static::$config->get( 'test_config', 'test2' ) );
	}

	public function test_get_only_plugin_config() {
		$this->assertEquals( 'test4', static::$config->get( 'test_config', 'test4' ) );
	}

	public function test_nothing() {
		$this->assertEmpty( static::$config->get( 'test_config', 'test5' ) );
	}

	public function test_default() {
		$this->assertEquals( 'test6', static::$config->get( 'test_config', 'test5', 'test6' ) );
	}
}

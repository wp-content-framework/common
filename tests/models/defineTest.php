<?php
/**
 * WP_Framework_Common Models Define Test
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models;

use WP_Framework_Common\Classes\Models\Define;
use WP_Framework_Common\Tests\TestCase;

/**
 * Class DefineTest
 * @package WP_Framework_Common\Tests\Models
 * @group wp_framework
 * @group models
 */
class DefineTest extends TestCase {

	/**
	 * @var Define $define
	 */
	private static $define;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$define = static::$app->define;
	}

	public function test_plugin_property() {
		$this->assertEquals( static::$plugin_name, static::$define->plugin_name );
		$this->assertEquals( ucfirst( static::$plugin_name ), static::$define->plugin_namespace );
		$this->assertNotEmpty( static::$define->plugin_file );
		$this->assertNotEmpty( static::$define->plugin_dir );
		$this->assertNotEmpty( static::$define->plugin_dir_name );
		$this->assertNotEmpty( static::$define->plugin_base_name );
		$this->assertNotEmpty( static::$define->plugin_assets_dir );
		$this->assertNotEmpty( static::$define->plugin_src_dir );
		$this->assertNotEmpty( static::$define->plugin_configs_dir );
		$this->assertNotEmpty( static::$define->plugin_views_dir );
		$this->assertEmpty( static::$define->plugin_languages_dir );
		$this->assertNotEmpty( static::$define->plugin_assets_url );
	}
}

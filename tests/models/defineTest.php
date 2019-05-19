<?php
/**
 * WP_Framework_Common Models Define Test
 *
 * @version 0.0.49
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
	 * @var Define $_define
	 */
	private static $_define;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_define = static::$app->define;
	}

	public function test_plugin_property() {
		$this->assertEquals( static::$plugin_name, static::$_define->plugin_name );
		$this->assertEquals( ucfirst( static::$plugin_name ), static::$_define->plugin_namespace );
		$this->assertNotEmpty( static::$_define->plugin_file );
		$this->assertNotEmpty( static::$_define->plugin_dir );
		$this->assertNotEmpty( static::$_define->plugin_dir_name );
		$this->assertNotEmpty( static::$_define->plugin_base_name );
		$this->assertNotEmpty( static::$_define->plugin_assets_dir );
		$this->assertNotEmpty( static::$_define->plugin_src_dir );
		$this->assertNotEmpty( static::$_define->plugin_configs_dir );
		$this->assertNotEmpty( static::$_define->plugin_views_dir );
		$this->assertEmpty( static::$_define->plugin_languages_dir );
		$this->assertNotEmpty( static::$_define->plugin_assets_url );
	}
}
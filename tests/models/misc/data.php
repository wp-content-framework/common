<?php
/**
 * WP_Framework_Common Tests Models Misc Data
 *
 * @version 0.0.26
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Tests\Models\Misc;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Data
 * @package WP_Framework_Common\Tests\Models\Misc
 */
class Data {

	private $value;

	public function __construct( $value ) {
		$this->value = $value;
	}

	public function map_test() {
		return $this->value;
	}

	public function map_filter() {
		return $this->value % 2 === 1;
	}
}

<?php
/**
 * WP_Framework_Common Tests Models Misc Collection
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
 * Class Collection
 * @package WP_Framework_Common\Tests\Models\Misc
 */
class Collection {

	private $items;

	public function __construct( array $items ) {
		$this->items = $items;
	}

	public function all()
	{
		return $this->items;
	}

	public function to_array() {
		return $this->items;
	}
}

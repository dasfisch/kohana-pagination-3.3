<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pagination class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Pagination_Core {

	public function __construct(array $config = NULL)
	{
		// Make sure $config is an array
		$config = (array) $config;

		// Recursively load any requested config groups
		while (isset($config['group']) AND isset(Kohana::config('pagination')->$config['group']))
		{
			// Store config group name
			$name = $config['group'];
			unset($config['group']);

			// Add config group values, not overwriting existing keys
			$config += Kohana::config('pagination')->$name;
		}

		// Add default config values, not overwriting existing keys
		$config += Kohana::config('pagination')->default;
	}

} // End Pagination

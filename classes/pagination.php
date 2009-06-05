<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The Pagination class will create the pagination links for you;
 * however, it won't touch the data you are paginating.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Pagination_Core {

	protected $uri;
	protected $view;
	protected $auto_hide;
	protected $query_string;
	protected $items_per_page;
	protected $total_items;
	protected $total_pages;
	protected $current_page;
	protected $current_first_item;
	protected $current_last_item;
	protected $prev_page;
	protected $next_page;

	public static function factory(array $config = NULL)
	{
		return new Pagination($config);
	}

	public function __construct(array $config = NULL)
	{
		// Make sure $config is an array
		$config = (array) $config;

		// Load the pagination config file once (object)
		$config_file = Kohana::config('pagination');

		// Recursively load any requested config groups
		while (isset($config['group']) AND isset($config_file->$config['group']))
		{
			// Transfer config group name
			$group = $config['group'];
			unset($config['group']);

			// Add config group values, not overwriting existing keys
			$config += $config_file->$group;
		}

		// Add default config values, not overwriting existing keys
		$config += $config_file->default;

		// Convert config array to object properties
		foreach ($config as $key => $value)
		{
			$this->$key = $value;
		}

		// Use the current URI by default
		if ($this->uri === NULL)
		{
			$this->uri = Request::instance()->uri;
		}

		// Grab the current page number from the URL
		$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;

		// Clean up and calculate page variables
		$this->total_items        = (int) max(0, $this->total_items);
		$this->items_per_page     = (int) max(1, $this->items_per_page);
		$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
		$this->current_page       = (int) min(max(1, $this->current_page), max(1, $this->total_pages));
		$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
		$this->prev_page          = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
		$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
	}

	public function url($page = 1)
	{
		// Clean the page number
		$page = max(1, (int) $page);

		// Generate the full URL for a certain page
		return url::site($this->uri).url::query(array($this->query_string => $page));
	}

	public function render($view = NULL)
	{
		// Automatically hide pagination whenever it is superfluous
		if ($this->auto_hide === TRUE AND $this->total_pages < 2)
			return '';

		// Use the view from config
		if ($view === NULL)
		{
			$view = $this->view;
		}

		// Load the view file and pass on the whole pagination object
		return View::factory($view, get_object_vars($this))->set('page', $this)->render();
	}

	public function __toString()
	{
		return $this->render();
	}

	public function __get($key)
	{
		return isset($this->$key) ? $this->$key : NULL;
	}

} // End Pagination
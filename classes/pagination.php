<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This Pagination class will create pagination links for you;
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

	/**
	 * Creates a new Pagination object.
	 *
	 * @param   array  configuration
	 * @return  Pagination
	 */
	public static function factory(array $config = array())
	{
		return new Pagination($config);
	}

	/**
	 * Creates a new Pagination object.
	 *
	 * @param   array  configuration
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		if (isset($config['group']))
		{
			// Recursively load requested config groups
			$config += $this->load_config($config['group']);
		}

		// Add default config values, not overwriting existing keys
		$config += $this->load_config();

		// Load config into object and calculate pagination variables
		$this->config($config);
	}

	/**
	 * Loads a pagination config group from the config file. One config group can
	 * refer to another as its parent, which will be recursively loaded.
	 *
	 * @param   string   name of the pagination config group
	 * @param   boolean  enable caching
	 * @return  array    configuration
	 */
	public function load_config($group = 'default', $cache = TRUE)
	{
		// Load the pagination config file (object)
		$config_file = Kohana::config('pagination', $cache);

		// Initialize the $config array
		$config['group'] = $group;

		// Recursively load requested config groups
		while (isset($config['group']) AND isset($config_file->$config['group']))
		{
			// Temporarily store config group name
			$group = $config['group'];
			unset($config['group']);

			// Add config group values, not overwriting existing keys
			$config += $config_file->$group;
		}

		// Get rid of possible stray config group names
		unset($config['group']);

		// Return the $config array
		return $config;
	}

	/**
	 * Loads configuration settings into the object and (re)calculates all
	 * pagination variables.
	 *
	 * @chainable
	 * @param   array  configuration
	 * @return  Pagination
	 */
	public function config(array $config = array())
	{
		if (isset($config['group']))
		{
			// Recursively load requested config groups
			$config += $this->load_config($config['group']);
		}

		// Convert config array to object properties
		foreach ($config as $key => $value)
		{
			$this->$key = $value;
		}

		if ($this->uri === NULL)
		{
			// Use the current URI by default
			$this->uri = Request::instance()->uri;
		}

		// Grab the current page number from the URL
		$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;

		// Clean up and calculate pagination variables
		$this->total_items        = (int) max(0, $this->total_items);
		$this->items_per_page     = (int) max(1, $this->items_per_page);
		$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
		$this->current_page       = (int) min(max(1, $this->current_page), max(1, $this->total_pages));
		$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
		$this->prev_page          = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
		$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;

		// Chainable method
		return $this;
	}

	/**
	 * Generates the full URL for a certain page.
	 *
	 * @param   integer  page number
	 * @return  string   page URL
	 */
	public function url($page = 1)
	{
		// Clean the page number
		$page = max(1, (int) $page);

		// Generate the URL
		return url::site($this->uri).url::query(array($this->query_string => $page));
	}

	/**
	 * Renders the pagination links.
	 *
	 * @param   string   view file to use; style
	 * @param   boolean  hide pagination for single pages
	 * @return  string   pagination output (HTML)
	 */
	public function render($view = NULL, $auto_hide = NULL)
	{
		// Possibly overload config settings
		$view      = ($view === NULL) ? $this->view : $view;
		$auto_hide = ($auto_hide === NULL) ? $this->auto_hide : $auto_hide;

		// Automatically hide pagination whenever it is superfluous
		if ($auto_hide === TRUE AND $this->total_pages < 2)
			return '';

		// Load the view file and pass on the whole Pagination object
		return View::factory($view, get_object_vars($this))->set('page', $this)->render();
	}

	/**
	 * Renders the pagination links.
	 *
	 * @return  string  pagination output (HTML)
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Returns a Pagination property.
	 *
	 * @param   string  URI of the request
	 * @return  mixed   Pagination property; NULL if not found
	 */
	public function __get($key)
	{
		return isset($this->$key) ? $this->$key : NULL;
	}

	/**
	 * Updates a single config setting, and recalculates all pagination variables.
	 * Setting multiple config items should be done via the config() method.
	 *
	 * @param   string  config key
	 * @param   mixed   config value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->config(array($key => $value));
	}

} // End Pagination
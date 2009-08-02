<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pagination links generator.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Pagination {

	// System defaults
	protected $config = array(
		'current_page'   => array('source' => 'query', 'key' => 'page'),
		'total_items'    => 0,
		'items_per_page' => 10,
		'view'           => 'pagination/basic',
		'auto_hide'      => TRUE,
	);

	protected $current_page;
	protected $total_items;
	protected $items_per_page;
	protected $total_pages;
	protected $current_first_item;
	protected $current_last_item;
	protected $prev_page;
	protected $next_page;
	protected $first_page;
	protected $last_page;

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
		// Overwrite system defaults with application defaults
		$this->config = array_merge($this->config, $this->config_group());

		// Pagination setup
		$this->setup($config);
	}

	/**
	 * Retrieves a pagination config group from the config file. One config group can
	 * refer to another as its parent, which will be recursively loaded.
	 *
	 * @param   string  pagination config group; "default" if none given
	 * @return  array   config settings
	 */
	public function config_group($group = 'default')
	{
		// Load the pagination config file
		$config_file = Kohana::config('pagination');

		// Initialize the $config array
		$config['group'] = (string) $group;

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
	 * pagination variables if needed.
	 * You can call this method to update any config settings after the object has
	 * already been created.
	 *
	 * @param   array   configuration
	 * @return  object  Pagination
	 */
	public function setup(array $config = array())
	{
		if (isset($config['group']))
		{
			// Recursively load requested config groups
			$config += $this->config_group($config['group']);
		}

		// Overwrite the current config settings
		$this->config = array_merge($this->config, $config);

		// Only (re)calculate pagination when needed
		if ($this->current_page === NULL
			OR isset($config['current_page'])
			OR isset($config['total_items'])
			OR isset($config['items_per_page']))
		{
			// Retrieve the current page number
			switch ($this->config['current_page']['source'])
			{
				case 'query':
					$this->current_page = isset($_GET[$this->config['current_page']['key']])
						? (int) $_GET[$this->config['current_page']['key']]
						: 1;
					break;

				case 'route':
					$this->current_page = (int) Request::instance()->param($this->config['current_page']['key'], 1);
					break;
			}

			// Calculate and clean all pagination variables
			$this->total_items        = (int) max(0, $this->config['total_items']);
			$this->items_per_page     = (int) max(1, $this->config['items_per_page']);
			$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
			$this->current_page       = (int) min(max(1, $this->current_page), max(1, $this->total_pages));
			$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
			$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
			$this->prev_page          = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
			$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
			$this->first_page         = ($this->current_page === 1) ? FALSE : 1;
			$this->last_page          = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
		}

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

		switch ($this->config['current_page']['source'])
		{
			case 'query':
				return URL::site(Request::instance()->uri).URL::query(array($this->config['current_page']['key'] => $page));

			case 'route':
				return URL::site(Request::instance()->uri(array($this->config['current_page']['key'] => $page))).URL::query();
		}

		return '#';
	}

	/**
	 * Renders the pagination links.
	 *
	 * @param   string  view file to use; overrides config view setting
	 * @return  string  pagination output (HTML)
	 */
	public function render($view = NULL)
	{
		// Automatically hide pagination whenever it is superfluous
		if ($this->config['auto_hide'] === TRUE AND $this->total_pages <= 1)
			return '';

		if ($view === NULL)
		{
			// Use the view from config
			$view = $this->config['view'];
		}

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
	 * Updates a single config setting, and recalculates pagination if needed.
	 *
	 * @param   string  config key
	 * @param   mixed   config value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->setup(array($key => $value));
	}

} // End Pagination
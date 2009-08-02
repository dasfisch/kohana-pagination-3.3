<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Pagination extends Controller {

	public function action_index()
	{
		$demo[] = Pagination::factory(array(
			'total_items'    => 211,
		))->render();

		$demo[] = Pagination::factory(array(
			'current_page'   => array('source' => 'route', 'key' => 'page'),
			'total_items'    => 193,
			'items_per_page' => 20,
		))->render();

		$demo[] = Pagination::factory(array(
			'current_page'   => array('source' => 'query_string', 'key' => 'other_pagenr'),
			'total_items'    => 61,
		))->render();

		$demo[] = Pagination::factory(array(
			'total_items'    => 8,
			'auto_hide'      => FALSE,
		))->render();

		$this->request->response = '<h1>Pagination demos — <a href="'.URL::site($this->request->controller).'">reset</a></h1>'.implode('', $demo);
	}

}
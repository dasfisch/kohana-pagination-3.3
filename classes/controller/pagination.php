<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Pagination extends Controller {

	public function action_index()
	{
		$demo[] = Pagination::factory(array(
			'total_items'    => 211,
		))->render();

		$demo[] = Pagination::factory(array(
			'total_items'    => 193,
			'items_per_page' => 20,
			'query_string'   => 'p2',
			'uri'            => 'pagination',
		))->render();

		$demo[] = Pagination::factory(array(
			'total_items'    => 51,
			'query_string'   => 'p3',
			'uri'            => 'pagination/index',
		))->render();

		$demo[] = Pagination::factory(array(
			'total_items'    => 8,
			'query_string'   => 'p4',
			'auto_hide'      => FALSE,
		))->render();

		$this->request->response = '<h1>Pagination demos <a href="'.url::site($this->request->uri).'">[reset]</a></h1>'.implode('', $demo);
	}

}
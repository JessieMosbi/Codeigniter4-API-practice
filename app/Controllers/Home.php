<?php

namespace App\Controllers;

/**
 * Default controller from CodeIgniter4.
 */
class Home extends BaseController
{
	/**
	 * Homepage
	 *
	 * @return string
	 */
	public function index(): string
	{
		return view('welcome_message');
	}
}

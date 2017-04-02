<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = new Route('sitemap.xml', 'Homepage:sitemap');
		$router[] = new Route('rss.xml', 'Homepage:rss');
		$router[] = new Route('<presenter>/<action>[/<id>]', [
			'presenter'	=> [
				Route::VALUE		=> 'Homepage',
				Route::FILTER_TABLE	=> [
					'fotogalerie'			=> 'Photogallery',
					'historie'				=> 'History',
					'kontakt'				=> 'Contact',
					'novinky'				=> 'News',
					'turistika'				=> 'Tourists',
					'uredni-deska'			=> 'OfficialDesk',
					'zakladni-informace'	=> 'BasicInformations',
				],
			],
			'action'	=> [
				Route::VALUE		=> 'default',
				Route::FILTER_TABLE	=> [
					'zobrazit'	=> 'show',
				],
			],
			'id'		=> null,
		]);
		return $router;
	}

}

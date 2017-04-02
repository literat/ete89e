<?php

namespace App\Presenters;

use Nette;


class HomepagePresenter extends Nette\Application\UI\Presenter
{

	public function renderRss()
	{
        $this->template->posts = $this->posts->getAllPosts()->order('date DESC')->limit(50);
    }

    public function renderSitemap()
    {
        $this->template->sitemap = $this->posts->getAllPosts();
    }

}

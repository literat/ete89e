<?php

namespace App\Presenters;

use App\Model\ArticleManager;
use Nette\Application\UI\Presenter as BasePresenter;

class HomepagePresenter extends BasePresenter
{

	/**
	 * @var ArticleManager
	 */
	private $articleManager;

	/**
	 * @param ArticleManager $articleManager
	 */
	public function __construct(ArticleManager $articleManager)
	{
		$this->setArticleManager($articleManager);
	}

	public function renderRss()
	{
		$this->template->posts = $this->getArticleManager()
			->getAllPosts()
			->order('created_at DESC')
			->limit(50);
	}

	public function renderSitemap()
	{
		$this->template->sitemap = $this->getArticleManager()->getAllPosts();
	}

	/**
	 * @return ArticleManager
	 */
	protected function getArticleManager()
	{
		return $this->articleManager;
	}

	/**
	 * @param ArticleManager $articleManager
	 */
	protected function setArticleManager(ArticleManager $manager)
	{
		$this->articleManager = $manager;

		return $this;
	}

}

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

	public function beforeRender()
	{
		$this->template->setFile(dirname(__FILE__) . '/templates/Homepage/Sitemap.latte');
		$this->template->sitemap = $this->getArticleManager()->getAllPosts();
		$output = (string) $this->template;
		$handle = fopen(__DIR__ . '/../../sitemap.xml', 'w'); // před jméno souboru dáme nette.safe://
		fwrite($handle, $output); // zatím se píše do pomocného souboru
		fclose($handle); // a teprve teď se přejmenuje na test.txt
		$this->template->setFile(null);
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

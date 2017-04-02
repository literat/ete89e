<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter as BasePresenter;
use Nette\Database\Context;

class NewsPresenter extends BasePresenter
{

	/**
	 * @var Context
	 */
	private $database;

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderShow($id)
	{
		$post = $this->getDatabase()->table('posts')->get($id);
		if (!$post) {
			$this->error('Stránka nebyla nalezena');
		}

		$this->template->post = $post;
	}

	/**
	 * @return void
	 */
	public function renderDefault()
	{
		$this->template->posts = $this->getDatabase()->table('posts')
			->order('created_at DESC')
			->limit(10);
	}

	/**
	 * @return Context
	 */
	protected function getDatabase()
	{
		return $this->database;
	}

	/**
	 * @param Context $database
	 */
	protected function setDatabase(Context $database)
	{
		$this->database = $database;

		return $this;
	}

}

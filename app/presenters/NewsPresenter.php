<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter as BasePresenter;
use Nette\Database\Context;
use Nette\Application\UI\Form;
use App\Model\ArticleManager;

class NewsPresenter extends BasePresenter
{

	/**
	 * @var ArticleManager
	 */
	private $articleManager;

	private $database;

	/**
	 * @param ArticleManager $articleManager
	 */
	public function __construct(ArticleManager $articleManager, Context $database)
	{
		$this->setArticleManager($articleManager);
		$this->database = $database;
	}

	public function actionEdit($id)
	{
		$post = $this->getArticleManager()->find($id);
		if (!$post) {
			$this->error('Příspěvek nebyl nalezen');
		}
		$this['postForm']->setDefaults($post->toArray());
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderShow($id)
	{
		$post = $this->getArticleManager()->find($id);
		if (!$post) {
			$this->error('Stránka nebyla nalezena');
		}

		$this->template->post = $post;
		$this->template->comments = $post->related('comment')->order('created_at');
	}

	/**
	 * @return void
	 */
	public function renderDefault()
	{
		$this->template->posts = $this->getArticleManager()->getPublicArticles()->limit(5);
	}

	public function commentFormSucceeded($form, $values)
	{
		$id = $this->getParameter('id');

		$this->getDatabase()->table('comments')->insert([
			'post_id' => $id,
			'name' => $values->name,
			'email' => $values->email,
			'content' => $values->content,
		]);

		$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}

	public function postFormSucceeded($form, $values)
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
		}

		$postId = $this->getParameter('id');

		if ($postId) {
			$post = $this->getArticleManager()->saveArticle($postId, $values);
		} else {
			$post = $this->getArticleManager()->createArticle($values);
		}

		$this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
		$this->redirect('show', $post->id);
	}

	protected function createComponentCommentForm()
	{
		$form = new Form;

		$form->addText('name', 'Jméno:')
			->setRequired();

		$form->addEmail('email', 'Email:');

		$form->addTextArea('content', 'Komentář:')
			->setRequired();

		$form->addSubmit('send', 'Publikovat komentář');

		$form->onSuccess[] = [$this, 'commentFormSucceeded'];

		return $form;
	}

	protected function createComponentPostForm()
	{
		$form = new Form;
		$form->addText('title', 'Titulek:')
			->setRequired();
		$form->addTextArea('content', 'Obsah:')
			->setRequired();

		$form->addSubmit('send', 'Uložit a publikovat');
		$form->onSuccess[] = [$this, 'postFormSucceeded'];

		return $form;
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

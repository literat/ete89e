<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter as BasePresenter;
use Nette\Database\Context;
use Nette\Application\UI\Form;
use App\Model\ArticleManager;

class NewsPresenter extends BasePresenter
{

	const ITEM_PER_PAGE = 5;

	/**
	 * @var ArticleManager
	 */
	private $articleManager;

	private $database;

	private $paginator;

	public $page = 1;

	public $onShowPage;

	private $texy;

	/**
	 * @param ArticleManager $articleManager
	 */
	public function __construct(ArticleManager $articleManager, Context $database)
	{
		$this->setArticleManager($articleManager);
		$this->setDatabase($database);
	}

	public function injectTexy(\Texy\Texy $texy)
	{
		$this->texy = $texy;
	}

	public function handleShowPage($page)
	{
		$this->onShowPage($this, $page);
	}

	public function loadState(array $params)
	{
		parent::loadState($params);
		$this->getPaginator()->page = $this->page;
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
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
	public function actionDelete($id)
	{
		$result = $this->getArticleManager()->destroy($id);
		$this->flashMessage('Příspěvek byl smazán');
		$this->redirect('News:default');
	}

	/**
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

        $this->template->addFilter('texy', function ($text) {
            return $this->texy->process($text);
        });

		$this->template->actuals = $this->getArticleManager()->getActualArticles()->limit(5);
		$this->template->mostViewed = $this->getArticleManager()->mostViewed()->limit(5);
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function renderShow($id)
	{
		$this->getArticleManager()->incrementViews($id);
		$post = $this->getArticleManager()->find($id);
		if (!$post) {
			$this->error('Stránka nebyla nalezena');
		}

		$this->template->post = $post;
		$this->template->comments = $post->related('comment')->order('created_at');
		$this->template->mostViewed = $this->getArticleManager()->mostViewed();
	}

	/**
	 * @return void
	 */
	public function renderDefault()
	{
		$paginator = $this->getPaginator();
		$paginator->setItemCount($this->getArticleManager()->countArticles());
		$paginator->setItemsPerPage(self::ITEM_PER_PAGE);
		$paginator->setPage($paginator->page);

		$this->template->paginator = $paginator;
		$this->template->posts = $this->getArticleManager()
			->getPublicArticles()
			->limit($paginator->getLength(), $paginator->getOffset());
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
			->setRequired()->setHtmlAttribute('class', 'form-control');

		$form->addSubmit('send', 'Publikovat komentář');

		$form->onSuccess[] = [$this, 'commentFormSucceeded'];

		// setup form rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['form']['container'] = 'div class="contactForm"';
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class="form-group"';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['.text'] = 'form-control';
		$renderer->wrappers['control']['.email'] = 'form-control';
		$renderer->wrappers['control']['.submit'] = 'btn btn-theme';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

		return $form;
	}

	protected function createComponentPostForm()
	{
		$form = new Form;
		$form->addText('title', 'Titulek:')
			->setRequired();
		$form->addTextArea('content', 'Obsah:')
			->setRequired()->setHtmlAttribute('class', 'form-control');

		$form->addSubmit('send', 'Uložit a publikovat');
		$form->onSuccess[] = [$this, 'postFormSucceeded'];

		// setup form rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['form']['container'] = 'div class="contactForm"';
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class="form-group"';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['.text'] = 'form-control';
		$renderer->wrappers['control']['.submit'] = 'btn btn-theme';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

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
	public function setDatabase(Context $database)
	{
		$this->database = $database;

		return $this;
	}

	public function getPaginator()
	{
		if(!$this->paginator) {
			$this->paginator = new \Nette\Utils\Paginator;
		}

		return $this->paginator;
	}

}

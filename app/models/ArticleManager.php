<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;

class ArticleManager
{
	use Nette\SmartObject;

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
	 * @return ActiveRow
	 */
	public function getPublicArticles()
	{
		return $this->getDatabase()->table('posts')
			->where('created_at < ', new \DateTime())
			->order('created_at DESC');
	}

	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllPosts()
	{
		return $this->getDatabase()->table('posts');
	}

	/**
	 * @param  array $values
	 * @return ActiveRow
	 */
	public function createArticle($values)
	{
		return $this->getDatabase()->table('posts')->insert($values);
	}

	/**
	 * @param  integer $postId
	 * @param  array $values
	 * @return ActiveRow
	 */
	public function saveArticle($postId, $values)
	{
		$post = $this->getDatabase()->table('posts')->get($postId);
		$post->update($values);

		return $post;
	}

	/**
	 * @param  integer $postId
	 * @return ActiveRow
	 */
	public function find($id)
	{
		return $this->getDatabase()->table('posts')->get($id);
	}

	/**
	 * @param  integer $postId
	 * @return ActiveRow
	 */
	public function destroy($id)
	{
		return $this->getDatabase()->table('posts')->where('id', $id)->delete();
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

}

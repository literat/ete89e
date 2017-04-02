<?php

namespace Model;

class Posts extends \Nette\Object {

    /** @var \Nette\Database\SelectionFactory @inject */
    public $sf;

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getAllPosts() {
        return $this->sf->table('posts');
    }

}
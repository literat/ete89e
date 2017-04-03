<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class SignPresenter extends Nette\Application\UI\Presenter
{

	public function actionCreate()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function actionEdit($postId)
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function actionOut()
	{
	    $this->getUser()->logout();
	    $this->flashMessage('Odhlášení bylo úspěšné.');
	    $this->redirect('Homepage:');
	}

	protected function createComponentSignInForm()
	{
		$form = new Form;
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Prosím vyplňte své uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím vyplňte své heslo.');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = [$this, 'signInFormSucceeded'];

		// setup form rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['form']['container'] = 'div class="contactForm"';
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class="form-group"';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = NULL;
		$renderer->wrappers['control']['.text'] = 'form-control';
		$renderer->wrappers['control']['.password'] = 'form-control';
		$renderer->wrappers['control']['.submit'] = 'btn btn-theme';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

		return $form;
	}

	/**
	 * @param $form Nette\Application\UI\Form
	 * @param $values Nette\Utils\ArrayHash
	 */
	public function signInFormSucceeded($form, $values)
	{
		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('Nesprávné přihlašovací jméno nebo heslo.');
		}
	}

}

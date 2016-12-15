<?php

use Nette\Application\Routers\Route;
use Nette\Forms\Form;
use Nette\Mail\Message;
use Nette\Utils\Json;

// Load libraries
require __DIR__ . '/app/libs/nette.phar';

$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(__DIR__ . '/app/temp/log');

// Configure libraries
$configurator->setTempDirectory(__DIR__ . '/app/temp');

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/app/config/config.neon', $configurator::AUTO);
$container = $configurator->createContainer();
$mailer = $container->getService('mailer');

// Setup routes
$router = $container->getService('router');
$router[] = new Route('', function($presenter) use ($mailer) {

	$page = $presenter->request->getParameter('page');

	switch($page) {
		case 'kontakt':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/kontakt.latte');
			$template->activeContact = 'class="active"';
			break;
		case 'novinky':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/novinky.latte');
			$template->activeNews = 'class="active"';
			break;
		case 'fotogalerie':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/fotogalerie.latte');
			$template->activePhotogallery = 'class="active"';
			break;
		case 'zakladni-informace':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/zakladni-informace.latte');
			$template->activeOffice = ' active';
			break;
		case 'uredni-deska':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/uredni-deska.latte');
			$template->activeOffice = ' active';
			break;
		case 'historie':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/historie.latte');
			$template->activeOffice = ' active';
			break;
		case 'turistika':
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/turistika.latte');
			$template->activeTurist = ' active';
			break;
		default:
			$template = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/main.latte');
			$template->activeMain = 'class="active"';
			break;
	}

	// create contact form
	$form = new Form;
	$form->addText('name', 'Vaše jméno')
		->addRule(Form::FILLED, 'Zadejte vaše jméno')
		->setAttribute('class', 'form-control')
		->setAttribute('placeholder', 'Vaše jméno');
	$form->addText('email', 'Váš E-mail')
		->addRule(Form::FILLED, 'Zadejte váš e-mail')
		->addRule(Form::EMAIL, 'Zadejte platnou e-mailovou adresu')
		->setAttribute('class', 'form-control')
		->setAttribute('placeholder', 'Váš E-mail');
	$form->addText('subject', 'Předmět')
		->addRule(Form::FILLED, 'Zadejte předmět zprávy')
		->setAttribute('class', 'form-control')
		->setAttribute('placeholder', 'Předmět');
	$form->addTextArea('message', 'Zpráva')
		->addRule(Form::FILLED, 'Zadejte zprávu')
		->setAttribute('class', 'form-control')
		->setAttribute('placeholder', 'Prosím, něco hezkého mi napište...')
		->setAttribute('row', 10);
	$form->addHidden('gifts')
		->setAttribute('id', 'frm-gifts');
	$form->addSubmit('send', 'ODESLAT ZPRÁVU')
		->setAttribute('class', 'btn btn-default btn-xl wow tada');

	// create template

	if(!isset($template->flashMessage)) {
		$template->flashMessage = '';
	}
	if(!isset($template->resetForm)) {
		$template->resetForm = '';
	}

	// assign form
	$template->form = $form;

	// form on success
	if ($form->isSuccess()) {
		$values = $form->getValues();

		$message = new Message;
		$message->addTo('tomas@litera.me')
			->addTo('dita@litera.me')
			->setFrom($values['email'], $values['name'])
			->setSubject($values['subject'])
			->setBody($values['message']);

		$mailTemplate = $presenter->createTemplate()->setFile(__DIR__ . '/app/templates/email.latte');
		$mailTemplate->title = 'Zpráva ze svatebního formuláře';
		$mailTemplate->values = $values;

		$message->setHtmlBody($mailTemplate);
		$mailer->send($message);

		$template->flashMessage = 'Vaše zpráva byla odeslána! Děkuji.';
		$template->resetForm = '<script>
			document.getElementById("frm-name").value = "";
			document.getElementById("frm-email").value = "";
			document.getElementById("frm-subject").value = "";
			document.getElementById("frm-message").innerText = "";
			document.getElementById("frm-gifts").value = "";
		</script>';
		$presenter->redirectUrl($presenter->context->httpRequest->url->baseUrl . '#contact-form-section');
	}

	return $template;
});

// Run the application!
$container->getService('application')->run();

<?php

namespace Istrix\Mail;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use SendGrid;
use SendGrid\Email;

class SendgridMailer extends Object implements IMailer
{
	const ENDPOINT = "https://api.sendgrid.com/";

	/** @var  string */
	private $key;

	/**
	 * MailSender constructor
	 *
	 * @param string $key
	 */
	public function __construct($key)
	{
		$this->key = $key;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * Sends email to sendgrid
	 *
	 * @param Message $message
	 * @throws SendGrid\Exception
	 */
	public function send(Message $message)
	{
		$sendGrid = new SendGrid($this->key);
		$email = new Email();

		$from = $message->getFrom();
		reset($from);
		$key = key($from);

		$email->setFrom($key)
			->setFromName($from[$key])
			->setSubject($message->getSubject())
			->setText($message->getBody())
			->setHtml($message->getHtmlBody());

		foreach($message->getHeader('To') as $recipient => $name) {
			$email->addTo($recipient);
		}

		$sendGrid->send($email);
	}

}
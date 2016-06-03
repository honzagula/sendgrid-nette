# sendgrid-nette
Sendgrid integration for Nette mailer

## Install
```
composer require istrix/sendgrid-nette
```

## Configuration
In config add:

```
parameters:
	sendgrid:
		key: 'yourkey'

services:
	nette.mailer: Istrix\MailSender\SendgridMailer(%sendgrid.key%)
```

## Usage
Just inject IMailer and send message...

```php
	/** @var IMailer @inject */
	public $iMailer;
	
	protected function sendMail() {
		...
		$this->iMailer->send($message);
		...
	}
	
```
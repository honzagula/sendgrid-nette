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
Inject IMailer and you are ready to send...

```php
	/** @var IMailer @injet */
	public $iMailer;
	
	protected function sendMail() {
		...
		$this->iMailer->send($message);
		...
	}
	
```
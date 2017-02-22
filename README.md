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
	nette.mailer: Istrix\Mail\SendgridMailer(%sendgrid.key%, %tempDir%, 'default subject in case you do not specify it with the message')
```

## Usage
Just inject IMailer and send message...

```php
	/** @var IMailer @inject */
	public $mailer;
	
	protected function sendMail() {
		...
		$this->mailer->send($message);
		...
	}
	
```

### Embedded images

If you wish to use inline/embedded images:

```php
	/** @var IMailer @inject */
	public $mailer;
	
	protected function sendMail() {
		...
		$embeddedImages = [
		    new SendGridInlineFile('logo.png', file_get_contents('logo.png'), 'image/png', 'anystringusedascid')
		];
		$this->mailer->send($message, $embeddedImages);
		...
	}
	
```

To include the embedded image into e-mail HTML template, use:

```html
        <img src="cid:anystringusedascid">
```
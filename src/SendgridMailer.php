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

    /** @var string */
    private $key;

    /** @var string */
    private $tempFolder;

    /** @var array */
    private $tempFiles = [];

    /**
     * MailSender constructor
     *
     * @param string $key
     * @param string $tempFolder
     */
    public function __construct($key, $tempFolder)
    {
        $this->key = $key;
        $this->tempFolder = $tempFolder;
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
     *
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

        foreach ($message->getAttachments() as $attachement) {
            $header = $attachement->getHeader('Content-Disposition');
            preg_match('/filename\=\"(.*)\"/', $header, $result);
            $originalFileName = $result[1];

            $filePath = $this->saveTempAttachement($attachement->getBody());

            $email->addAttachment($filePath, $originalFileName);
        }

        foreach ($message->getHeader('To') as $recipient => $name) {
            $email->addTo($recipient);
        }

        $sendGrid->send($email);

        $this->cleanUp();
    }

    private function saveTempAttachement($body)
    {
        $filePath = $this->tempFolder . '/' . md5($body);
        file_put_contents($filePath, $body);
        array_push($this->tempFiles, $filePath);

        return $filePath;
    }

    private function cleanUp()
    {
        foreach ($this->tempFiles as $file) {
            unlink($file);
        }
    }

}

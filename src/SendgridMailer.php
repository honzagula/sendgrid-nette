<?php

namespace Istrix\Mail;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use SendGrid;
use SendGrid\Email;

class SendgridMailer extends Object implements IMailer {

    /** @var string */
    private $key;

    /** @var string */
    private $tempFolder;

    /** @var array */
    private $tempFiles = [];
    
    /** @var string */
    private $defaultSubject;

    /**
     * MailSender constructor
     *
     * @param string $key
     * @param string $tempFolder
     */
    public function __construct($key, $tempFolder, $defaultSubject = NULL) {
        $this->key = $key;
        $this->tempFolder = $tempFolder;
        $this->defaultSubject = $defaultSubject ?: $_SERVER['HTTP_HOST'];
    }

    /**
     * @param string $key
     */
    public function setKey($key) {
        $this->key = $key;
    }

    /**
     * Sends email to sendgrid
     *
     * @param Message $message
     *
     * @throws SendGrid\Exception
     */
    public function send(Message $message, array $embedFiles = []) {
        $sendGrid = new SendGrid($this->key);

        //prepare From - sender data
        $fromData = $message->getFrom();
        reset($fromData);
        $fromKey = key($fromData);
        $from = new Email($fromData[$fromKey], $fromKey);
        
        $mail = new SendGrid\Mail();  
        $mail->setFrom($from);
        $mail->setSubject($message->getSubject() ?: $this->defaultSubject);
        $mail->addContent(new SendGrid\Content("text/plain", $message->getBody()));
        $mail->addContent(new SendGrid\Content("text/html", $message->getHtmlBody()));
        
        foreach ($message->getAttachments() as $attachement) {
            $header = $attachement->getHeader('Content-Disposition');
            preg_match('/filename\=\"(.*)\"/', $header, $result);
            $originalFileName = $result[1];

            $att = new SendGrid\Attachment();
            $att->setType($attachement->getHeader('Content-Type'));
            $att->setFilename($originalFileName);
            $att->setContent(base64_encode($attachement->getBody()));
            $att->setDisposition('attachment');
            $att->setContentID(\Nette\Utils\Random::generate(10));
            $mail->addAttachment($att);
        }

        foreach ($embedFiles as $attachement) {
            if (!$attachement instanceof SendGridInlineFile) {
                throw new \InvalidArgumentException('Parameter $embedFiles must be an array containing SendGridInlineFile objects');
            }
            
            $att = new SendGrid\Attachment();
            $att->setType($attachement->contentType);
            $att->setFilename($attachement->filename);
            $att->setContent(base64_encode($attachement->content));
            $att->setDisposition('inline');
            $att->setContentID($attachement->contentId);
            $mail->addAttachment($att);
        }
        
        //add more recipients, CCs and BCCs
        $personalization = new SendGrid\Personalization;
        foreach ($message->getHeader('To') as $recipient => $name) {
            $personalization->addTo(new Email($name, $recipient));
        }
        
        if ($message->getHeader('Cc') !== NULL) {
            foreach ($message->getHeader('Cc') as $recipient => $name) {
                $personalization->addCc(new Email($name, $recipient));
            }
        }
        
        if ($message->getHeader('Bcc') !== NULL) {
            foreach ($message->getHeader('Bcc') as $recipient => $name) {
                $personalization->addBcc(new Email($name, $recipient));
            }
        }

        $mail->addPersonalization($personalization);
        
        $response = $sendGrid->client->mail()->send()->post($mail);
//        \Tracy\Debugger::barDump($response, 'sendgrid response');
            
        $this->cleanUp();
    }

    private function saveTempAttachement($body) {
        $filePath = $this->tempFolder . '/' . md5($body);
        file_put_contents($filePath, $body);
        array_push($this->tempFiles, $filePath);

        return $filePath;
    }

    private function cleanUp() {
        foreach ($this->tempFiles as $file) {
            unlink($file);
        }
    }

}

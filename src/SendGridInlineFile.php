<?php

namespace Istrix\Mail;

class SendGridInlineFile
{
    
    public $filename;
    public $content;
    public $contentType;
    public $contentId;

    public function __construct($filename, $content, $contentType, $contentId)
    {
        $this->filename = $filename;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->contentId = $contentId;
    }
    
}
<?php

namespace TuEmpresa\GraphMail\Transport;

use Illuminate\Mail\Transport\Transport;
use Symfony\Component\Mime\Email;
use TuEmpresa\GraphMail\GraphMailService;

class GraphTransport extends Transport
{
    protected GraphMailService $graph;

    public function __construct(GraphMailService $graph)
    {
        $this->graph = $graph;
    }

    public function send(Email \$email, ?\Symfony\Component\Mime\RawMessage \$message = null): void
    {
        \$to = array_map(fn(\$a) => \$a->getAddress(), \$email->getTo());
        \$cc = array_map(fn(\$a) => \$a->getAddress(), \$email->getCc());
        \$subject = \$email->getSubject();
        \$html = \$email->getHtmlBody() ?? \$email->getTextBody();
        \$from = config('graphmail.from');

        \$attachments = [];
        foreach (\$email->getAttachments() as \$attachment) {
            \$attachments[] = [
                'name' => \$attachment->getFilename(),
                'content_base64' => base64_encode(\$attachment->getBody())
            ];
        }

        \$this->graph->sendMail([
            'to' => \$to,
            'cc' => \$cc,
            'from' => \$from,
            'subject' => \$subject,
            'body' => \$html,
            'attachments' => \$attachments
        ]);
    }
}
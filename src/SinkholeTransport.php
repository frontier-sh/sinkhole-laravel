<?php

namespace Frontier\Sinkhole;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class SinkholeTransport extends AbstractTransport
{
    public function __construct(
        private string $endpoint,
        private string $apiKey,
        private string $channel = 'default',
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $headers = [];
        foreach ($email->getHeaders()->all() as $header) {
            $headers[$header->getName()] = $header->getBodyAsString();
        }

        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'filename'     => $attachment->getFilename() ?? 'attachment',
                'content_type' => $attachment->getContentType(),
                'content'      => base64_encode($attachment->getBody()),
            ];
        }

        $payload = [
            'to'         => $this->formatAddresses($email->getTo()),
            'from'       => $this->formatAddresses($email->getFrom()),
            'subject'    => $email->getSubject() ?? '(no subject)',
            'html'       => $email->getHtmlBody(),
            'text'       => $email->getTextBody(),
            'headers'    => $headers,
            'message_id' => $email->generateMessageId(),
            'channel'    => $this->channel,
        ];

        if (! empty($attachments)) {
            $payload['attachments'] = $attachments;
        }

        $response = Http::timeout(5)
            ->withHeader('X-API-Key', $this->apiKey)
            ->post($this->endpoint . '/ingest', $payload);

        if (! $response->successful()) {
            throw new TransportException(
                "Sinkhole ingest failed: HTTP {$response->status()}"
            );
        }
    }

    /**
     * @param Address[] $addresses
     */
    private function formatAddresses(array $addresses): string
    {
        return implode(', ', array_map(function (Address $address) {
            if ($address->getName()) {
                return "{$address->getName()} <{$address->getAddress()}>";
            }
            return $address->getAddress();
        }, $addresses));
    }

    public function __toString(): string
    {
        return 'sinkhole';
    }
}

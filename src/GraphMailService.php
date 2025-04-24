<?php

namespace TuEmpresa\GraphMail;

use GuzzleHttp\Client;
use TheNetworg\OAuth2\Client\Provider\Azure;

class GraphMailService
{
    protected Azure $provider;
    protected string $accessToken;

    public function __construct()
    {
        $this->provider = new Azure([
            'clientId'                => config('graphmail.client_id'),
            'clientSecret'            => config('graphmail.client_secret'),
            'redirectUri'             => 'http://localhost',
            'urlAuthorize'            => 'https://login.microsoftonline.com/' . config('graphmail.tenant_id') . '/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/' . config('graphmail.tenant_id') . '/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => ['https://graph.microsoft.com/.default'],
            'resource'                => 'https://graph.microsoft.com'
        ]);

        $token = $this->provider->getAccessToken('client_credentials');
        $this->accessToken = $token->getToken();
    }

    public function sendMail(array $data): array
    {
        $client = new Client();
        $toEmails = array_map(fn($e) => ['emailAddress' => ['address' => $e]], $data['to']);
        $ccEmails = array_map(fn($e) => ['emailAddress' => ['address' => $e]], $data['cc'] ?? []);

        $attachments = [];
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $attachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $file['name'],
                    'contentBytes' => $file['content_base64']
                ];
            }
        }

        $payload = [
            'message' => [
                'subject' => $data['subject'],
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $data['body'],
                ],
                'toRecipients' => $toEmails,
                'ccRecipients' => $ccEmails,
                'attachments' => $attachments
            ],
            'saveToSentItems' => true
        ];

        $response = $client->post("https://graph.microsoft.com/v1.0/users/{$data['from']}/sendMail", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $payload
        ]);

        return ['status' => 'sent', 'code' => $response->getStatusCode()];
    }
}
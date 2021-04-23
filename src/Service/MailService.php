<?php

namespace App\Service;
use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailService
{
    private $api_key;
    private $api_secret_key;

    public function __construct(ContainerInterface $container)
    {
        $this->api_key = $container->getParameter('mailjet_key');
        $this->api_secret_key = $container->getParameter('mailjet_secret');
    }

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key,$this->api_secret_key, true, ['version' => 'v3.1']);
        //$mj = new Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'), true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "halotthierry34@gmail.com",
                        'Name' => "Halot thierry"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 2818485,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}
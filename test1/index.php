<?php

require_once __DIR__ . '/src/AmoCrmV4Client.php';

define('SUB_DOMAIN', 'testoviimyrubikonru');
define('CLIENT_ID', 'dde5ffdf-9504-46a2-b234-23677ad11cf8');
define('CLIENT_SECRET', '9X7Aa9cWcYzvW3nL13boCocsjEdh2bNibOv9vquE2yK6o3uHyw7ES19DgVeP9U9w');
define('CODE', 'def5020085ba94cce1b9063744de22e254fee354d413ee461c3dd485aa28bec97ddb3023c788e061335c5f7fdf85eb3070dc36ade60fe73d3c2232b22a153a1155dfbeb2a4105105b48393b745b312ce991a2966de4176a17ad4769029caec3995af96c84a2dad3413d8c9ed9a331962502a71de1421eea58ee50ba290c76fdb633201899cdcc839d53b3287a1a414ec7b199a64b557cfd1cde507a62f190530024272f0664b5c104937a71ac405f8b0f98cf0cbf157999541a52dcec1a493b9d76069e60b19d0459887c165241d82326409209bc027814ec439d02c65b941c1ed102238bd4455b76c8694b93ade23ed834d9effe0bd6011f9cc49707b3bc1f944c5fb683b7dc06a2c094c283c651a6cace5c65e2daaa41b38fcc252e4b0dea7411c6399a77b50c1000ae6cc8a516add32516005a01359fa7c9ad32692274b6de218b860344397d288ccefa471be2a3fff4c2e6b9b4928b3ff82c518ba5230c57cedfd2d43bbc9984ad829ee112defadbee327393d003be98060ac5e0982baebd25c20bb4ba189ce0bee679d5ed80d858f3456dd945c2afeb3fe311c10c5c0721bd4115559ecdd92d0e6ee16b1fc2b68e7b05e8b12dd5f23fd309028565c3d23a1943aae9a38f310911db88b2d0cb0cc83673730b378e48c0824116414808930080830cf9e0e245a9b12f44f7b8ab89caa4a0949d5');
define('REDIRECT_URL', 'https://testoviimyrubikonru.amocrm.ru');

echo "<pre>";

function createContact($amoV4Client, $name, $email, $phone)
{

    $endpoint = 'contacts';

    // Данные для создания контакта
    $contactData = [
        [
            'name' => $name,
            'custom_fields_values' => [
                [
                    'field_id' => 335853,
                    'values' => [
                        ['value' => $email]
                    ]
                ],
                [
                    'field_id' => 335851,
                    'values' => [
                        ['value' => $phone]
                    ]
                ]
            ]
        ]
    ];

    $result = $amoV4Client->POSTRequestAPI($endpoint, $contactData);

    return $result['_embedded']['contacts'][0]['id'];
}


function createLeadWithContact($amoV4Client, $name, $email, $phone, $price, $contactID)
{

        $endpoint = 'leads';

        // Данные для создания сделки
        $leadData = [
            [
            'name' => 'Сделка ' . date('d.m.Y H:i:s'), // Пример названия сделки
            'price' => (int)$price,
            'contacts' => [
                [
                    'name' => $name
                ]
            ]
            ]
        ];

        $result = $amoV4Client->POSTRequestAPI($endpoint, $leadData);

    return $result['_embedded']['leads'][0]['id'];
    }


try {
    $amoV4Client = new AmoCrmV4Client(SUB_DOMAIN, CLIENT_ID, CLIENT_SECRET, CODE, REDIRECT_URL);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $price = $_POST['price'];


// Создание контакта и получение его ID
        $contactId = createContact($amoV4Client, $name, $email, $phone);
        echo "Contact ID: " . $contactId . PHP_EOL;

// Создание сделки с привязкой к контакту
        $result = createLeadWithContact($amoV4Client, $name, $email, $phone, $price, $contactId);
        echo "Lead creation result: " . json_encode($result) . PHP_EOL;

        $leadID = $result;
        $test = $amoV4Client->POSTRequestApi("contacts/{$contactId}/link", [
            [
            'to_entity_id' => $leadID,
            'to_entity_type'=>'leads'
            ]
        ]);
        echo "RESULT:  " .json_encode($test) . PHP_EOL ;


        // Возвращаем ответ клиенту
        if ($result) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo 'Error: Failed to create contact';
        }
    } else {
        http_response_code(405);
        echo 'Method Not Allowed';
    }
} catch (Exception $e) {
    // Обработка ошибки, если что-то пошло не так
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}


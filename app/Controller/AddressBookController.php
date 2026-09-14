<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\AddressBookModel;

class AddressBookController
{
    public function __construct(private array $config)
    {
    }

    public function download(): void
    {
        if (($this->config['user']['group_living_place'] ?? 'n') !== 'j') {
            http_response_code(403);
            exit(__('You are not authorised to download the address book.'));
        }
        $vcard = (new AddressBookModel($this->config))->getVcardFile();
        header('Content-Type: text/vcard; charset=utf-8');
        header('Content-Disposition: attachment; filename="address-book.vcf"');
        header('Content-Length: ' . strlen($vcard));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $vcard;
        exit;
    }
}

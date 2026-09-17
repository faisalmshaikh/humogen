<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\GrowConnectionsModel;

class GrowConnectionsController
{
    public function __construct(private array $config) {}

    public function list(?string $sourceGedcom = null): array
    {
        if (($this->config['user']['group_living_place'] ?? 'n') !== 'j') {
            http_response_code(403);
            exit(__('You are not authorised to view connection growth data.'));
        }
        $data = (new GrowConnectionsModel($this->config))->getData($sourceGedcom);
        $data['title'] = __('Grow Connections');
        return $data;
    }
}

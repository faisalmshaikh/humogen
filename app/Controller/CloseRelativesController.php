<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\CloseRelativesModel;

class CloseRelativesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail(string $id): array
    {
        $graph = (new CloseRelativesModel($this->config))->getGraph($id);
        $graph['title'] = __('Close Relatives');
        return $graph;
    }
}

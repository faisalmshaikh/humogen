<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\PendingApprovalModel;

class PendingApprovalController
{
    private $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $model = new PendingApprovalModel($this->admin_config);
        $alert = $model->approveUser();

        return [
            'alert' => $alert,
            'users' => $model->getPendingUsers(),
            'groups' => $model->getUserGroups(),
        ];
    }
}

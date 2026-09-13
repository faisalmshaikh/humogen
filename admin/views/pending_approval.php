<?php
if (!defined('ADMIN_PAGE')) {
    exit;
}

function pendingApprovalEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>

<h1 class="center"><?= __('Pending Approval'); ?></h1>

<?php if (!empty($pending_approval['alert'])) { ?>
    <div class="alert alert-info" role="alert">
        <?= pendingApprovalEscape($pending_approval['alert']); ?>
    </div>
<?php } ?>

<?php if (!$pending_approval['users']) { ?>
    <div class="alert alert-success" role="alert"><?= __('There are no users awaiting approval.'); ?></div>
<?php } else { ?>
    <form method="post" action="index.php?page=pending_approval">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th><?= __('Select'); ?></th>
                        <th><?= __('Username'); ?></th>
                        <th><?= __('Email'); ?></th>
                        <th><?= __('Full Name'); ?></th>
                        <th><?= __('GEDCOM number'); ?></th>
                        <th><?= __('User group'); ?></th>
                        <th><?= __('Details'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_approval['users'] as $user) {
                        $modalId = 'pending-user-' . (int) $user->user_id;
                    ?>
                        <tr>
                            <td>
                                <input type="radio" name="approved_user_id" value="<?= (int) $user->user_id; ?>" required aria-label="<?= __('Select'); ?> <?= pendingApprovalEscape($user->user_name); ?>">
                            </td>
                            <td><?= pendingApprovalEscape($user->user_name); ?></td>
                            <td><?= pendingApprovalEscape($user->user_mail); ?></td>
                            <td><?= pendingApprovalEscape($user->user_full_name ?? ''); ?></td>
                            <td>
                                <input type="text" name="approved_gedcom_nbr[<?= (int) $user->user_id; ?>]" value="<?= pendingApprovalEscape($user->user_gedcomnbr ?? ''); ?>" maxlength="7" class="form-control form-control-sm" aria-label="<?= __('GEDCOM number'); ?>">
                            </td>
                            <td>
                                <select name="approved_group_id[<?= (int) $user->user_id; ?>]" class="form-select form-select-sm" aria-label="<?= __('User group'); ?>">
                                    <?php foreach ($pending_approval['groups'] as $group) { ?>
                                        <option value="<?= (int) $group->group_id; ?>" <?= (int) $user->user_group_id === (int) $group->group_id ? 'selected' : ''; ?>>
                                            <?= pendingApprovalEscape($group->group_name); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#<?= $modalId; ?>" aria-label="<?= __('View details'); ?>">
                                    <img src="images/search.png" alt="<?= __('Details'); ?>">
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php foreach ($pending_approval['users'] as $user) {
            $modalId = 'pending-user-' . (int) $user->user_id;
        ?>
            <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-labelledby="<?= $modalId; ?>-label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="<?= $modalId; ?>-label"><?= pendingApprovalEscape($user->user_name); ?></h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('Close'); ?>"></button>
                        </div>
                        <div class="modal-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4"><?= __('Phone number'); ?></dt>
                                <dd class="col-sm-8"><?= pendingApprovalEscape($user->user_phone ?? ''); ?></dd>
                                <dt class="col-sm-4"><?= __('Address'); ?></dt>
                                <dd class="col-sm-8"><?= nl2br(pendingApprovalEscape($user->user_address ?? '')); ?></dd>
                                <dt class="col-sm-4"><?= __('Date of birth'); ?></dt>
                                <dd class="col-sm-8"><?= pendingApprovalEscape($user->user_birth_date ?? ''); ?></dd>
                                <dt class="col-sm-4"><?= __('Parent\'s Names'); ?></dt>
                                <dd class="col-sm-8"><?= pendingApprovalEscape(trim(($user->user_father_name ?? '') . ' / ' . ($user->user_mother_name ?? ''), ' /')); ?></dd>
                                <dt class="col-sm-4"><?= __('Paternal grandparents'); ?></dt>
                                <dd class="col-sm-8"><?= pendingApprovalEscape($user->user_paternal_grandparent_names ?? ''); ?></dd>
                                <dt class="col-sm-4"><?= __('Maternal grandparents'); ?></dt>
                                <dd class="col-sm-8"><?= pendingApprovalEscape($user->user_maternal_grandparent_names ?? ''); ?></dd>
                                <dt class="col-sm-4"><?= __('Relative name for reference'); ?></dt>
                                <dd class="col-sm-8"><?= pendingApprovalEscape($user->user_reference_name ?? ''); ?></dd>
                                <dt class="col-sm-4"><?= __('Message'); ?></dt>
                                <dd class="col-sm-8"><?= nl2br(pendingApprovalEscape($user->user_remark ?? '')); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <button type="submit" name="approve_user" value="1" class="btn btn-success">
            <?= __('Approve selected user'); ?>
        </button>
    </form>
<?php } ?>

<?php

/**
 * June 2024: split into MVC files.
 */

if (isset($_POST['send_mail']) && $register["register_allowed"] == true) {
    if (!$register["error"]) {
?>
        <h2><?= __('Registration completed'); ?></h2>
        <?= __('At this moment you are registered in the user-group "guest". The administrator will check your registration, and select a user-group for you.'); ?><br>
    <?php
    } else {
        $register["show_form"] = true;
    ?>
        <div class="alert alert-info" role="alert">
            <?= $register["error"]; ?>
        </div>
    <?php
    }
}

if ($register["show_form"]) {
    $email = '';
    // Used in older HuMo-genealogy versions. Backwards compatible...
    if (isset($selectedFamilyTree->tree_email)) {
        $email = $selectedFamilyTree->tree_email;
    }
    if ($humo_option["general_email"]) {
        $email = $humo_option["general_email"];
    }

    if (!$email) {
    ?>
        <div class="alert alert-info" role="alert">
            <?= __('The register function has been switched off!'); ?>
        </div>
    <?php
    } else {
        $path = 'index.php?page=register';
        if ($humo_option["url_rewrite"] == "j") {
            $path = 'register';
        }
        //$menu_path_register = $processLinks->get_link($uri_path, 'register');
    ?>

        <h1 class="my-4"><?= __('User registration form'); ?></h1>

        <?php if (isset($_POST['send_mail']) && $register["error"] == false) { ?>
            <div class="alert alert-info" role="alert">
                <?= __('Wrong answer to the block-spam question! Try again...'); ?>
            </div>
        <?php } ?>

        <div class="container">
            <form action="<?= $path; ?>" method="post">
                <div class="mb-2 row">
                    <label for="name" class="col-sm-3 col-form-label"><?= __('Name'); ?></label>
                    <div class="col-sm-5">
                        <input type="text" id="name" class="form-control" name="register_name" value="<?= htmlspecialchars($register["name"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" maxlength="25" pattern="[A-Za-z0-9._+\-]+" required title="<?= __('Use only letters, numbers, and - _ . + characters.'); ?>">
                    </div>
                </div>

                <?php
                $registrationTextFields = [
                    'register_father_name' => "Father's name",
                    'register_mother_name' => "Mother's name",
                    'register_reference_name' => 'Relative name for reference',
                    'register_address' => 'Address',
                    'register_paternal_grandparent_names' => "Paternal grandparent's names",
                    'register_maternal_grandparent_names' => "Maternal grandparent's names",
                    'register_phone' => 'Phone number',
                ];
                foreach ($registrationTextFields as $field => $label) {
                ?>
                    <div class="mb-2 row">
                        <label for="<?= $field; ?>" class="col-sm-3 col-form-label"><?= __($label); ?></label>
                        <div class="col-sm-5">
                            <input type="text" id="<?= $field; ?>" class="form-control" name="<?= $field; ?>" value="<?= htmlspecialchars($register[$field], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                            <?php if ($field === 'register_reference_name') { ?>
                                <small class="form-text text-muted">This relative may be contacted to approve your user registration</small>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="mb-2 row">
                    <label for="register_birth_date" class="col-sm-3 col-form-label"><?= __('Date of birth'); ?></label>
                    <div class="col-sm-5">
                        <input type="date" id="register_birth_date" class="form-control" name="register_birth_date" value="<?= htmlspecialchars($register['register_birth_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_marital_status" class="col-sm-3 col-form-label"><?= __('Marital status'); ?></label>
                    <div class="col-sm-5">
                        <select id="register_marital_status" class="form-select" name="register_marital_status">
                            <option value=""></option>
                            <?php foreach (['Single', 'Married', 'Divorced', 'Widowed', 'Separated'] as $maritalStatus) { ?>
                                <option value="<?= $maritalStatus; ?>" <?= $register['register_marital_status'] === $maritalStatus ? 'selected' : ''; ?>><?= __($maritalStatus); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="mail_sender" class="col-sm-3 col-form-label"><?= __('E-mail address'); ?></label>
                    <div class="col-sm-5">
                        <input type="email" id="register_mail" class="form-control" name="register_mail" value="<?= htmlspecialchars($register["mail"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_password" class="col-sm-3 col-form-label"><?= __('Password'); ?></label>
                    <div class="col-sm-5">
                        <input type="password" id="register_password" class="form-control" name="register_password" minlength="8" required title="<?= __('8 characters minimum, including uppercase, lowercase, digit and special character'); ?>">
                        <small class="form-text text-muted"><?= __('Password must be at least 8 characters and include uppercase, lowercase, a digit, and a special character.'); ?></small>
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_repeat_password" class="col-sm-3 col-form-label"><?= __('Repeat password'); ?></label>
                    <div class="col-sm-5">
                        <input type="password" id="register_repeat_password" class="form-control" name="register_repeat_password" minlength="8" required title="<?= __('8 characters minimum, including uppercase, lowercase, digit and special character'); ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_text" class="col-sm-3 col-form-label"><?= __('Message'); ?></label>
                    <div class="col-sm-5">
                        <textarea id="register_text" class="form-control" name="register_text" style="height:200px"><?= htmlspecialchars($register["text"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                    </div>
                </div>

                <?php if ($humo_option["use_spam_question"] == 'y') { ?>
                    <div class="mb-2 row">
                        <label for="register_block_spam" class="col-sm-3 col-form-label"><?= __('Please answer the block-spam-question:'); ?></label>
                        <div class="col-sm-5">
                            <?= $humo_option["block_spam_question"]; ?>
                            <input type="text" id="register_block_spam" class="form-control" name="register_block_spam">
                        </div>
                    </div>
                <?php } ?>

                <div class="mb-2 row">
                    <label for="2fa_code" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-5">
                        <input type="submit" class="btn btn-success" name="send_mail" value="<?= __('Send'); ?>">
                    </div>
                </div>
            </form>
        </div>
<?php
    }
}

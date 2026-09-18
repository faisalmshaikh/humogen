<?php

/**
 * Front page login form.
 */

if ($user['group_menu_login'] != 'j') {
    echo 'Access to this page is blocked.';
    exit;
}

$path_login = $processLinks->get_link($uri_path, 'login');
$path_reset_password = $processLinks->get_link($uri_path, 'reset_password');
$social_asset_path = $humo_option['url_rewrite'] == 'j' ? $uri_path . 'assets/social/' : 'assets/social/';
?>

<h1 class="my-4"><?= __('Login'); ?></h1>

<div class="container">
    <?php if ($index['fault'] == true) { ?>
        <div class="alert alert-warning">
            <strong><?= __('No valid username or password.'); ?></strong>
        </div>
    <?php } ?>
    <?php if (!empty($index['social_error'])) { ?>
        <div class="alert alert-warning">
            <strong><?= htmlspecialchars($index['social_error'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    <?php } ?>

    <form action="<?= $path_login; ?>" method="post">
        <div class="mb-2 row">
            <label for="username" class="col-sm-3 col-form-label"><?= __('Username or e-mail address'); ?></label>
            <div class="col-sm-5">
                <input type="text" id="username" class="form-control" name="username">
            </div>
        </div>

        <div class="mb-2 row">
            <label for="password" class="col-sm-3 col-form-label"><?= __('Password'); ?></label>
            <div class="col-sm-5">
                <input type="password" id="password" class="form-control" name="password">
            </div>
        </div>

        <div class="mb-2 row">
            <label for="2fa_code" class="col-sm-3 col-form-label"><?= __('Two factor authentication (2FA) code if needed'); ?></label>
            <div class="col-sm-5">
                <input type="text" id="2fa_code" name="2fa_code" class="form-control">
            </div>
        </div>

        <div class="mb-2 row">
            <label for="send_mail" class="col-sm-3 col-form-label"></label>
            <div class="col-sm-8">
                <input type="submit" class="btn btn-success" name="send_mail" value="<?= __('Login'); ?>">
            </div>
        </div>
    </form>

    <style>
        .social-login-options {
            max-width: 360px;
        }

        .social-login-button {
            align-items: center;
            border: 1px solid transparent;
            border-radius: .25rem;
            display: flex;
            font-weight: 600;
            justify-content: center;
            min-height: 44px;
            padding: .65rem 1rem;
            text-decoration: none;
            transition: filter .15s ease, transform .15s ease;
            width: 100%;
        }

        .social-login-button:hover,
        .social-login-button:focus-visible {
            filter: brightness(.94);
            transform: translateY(-1px);
        }

        .social-login-mark {
            align-items: center;
            display: inline-flex;
            height: 1.4rem;
            justify-content: center;
            margin-right: .75rem;
            width: 1.4rem;
        }

        .social-login-mark img {
            display: block;
            height: 1.4rem;
            width: 1.4rem;
        }

        .social-login-google {
            background: #fff;
            border-color: #dadce0;
            color: #3c4043;
        }

        .social-login-google .social-login-mark {
            color: #4285f4;
        }

        .social-login-facebook {
            background: #1877f2;
            color: #fff;
        }

        .social-login-facebook .social-login-mark {
            font-family: Arial, sans-serif;
        }

        .social-login-apple {
            background: #000;
            color: #fff;
        }
    </style>

    <div class="social-login-options mt-4">
        <p><?= __('Sign in with a linked social account'); ?></p>
        <div class="d-grid gap-2">
            <a class="social-login-button social-login-google" href="index.php?page=social_login&amp;provider=google" aria-label="Sign in with Google">
                <span class="social-login-mark" aria-hidden="true"><img src="<?= htmlspecialchars($social_asset_path . 'google.svg', ENT_QUOTES, 'UTF-8'); ?>" alt=""></span>
                <span><?= __('Sign in with Google'); ?></span>
            </a>
            <a class="social-login-button social-login-facebook" href="index.php?page=social_login&amp;provider=facebook" aria-label="Log in with Facebook">
                <span class="social-login-mark" aria-hidden="true"><img src="<?= htmlspecialchars($social_asset_path . 'facebook.svg', ENT_QUOTES, 'UTF-8'); ?>" alt=""></span>
                <span><?= __('Log in with Facebook'); ?></span>
            </a>
            <a class="social-login-button social-login-apple" href="index.php?page=social_login&amp;provider=apple" aria-label="Sign in with Apple">
                <span class="social-login-mark" aria-hidden="true"><img src="<?= htmlspecialchars($social_asset_path . 'apple.svg', ENT_QUOTES, 'UTF-8'); ?>" alt=""></span>
                <span><?= __('Sign in with Apple'); ?></span>
            </a>
        </div>
    </div>

    <!-- Only use password retrieval option if sender mail is set in admin settings and is a valid mail address -->
    <?php if ($humo_option["password_retrieval"] && filter_var($humo_option["password_retrieval"], FILTER_VALIDATE_EMAIL)) { ?>
        <div class="center">
            <form name="forget_form" method="post" action="<?= $path_reset_password; ?>">
                <input type="hidden" name="forgotpw" value="1">
                <input type="submit" name="Submit" value="<?= __('Forgot password'); ?>" class="btn btn-secondary">
            </form>
        </div>
    <?php } ?>
</div>

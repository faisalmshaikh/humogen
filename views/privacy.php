<?php
$privacyContact = trim((string) ($humo_option['general_email'] ?? ''));
?>

<div class="container my-4">
    <h1>Privacy Policy</h1>
    <p><strong>Effective date:</strong> 18 September 2026</p>

    <p>
        This Privacy Policy explains how this Khandesh Shijrah (Family Tree) website collects, uses, stores,
        and protects personal information when you browse the site, create an account, use
        genealogy features, or sign in with Google, Facebook, or Apple.
    </p>

    <h2>Who operates this website</h2>
    <p>
        This website is operated by its site owner or administrator. The operator is responsible
        for the family-tree data published on this installation and for responding to privacy
        requests. Contact details are provided at the end of this policy.
    </p>

    <h2>Information we collect</h2>
    <ul>
        <li>
            <strong>Account information:</strong> username, email address, name, profile details,
            registration details, and other information you choose to provide.
        </li>
        <li>
            <strong>Login and security information:</strong> password hashes, two-factor
            authentication settings and secret, session information, login history, and IP
            address information used for security and abuse prevention. Plain-text passwords are
            not intended to be stored.
        </li>
        <li>
            <strong>Social login information:</strong> when you use a linked Google, Facebook,
            or Apple account, the site receives the provider name, a provider-specific account
            identifier, and any email address returned by that provider. The site does not receive
            or store your social-media password.
        </li>
        <li>
            <strong>Genealogy and contact information:</strong> family-tree records, notes,
            photographs, sources, messages, submissions, and other content you or an administrator
            adds to the site.
        </li>
        <li>
            <strong>Technical information:</strong> session cookies and preference data, such as
            language, theme, selected family tree, favourites, and display settings. The site may
            also record technical request information needed to operate and secure the service.
        </li>
    </ul>

    <h2>How we use information</h2>
    <ul>
        <li>To provide family-tree pages, search, reports, account settings, and other requested features.</li>
        <li>To authenticate users and keep linked social accounts associated with the correct Family Tree account.</li>
        <li>To protect accounts, enforce access and privacy settings, prevent abuse, and investigate security incidents.</li>
        <li>To respond to requests, maintain the site, troubleshoot errors, and improve reliability.</li>
        <li>To meet legal obligations or respond to lawful requests where required.</li>
    </ul>

    <h2>Social login providers</h2>
    <p>
        Social login is optional. When you choose a provider, your browser is redirected to that
        provider so it can authenticate you. The provider may process information under its own
        privacy policy. After authentication, this site stores only the provider identity needed
        to recognize your linked Family Tree account and, where returned, the provider email address.
        Linking is not performed solely because two accounts have the same email address.
    </p>
    <p>
        You can stop using a social login by asking the site administrator to remove its link from
        your Family Tree account. Removing a link does not necessarily delete the separate account held
        by Google, Facebook, or Apple.
    </p>

    <h2>Genealogy information and privacy</h2>
    <p>
        Genealogy records may contain information about living or deceased people and may have
        been supplied by different contributors. The site applies its configured privacy filters
        and access groups, but you should not submit information that you are not authorized to
        share. If information about you or someone you represent is displayed incorrectly or
        should be restricted, contact the site administrator.
    </p>

    <h2>Cookies and sessions</h2>
    <p>
        The site uses a session cookie to keep you signed in and may use cookies or local
        preferences for language, theme, favourites, selected family trees, and display settings.
        These technologies are used to operate the site and remember choices; they are not used by
        this application to sell personal information or for behavioral advertising. See the
        <a href="<?= htmlspecialchars($processLinks->get_link($uri_path, 'cookies'), ENT_QUOTES, 'UTF-8'); ?>">Cookie Information</a>
        page for more detail.
    </p>

    <h2>Sharing and international transfers</h2>
    <p>
        We do not sell personal information. Information may be shared with the social provider
        you select during authentication, hosting or infrastructure providers needed to operate
        the site, and public authorities where legally required. Those providers may process
        information in countries outside your own. The site operator should ensure appropriate
        safeguards are in place for any international transfer.
    </p>

    <h2>Retention and security</h2>
    <p>
        Information is retained for as long as needed to provide the service, maintain account and
        genealogy records, meet legal obligations, resolve disputes, and protect the site. Login
        and security records may be retained for as long as reasonably necessary for those purposes.
        The operator uses access controls, password hashing, session protections, and other
        reasonable safeguards, but no internet service can guarantee absolute security.
    </p>

    <h2>Your choices and rights</h2>
    <p>
        Depending on where you live, you may have rights to request access to, correction of,
        export of, restriction of, or deletion of your personal information, and to object to or
        withdraw consent for certain processing. You may also have the right to complain to your
        local data-protection authority. Requests may be subject to identity verification and legal
        exceptions, including obligations to preserve genealogy or security records.
    </p>

    <h2>Children</h2>
    <p>
        The site is not directed at children who are below the minimum age permitted by applicable
        law. Please do not create an account or submit personal information if you are not permitted
        to do so.
    </p>

    <h2>Changes to this policy</h2>
    <p>
        We may update this policy when the site, its providers, or applicable requirements change.
        The effective date above identifies the current version. Continued use of the site after an
        update means the revised policy is available for review.
    </p>

    <h2>Contact</h2>
    <?php if (filter_var($privacyContact, FILTER_VALIDATE_EMAIL)) { ?>
        <p>For privacy questions or requests, contact the site administrator at
            <a href="mailto:<?= htmlspecialchars($privacyContact, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($privacyContact, ENT_QUOTES, 'UTF-8'); ?></a>.
        </p>
    <?php } else { ?>
        <p>For privacy questions or requests, contact the site administrator using the contact details published by the site owner.</p>
    <?php } ?>
</div>

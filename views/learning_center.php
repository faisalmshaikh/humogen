<?php

$loggedIn = !empty($_SESSION['user_id']);
$publicSections = ['A', 'J', 'K'];

$sections = [
    'A' => [
        'title' => 'Getting Started',
        'intro' => 'A short introduction to using the family tree and finding your way around the website.',
        'faqs' => [
            ['What is our Family Tree?', 'It is a shared online record of our family relationships and the life information that relatives have chosen to preserve.'],
            ['Who can access the family tree?', 'Access depends on the privacy settings chosen by the family-tree administrators. Some introductory information may be public, while fuller information may require an account.'],
            ['Why do I need an account?', 'An account helps the administrators protect family information, identify contributors, and provide features that are intended for registered relatives.'],
            ['What can a registered user do?', 'A registered user can explore the available family-tree features and, where enabled, submit corrections or additions for review. Registration does not automatically connect an account to a person in the tree.'],
            ['Can I use the family tree on my mobile phone?', 'Yes. The website is designed to work in a modern mobile browser. A separate mobile app is not required.'],
            ['How do I contact the family-tree administrator?', 'Use the Contact option on the website or the contact details supplied by the family-tree administrators.'],
        ],
    ],
    'B' => [
        'title' => 'Finding People',
        'intro' => 'Use the people search and its criteria to narrow a large family tree to the person you are looking for.',
        'faqs' => [
            ['How do I search for a person?', 'Open the Persons or Search option, enter the information you know, and submit the search. Start with a small amount of information and add criteria if there are too many results.'],
            ['Can I search by first name, surname, or maiden name?', 'Yes. Use the relevant name fields. Maiden names and other names may be stored separately, so try more than one search when necessary.'],
            ['Can I search using partial names?', 'Usually, yes. A partial name can help when you are unsure of spelling. Try both the full and partial spelling if the first search does not find the person.'],
            ['How do I distinguish people with the same name?', 'Compare the birth date, place, parents, spouse, and other details shown in the results before opening the family page.'],
            ['Why cannot I find someone I know is in the tree?', 'The person may be recorded under another spelling, have privacy restrictions, or be in another family tree. Try fewer search criteria and contact the administrators if the problem remains.'],
        ],
    ],
    'C' => [
        'title' => 'Exploring a Person',
        'intro' => 'A person page links the individual to parents, partners, children, and other family-tree reports.',
        'faqs' => [
            ['What information is shown on a person page?', 'The page may show names, dates, places, relationships, sources, photographs, and links to reports. Privacy settings can hide some information.'],
            ['How do I see someone’s parents, children, or spouse?', 'Open the person’s family page and use the relationship links or family-group sections to move through the family.'],
            ['How do I see previous generations or descendants?', 'Use the ancestor and descendant reports, charts, or the family links available from the person page.'],
            ['Why is some information missing?', 'The information may not yet be known, may not have been submitted, or may be hidden by privacy rules. Missing information can be reported to the administrators.'],
        ],
    ],
    'D' => [
        'title' => 'Relationship Calculator',
        'intro' => 'The Relationship Calculator helps compare two people in the family tree.',
        'faqs' => [
            ['How do I calculate my relationship to another person?', 'Choose the two people in the Relationship Calculator and run the calculation. The result is based on the family relationships recorded in the tree.'],
            ['Can I calculate the relationship between two other people?', 'Yes, if both people can be selected from the family tree. You do not have to be one of the people being compared.'],
            ['What does “cousin once removed” mean?', '“Removed” describes a difference in generation. For example, your cousin’s child is normally your first cousin once removed.'],
            ['Why can the result be complicated?', 'A person can be related through more than one branch, marriage, or repeated ancestors. The calculator reports the relationships supported by the recorded data.'],
        ],
    ],
    'E' => [
        'title' => 'Close Relatives',
        'intro' => 'Close Relatives presents a focused view of parents, grandparents, siblings, children, spouses, and nearby family branches.',
        'faqs' => [
            ['What is the Close Relatives feature?', 'It is a visual summary of the selected person’s immediate family and nearby relatives.'],
            ['How are close relatives determined?', 'The view follows the parent, spouse, child, and sibling relationships recorded in the family tree.'],
            ['Can I see relatives on both sides of the family?', 'Yes. Where the data is available, the page displays paternal and maternal branches, together with the selected person’s spouse and descendants.'],
            ['How do I report incorrect information?', 'Use the editable table and submit the proposed changes, or contact the administrators with the evidence supporting the correction.'],
        ],
    ],
    'F' => [
        'title' => 'Outline Report',
        'intro' => 'The Outline Report provides a structured view of descendants across several generations.',
        'faqs' => [
            ['What information does the Outline Report show?', 'It lists a selected person or couple and their descendants in generation order, using the information available in the tree.'],
            ['How do I explore a particular branch?', 'Start with the relevant ancestor and follow the indented descendants. Links on names can open the corresponding family page.'],
            ['Why might someone be missing?', 'The person may not be connected in the data, may be hidden by privacy settings, or may fall outside the report’s selected depth.'],
            ['How can I report an error in the report?', 'Record the person and relationship affected, then submit a correction through the available page controls or contact the administrators.'],
        ],
    ],
    'G' => [
        'title' => 'Correcting Information',
        'intro' => 'Family members can help improve the tree by reporting inaccurate or incomplete information.',
        'faqs' => [
            ['I found an incorrect name or date. What should I do?', 'Submit the corrected information with the person’s name or GEDCOM number and explain how you know the correction is accurate.'],
            ['Can I directly edit another person’s details?', 'Use the site’s submission tools rather than changing the published record directly. Administrators review proposed changes before applying them.'],
            ['Why are changes not immediately visible?', 'Submissions may need checking against records or discussion with other relatives before they are published.'],
            ['What evidence should I provide?', 'Give a source, document, photograph, link, or a clear explanation of how the information was confirmed.'],
            ['Can I submit multiple corrections?', 'Yes. Keep each correction clear and identify the person and field affected so it can be reviewed efficiently.'],
        ],
    ],
    'H' => [
        'title' => 'Adding New Family Members',
        'intro' => 'Follow the contribution process when a relative or relationship is missing from the tree.',
        'faqs' => [
            ['Who can add a new family member?', 'Registered users may submit additions where this feature is enabled. Administrators review the information before it becomes part of the published tree.'],
            ['When should I add a new person rather than update an existing person?', 'Search carefully first. Add a person only when no existing record represents the same individual.'],
            ['What information should I provide?', 'Provide the person’s name, relationships, dates, places, and supporting evidence where available. Approximate information should be clearly identified.'],
            ['How do I avoid duplicate people?', 'Search by several name spellings and compare parents, spouse, dates, and places before submitting a new person.'],
        ],
    ],
    'I' => [
        'title' => 'Updating Existing People',
        'intro' => 'Use the contribution workflow to suggest improvements to an existing person or relationship.',
        'faqs' => [
            ['How do I update someone’s information?', 'Open the relevant person or close-relatives page, edit the permitted fields locally, and submit the changes for review.'],
            ['Can I update my own information?', 'You may submit information about yourself where the site allows it. Privacy and administrator review rules still apply.'],
            ['Can I add a missing spouse, parent, or child?', 'Yes, provide the relationship and supporting information through the available contribution process.'],
            ['Who approves updates?', 'The family-tree administrators or designated contributors review submissions and decide when the published data should be changed.'],
        ],
    ],
    'J' => [
        'title' => 'Registration',
        'intro' => 'Registration creates an account for relatives who need access to member features and contribution tools.',
        'faqs' => [
            ['Why should I register?', 'Registration identifies you as a relative or contributor and can unlock features that are not available to logged-out visitors.'],
            ['How do I register?', 'Choose Register from the login menu, complete the form, answer the block-spam question when you have the secret code, and submit the form.'],
            ['What is a secret code on the registration form?', 'The secret code helps ensure that the registration form is being completed by a family member. Because it is shared only within family groups of relatives, it helps prevent external members from registering on our family tree and gaining access. Secret codes are changed periodically for security reasons.'],
            ['Can I submit the form without a secret code?', 'Yes. Without a secret code, your user will not be created immediately and you will not have immediate access. Instead, an email will be sent to the current Shijrah Administrator, who will review your registration details and approve access.'],
            ['Where can I find the secret code?', 'You can obtain the secret code from the Shijrah Administrator. The administrator’s contact details are provided at the bottom of the screen.'],
            ['I provided the secret code, but my access still appears limited. Why?', 'A registration made with a secret code grants access immediately, but it is still subject to manual review by the administrator. Until the administrator approves the login, you may have limited access to Shijrah. Once approved, you will gain full access.'],
            ['What information is required?', 'The form identifies the information required. Provide accurate contact details so the administrators can reach you about the registration.'],
            ['Do I need to use my real name?', 'Use information that allows the administrators to identify and contact you appropriately.'],
            ['Why have I not received a registration email?', 'Check your spam folder and confirm that the email address was entered correctly. Contact the administrators if the message still does not arrive.'],
            ['Does registering automatically connect my account to my family-tree record?', 'No. An account and a person record in the family tree are separate until an administrator confirms the connection.'],
        ],
    ],
    'K' => [
        'title' => 'Password & Account Problems',
        'intro' => 'These steps cover common login, password-reset, and account-access problems.',
        'faqs' => [
            ['I forgot my password. What do I do?', 'Use the password-reset link on the Login page and follow the instructions sent to your registered email address.'],
            ['I requested a password reset but did not receive an email.', 'Check spam or junk folders, wait a few minutes, and confirm that you used the registered email address. Contact the administrators if you no longer have access to it.'],
            ['How do I change my password?', 'Log in, open User settings, and use the password-change option if it is available for your account.'],
            ['How do I log out?', 'Choose Logoff from the user menu. Always log out on a shared or public computer.'],
            ['I cannot log in even though my password is correct.', 'Check the username and email address, try a password reset, and contact the administrators if the problem continues.'],
            ['My password-reset link has expired. What should I do?', 'Request a new reset link and use the newest email rather than an older link.'],
        ],
    ],
    'L' => [
        'title' => 'Privacy & Deceased/Living People',
        'intro' => 'Family information is handled according to the privacy choices and policies configured by the administrators.',
        'faqs' => [
            ['Can everyone see living relatives?', 'Not necessarily. Living people and sensitive details may be hidden from visitors or shown only to logged-in users.'],
            ['Why is information about some people hidden?', 'Privacy rules protect living people and information that the administrators have chosen not to publish.'],
            ['Can I request removal or correction of information?', 'Yes. Contact the administrators and explain the person, information, and reason for the request.'],
            ['What information should I not add?', 'Do not submit passwords, financial information, identity documents, or other sensitive information that is not needed for family history.'],
        ],
    ],
    'M' => [
        'title' => 'Troubleshooting',
        'intro' => 'Try these checks before contacting the administrators about a technical problem.',
        'faqs' => [
            ['The page is not loading. What should I do?', 'Refresh the page, check your internet connection, and try a current browser. If only one page fails, note its address when contacting support.'],
            ['The family tree is slow. Why?', 'Large reports and searches can take longer. Try narrower search criteria and avoid opening several large reports at once.'],
            ['I cannot submit a change.', 'Check that required fields are complete, then try again. Save the details and contact the administrators if the problem persists.'],
            ['The website does not look right on my phone.', 'Use an up-to-date browser, rotate the device if needed, and try closing and reopening the page.'],
            ['I did not receive an email.', 'Check spam or junk folders and verify the email address. The administrators can investigate delivery problems.'],
        ],
    ],
    'N' => [
        'title' => 'Fun Discovery Tutorials',
        'intro' => 'Use the family tree to discover yourself, your ancestors, cousins, descendants, and family branches.',
        'faqs' => [
            ['How can I find myself in the tree?', 'Search for your name, compare the dates and relationships, and open the person who matches your family information.'],
            ['How can I discover my cousins?', 'Open your parents and grandparents, then follow their children and descendants through the family pages or reports.'],
            ['How can I explore a whole family branch?', 'Start with an ancestor and use an outline or descendant report to follow successive generations.'],
            ['How can I find out how I am related to someone?', 'Select yourself and the other person in the Relationship Calculator.'],
        ],
    ],
    'O' => [
        'title' => 'Your First 10 Minutes',
        'intro' => 'A suggested first visit: register, find yourself, open your family page, explore ancestors and descendants, and report anything that needs correction.',
        'faqs' => [
            ['What should I do first?', 'Start with the search, find a matching family member, and follow the links to parents, children, and other relatives. Registration gives access to additional member features.'],
        ],
    ],
    'P' => [
        'title' => 'Video Playlist Guide',
        'intro' => 'The planned videos will be organised into playlists for getting started, finding family, relationships, family branches, contributions, and accounts.',
        'faqs' => [
            ['Will there be a video for every FAQ?', 'No. FAQs explain simple answers in text. Videos will focus on tasks where seeing the screen makes the process easier to understand.'],
            ['Where will the videos appear?', 'A video placeholder is reserved in each section. Videos will be added later by the family-tree administrators.'],
        ],
    ],
];
?>

<style>
    .learning-center .accordion-button {
        font-weight: 600;
    }

    .learning-center .accordion-body {
        color: #6c757d;
    }
</style>

<div class="container-fluid py-3 learning-center">
    <div class="text-center mb-4">
        <h1><?= __('Learning Center'); ?></h1>
        <p class="lead">Learn how to explore, understand, and help preserve our family history.</p>
    </div>

    <?php if (!$loggedIn) { ?>
        <div class="alert alert-info">
            Sections A, J, and K are available without logging in. Please log in to view the remaining Learning Center topics.
        </div>
    <?php } ?>

    <?php foreach ($sections as $key => $section) {
        if (!$loggedIn && !in_array($key, $publicSections, true)) {
            continue;
        }
    ?>
        <section class="mb-4" id="learning-center-<?= strtolower($key); ?>">
            <h2><?= htmlspecialchars($key . '. ' . $section['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
            <p><?= htmlspecialchars($section['intro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <div class="accordion" id="learning-accordion-<?= strtolower($key); ?>">
                <?php foreach ($section['faqs'] as $index => $faq) {
                    $faqId = strtolower($key) . '-' . $index;
                ?>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="learning-heading-<?= $faqId; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#learning-collapse-<?= $faqId; ?>" aria-expanded="false" aria-controls="learning-collapse-<?= $faqId; ?>">
                                <?= htmlspecialchars($faq[0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                            </button>
                        </h3>
                        <div id="learning-collapse-<?= $faqId; ?>" class="accordion-collapse collapse" aria-labelledby="learning-heading-<?= $faqId; ?>" data-bs-parent="#learning-accordion-<?= strtolower($key); ?>">
                            <div class="accordion-body"><?= htmlspecialchars($faq[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="alert alert-light border mt-3 mb-0">
                <strong>Video placeholder:</strong> A tutorial video for this section will be added later.
            </div>
        </section>
    <?php } ?>
</div>

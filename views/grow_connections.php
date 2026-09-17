<?php

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$source = $data['source'] ?? null;
$baseUrl = 'index.php?page=grow_connections&amp;tree_id=' . (int) $tree_id;
?>
<h1 class="my-4"><?= __('Grow Connections'); ?></h1>
<p><?= __('Living people with a phone number, and the close relatives they may help us reach.'); ?></p>

<?php if ($source) { ?>
    <p><a href="<?= $baseUrl; ?>">&larr; <?= __('Back to Grow Connections'); ?></a></p>
    <h2><?= $escape($source['name']); ?></h2>
    <p><?= __('Close relatives without a phone number'); ?> (<?= count($data['missing']); ?>)</p>
    <?php if (!$data['missing']) { ?><div class="alert alert-info"><?= __('No close relatives without a phone number were found.'); ?></div><?php } else { ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead><tr><th><?= __('Gedcom Number'); ?></th><th><?= __('Person\'s Name'); ?></th><th><?= __('Address'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($data['missing'] as $person) { ?>
                    <tr><td><?= $escape($person['gedcom']); ?></td><td><a href="index.php?page=family&amp;tree_id=<?= (int) $tree_id; ?>&amp;main_person=<?= rawurlencode($person['gedcom']); ?>"><?= $escape($person['name']); ?></a></td><td><?= $escape($person['address']); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead><tr><th><?= __('Gedcom Number'); ?></th><th><?= __('Person\'s Name'); ?></th><th><?= __('Phone'); ?></th><th><?= __('Address'); ?></th><th><?= __('Reach count'); ?></th></tr></thead>
            <tbody>
            <?php foreach ($data['sources'] as $person) { ?>
                <tr><td><?= $escape($person['gedcom']); ?></td><td><?= $escape($person['name']); ?></td><td><?= $escape($person['phone']); ?></td><td><?= $escape($person['address']); ?></td><td><a href="<?= $baseUrl; ?>&amp;source=<?= rawurlencode($person['gedcom']); ?>"><?= (int) $person['reach_count']; ?></a></td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>

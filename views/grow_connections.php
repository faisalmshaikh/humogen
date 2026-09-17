<?php

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$source = $data['source'] ?? null;
$sortOrder = ($_GET['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$nextSortOrder = $sortOrder === 'asc' ? 'desc' : 'asc';
$baseUrl = 'index.php?page=grow_connections&amp;tree_id=' . (int) $tree_id;
?>
<h1 class="my-4"><?= __('Grow Connections'); ?></h1>
<p><?= __('Living people with a phone number, and the close relatives they may help us reach.'); ?></p>

<?php if ($source) { ?>
    <?php $grow_connections_html_token = bin2hex(random_bytes(32)); $_SESSION['grow_connections_html_token'] = $grow_connections_html_token; ?>
    <p><a href="<?= $baseUrl; ?>">&larr; <?= __('Back to Grow Connections'); ?></a></p>
    <h2><?= $escape($source['name']); ?></h2>
    <p><?= __('Close relatives without a phone number'); ?> (<?= count($data['missing']); ?>)</p>
    <button type="button" class="btn btn-sm btn-success mb-3" onclick="exportGrowConnectionsHtml()"><?= __('Copy HTML table link'); ?></button>
    <span id="grow-connections-html-status" class="ms-2" role="status"></span>
    <?php if (!$data['missing']) { ?><div class="alert alert-info"><?= __('No close relatives without a phone number were found.'); ?></div><?php } else { ?>
        <div class="table-responsive">
            <table id="grow-connections-table" class="table table-striped table-bordered align-middle">
            <thead><tr><th><?= __('Gedcom Number'); ?></th><th><?= __('Person\'s Name'); ?></th><th><?= __('Birth Date'); ?></th><th><?= __('Death Date'); ?></th><th><?= __('Phone'); ?></th><th><?= __('Address'); ?></th><th><?= __('Relation'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($data['missing'] as $person) { ?>
                    <tr><td><?= $escape($person['gedcom']); ?></td><td><a href="index.php?page=family&amp;tree_id=<?= (int) $tree_id; ?>&amp;main_person=<?= rawurlencode($person['gedcom']); ?>"><?= $escape($person['name']); ?></a></td><td><?= $escape($person['birth_date']); ?></td><td><?= $escape($person['death_date']); ?></td><td><?= $escape($person['phone']); ?></td><td><?= $escape($person['address']); ?></td><td><?= $escape($person['relation']); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <script>
        function copyGrowConnectionsUrl(url) {
            if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(url);
            const input = document.createElement('textarea');
            input.value = url; input.setAttribute('readonly', ''); input.style.position = 'fixed'; input.style.opacity = '0';
            document.body.appendChild(input); input.select();
            const copied = document.execCommand('copy'); input.remove();
            return copied ? Promise.resolve() : Promise.reject(new Error(<?= json_encode(__('Unable to copy the page link to the clipboard.')); ?>));
        }

        function exportGrowConnectionsHtml() {
            const table = document.getElementById('grow-connections-table');
            const status = document.getElementById('grow-connections-html-status');
            if (!table) { status.textContent = <?= json_encode(__('The drill-down table is not available.')); ?>; return; }
            const exportTable = table.cloneNode(true);
            exportTable.querySelectorAll('a').forEach(link => link.replaceWith(document.createTextNode(link.textContent)));
            status.textContent = <?= json_encode(__('Generating HTML page...')); ?>;
            const endpoint = new URL(window.location.href);
            endpoint.searchParams.set('grow_html', '1');
            fetch(endpoint.toString(), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Grow-Connections-HTML-Token': <?= json_encode($grow_connections_html_token); ?> }, credentials: 'same-origin', body: JSON.stringify({ tableHtml: exportTable.outerHTML }) })
                .then(response => response.json().then(result => { if (!response.ok || !result.success) throw new Error(result.message || <?= json_encode(__('Unable to generate the HTML page.')); ?>); return result.url; }))
                .then(url => copyGrowConnectionsUrl(url).then(() => url))
                .then(url => { const link = document.createElement('a'); link.href = url; link.target = '_blank'; link.rel = 'noopener'; link.textContent = url; status.replaceChildren(document.createTextNode(<?= json_encode(__('HTML page link copied:')); ?> + ' '), link); })
                .catch(error => { status.textContent = error.message || <?= json_encode(__('Unable to generate the HTML page.')); ?>; });
        }
    </script>
<?php } else { ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead><tr><th><?= __('Gedcom Number'); ?></th><th><?= __('Person\'s Name'); ?></th><th><?= __('Phone'); ?></th><th><?= __('Address'); ?></th><th><a href="<?= $baseUrl; ?>&amp;sort_order=<?= $nextSortOrder; ?>"><?= __('Reach count'); ?> <?= $sortOrder === 'asc' ? '▲' : '▼'; ?></a></th></tr></thead>
            <tbody>
            <?php foreach ($data['sources'] as $person) { ?>
                <tr><td><?= $escape($person['gedcom']); ?></td><td><?= $escape($person['name']); ?></td><td><?= $escape($person['phone']); ?></td><td><?= $escape($person['address']); ?></td><td><a href="<?= $baseUrl; ?>&amp;source=<?= rawurlencode($person['gedcom']); ?>"><?= (int) $person['reach_count']; ?></a></td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>

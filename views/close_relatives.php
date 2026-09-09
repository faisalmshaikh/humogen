<?php

$graphJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
    .close-relatives-toolbar { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .close-relatives-viewport { width:100%; height:calc(100vh - 320px); min-height:360px; overflow:auto; border:1px solid #ced4da; background:#fff; touch-action:none; }
    .close-relatives-canvas { position:relative; width:100%; height:100%; min-width:100%; min-height:100%; transform-origin:top left; }
    .close-relatives-chart { position:absolute; inset:0; width:100%; height:100%; }
    .close-relatives-table td[data-editable="true"] { min-width:8rem; cursor:default; }
    .close-relatives-table td[data-editable="true"][contenteditable="true"] { cursor:text; outline:2px solid #86b7fe; outline-offset:-2px; }
    .close-relatives-table td:first-child { min-width:3rem; white-space:nowrap; }
    .close-relatives-legend span { display:inline-block; padding:.25rem .6rem; margin-right:.5rem; border:1px solid #adb5bd; border-radius:.25rem; }
    .close-relatives-legend .male { background:#9ec5fe; }
    .close-relatives-legend .female { background:#f1aeb5; }
</style>

<h1 class="my-4"><?= __('Close Relatives'); ?></h1>
<div class="close-relatives-toolbar mb-2">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-zoom-out">−</button>
    <span id="close-relatives-zoom-level">100%</span>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-zoom-in">+</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-zoom-reset"><?= __('Reset zoom'); ?></button>
    <label for="close-relatives-depth" class="ms-2 mb-0"><?= __('Tree depth'); ?>:</label>
    <select class="form-select form-select-sm w-auto" id="close-relatives-depth">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="-1" selected><?= __('All'); ?></option>
    </select>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-center"><?= __('Center tree'); ?></button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-orientation"><?= __('Horizontal tree'); ?></button>
</div>
<p><?= __('Click a person to expand or collapse their relatives. Drag the background to pan, or use the mouse wheel to zoom.'); ?></p>
<div class="close-relatives-legend mb-2">
    <span class="male"><?= __('Male'); ?></span>
    <span class="female"><?= __('Female'); ?></span>
</div>

<?php if (!$data['main_person']) { ?>
    <div class="alert alert-warning"><?= __('The requested person could not be found.'); ?></div>
<?php } else { ?>
    <div class="close-relatives-viewport" aria-label="<?= __('Close relatives tree'); ?>">
        <div class="close-relatives-canvas" id="close-relatives-canvas">
            <div class="close-relatives-chart" id="close-relatives-chart" role="img" aria-label="<?= __('Close Relatives'); ?>"></div>
        </div>
    </div>
    <?php
    $close_relatives_sheet_token = bin2hex(random_bytes(32));
    $_SESSION['close_relatives_sheet_token'] = $close_relatives_sheet_token;
    ?>
    <div class="table-responsive mt-4">
        <table class="table table-sm table-bordered close-relatives-table" id="close-relatives-table">
            <thead>
                <tr>
                    <th aria-label="<?= __('Person Popup'); ?>"></th>
                    <th><?= __('First Name'); ?></th>
                    <th><?= __('GEDCOM Number'); ?></th>
                    <th><?= __('Birth Date'); ?></th>
                    <th><?= __('Death Date'); ?></th>
                    <th><?= __('Phone Number'); ?></th>
                    <th><?= __('Address'); ?></th>
                    <th><?= __('Relation Name'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="mb-4">
        <button type="button" class="btn btn-sm btn-success" id="close-relatives-submit">
            <?= __('Submit Changes'); ?>
        </button>
        <span id="close-relatives-submit-status" class="ms-2" role="status"></span>
    </div>
    <script src="assets/echarts/echarts.min.js"></script>
    <script type="application/json" id="close-relatives-data"><?= $graphJson; ?></script>
    <script>
        (() => {
            const data = JSON.parse(document.getElementById('close-relatives-data').textContent);
            const viewport = document.querySelector('.close-relatives-viewport');
            const canvas = document.getElementById('close-relatives-canvas');
            const chartElement = document.getElementById('close-relatives-chart');
            const nodesById = new Map(data.nodes.map(node => [Number(node.id), node]));
            const mainNode = data.nodes.find(node => node.gedcom === data.main_person);
            const familyEdges = data.edges.filter(edge => !['Spouse', 'Sibling'].includes(edge.label));
            const siblingEdges = data.edges.filter(edge => edge.label === 'Sibling');
            const spouseEdge = data.edges.find(edge => edge.label === 'Spouse');
            const parentsOf = id => [...new Set(familyEdges.filter(edge => Number(edge.to) === id).map(edge => Number(edge.from)))];
            const childrenOf = id => [...new Set(familyEdges.filter(edge => Number(edge.from) === id).map(edge => Number(edge.to)))];
            const nodeSex = id => nodesById.get(id)?.sex || '';

            const decodeHtml = value => {
                const element = document.createElement('textarea');
                element.innerHTML = value || '';
                return element.value;
            };
            const nodeColor = node => node.sex === 'M' ? '#9ec5fe' : node.sex === 'F' ? '#f1aeb5' : '#eee';
            const relationById = new Map();
            const buildPersonNode = (id, children = [], relation = '') => {
                const node = nodesById.get(id);
                if (!node) return null;
                relationById.set(id, relation);
                return {
                    id: node.id,
                    name: decodeHtml(node.first_name || node.name),
                    symbol: 'circle',
                    itemStyle: {
                        color: nodeColor(node),
                        borderColor: node.gedcom === data.main_person ? '#0d6efd' : '#68727e',
                        borderWidth: node.gedcom === data.main_person ? 3 : 1
                    },
                    children: children.filter(Boolean)
                };
            };
            const buildGroupNode = (id, label, children) => ({
                id,
                name: label,
                isGroup: true,
                symbol: 'roundRect',
                symbolSize: [80, 24],
                itemStyle: { color: '#e9ecef', borderColor: '#68727e', borderWidth: 1 },
                children: children.filter(Boolean)
            });

            const mainId = mainNode ? Number(mainNode.id) : null;
            const mainParents = mainId === null ? [] : parentsOf(mainId);
            const fatherId = mainParents.find(id => nodeSex(id) === 'M');
            const motherId = mainParents.find(id => nodeSex(id) === 'F');
            const spouseId = spouseEdge ? Number(spouseEdge.to) : null;
            const childrenWithDescendants = (personId, relation, descendantRelation) => childrenOf(personId)
                .map(childId => buildPersonNode(
                    childId,
                    childrenOf(childId).map(grandchildId => buildPersonNode(
                        grandchildId,
                        [],
                        typeof descendantRelation === 'function' ? descendantRelation(grandchildId) : descendantRelation
                    )),
                    relation
                ));
            const siblingIds = [...new Set(siblingEdges
                .filter(edge => parentsOf(mainId).includes(Number(edge.from)))
                .map(edge => Number(edge.to)))];

            const buildParentBranch = (parentId, side) => {
                if (parentId === undefined) return null;
                const grandparents = parentsOf(parentId)
                    .sort((left, right) => (nodeSex(left) === 'M' ? 0 : 1) - (nodeSex(right) === 'M' ? 0 : 1))
                    .slice(0, 2);
                const grandmotherId = grandparents.find(id => nodeSex(id) === 'F');
                const auntsAndUncles = grandmotherId === undefined ? [] : childrenOf(grandmotherId)
                    .filter(id => id !== parentId)
                    .map(id => buildPersonNode(
                        id,
                        childrenOf(id).map(childId => buildPersonNode(childId, [], `${side} cousin`)),
                        `${side} ${nodeSex(id) === 'M' ? 'uncle' : 'aunt'}`
                    ));
                const grandparentNodes = grandparents.map(grandparentId => buildPersonNode(
                    grandparentId,
                    grandparentId === grandmotherId ? auntsAndUncles : [],
                    `${side} ${nodeSex(grandparentId) === 'M' ? 'grandfather' : 'grandmother'}`
                ));
                return buildPersonNode(parentId, grandparentNodes, nodeSex(parentId) === 'M' ? 'Father' : 'Mother');
            };

            const buildSpouseBranch = spousePersonId => {
                if (spousePersonId === null) return null;
                const spouseParents = parentsOf(spousePersonId);
                const spouseFatherId = spouseParents.find(id => nodeSex(id) === 'M');
                const spouseMotherId = spouseParents.find(id => nodeSex(id) === 'F');
                const spouseMotherChildren = spouseMotherId === undefined ? [] : childrenOf(spouseMotherId)
                    .filter(id => id !== spousePersonId)
                    .map(id => buildPersonNode(id, [], 'Sibling of spouse'));
                return buildPersonNode(spousePersonId, [
                    spouseFatherId === undefined ? null : buildPersonNode(spouseFatherId, [], 'Father of spouse'),
                    spouseMotherId === undefined ? null : buildPersonNode(spouseMotherId, spouseMotherChildren, 'Mother of spouse')
                ], 'Spouse');
            };

            const treeData = [buildPersonNode(mainId, [
                buildParentBranch(fatherId, 'Paternal'),
                buildParentBranch(motherId, 'Maternal'),
                buildSpouseBranch(spouseId),
                buildGroupNode('siblings-group', 'Siblings', siblingIds.map(id => buildPersonNode(
                    id,
                    childrenWithDescendants(
                        id,
                        nodeSex(id) === 'M' ? 'Brother' : 'Sister',
                        childId => nodeSex(childId) === 'M' ? 'Nephew' : 'Niece'
                    ),
                    nodeSex(id) === 'M' ? 'Brother' : 'Sister'
                ))),
                buildGroupNode('children-group', 'Children', childrenWithDescendants(mainId, 'Child', 'Grandchild'))
            ], 'Main person')].filter(Boolean);

            // Keep the table relation tied to the main person even if a person
            // is encountered again while building another family branch.
            siblingIds.forEach(siblingId => {
                childrenOf(siblingId).forEach(childId => {
                    relationById.set(childId, nodeSex(childId) === 'M' ? 'Nephew' : 'Niece');
                });
            });

            const tableNodeIds = [];
            const collectTreeNodes = nodes => nodes.forEach(node => {
                if (!node) return;
                if (node.isGroup) {
                    collectTreeNodes(node.children || []);
                    return;
                }
                if (tableNodeIds.includes(Number(node.id))) return;
                tableNodeIds.push(Number(node.id));
                collectTreeNodes(node.children || []);
            });
            collectTreeNodes(treeData);

            const escapeHtml = value => {
                const element = document.createElement('div');
                element.textContent = value || '';
                return element.innerHTML;
            };
            const renderTable = () => {
                const body = document.querySelector('#close-relatives-table tbody');
                body.replaceChildren();
                tableNodeIds.forEach(id => {
                    const node = nodesById.get(id);
                    if (!node) return;
                    const relation = relationById.get(id) || 'Relative';
                    const row = document.createElement('tr');
                    row.innerHTML = `<td data-editable="false">${node.popup || ''}</td>`
                        + `<td data-editable="true"><a href="${escapeHtml(node.family_url)}">${escapeHtml(node.first_name || node.name)}</a></td>`
                        + `<td data-editable="false">${escapeHtml(node.gedcom)}</td>`
                        + `<td data-editable="true">${escapeHtml(node.birth_date)}</td>`
                        + `<td data-editable="true">${escapeHtml(node.death_date)}</td>`
                        + `<td data-editable="true">${escapeHtml(node.phone)}</td>`
                        + `<td data-editable="true">${escapeHtml(node.address)}</td>`
                        + `<td data-editable="false">${escapeHtml(relation)}</td>`;
                    row.querySelectorAll('td[data-editable="true"]').forEach(cell => {
                        cell.spellcheck = false;
                        cell.contentEditable = 'false';
                        cell.addEventListener('dblclick', event => {
                            event.preventDefault();
                            event.stopPropagation();
                            cell.contentEditable = 'true';
                            cell.focus();
                        });
                        cell.addEventListener('blur', () => {
                            cell.contentEditable = 'false';
                        });
                    });
                    const nameLink = row.cells[1].querySelector('a');
                    let navigationTimer;
                    nameLink.addEventListener('click', event => {
                        event.preventDefault();
                        clearTimeout(navigationTimer);
                        navigationTimer = setTimeout(() => { window.location.href = nameLink.href; }, 250);
                    });
                    nameLink.addEventListener('dblclick', event => {
                        event.preventDefault();
                        event.stopPropagation();
                        clearTimeout(navigationTimer);
                        row.cells[1].contentEditable = 'true';
                        row.cells[1].focus();
                    });
                    body.appendChild(row);
                });
            };

            let orientation = 'TB';
            const makeLabel = () => ({
                position: orientation === 'TB' ? 'top' : 'left',
                verticalAlign: 'middle',
                align: orientation === 'TB' ? 'center' : 'right',
                rotate: orientation === 'TB' ? 90 : 0,
                fontSize: 12,
                formatter: params => params.data.name
            });
            const makeLeavesLabel = () => ({
                ...makeLabel(),
                position: orientation === 'TB' ? 'bottom' : 'right',
                align: orientation === 'TB' ? 'center' : 'left'
            });
            const chart = echarts.init(chartElement);
            const chartOption = {
                animationDuration: 500,
                animationDurationUpdate: 750,
                series: [{
                    type: 'tree',
                    data: treeData,
                    layout: 'orthogonal',
                    orient: orientation,
                    top: '8%',
                    left: '5%',
                    bottom: '8%',
                    right: '5%',
                    symbol: 'circle',
                    symbolSize: 9,
                    roam: true,
                    expandAndCollapse: true,
                    initialTreeDepth: -1,
                    lineStyle: { color: '#59636e', width: 1.5 },
                    label: makeLabel(),
                    leaves: { label: makeLeavesLabel() },
                    emphasis: { focus: 'ancestor' }
                }]
            };

            let selectedDepth = -1;
            const setTreeDepth = depth => {
                selectedDepth = depth;
                chart.setOption({
                    ...chartOption,
                    series: [{
                        ...chartOption.series[0],
                        orient: orientation,
                        initialTreeDepth: depth,
                        label: makeLabel(),
                        leaves: { label: makeLeavesLabel() }
                    }]
                }, true);
            };

            let zoom = 1;
            const applyZoom = nextZoom => {
                zoom = Math.max(.5, Math.min(1.75, nextZoom));
                canvas.style.transform = `scale(${zoom})`;
                if (zoom === 1) {
                    canvas.style.minHeight = '';
                    canvas.style.minWidth = '';
                    viewport.style.minHeight = '';
                    viewport.style.minWidth = '';
                } else {
                    canvas.style.minHeight = `${viewport.clientHeight}px`;
                    canvas.style.minWidth = `${viewport.clientWidth}px`;
                    viewport.style.minHeight = `${Math.max(viewport.clientHeight, viewport.clientHeight * zoom)}px`;
                    viewport.style.minWidth = `${Math.max(viewport.clientWidth, viewport.clientWidth * zoom)}px`;
                }
                document.getElementById('close-relatives-zoom-level').textContent = `${Math.round(zoom * 100)}%`;
            };

            document.getElementById('close-relatives-zoom-out').addEventListener('click', () => applyZoom(zoom - .1));
            document.getElementById('close-relatives-zoom-in').addEventListener('click', () => applyZoom(zoom + .1));
            document.getElementById('close-relatives-zoom-reset').addEventListener('click', () => applyZoom(1));
            const depthSelect = document.getElementById('close-relatives-depth');
            depthSelect.addEventListener('change', () => {
                setTreeDepth(Number(depthSelect.value));
            });
            document.getElementById('close-relatives-center').addEventListener('click', () => {
                applyZoom(1);
                chart.dispatchAction({ type: 'restore' });
                setTreeDepth(selectedDepth);
                chart.resize();
            });
            const orientationButton = document.getElementById('close-relatives-orientation');
            const updateOrientationButton = () => {
                orientationButton.textContent = orientation === 'TB'
                    ? <?= json_encode(__('Horizontal tree')); ?>
                    : <?= json_encode(__('Vertical tree')); ?>;
            };
            orientationButton.addEventListener('click', () => {
                orientation = orientation === 'TB' ? 'LR' : 'TB';
                updateOrientationButton();
                setTreeDepth(selectedDepth);
            });
            const submitButton = document.getElementById('close-relatives-submit');
            const submitStatus = document.getElementById('close-relatives-submit-status');
            submitButton.addEventListener('click', () => {
                const headers = ['', 'First Name', 'GEDCOM Number', 'Birth Date', 'Death Date', 'Phone Number', 'Address', 'Relation Name'];
                const rows = [headers];
                document.querySelectorAll('#close-relatives-table tbody tr').forEach(row => {
                    rows.push(Array.from(row.cells).map((cell, index) => index === 0 ? '' : cell.innerText.replace(/\s+/g, ' ').trim()));
                });
                submitButton.disabled = true;
                submitStatus.textContent = <?= json_encode(__('Submitting changes to Google Sheets...')); ?>;
                const endpoint = new URL(window.location.href);
                endpoint.searchParams.set('google_sheet', '1');
                fetch(endpoint.toString(), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Close-Relatives-Sheet-Token': <?= json_encode($close_relatives_sheet_token); ?>
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rows, mainPerson: data.main_person })
                }).then(response => response.json()).then(result => {
                    submitStatus.textContent = result.success
                        ? <?= json_encode(__('Changes submitted to Google Sheets.')); ?>
                        : (result.message || <?= json_encode(__('Unable to submit changes to Google Sheets.')); ?>);
                }).catch(() => {
                    submitStatus.textContent = <?= json_encode(__('Unable to submit changes to Google Sheets.')); ?>;
                }).finally(() => {
                    submitButton.disabled = false;
                });
            });
            const resizeChart = () => {
                chart.resize();
                if (zoom === 1) {
                    viewport.style.minHeight = '';
                    viewport.style.minWidth = '';
                } else {
                    applyZoom(zoom);
                }
            };
            window.addEventListener('resize', resizeChart);
            if (window.ResizeObserver) new ResizeObserver(resizeChart).observe(viewport);
            renderTable();
            chart.setOption(chartOption);
        })();
    </script>
<?php } ?>

<?php

$graphJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
    .close-relatives-toolbar { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .close-relatives-viewport { height:720px; overflow:auto; border:1px solid #ced4da; background:#fff; touch-action:none; }
    .close-relatives-canvas { position:relative; width:1800px; height:1200px; transform-origin:top left; }
    .close-relatives-chart { position:absolute; inset:0; width:1800px; height:1200px; }
    .close-relatives-center { position:absolute; left:50%; top:50%; display:flex; gap:10px; transform:translate(-50%, -50%); z-index:2; }
    .close-relatives-center .close-relatives-node { position:relative; left:auto; top:auto; }
    .close-relatives-node { width:210px; min-height:82px; padding:.35rem .5rem; border:2px solid #68727e; border-radius:10px; box-sizing:border-box; color:#18212b; font-size:12px; cursor:pointer; user-select:none; box-shadow:0 2px 5px #0002; }
    .close-relatives-node.main { border:4px solid #0d6efd; }
    .close-relatives-node .node-name { display:inline-block; vertical-align:middle; font-size:13px; font-weight:600; line-height:1.2; margin-bottom:.25rem; }
    .close-relatives-node .node-contact { display:block; line-height:1.25; overflow-wrap:anywhere; }
    .close-relatives-node .node-popup { display:inline-block; vertical-align:middle; margin-right:.2rem; }
    .close-relatives-node .node-popup .dropdown { display:inline-block; }
    .close-relatives-node .node-popup .btn { padding:0 .15rem; }
    .close-relatives-node .dropdown-menu { font-size:12px; }
    .close-relatives-node.male { background:#d9efff; }
    .close-relatives-node.female { background:#fff6c7; }
    .close-relatives-node.unknown { background:#eee; }
    .close-relatives-legend span { display:inline-block; padding:.25rem .6rem; margin-right:.5rem; border:1px solid #adb5bd; border-radius:.25rem; }
    .close-relatives-legend .male { background:#d9efff; }
    .close-relatives-legend .female { background:#fff6c7; }
</style>

<h1 class="my-4"><?= __('Close Relatives'); ?></h1>
<div class="close-relatives-toolbar mb-2">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-zoom-out">−</button>
    <span id="close-relatives-zoom-level">100%</span>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-zoom-in">+</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-zoom-reset"><?= __('Reset zoom'); ?></button>
    <label class="form-check form-check-inline ms-2 mb-0">
        <input class="form-check-input" type="checkbox" id="close-relatives-show-contacts">
        <span class="form-check-label"><?= __('Show phone and address for other relatives'); ?></span>
    </label>
</div>
<p><?= __('Drag the chart to pan, use the mouse wheel to zoom, or click a person to open their page.'); ?></p>
<div class="close-relatives-legend mb-2">
    <span class="male"><?= __('Male'); ?></span>
    <span class="female"><?= __('Female'); ?></span>
</div>

<?php if (!$data['main_person']) { ?>
    <div class="alert alert-warning"><?= __('The requested person could not be found.'); ?></div>
<?php } else { ?>
    <div class="close-relatives-viewport" aria-label="<?= __('Close relatives radial tree'); ?>">
        <div class="close-relatives-canvas" id="close-relatives-canvas">
            <div class="close-relatives-chart" id="close-relatives-chart" role="img" aria-label="<?= __('Close Relatives'); ?>"></div>
            <div class="close-relatives-center" id="close-relatives-center"></div>
        </div>
    </div>
    <script src="assets/echarts/echarts.min.js"></script>
    <script type="application/json" id="close-relatives-data"><?= $graphJson; ?></script>
    <script>
        (() => {
            const data = JSON.parse(document.getElementById('close-relatives-data').textContent);
            const canvas = document.getElementById('close-relatives-canvas');
            const chartElement = document.getElementById('close-relatives-chart');
            const centerElement = document.getElementById('close-relatives-center');
            const nodesById = new Map(data.nodes.map(node => [Number(node.id), node]));
            const decodeHtml = value => {
                const element = document.createElement('textarea');
                element.innerHTML = value || '';
                return element.value;
            };
            const mainNode = data.nodes.find(node => node.gedcom === data.main_person);
            const spouseEdge = data.edges.find(edge => edge.label === 'Spouse');
            const spouseNode = spouseEdge ? nodesById.get(Number(spouseEdge.to)) : null;
            const primaryIds = new Set([mainNode, spouseNode].filter(Boolean).map(node => Number(node.id)));
            const neighbors = new Map(data.nodes.map(node => [Number(node.id), []]));
            const phoneLabel = <?= json_encode(__('Phone')); ?>;
            const addressLabel = <?= json_encode(__('Address')); ?>;

            data.edges.filter(edge => edge.label !== 'Spouse').forEach(edge => {
                const from = Number(edge.from);
                const to = Number(edge.to);
                if (!neighbors.has(from) || !neighbors.has(to)) return;
                neighbors.get(from).push(to);
                neighbors.get(to).push(from);
            });

            const nodeColor = node => node.sex === 'M' ? '#d9efff' : node.sex === 'F' ? '#fff6c7' : '#eee';
            const buildBranch = (id, parentId, seen) => {
                if (seen.has(id)) return null;
                seen.add(id);
                const node = nodesById.get(id);
                if (!node) return null;
                const children = [...new Set(neighbors.get(id) || [])]
                    .filter(childId => childId !== parentId && !seen.has(childId))
                    .map(childId => buildBranch(childId, id, seen))
                    .filter(Boolean);
                return {
                    id: node.id,
                    name: decodeHtml(node.name),
                    phone: decodeHtml(node.phone),
                    address: decodeHtml(node.address),
                    family_url: decodeHtml(node.family_url),
                    popup: node.popup,
                    isPrimary: primaryIds.has(id),
                    symbol: primaryIds.has(id) ? 'none' : 'circle',
                    label: primaryIds.has(id) ? { show: false } : undefined,
                    itemStyle: { color: nodeColor(node), borderColor: node.gedcom === data.main_person ? '#0d6efd' : '#68727e', borderWidth: node.gedcom === data.main_person ? 3 : 1 },
                    children
                };
            };

            const seen = new Set();
            const primaryBranches = [mainNode, spouseNode]
                .filter(Boolean)
                .map(node => buildBranch(Number(node.id), null, seen))
                .filter(Boolean);
            const treeData = [{ id: 'close-relatives-root', name: '', symbol: 'none', label: { show: false }, children: primaryBranches }];
            data.nodes.forEach(node => {
                if (!seen.has(Number(node.id))) {
                    const branch = buildBranch(Number(node.id), null, seen);
                    if (branch) treeData[0].children.push(branch);
                }
            });

            const contactText = node => {
                const showContacts = node.isPrimary || document.getElementById('close-relatives-show-contacts').checked;
                const details = [];
                if (showContacts && node.phone) details.push(phoneLabel + ': ' + node.phone);
                if (showContacts && node.address) details.push(addressLabel + ': ' + node.address);
                return details;
            };

            const chart = echarts.init(chartElement);
            const chartOption = {
                animationDuration: 500,
                animationDurationUpdate: 750,
                series: [{
                    type: 'tree',
                    data: treeData,
                    layout: 'radial',
                    top: '3%',
                    left: '3%',
                    bottom: '3%',
                    right: '3%',
                    symbol: 'circle',
                    symbolSize: 9,
                    roam: true,
                    expandAndCollapse: false,
                    initialTreeDepth: -1,
                    lineStyle: { color: '#59636e', width: 1.5 },
                    label: {
                        position: 'left',
                        verticalAlign: 'middle',
                        align: 'right',
                        fontSize: 12,
                        formatter: params => [params.data.name, ...contactText(params.data)].filter(Boolean).join('\n')
                    },
                    leaves: {
                        label: {
                            position: 'right',
                            verticalAlign: 'middle',
                            align: 'left'
                        }
                    },
                    emphasis: { focus: 'ancestor' }
                }]
            };

            const renderPrimaryCards = () => {
                centerElement.replaceChildren();
                [mainNode, spouseNode].filter(Boolean).forEach(node => {
                    const card = document.createElement('div');
                    const sexClass = node.sex === 'M' ? 'male' : node.sex === 'F' ? 'female' : 'unknown';
                    card.className = 'close-relatives-node ' + sexClass + (node === mainNode ? ' main' : '');
                    card.innerHTML = `<span class="node-popup">${node.popup}</span><a class="node-name" href="${node.family_url}">${node.name}</a>`
                        + contactText({ ...node, isPrimary: true }).map(detail => `<span class="node-contact">${detail}</span>`).join('');
                    card.addEventListener('click', event => {
                        if (event.target.closest('a, button, input')) return;
                        window.location.href = node.family_url;
                    });
                    centerElement.appendChild(card);
                });
            };

            let zoom = 1;
            const applyZoom = nextZoom => {
                zoom = Math.max(.5, Math.min(1.75, nextZoom));
                canvas.style.transform = `scale(${zoom})`;
                canvas.parentElement.style.minHeight = `${1200 * zoom}px`;
                canvas.parentElement.style.minWidth = `${1800 * zoom}px`;
                document.getElementById('close-relatives-zoom-level').textContent = `${Math.round(zoom * 100)}%`;
            };

            chart.on('click', params => {
                if (params.data && params.data.family_url) window.location.href = params.data.family_url;
            });
            document.getElementById('close-relatives-show-contacts').addEventListener('change', () => {
                chart.setOption(chartOption, true);
                renderPrimaryCards();
            });
            document.getElementById('close-relatives-zoom-out').addEventListener('click', () => applyZoom(zoom - .1));
            document.getElementById('close-relatives-zoom-in').addEventListener('click', () => applyZoom(zoom + .1));
            document.getElementById('close-relatives-zoom-reset').addEventListener('click', () => applyZoom(1));
            window.addEventListener('resize', () => chart.resize());
            chart.setOption(chartOption);
            renderPrimaryCards();
        })();
    </script>
<?php } ?>

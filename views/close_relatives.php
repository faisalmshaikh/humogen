<?php

$graphJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
    .close-relatives-toolbar { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .close-relatives-viewport { width:100%; height:calc(100vh - 320px); min-height:360px; overflow:auto; border:1px solid #ced4da; background:#fff; touch-action:none; }
    .close-relatives-canvas { position:relative; width:100%; height:100%; min-width:100%; min-height:100%; transform-origin:top left; }
    .close-relatives-chart { position:absolute; inset:0; width:100%; height:100%; }
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
            const familyEdges = data.edges.filter(edge => edge.label !== 'Spouse');
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
            const buildPersonNode = (id, children = []) => {
                const node = nodesById.get(id);
                if (!node) return null;
                return {
                    id: node.id,
                    name: decodeHtml(node.name),
                    symbol: 'circle',
                    itemStyle: {
                        color: nodeColor(node),
                        borderColor: node.gedcom === data.main_person ? '#0d6efd' : '#68727e',
                        borderWidth: node.gedcom === data.main_person ? 3 : 1
                    },
                    children: children.filter(Boolean)
                };
            };

            const mainId = mainNode ? Number(mainNode.id) : null;
            const mainParents = mainId === null ? [] : parentsOf(mainId);
            const fatherId = mainParents.find(id => nodeSex(id) === 'M');
            const motherId = mainParents.find(id => nodeSex(id) === 'F');
            const spouseId = spouseEdge ? Number(spouseEdge.to) : null;

            const buildParentBranch = parentId => {
                if (parentId === undefined) return null;
                const grandparents = parentsOf(parentId)
                    .sort((left, right) => (nodeSex(left) === 'M' ? 0 : 1) - (nodeSex(right) === 'M' ? 0 : 1))
                    .slice(0, 2);
                const grandmotherId = grandparents.find(id => nodeSex(id) === 'F');
                const auntsAndUncles = grandmotherId === undefined ? [] : childrenOf(grandmotherId)
                    .filter(id => id !== parentId)
                    .map(id => buildPersonNode(id, childrenOf(id).map(childId => buildPersonNode(childId))));
                const grandparentNodes = grandparents.map(grandparentId => buildPersonNode(
                    grandparentId,
                    grandparentId === grandmotherId ? auntsAndUncles : []
                ));
                return buildPersonNode(parentId, grandparentNodes);
            };

            const buildSpouseBranch = spousePersonId => {
                if (spousePersonId === null) return null;
                const spouseParents = parentsOf(spousePersonId);
                const spouseFatherId = spouseParents.find(id => nodeSex(id) === 'M');
                const spouseMotherId = spouseParents.find(id => nodeSex(id) === 'F');
                const spouseMotherChildren = spouseMotherId === undefined ? [] : childrenOf(spouseMotherId)
                    .filter(id => id !== spousePersonId)
                    .map(id => buildPersonNode(id));
                return buildPersonNode(spousePersonId, [
                    spouseFatherId === undefined ? null : buildPersonNode(spouseFatherId),
                    spouseMotherId === undefined ? null : buildPersonNode(spouseMotherId, spouseMotherChildren)
                ]);
            };

            const treeData = [buildPersonNode(mainId, [
                buildParentBranch(fatherId),
                buildParentBranch(motherId),
                buildSpouseBranch(spouseId)
            ])].filter(Boolean);

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
            chart.setOption(chartOption);
        })();
    </script>
<?php } ?>

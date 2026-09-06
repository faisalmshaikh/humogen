<?php

$graphJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
    .close-relatives-toolbar { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .close-relatives-viewport { height:720px; overflow:auto; border:1px solid #ced4da; background:#fff; touch-action:none; }
    .close-relatives-canvas { position:relative; width:1800px; height:1200px; transform-origin:top left; }
    .close-relatives-chart { position:absolute; inset:0; width:1800px; height:1200px; }
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
    <label for="close-relatives-depth" class="ms-2 mb-0"><?= __('Tree depth'); ?>:</label>
    <select class="form-select form-select-sm w-auto" id="close-relatives-depth">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3" selected>3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="-1"><?= __('All'); ?></option>
    </select>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="close-relatives-center"><?= __('Center tree'); ?></button>
</div>
<p><?= __('Click a person to expand or collapse their relatives. Drag a name to move that node, or drag the background to pan.'); ?></p>
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
        </div>
    </div>
    <script src="assets/echarts/echarts.min.js"></script>
    <script type="application/json" id="close-relatives-data"><?= $graphJson; ?></script>
    <script>
        (() => {
            const data = JSON.parse(document.getElementById('close-relatives-data').textContent);
            const canvas = document.getElementById('close-relatives-canvas');
            const chartElement = document.getElementById('close-relatives-chart');
            const nodesById = new Map(data.nodes.map(node => [Number(node.id), node]));
            const mainNode = data.nodes.find(node => node.gedcom === data.main_person);
            const neighbors = new Map(data.nodes.map(node => [Number(node.id), []]));

            data.edges.filter(edge => edge.label !== 'Spouse').forEach(edge => {
                const from = Number(edge.from);
                const to = Number(edge.to);
                if (!neighbors.has(from) || !neighbors.has(to)) return;
                neighbors.get(from).push(to);
                neighbors.get(to).push(from);
            });

            const decodeHtml = value => {
                const element = document.createElement('textarea');
                element.innerHTML = value || '';
                return element.value;
            };
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
                    symbol: 'circle',
                    draggable: true,
                    itemStyle: {
                        color: nodeColor(node),
                        borderColor: node.gedcom === data.main_person ? '#0d6efd' : '#68727e',
                        borderWidth: node.gedcom === data.main_person ? 3 : 1
                    },
                    children
                };
            };

            const seen = new Set();
            const roots = [mainNode]
                .filter(Boolean)
                .map(node => buildBranch(Number(node.id), null, seen))
                .filter(Boolean);
            const treeData = [{ id: 'close-relatives-root', name: '', symbol: 'none', label: { show: false }, children: roots }];
            data.nodes.forEach(node => {
                if (!seen.has(Number(node.id))) {
                    const branch = buildBranch(Number(node.id), null, seen);
                    if (branch) treeData[0].children.push(branch);
                }
            });

            const personLabel = {
                position: 'left',
                verticalAlign: 'middle',
                align: 'right',
                fontSize: 12,
                formatter: params => params.data.name
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
                    expandAndCollapse: true,
                    initialTreeDepth: 3,
                    lineStyle: { color: '#59636e', width: 1.5 },
                    label: personLabel,
                    leaves: { label: { ...personLabel, position: 'right', align: 'left' } },
                    emphasis: { focus: 'ancestor' }
                }]
            };

            let selectedDepth = 3;
            const setTreeDepth = depth => {
                selectedDepth = depth;
                chart.setOption({
                    ...chartOption,
                    series: [{ ...chartOption.series[0], initialTreeDepth: depth }]
                }, true);
            };

            let zoom = 1;
            const applyZoom = nextZoom => {
                zoom = Math.max(.5, Math.min(1.75, nextZoom));
                canvas.style.transform = `scale(${zoom})`;
                canvas.parentElement.style.minHeight = `${1200 * zoom}px`;
                canvas.parentElement.style.minWidth = `${1800 * zoom}px`;
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
            window.addEventListener('resize', () => chart.resize());
            chart.setOption(chartOption);

            const installNodeDragging = () => {
                const series = chart.getModel().getSeriesByIndex(0);
                const chartData = series && series.getData();
                if (!chartData) return;
                chartData.eachItemGraphicEl(graphic => {
                    if (!graphic || graphic.__closeRelativesDragging) return;
                    graphic.__closeRelativesDragging = true;
                    graphic.draggable = true;
                    graphic.cursor = 'move';
                });
            };
            chart.on('finished', installNodeDragging);
            installNodeDragging();
        })();
    </script>
<?php } ?>

<?php

$graphJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
    .close-relatives-chart {
        min-height: 720px;
        overflow: hidden;
        border: 1px solid #ced4da;
        background: #fff;
        touch-action: none;
    }

    .close-relatives-chart svg {
        display: block;
        width: 100%;
        min-height: 720px;
        cursor: grab;
    }

    .close-relatives-chart svg.dragging {
        cursor: grabbing;
    }

    .close-relatives-edge {
        stroke: #59636e;
        stroke-width: 2;
    }

    .close-relatives-edge.spouse {
        stroke-dasharray: 7 6;
    }

    .close-relatives-edge-label {
        font-size: 14px;
        fill: #39424e;
        paint-order: stroke;
        stroke: #fff;
        stroke-width: 5px;
        stroke-linejoin: round;
    }

    .close-relatives-node rect {
        stroke: #68727e;
        stroke-width: 2;
        rx: 10;
    }

    .close-relatives-node.main rect {
        stroke: #0d6efd;
        stroke-width: 4;
    }

    .close-relatives-node text {
        pointer-events: none;
        text-anchor: middle;
        fill: #18212b;
        font-size: 15px;
    }

    .close-relatives-node .node-name {
        font-weight: 600;
    }

    .close-relatives-legend span {
        display: inline-block;
        padding: .25rem .6rem;
        margin-right: .5rem;
        border: 1px solid #adb5bd;
        border-radius: .25rem;
    }

    .close-relatives-legend .male { background: #d9efff; }
    .close-relatives-legend .female { background: #fff6c7; }
</style>

<h1 class="my-4"><?= __('Close Relatives'); ?></h1>
<p><?= __('Drag any person to reposition the chart. The relationship lines remain connected.'); ?></p>
<div class="close-relatives-legend mb-2">
    <span class="male"><?= __('Male'); ?></span>
    <span class="female"><?= __('Female'); ?></span>
</div>

<?php if (!$data['main_person']) { ?>
    <div class="alert alert-warning"><?= __('The requested person could not be found.'); ?></div>
<?php } else { ?>
    <div class="close-relatives-chart" aria-label="<?= __('Close relatives network chart'); ?>">
        <svg id="close-relatives-svg" viewBox="0 0 1400 900" role="img" aria-labelledby="close-relatives-title">
            <title id="close-relatives-title"><?= __('Close Relatives'); ?></title>
            <g id="close-relatives-edges"></g>
            <g id="close-relatives-nodes"></g>
        </svg>
    </div>
    <script type="application/json" id="close-relatives-data"><?= $graphJson; ?></script>
    <script>
        (() => {
            const data = JSON.parse(document.getElementById('close-relatives-data').textContent);
            const svg = document.getElementById('close-relatives-svg');
            const edgeLayer = document.getElementById('close-relatives-edges');
            const nodeLayer = document.getElementById('close-relatives-nodes');
            const nodeWidth = 180;
            const nodeHeight = 58;
            const positions = new Map();
            const elements = new Map();
            const center = { x: 700, y: 420 };

            const createSvg = (tag, attributes) => {
                const element = document.createElementNS('http://www.w3.org/2000/svg', tag);
                Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
                return element;
            };

            const nodeById = new Map(data.nodes.map(node => [Number(node.id), node]));
            const connected = new Map();
            data.nodes.forEach(node => connected.set(Number(node.id), []));
            data.edges.forEach(edge => {
                connected.get(Number(edge.from))?.push(Number(edge.to));
                connected.get(Number(edge.to))?.push(Number(edge.from));
            });

            const mainId = data.nodes.find(node => node.gedcom === data.main_person)?.id;
            positions.set(Number(mainId), center);
            const ordered = data.nodes.filter(node => Number(node.id) !== Number(mainId));
            ordered.forEach((node, index) => {
                const angle = (index / Math.max(ordered.length, 1)) * Math.PI * 2 - Math.PI / 2;
                const radius = 260 + (index % 3) * 95;
                positions.set(Number(node.id), {
                    x: center.x + Math.cos(angle) * radius,
                    y: center.y + Math.sin(angle) * radius
                });
            });

            const endpoint = (from, to) => {
                const dx = to.x - from.x;
                const dy = to.y - from.y;
                const scale = 1 / Math.max(Math.abs(dx) / (nodeWidth / 2), Math.abs(dy) / (nodeHeight / 2));
                return { x: from.x + dx * scale, y: from.y + dy * scale };
            };

            const renderEdges = () => {
                edgeLayer.replaceChildren();
                data.edges.forEach(edge => {
                    const from = positions.get(Number(edge.from));
                    const to = positions.get(Number(edge.to));
                    if (!from || !to) return;
                    const start = endpoint(from, to);
                    const end = endpoint(to, from);
                    const line = createSvg('line', {
                        x1: start.x, y1: start.y, x2: end.x, y2: end.y,
                        class: 'close-relatives-edge ' + (edge.style === 'dotted' ? 'spouse' : '')
                    });
                    edgeLayer.appendChild(line);
                    if (edge.label) {
                        const label = createSvg('text', {
                            x: (start.x + end.x) / 2,
                            y: (start.y + end.y) / 2 - 7,
                            class: 'close-relatives-edge-label'
                        });
                        label.textContent = edge.label;
                        edgeLayer.appendChild(label);
                    }
                });
            };

            const renderNodes = () => {
                nodeLayer.replaceChildren();
                data.nodes.forEach(node => {
                    const id = Number(node.id);
                    const position = positions.get(id);
                    const group = createSvg('g', {
                        class: 'close-relatives-node ' + (node.gedcom === data.main_person ? 'main' : ''),
                        transform: `translate(${position.x - nodeWidth / 2},${position.y - nodeHeight / 2})`,
                        tabindex: '0'
                    });
                    const fill = node.sex === 'M' ? '#d9efff' : node.sex === 'F' ? '#fff6c7' : '#eeeeee';
                    group.appendChild(createSvg('rect', { width: nodeWidth, height: nodeHeight, fill }));
                    const name = createSvg('text', { x: nodeWidth / 2, y: 25, class: 'node-name' });
                    name.textContent = node.name;
                    group.appendChild(name);
                    const gedcom = createSvg('text', { x: nodeWidth / 2, y: 45 });
                    gedcom.textContent = node.gedcom;
                    group.appendChild(gedcom);
                    group.addEventListener('pointerdown', event => {
                        event.preventDefault();
                        svg.classList.add('dragging');
                        group.setPointerCapture(event.pointerId);
                        const move = moveEvent => {
                            const point = svg.createSVGPoint();
                            point.x = moveEvent.clientX;
                            point.y = moveEvent.clientY;
                            const svgPoint = point.matrixTransform(svg.getScreenCTM().inverse());
                            positions.set(id, { x: svgPoint.x, y: svgPoint.y });
                            group.setAttribute('transform', `translate(${svgPoint.x - nodeWidth / 2},${svgPoint.y - nodeHeight / 2})`);
                            renderEdges();
                        };
                        const stop = () => {
                            svg.classList.remove('dragging');
                            group.removeEventListener('pointermove', move);
                            group.removeEventListener('pointerup', stop);
                            group.removeEventListener('pointercancel', stop);
                        };
                        group.addEventListener('pointermove', move);
                        group.addEventListener('pointerup', stop);
                        group.addEventListener('pointercancel', stop);
                    });
                    elements.set(id, group);
                    nodeLayer.appendChild(group);
                });
            };

            renderEdges();
            renderNodes();
        })();
    </script>
<?php } ?>

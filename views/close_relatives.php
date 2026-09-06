<?php

$graphJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
    .close-relatives-toolbar { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .close-relatives-viewport { height:720px; overflow:auto; border:1px solid #ced4da; background:#fff; touch-action:none; }
    .close-relatives-canvas { position:relative; width:1800px; height:1200px; transform-origin:top left; }
    .close-relatives-canvas > svg,
    .close-relatives-nodes { position:absolute; inset:0; width:1800px; height:1200px; }
    .close-relatives-edge { stroke:#59636e; stroke-width:2; }
    .close-relatives-edge.spouse { stroke-dasharray:7 6; }
    .close-relatives-edge-label { font-size:14px; fill:#39424e; paint-order:stroke; stroke:#fff; stroke-width:5px; stroke-linejoin:round; }
    .close-relatives-node { position:absolute; width:210px; min-height:82px; padding:.35rem .5rem; border:2px solid #68727e; border-radius:10px; box-sizing:border-box; color:#18212b; font-size:12px; cursor:grab; user-select:none; box-shadow:0 2px 5px #0002; }
    .close-relatives-node.dragging { cursor:grabbing; z-index:10; }
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
<p><?= __('Drag any person to reposition the chart. The relationship lines remain connected.'); ?></p>
<div class="close-relatives-legend mb-2">
    <span class="male"><?= __('Male'); ?></span>
    <span class="female"><?= __('Female'); ?></span>
</div>

<?php if (!$data['main_person']) { ?>
    <div class="alert alert-warning"><?= __('The requested person could not be found.'); ?></div>
<?php } else { ?>
    <div class="close-relatives-viewport" aria-label="<?= __('Close relatives network chart'); ?>">
        <div class="close-relatives-canvas" id="close-relatives-canvas">
            <svg viewBox="0 0 1800 1200" role="img" aria-labelledby="close-relatives-title">
                <title id="close-relatives-title"><?= __('Close Relatives'); ?></title>
                <g id="close-relatives-edges"></g>
            </svg>
            <div class="close-relatives-nodes" id="close-relatives-nodes"></div>
        </div>
    </div>
    <script type="application/json" id="close-relatives-data"><?= $graphJson; ?></script>
    <script>
        (() => {
            const data = JSON.parse(document.getElementById('close-relatives-data').textContent);
            const canvas = document.getElementById('close-relatives-canvas');
            const edgeLayer = document.getElementById('close-relatives-edges');
            const nodeLayer = document.getElementById('close-relatives-nodes');
            const cardWidth = 210;
            const cardHeight = 82;
            const positions = new Map();
            let zoom = 1;

            const createSvg = (tag, attributes) => {
                const element = document.createElementNS('http://www.w3.org/2000/svg', tag);
                Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
                return element;
            };

            const mainId = data.nodes.find(node => node.gedcom === data.main_person)?.id;
            const spouseIndex = data.edges.findIndex(edge => edge.label === 'Spouse');
            const spouseId = spouseIndex >= 0 ? Number(data.edges[spouseIndex].to) : null;
            const primaryIds = new Set([Number(mainId), spouseId].filter(id => id !== null && !Number.isNaN(id)));
            const center = { x: 900, y: 600 };
            positions.set(Number(mainId), { x: 790, y: center.y });
            if (spouseId !== null) positions.set(spouseId, { x: 1010, y: center.y });

            // Use the graph's parent-child edges as a lightweight layout tree. Each
            // newly reached family member is placed around the person it is reached
            // from, which keeps children clustered around their parent instead of in
            // an unrelated grid.
            const neighbors = new Map();
            data.nodes.forEach(node => neighbors.set(Number(node.id), []));
            data.edges.filter(edge => edge.label !== 'Spouse').forEach(edge => {
                const from = Number(edge.from);
                const to = Number(edge.to);
                if (!neighbors.has(from) || !neighbors.has(to)) return;
                neighbors.get(from).push(to);
                neighbors.get(to).push(from);
            });

            const occupied = () => [...positions.values()];
            const collides = point => occupied().some(other =>
                Math.abs(point.x - other.x) < cardWidth + 24 && Math.abs(point.y - other.y) < cardHeight + 24
            );
            const placeAround = (parentId, ids, depth) => {
                const parent = positions.get(parentId);
                if (!parent || !ids.length) return;
                const direction = parentId === Number(mainId) || parentId === spouseId
                    ? -Math.PI / 2
                    : Math.atan2(parent.y - center.y, parent.x - center.x);
                const radius = 210 + Math.min(depth, 5) * 42;
                const spread = Math.min(Math.PI * 1.8, Math.max(Math.PI / 2, ids.length * .65));
                const start = direction - spread / 2;
                ids.forEach((id, index) => {
                    let angle = start + (ids.length === 1 ? spread / 2 : index * spread / (ids.length - 1));
                    let point = { x: parent.x + Math.cos(angle) * radius, y: parent.y + Math.sin(angle) * radius };
                    let attempts = 0;
                    while (collides(point) && attempts < 40) {
                        angle += .18;
                        point = { x: parent.x + Math.cos(angle) * radius, y: parent.y + Math.sin(angle) * radius };
                        attempts++;
                    }
                    positions.set(id, point);
                });
            };

            const queue = [...primaryIds];
            let depth = 1;
            while (queue.length) {
                const currentLevel = queue.splice(0, queue.length);
                currentLevel.forEach(parentId => {
                    const next = [...new Set(neighbors.get(parentId) || [])]
                        .filter(id => !positions.has(id));
                    placeAround(parentId, next, depth);
                    next.forEach(id => {
                        queue.push(id);
                    });
                });
                depth++;
            }

            // Keep disconnected or multiply-connected nodes visible as a last resort.
            data.nodes.forEach((node, index) => {
                const id = Number(node.id);
                if (!positions.has(id)) {
                    positions.set(id, { x: 160 + (index % 6) * 300, y: 120 + Math.floor(index / 6) * 190 });
                }
            });

            const endpoint = (from, to) => {
                const dx = to.x - from.x;
                const dy = to.y - from.y;
                const scale = 1 / Math.max(Math.abs(dx) / (cardWidth / 2), Math.abs(dy) / (cardHeight / 2));
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
                    edgeLayer.appendChild(createSvg('line', {
                        x1:start.x, y1:start.y, x2:end.x, y2:end.y,
                        class:'close-relatives-edge ' + (edge.style === 'dotted' ? 'spouse' : '')
                    }));
                    if (edge.label) {
                        const label = createSvg('text', {
                            x:(start.x + end.x) / 2, y:(start.y + end.y) / 2 - 7,
                            class:'close-relatives-edge-label'
                        });
                        label.textContent = edge.label;
                        edgeLayer.appendChild(label);
                    }
                });
            };

            const renderNodes = () => {
                nodeLayer.replaceChildren();
                const showOtherContacts = document.getElementById('close-relatives-show-contacts').checked;
                data.nodes.forEach(node => {
                    const id = Number(node.id);
                    const position = positions.get(id);
                    const showContacts = primaryIds.has(id) || showOtherContacts;
                    const card = document.createElement('div');
                    const sexClass = node.sex === 'M' ? 'male' : node.sex === 'F' ? 'female' : 'unknown';
                    card.className = 'close-relatives-node ' + sexClass + (node.gedcom === data.main_person ? ' main' : '');
                    card.style.left = `${position.x - cardWidth / 2}px`;
                    card.style.top = `${position.y - cardHeight / 2}px`;
                    card.dataset.nodeId = id;
                    card.innerHTML = `<span class="node-popup">${node.popup}</span><a class="node-name" href="${node.family_url}">${node.name}</a>`
                        + (showContacts && node.phone ? `<span class="node-contact"><strong><?= __('Phone'); ?>:</strong> ${node.phone}</span>` : '')
                        + (showContacts && node.address ? `<span class="node-contact"><strong><?= __('Address'); ?>:</strong> ${node.address}</span>` : '')

                    card.addEventListener('pointerdown', event => {
                        if (event.target.closest('a, button, input')) return;
                        event.preventDefault();
                        card.classList.add('dragging');
                        card.setPointerCapture(event.pointerId);
                        const move = moveEvent => {
                            const rect = canvas.getBoundingClientRect();
                            const x = (moveEvent.clientX - rect.left) / zoom;
                            const y = (moveEvent.clientY - rect.top) / zoom;
                            positions.set(id, { x, y });
                            card.style.left = `${x - cardWidth / 2}px`;
                            card.style.top = `${y - cardHeight / 2}px`;
                            renderEdges();
                        };
                        const stop = () => {
                            card.classList.remove('dragging');
                            card.removeEventListener('pointermove', move);
                            card.removeEventListener('pointerup', stop);
                            card.removeEventListener('pointercancel', stop);
                        };
                        card.addEventListener('pointermove', move);
                        card.addEventListener('pointerup', stop);
                        card.addEventListener('pointercancel', stop);
                    });
                    nodeLayer.appendChild(card);
                });
            };

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
            document.getElementById('close-relatives-show-contacts').addEventListener('change', renderNodes);
            renderEdges();
            renderNodes();
        })();
    </script>
<?php } ?>

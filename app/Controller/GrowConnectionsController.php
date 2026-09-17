<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\GrowConnectionsModel;

class GrowConnectionsController
{
    private const HTML_DIRECTORY = '/home/khandesh21at/public_html';

    public function __construct(private array $config) {}

    public function list(?string $sourceGedcom = null, string $sortOrder = 'asc'): array
    {
        if (($this->config['user']['group_living_place'] ?? 'n') !== 'j') {
            http_response_code(403);
            exit(__('You are not authorised to view connection growth data.'));
        }
        $data = (new GrowConnectionsModel($this->config))->getData($sourceGedcom, $sortOrder);
        $data['title'] = __('Grow Connections');
        return $data;
    }

    public function exportHtml(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $requestToken = $_SERVER['HTTP_X_GROW_CONNECTIONS_HTML_TOKEN'] ?? '';
            $payload = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) {
                throw new \RuntimeException('Invalid Grow Connections export data.');
            }

            $filename = $payload['filename'] ?? '';
            $isExistingExport = is_string($filename) && preg_match('/^grow_connections_[a-f0-9]{32}\.html$/', $filename);
            $exportTokens = $_SESSION['grow_connections_html_export_tokens'] ?? [];
            if (!is_array($exportTokens)) {
                $exportTokens = [];
            }
            $_SESSION['grow_connections_html_export_tokens'] = $exportTokens;
            $sessionToken = $isExistingExport
                ? ($exportTokens[$filename] ?? ($_SESSION['grow_connections_html_token'] ?? ''))
                : ($_SESSION['grow_connections_html_token'] ?? '');
            if (!$requestToken || !$sessionToken || !hash_equals($sessionToken, $requestToken)) {
                throw new \RuntimeException('Invalid Grow Connections export request.');
            }
            $tableHtml = $payload['tableHtml'] ?? '';
            if (!is_string($tableHtml) || $tableHtml === '' || strlen($tableHtml) > 2000000) {
                throw new \RuntimeException('Invalid Grow Connections table.');
            }
            $tableHtml = $this->sanitizeTable($tableHtml);
            if (!$isExistingExport) {
                $filename = 'grow_connections_' . bin2hex(random_bytes(16)) . '.html';
            }
            $_SESSION['grow_connections_html_export_tokens'][$filename] = $requestToken;
            $filePath = self::HTML_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
            $contents = $this->buildExportDocument($tableHtml, $filename);
            if (file_put_contents($filePath, $contents, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to save the Grow Connections page.');
            }
            echo json_encode(['success' => true, 'url' => $this->getPublicExportUrl($filename)]);
        } catch (\Throwable $exception) {
            error_log('Grow Connections HTML export failed: ' . $exception->getMessage());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $exception instanceof \RuntimeException ? $exception->getMessage() : 'Unable to generate the Grow Connections page.']);
        }
    }

    private function buildExportDocument(string $tableHtml, string $filename): string
    {
        $endpoint = $this->getExportEndpoint();
        $token = $_SESSION['grow_connections_html_token'] ?? '';

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Grow Connections</title><style>'
            . 'body{font-family:Arial,sans-serif;margin:1rem}.table{border-collapse:collapse;width:100%}'
            . '.table th,.table td{border:1px solid #ccc;padding:.35rem;text-align:left}'
            . '.grow-connections-editable{background:#fffbe6;outline:1px dashed #999}'
            . '.grow-connections-toolbar{display:flex;justify-content:flex-end;gap:.5rem;margin-bottom:.5rem}'
            . '.grow-connections-edit-button{border:1px solid #999;background:#f8f9fa;color:#555;cursor:pointer;font-size:.9rem;line-height:1;padding:.3rem .45rem}'
            . '.grow-connections-edit-button:hover{color:#000;background:#e9ecef}'
            . '#grow-connections-export-status{margin-left:.75rem}'
            . '</style></head><body>'
            . '<div class="grow-connections-toolbar"><button type="button" class="grow-connections-edit-button" id="grow-connections-export-edit" title="Edit table" aria-label="Edit table">✎</button>'
            . '<button type="button" id="grow-connections-export-save">Save</button><span id="grow-connections-export-status" role="status"></span></div>'
            . $tableHtml
            . '<script>'
            . 'const growConnectionsExportEndpoint=' . json_encode($endpoint, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'const growConnectionsExportToken=' . json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'const growConnectionsExportFilename=' . json_encode($filename, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'const growConnectionsExportTable=document.getElementById("grow-connections-table");'
            . 'const growConnectionsExportStatus=document.getElementById("grow-connections-export-status");'
            . 'const growConnectionsEditableColumns=[2,3,4,5];'
            . 'function enableGrowConnectionsCellEditing(cell){cell.contentEditable="true";cell.classList.add("grow-connections-editable");cell.focus();}'
            . 'function enableGrowConnectionsTableEditing(){growConnectionsExportTable.querySelectorAll("tbody tr").forEach(row=>row.querySelectorAll("td").forEach((cell,index)=>{if(growConnectionsEditableColumns.includes(index)){enableGrowConnectionsCellEditing(cell);}}));}'
            . 'document.getElementById("grow-connections-export-edit").addEventListener("click",enableGrowConnectionsTableEditing);'
            . 'document.getElementById("grow-connections-export-save").addEventListener("click",()=>{'
            . 'growConnectionsExportStatus.textContent="Saving changes...";'
            . 'fetch(growConnectionsExportEndpoint,{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-Grow-Connections-HTML-Token":growConnectionsExportToken},credentials:"same-origin",body:JSON.stringify({filename:growConnectionsExportFilename,tableHtml:growConnectionsExportTable.outerHTML})})'
            . '.then(response=>response.text().then(body=>{let result;try{result=JSON.parse(body);}catch(error){throw new Error("The server returned an invalid response.");}if(!response.ok||!result.success){throw new Error(result.message||"Unable to save changes.");}return result;}))'
            . '.then(()=>{growConnectionsExportStatus.textContent="Changes saved.";})'
            . '.catch(error=>{growConnectionsExportStatus.textContent=error.message||"Unable to save changes.";});});'
            . '</script></body></html>';
    }

    private function getExportEndpoint(): string
    {
        $uriPath = rtrim((string) ($this->config['uri_path'] ?? ''), '/') . '/';
        return $uriPath . 'index.php?page=grow_connections&grow_html=1';
    }

    private function sanitizeTable(string $tableHtml): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="UTF-8"><div id="grow-export-root">' . $tableHtml . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $document->getElementById('grow-export-root');
        $table = $root ? $root->getElementsByTagName('table')->item(0) : null;
        if (!$table instanceof \DOMElement) throw new \RuntimeException('The Grow Connections table is not available.');
        $allowedTags = ['table', 'thead', 'tbody', 'tr', 'th', 'td'];
        $allowedAttributes = ['id', 'class', 'colspan', 'rowspan', 'style'];
        $sanitize = function (\DOMNode $node) use (&$sanitize, $allowedTags, $allowedAttributes): void {
            for ($child = $node->firstChild; $child; ) {
                $next = $child->nextSibling;
                if ($child instanceof \DOMElement) {
                    if (!in_array(strtolower($child->tagName), $allowedTags, true)) {
                        while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                        $node->removeChild($child);
                    } else {
                        for ($i = $child->attributes->length - 1; $i >= 0; $i--) {
                            $attribute = $child->attributes->item($i);
                            if (!in_array(strtolower($attribute->name), $allowedAttributes, true)) {
                                $child->removeAttributeNode($attribute);
                            }
                        }
                        if ($child->hasAttribute('style')) {
                            $style = $child->getAttribute('style');
                            preg_match_all('/(?:padding-inline-start\s*:\s*\d+px|background-color\s*:\s*#[0-9a-f]{3,8})/i', $style, $matches);
                            $child->setAttribute('style', implode('; ', $matches[0]));
                        }
                        $sanitize($child);
                    }
                }
                $child = $next;
            }
        };
        $sanitize($table);
        return $document->saveHTML($table);
    }

    private function getPublicExportUrl(string $filename): string
    {
        $uriPath = $this->config['uri_path'] ?? '';
        $parsedPath = parse_url($uriPath, PHP_URL_PATH);
        $appPath = rtrim(is_string($parsedPath) ? $parsedPath : '', '/');
        $publicPath = rtrim(str_replace('\\', '/', dirname($appPath)), '/');
        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        return $scheme . '://' . $host . ($publicPath ? $publicPath : '') . '/' . rawurlencode($filename);
    }
}

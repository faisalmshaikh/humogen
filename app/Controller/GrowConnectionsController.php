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
            $sessionToken = $_SESSION['grow_connections_html_token'] ?? '';
            if (!$requestToken || !$sessionToken || !hash_equals($sessionToken, $requestToken)) {
                throw new \RuntimeException('Invalid Grow Connections export request.');
            }
            $payload = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            $tableHtml = $payload['tableHtml'] ?? '';
            if (!is_string($tableHtml) || $tableHtml === '' || strlen($tableHtml) > 2000000) {
                throw new \RuntimeException('Invalid Grow Connections table.');
            }
            $tableHtml = $this->sanitizeTable($tableHtml);
            $filename = 'grow_connections_' . bin2hex(random_bytes(16)) . '.html';
            $filePath = self::HTML_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
            $contents = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Grow Connections</title><style>body{font-family:Arial,sans-serif;margin:1rem}.table{border-collapse:collapse;width:100%}.table th,.table td{border:1px solid #ccc;padding:.35rem;text-align:left}</style></head><body>' . $tableHtml . '</body></html>';
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

    private function sanitizeTable(string $tableHtml): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="UTF-8"><div id="grow-export-root">' . $tableHtml . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $document->getElementById('grow-export-root');
        $table = $root ? $root->getElementsByTagName('table')->item(0) : null;
        if (!$table instanceof \DOMElement) throw new \RuntimeException('The Grow Connections table is not available.');
        $allowedTags = ['table', 'thead', 'tbody', 'tr', 'th', 'td'];
        $sanitize = function (\DOMNode $node) use (&$sanitize, $allowedTags): void {
            for ($child = $node->firstChild; $child; ) {
                $next = $child->nextSibling;
                if ($child instanceof \DOMElement) {
                    if (!in_array(strtolower($child->tagName), $allowedTags, true)) {
                        while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                        $node->removeChild($child);
                    } else {
                        while ($child->attributes->length) $child->removeAttributeNode($child->attributes->item(0));
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

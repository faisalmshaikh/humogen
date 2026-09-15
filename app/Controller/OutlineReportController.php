<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\OutlineReportModel;

global $GOOGLE_SHEET_ID;

define("GOOGLE_SHEET_ID", $GOOGLE_SHEET_ID);

class OutlineReportController
{
    //private const GOOGLE_SHEET_ID = '1cWXGL0mCFcBtKpoY6S_TADk2mhtxF438WIXEIVMZTq0';

    private const GOOGLE_SERVICE_ACCOUNT_FILE = __DIR__ . '/../../../../service-account.json';
    private const OUTLINE_HTML_DIRECTORY = '/home/khandesh21at/public_html';

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getOutlineReport(): array
    {
        $OutlineReportModel = new OutlineReportModel($this->config);

        $family_id = $OutlineReportModel->getFamilyId();
        $main_person = $OutlineReportModel->getMainPerson();

        $source_presentation =  $OutlineReportModel->getSourcePresentation();
        $picture_presentation =  $OutlineReportModel->getPicturePresentation();
        $text_presentation =  $OutlineReportModel->getTextPresentation();
        $maps_presentation = $OutlineReportModel->getMapsPresentation();
        $number_roman = $OutlineReportModel->getNumberRoman();
        $number_generation = $OutlineReportModel->getNumberGeneration();
        $descendant_report = $OutlineReportModel->getDescendantReport();
        $descendant_header = $OutlineReportModel->getDescendantHeader('Outline report', $family_id, $main_person);

        $show_details = $OutlineReportModel->getShowDetails();
        $show_date = $OutlineReportModel->getShowDate();
        $dates_behind_names = $OutlineReportModel->getDatesBehindNames();
        $nr_generations = $OutlineReportModel->getNrGenerations();

        // *** Generate outline report HTML. First line starts recursive function. ***
        $OutlineReportModel->outline_report_html($family_id, $main_person, 0);
        $outline_report_html = $OutlineReportModel->getHtmlOutput();

        return array(
            "family_id" => $family_id,
            "main_person" => $main_person,
            "source_presentation" => $source_presentation,
            "picture_presentation" => $picture_presentation,
            "text_presentation" => $text_presentation,
            "maps_presentation" => $maps_presentation,
            "number_roman" => $number_roman,
            "number_generation" => $number_generation,
            "descendant_report" => $descendant_report,
            "descendant_header" => $descendant_header,

            "show_details" => $show_details,
            "show_date" => $show_date,
            "dates_behind_names" => $dates_behind_names,
            "nr_generations" => $nr_generations,
            "outline_report_html" => $outline_report_html,

            "title" => __('Family')
        );
    }

    public function submitToGoogleSheet(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $requestToken = $_SERVER['HTTP_X_OUTLINE_SHEET_TOKEN'] ?? '';
            $sessionToken = $_SESSION['outline_sheet_token'] ?? '';
            if (!$requestToken || !$sessionToken || !hash_equals($sessionToken, $requestToken)) {
                throw new \RuntimeException('Invalid report submission request.');
            }

            $payload = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            $rows = $payload['rows'] ?? null;
            $mainPerson = $payload['mainPerson'] ?? '';
            $nrGenerations = $payload['nrGenerations'] ?? null;
            $sessionUserName = $_SESSION['user_name'] ?? '';
            if (!is_array($rows) || count($rows) === 0 || count($rows) > 5000) {
                throw new \RuntimeException('Invalid report data.');
            }
            if (!is_string($mainPerson) || !preg_match('/^[A-Za-z0-9_-]+$/', $mainPerson)) {
                throw new \RuntimeException('Invalid main person.');
            }
            if (!is_string($sessionUserName) || !preg_match('/^[A-Za-z0-9_-]+$/', $sessionUserName)) {
                throw new \RuntimeException('Invalid session user.');
            }
            if (filter_var($nrGenerations, FILTER_VALIDATE_INT) === false || (int) $nrGenerations < 1) {
                throw new \RuntimeException('Invalid number of generations.');
            }
            $worksheetTitle = $sessionUserName . '_' . $mainPerson . '_' . (int) $nrGenerations;

            $values = [];
            foreach ($rows as $row) {
                if (!is_array($row) || count($row) !== 6) {
                    throw new \RuntimeException('Invalid report row.');
                }

                $values[] = array_map(static function ($value): string {
                    if (!is_scalar($value)) {
                        throw new \RuntimeException('Invalid report cell.');
                    }
                    $text = (string) $value;
                    return function_exists('mb_substr') ? mb_substr($text, 0, 2000) : substr($text, 0, 2000);
                }, array_values($row));
            }

            $credentialsFile = getenv('HUMOGEN_GOOGLE_SERVICE_ACCOUNT') ?: self::GOOGLE_SERVICE_ACCOUNT_FILE;
            if (!is_readable($credentialsFile)) {
                throw new \RuntimeException('Google service-account credentials are unavailable.');
            }

            $client = new \Google\Client();
            $client->setApplicationName('HuMo-genealogy Outline Report');
            $client->setAuthConfig($credentialsFile);
            $client->setScopes([\Google\Service\Sheets::SPREADSHEETS]);

            $sheets = new \Google\Service\Sheets($client);
            $spreadsheet = $sheets->spreadsheets->get(GOOGLE_SHEET_ID, [
                'fields' => 'sheets(properties(sheetId,title))'
            ]);
            $worksheetExists = false;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $worksheetTitle) {
                    $worksheetExists = true;
                    break;
                }
            }

            if (!$worksheetExists) {
                $addSheetRequest = new \Google\Service\Sheets\Request([
                    'addSheet' => new \Google\Service\Sheets\AddSheetRequest([
                        'properties' => new \Google\Service\Sheets\SheetProperties([
                            'title' => $worksheetTitle
                        ])
                    ])
                ]);
                $sheets->spreadsheets->batchUpdate(
                    GOOGLE_SHEET_ID,
                    new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                        'requests' => [$addSheetRequest]
                    ])
                );
            }

            $worksheetRange = "'" . str_replace("'", "''", $worksheetTitle) . "'!A:Z";
            $sheets->spreadsheets_values->clear(
                GOOGLE_SHEET_ID,
                $worksheetRange,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
            $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
            $sheets->spreadsheets_values->update(
                GOOGLE_SHEET_ID,
                "'" . str_replace("'", "''", $worksheetTitle) . "'!A1",
                $body,
                ['valueInputOption' => 'USER_ENTERED']
            );

            echo json_encode(['success' => true, 'worksheet' => $worksheetTitle]);
        } catch (\Throwable $exception) {
            error_log('Outline report Google Sheets submission failed');
            error_log('Exception: ' . get_class($exception));
            error_log('Message: ' . $exception->getMessage());
            error_log('File: ' . $exception->getFile());
            error_log('Line: ' . $exception->getLine());

            if ($exception instanceof \Google\Service\Exception) {
                error_log('Google API errors: ' . json_encode($exception->getErrors()));
                error_log('HTTP code: ' . $exception->getCode());
            }

            http_response_code(400);
            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Google Sheets API request failed. Verify that the service account has Editor access to the spreadsheet and that the Google Sheets API is enabled.';
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        }
    }

    public function exportHtmlTable(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $requestToken = $_SERVER['HTTP_X_OUTLINE_HTML_TOKEN'] ?? '';
            $sessionToken = $_SESSION['outline_html_token'] ?? '';
            if (!$requestToken || !$sessionToken || !hash_equals($sessionToken, $requestToken)) {
                throw new \RuntimeException('Invalid report export request.');
            }

            $payload = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) {
                throw new \RuntimeException('Invalid report data.');
            }
            $tableHtml = $payload['tableHtml'] ?? '';
            if (!is_string($tableHtml) || $tableHtml === '' || strlen($tableHtml) > 2000000) {
                throw new \RuntimeException('Invalid report table.');
            }

            $tableHtml = $this->sanitizeOutlineTable($tableHtml);
            $filename = $payload['filename'] ?? '';
            if (!is_string($filename) || !preg_match('/^outline_report_[a-f0-9]{32}\.html$/', $filename)) {
                $filename = 'outline_report_' . bin2hex(random_bytes(16)) . '.html';
            }
            $filePath = self::OUTLINE_HTML_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
            $fileContents = $this->buildOutlineExportDocument($tableHtml, $filename);
            if (file_put_contents($filePath, $fileContents, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to save the report file.');
            }

            echo json_encode([
                'success' => true,
                'url' => $this->getPublicExportUrl($filename)
            ]);
        } catch (\Throwable $exception) {
            error_log('Outline report HTML export failed: ' . $exception->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $exception instanceof \RuntimeException
                    ? $exception->getMessage()
                    : 'Unable to generate the HTML report.'
            ]);
        }
    }

    private function sanitizeOutlineTable(string $tableHtml): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="UTF-8"><div id="outline-export-root">' . $tableHtml . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $document->getElementById('outline-export-root');
        $table = $root ? $root->getElementsByTagName('table')->item(0) : null;
        if (!$table instanceof \DOMElement || strtolower($table->tagName) !== 'table') {
            throw new \RuntimeException('The report table is not available.');
        }

        $allowedTags = ['table', 'thead', 'tbody', 'tr', 'th', 'td', 'span'];
        $allowedAttributes = ['id', 'class', 'colspan', 'rowspan', 'style'];
        $sanitize = function (\DOMNode $node) use (&$sanitize, $allowedTags, $allowedAttributes): void {
            for ($child = $node->firstChild; $child; ) {
                $next = $child->nextSibling;
                if ($child instanceof \DOMElement) {
                    if (!in_array(strtolower($child->tagName), $allowedTags, true)) {
                        if (strtolower($child->tagName) === 'a') {
                            while ($child->firstChild) {
                                $node->insertBefore($child->firstChild, $child);
                            }
                        }
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

    private function buildOutlineExportDocument(string $tableHtml, string $filename): string
    {
        $endpoint = $this->getOutlineExportEndpoint();
        $token = $_SESSION['outline_html_token'] ?? '';

        return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<title>Outline Report</title><style>'
            . 'body{font-family:Arial,sans-serif;margin:1rem}.table{border-collapse:collapse;width:100%}'
            . '.table th,.table td{border:1px solid #ccc;padding:.35rem;text-align:left}'
            . '.outline-report-editable{background:#fffbe6;outline:1px dashed #999}'
            . '.outline-cell-edit{float:right;border:0;background:transparent;color:#555;cursor:pointer;font-size:.8rem;line-height:1;padding:0 .15rem}'
            . '.outline-cell-edit:hover{color:#000}'
            . '#outline-export-status{margin-left:.75rem}'
            . '</style></head><body>'
            . '<button type="button" id="outline-export-submit">Submit Changes</button>'
            . '<span id="outline-export-status" role="status"></span>'
            . $tableHtml
            . '<script>'
            . 'const outlineExportEndpoint=' . json_encode($endpoint, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'const outlineExportToken=' . json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'const outlineExportFilename=' . json_encode($filename, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'const outlineExportTable=document.getElementById("outline-report-table");'
            . 'const outlineExportStatus=document.getElementById("outline-export-status");'
            . 'const outlineExportEditableColumns=[2,3,4,5];'
            . 'function enableOutlineCellEditing(cell){cell.contentEditable="true";cell.classList.add("outline-report-editable");cell.focus();}'
            . 'function addOutlineCellEditButton(cell){const button=document.createElement("button");button.type="button";button.className="outline-cell-edit";button.title="Edit cell";button.setAttribute("aria-label","Edit cell");button.textContent="✎";button.contentEditable="false";button.addEventListener("click",event=>{event.preventDefault();event.stopPropagation();enableOutlineCellEditing(cell);});cell.appendChild(button);}'
            . 'outlineExportTable.querySelectorAll("tbody tr").forEach(row=>row.querySelectorAll("td").forEach((cell,index)=>{'
            . 'if(outlineExportEditableColumns.includes(index)){'
            . 'addOutlineCellEditButton(cell);cell.addEventListener("dblclick",()=>enableOutlineCellEditing(cell));'
            . 'let longPressTimer=null,startX=0,startY=0;cell.addEventListener("touchstart",event=>{const touch=event.touches[0];startX=touch.clientX;startY=touch.clientY;longPressTimer=setTimeout(()=>enableOutlineCellEditing(cell),600);},{passive:true});'
            . 'cell.addEventListener("touchmove",event=>{const touch=event.touches[0];if(Math.abs(touch.clientX-startX)>10||Math.abs(touch.clientY-startY)>10){clearTimeout(longPressTimer);longPressTimer=null;}},{passive:true});'
            . 'cell.addEventListener("touchend",()=>{clearTimeout(longPressTimer);longPressTimer=null;},{passive:true});'
            . 'cell.addEventListener("touchcancel",()=>{clearTimeout(longPressTimer);longPressTimer=null;},{passive:true});'
            . '}}));'
            . 'document.getElementById("outline-export-submit").addEventListener("click",()=>{'
            . 'outlineExportStatus.textContent="Submitting changes...";'
            . 'fetch(outlineExportEndpoint,{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-Outline-HTML-Token":outlineExportToken},credentials:"same-origin",body:JSON.stringify({filename:outlineExportFilename,tableHtml:outlineExportTable.outerHTML})})'
            . '.then(response=>response.json()).then(result=>{outlineExportStatus.textContent=result.success?"Changes saved.":(result.message||"Unable to save changes.");})'
            . '.catch(()=>{outlineExportStatus.textContent="Unable to save changes.";});});'
            . '</script></body></html>';
    }

    private function getOutlineExportEndpoint(): string
    {
        $uriPath = rtrim((string) ($this->config['uri_path'] ?? ''), '/') . '/';
        return $uriPath . 'index.php?page=outline_report&outline_html=1';
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

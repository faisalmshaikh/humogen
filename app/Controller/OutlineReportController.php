<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\OutlineReportModel;

define("GOOGLE_SHEET_ID", $GOOGLE_SHEET_ID);

class OutlineReportController
{
    //private const GOOGLE_SHEET_ID = '1cWXGL0mCFcBtKpoY6S_TADk2mhtxF438WIXEIVMZTq0';

    private const GOOGLE_SERVICE_ACCOUNT_FILE = __DIR__ . '/../../../../service-account.json';

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
            error_log('Outline report Google Sheets submission failed: ' . $exception->getMessage());
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
}

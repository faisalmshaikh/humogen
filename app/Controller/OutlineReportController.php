<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\OutlineReportModel;

class OutlineReportController
{
    private const GOOGLE_SHEET_ID = '1cWXGL0mCFcBtKpoY6S_TADk2mhtxF438WIXEIVMZTq0';
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
            if (!is_array($rows) || count($rows) === 0 || count($rows) > 5000) {
                throw new \RuntimeException('Invalid report data.');
            }

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
            $sheets->spreadsheets_values->clear(
                self::GOOGLE_SHEET_ID,
                'A:Z',
                new \Google\Service\Sheets\ClearValuesRequest()
            );
            $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
            $sheets->spreadsheets_values->update(
                self::GOOGLE_SHEET_ID,
                'A1',
                $body,
                ['valueInputOption' => 'USER_ENTERED']
            );

            echo json_encode(['success' => true]);
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

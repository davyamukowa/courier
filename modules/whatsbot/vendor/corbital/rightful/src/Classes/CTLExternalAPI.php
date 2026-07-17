<?php

namespace Corbital\Rightful\Classes;

require_once __DIR__.'/../Config/Constants.php';
require_once __DIR__.'/../Config/Item.php';
require_once __DIR__.'/../Helpers/CtlUpdateLogHelper.php';

use Corbital\Rightful\Config\Constants;
use Corbital\Rightful\Helpers\IpHelper;
use Corbital\Rightful\Helpers\RequestHelper;
use ZipArchive;
use Carbon\Carbon;
use Exception;

/**
 * Class CTLExternalAPI.
 *
 * Handles license management, API communication, and software update routines.
 */
class CTLExternalAPI
{
    public readonly string $product_id;
    public readonly string $api_url;
    public readonly string $api_key;
    public readonly string $api_language;
    public readonly string $current_version;
    public readonly string $verify_type;
    public readonly string $root_path;
    public readonly string $modulePath;
    public readonly string $updateLogFilePath;
    public readonly string $support_url;
    public readonly string $renew_support_url;

    /**
     * Initializes server configuration, paths, and other parameters.
     */
    public function __construct()
    {
        // Set dynamic configuration values
        $configHandler = ConfigHandler::getInstance();
        $configHandler->set('root_path', TEMP_FOLDER.\DIRECTORY_SEPARATOR);

        // Set class properties using config values
        $this->product_id          = $configHandler->get('product_id') ?? '';
        $this->api_url             = rtrim(base64_decode($configHandler->get('api_url') ?? ''), '/');
        $this->current_version     = $configHandler->get('current_version') ?? '';
        $this->verify_type         = $configHandler->get('verify_type') ?? '';
        $this->root_path           = $configHandler->get('root_path') ?? '';
        $this->modulePath          = APP_MODULES_PATH;
        $this->updateLogFilePath   = APP_MODULES_PATH.($configHandler->get('module_name') ?? 'whatsbot').'/update.log';
        $this->support_url         = $configHandler->get('support_url') ?? '';
        $this->renew_support_url   = $configHandler->get('renew_support_url') ?? '';
    }

    public function getHeader()
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * Retrieves the current software version.
     *
     * @return string current version
     */
    public function getCurrentVersion()
    {
        return $this->current_version;
    }

    /**
     * Checks for updates on the server.
     *
     * @return array update check response
     */
    public function checkUpdate($token)
    {
        return [];
    }

    /**
     * Downloads and extracts an update, including optional SQL updates.
     *
     * @param string      $update_id      update identifier
     * @param bool        $needsSqlUpdate if true, includes an SQL update
     * @param string      $version        software version
     * @param string|null $license        optional license code
     * @param string|null $client         optional client identifier
     */
    public function downloadUpdate(
        string $update_id,
        bool $needsSqlUpdate,
        string $version,
        ?string $license = null,
        ?string $client = null,
        ?string $token = null
    ) {
        return ['status' => true, 'message' => 'Auto-update is disabled.'];
        ob_implicit_flush(true);

        update_log($version, $this->updateLogFilePath);
        update_log(Constants::PREPARING_MAIN_DOWNLOAD->value, $this->updateLogFilePath);

        $destinationPath = $this->root_path."\update_main_".str_replace(' . ', '_', $version).'.zip';
        $response        = $this->executeDownload('main', $update_id, $destinationPath, $license, $client, $token);
        if (!empty($response) && empty($response['success'])) {
            update_log($response['message'], $this->updateLogFilePath);
            update_log('', $this->updateLogFilePath, true);

            return ['status' => true, 'message' => $response['message']];
        }

        if (!$this->extractZipFile($destinationPath, $this->modulePath)) {
            update_log(Constants::UPDATE_EXTRACTION_ERROR->value, $this->updateLogFilePath);
        } else {
            update_log(Constants::MAIN_UPDATE_DONE->value, $this->updateLogFilePath);
        }

        if ($needsSqlUpdate) {
            update_log(Constants::PREPARING_SQL_DOWNLOAD->value, $this->updateLogFilePath);

            $destinationSql = $this->root_path.'/update_sql_'.str_replace(' . ', '_', $version).'.zip';
            $this->executeDownload('sql', $update_id, $destinationSql, $license, $client, $token);

            $this->extractZipFile($destinationSql, $this->modulePath);

            update_log(Constants::SQL_UPDATE_DONE->value, $this->updateLogFilePath);

            if ($this->importSqlDatabase($this->modulePath)) {
                @unlink($destinationSql);
                update_log(Constants::SQL_IMPORT_SUCCESS->value, $this->updateLogFilePath);
            } else {
                update_log(Constants::SQL_IMPORT_FAILED->value, $this->updateLogFilePath);
            }
        } else {
            update_log(Constants::UPDATE_WITHOUT_SQL->value, $this->updateLogFilePath);
        }

        update_log('', $this->updateLogFilePath, true);
    }

    public function getPurchaseData($code, $username)
    {
        return ['status_code' => 200, 'body' => json_encode(['success' => true, 'data' => []])];
    }

    public function registerLicense($data)
    {
        return ['status_code' => 200, 'body' => json_encode(['success' => true, 'data' => null])];
    }

    public function validateLicense($token, $data)
    {
        return ['status_code' => 200, 'body' => json_encode(['success' => true])];
    }

    /**
     * Executes a download request and saves the file to a local destination.
     *
     * @param string      $type        type of update (main, sql)
     * @param string      $updateId    update identifier
     * @param string      $destination file path to save the download
     * @param string|null $license     license code if provided
     * @param string|null $client      client identifier if provided
     *
     * @throws \Exception if the download fails
     */
    private function executeDownload(string $type, string $updateId, string $destination, ?string $license, ?string $client, ?string $token)
    { 
        $url          = "{$this->api_url}/download-update/{$type}/{$updateId}";
        $header = array_merge([
            'Authorization' => 'Bearer '.$token,
        ], $this->getHeader());

        $response     = RequestHelper::executeAndVerifyResponse('POST', $url, [
            'license_code'     => $license,
            'client_name'      => $client,
            'activated_domain' => $this->getFullUrl(),
            'item_id'          => $this->product_id,
        ], $header);

        $responseBody = json_decode($response['body'], true);

        if (isset($responseBody['success']) && $responseBody['success'] != true) {
            $errorMessages = $responseBody['errors'] ?? [$responseBody['message'] ?? 'License validation failed'];

            if (is_array($errorMessages) && isset($errorMessages['license_code'])) {
                return [
                    'success' => $responseBody['success'],
                    'message' => $errorMessages['license_code'][0],
                ];
            }

            return [
                'success' => $responseBody['success'],
                'message' => $responseBody['message'],
            ];
        }
        
        if (!empty($response['body'])) {
            file_put_contents($destination, $response['body']);
        }
    }

    /**
     * Extracts a downloaded ZIP file to the specified destination.
     *
     * @param string $source          path to the ZIP file
     * @param string $destinationPath extraction directory
     *
     * @return bool true on success, false on failure
     */
    private function extractZipFile(string $source, string $destinationPath): bool
    {
        // Check if the source file exists and is readable
        if (!file_exists($source) || !is_readable($source)) {
            update_log('Source file does not exist or is not readable: '.$source, $this->updateLogFilePath);

            return false;
        }

        // Ensure the destination directory exists and is writable
        if (!is_dir($destinationPath) && !mkdir($destinationPath, 0755, true)) {
            update_log('Failed to create destination directory: '.$destinationPath, $this->updateLogFilePath);

            return false;
        }

        if (!is_writable($destinationPath)) {
            update_log('Destination directory is not writable: '.$destinationPath, $this->updateLogFilePath);

            return false;
        }

        // Initialize ZipArchive and attempt to open the zip file
        $zip = new \ZipArchive();
        if (true !== $zip->open($source)) {
            update_log('Failed to open zip file: '.$source, $this->updateLogFilePath);

            return false;
        }

        // Extract the zip file contents
        if (!$zip->extractTo($destinationPath)) {
            $zip->close();
            update_log('Failed to extract zip file: '.$source, $this->updateLogFilePath);

            return false;
        }

        // Clean up and delete the zip file
        $zip->close();
        if (!unlink($source)) {
            update_log('Failed to delete the zip file after extraction: '.$source, $this->updateLogFilePath);

            return false;
        }

        update_log('Successfully extracted and deleted the zip file: '.$source, $this->updateLogFilePath);

        return true;
    }

    /**
     * Imports SQL from a file into the database.
     *
     * @param string $sqlFile path to the SQL file
     *
     * @return bool true on successful import, false on failure
     */
    private function importSqlDatabase(string $extractPath): bool
    {
        $sqlFile = null;

        // Search for an SQL file in the extracted directory
        foreach (scandir($extractPath) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $sqlFile = $extractPath.'/'.$file;
                break;
            }
        }

        if (! $sqlFile) {
            throw new \Exception('No SQL file found in extracted update.');
        }

        $db = get_instance()->db;  // Store database instance to avoid repetitive calls
        $db->trans_start();  // Start the transaction
        try {
            // Parse SQL file into individual statements
            $parser        = new SqlScriptParser();
            $sqlStatements = $parser->parse($sqlFile);

            foreach ($sqlStatements as $statement) {
                $distilled = $parser->removeComments($statement);

                // Execute query if the statement is not empty
                if (!empty($distilled) && !$db->query($distilled)) {
                    throw new Exception('SQL execution failed: '.$db->error().' | Query: '.$distilled);
                }
            }

            $db->trans_complete();  // Complete the transaction

            // Check if the transaction was successful
            if (false === $db->trans_status()) {
                $db->trans_rollback();  // Rollback if failed
                @unlink($sqlFile);  // Delete the SQL file after successful import
                return false;
            }

            $db->trans_commit();  // Commit the transaction
            @unlink($sqlFile);  // Delete the SQL file after successful import

            return true;
        } catch (Exception $e) {
            $db->trans_rollback();  // Rollback transaction on error
            update_log('SQL Error occurred: '.$e->getMessage(), $this->updateLogFilePath);
            @unlink($sqlFile);  // Delete the SQL file after successful import
            return false;
        }
    }

    /**
     * Constructs the full URL for the current server.
     *
     * @return string full URL
     */
    private function getFullUrl()
    {
        return preg_replace('/admin.*$/', 'admin', current_full_url());
    }


    public function checkSupportExpiryStatus($supportedUntil='')
    {  
        if ($supportedUntil) {
            $supportedDate = Carbon::parse($supportedUntil)->addDay(); // Add 1-day grace period
            $currentDate = Carbon::now();

            if ($currentDate->greaterThanOrEqualTo($supportedDate)) {
                return [
                    'success'    => false,
                    'type'       => 'danger',
                    'message'    => Constants::SUPPORT_EXPIRY_MESSAGE->value . " <a href='{$this->renew_support_url}' class='text-muted tw-font-semibold' target='_blank'><i class='fa-solid fa-up-right-from-square mright5 fa-fade'></i>Renew Support</a>",
                    'time_diff'  => '',
                    'support_url'=> base64_decode($this->support_url),
                ];
            }

            $timeDiff = $currentDate->diffForHumans($supportedDate, ['parts' => 1, 'join' => true, 'syntax' => Carbon::DIFF_ABSOLUTE]);

            return [
                'success'    => true,
                'type'       => 'success',
                'message'    => "Support will expire on <span class='text-capitalize tw-font-semibold'>{$supportedDate->format('d M, Y')} ({$timeDiff}).</span>",
                'time_diff'  => "{$timeDiff} left",
                'support_url'=> base64_decode($this->support_url),
            ];
        }

        return false;
    }

    public function getVersionLogs($item_id)
    {
        return [];
    }

    public function checkLicenseStatus($data)
    {
        return [];
    }
}

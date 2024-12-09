<?php

declare(strict_types=1);

namespace OCA\Downloader\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\ILogger;

use OCP\Files\IRootFolder;
use OCP\IUserSession;

opcache_invalidate(__FILE__, true);

class ApiController extends OCSController {

  public function __construct(string $appName, IRequest $request, ILogger $logger) {
    parent::__construct($appName, $request);
    $this->logger = $logger;
  }


  #[NoAdminRequired]
  #[NoCSRFRequired]
  #[ApiRoute(verb: 'POST', url: '/dlfile')]
  public function index(): DataResponse {
    // E.g. https://nextcloud.example.com/ocs/v2.php/apps/downloader/dlfile?format=json
    // $this->logger->debug('API route accessed', ['app' => 'downloader']);

    $request = $this->request;
    $mediaUrl = $request->getParam('mediaUrl');
    $fileName = $request->getParam('fileName');
    $convertToAudio = $request->getParam('fileFormat');


    if (empty($mediaUrl) || empty($fileName)) {
        return new DataResponse(['message' => 'Media URL and File Name cannot be empty.', 400, ['Content-Type' => 'application/json']]);
    }

    // Validate the Media URL
    if (!filter_var($mediaUrl, FILTER_VALIDATE_URL)) {
        return new DataResponse(['message' => 'Invalid URL. Please provide a valid URL.'], 400, ['Content-Type' => 'application/json']);
    }
    $parsedUrl = parse_url($mediaUrl);
    if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {
        return new DataResponse(['message' => 'The URL must use either HTTP or HTTPS.'], 400, ['Content-Type' => 'application/json']);
    }

    // Validate the File name
    if (!preg_match('/^[a-zA-Z0-9-_\.]+$/', $fileName)) {
        return new DataResponse(['message' => 'File name contains invalid characters.'], 400, ['Content-Type' => 'application/json']);
    }

    $formatParams = '';
    if ('default' !== $convertToAudio['id']) {
       $formatParams = ' --extract-audio --audio-format';
    }
    if ('opus' == $convertToAudio['id']) {
       $formatParams .= ' opus ';
    } else if ('vorbis' == $convertToAudio['id']) {
       $formatParams .= ' vorbis ';
    } else if ('mp3' == $convertToAudio['id']) {
       $formatParams .= ' mp3 --audio-quality 192K ';
    }

    if (!$this->commandExists('yt-dlp')) {
      $this->logger->warning('yt-dlp does not seem to be available on this server.', ['app' => 'downloader']);
      return new DataResponse(['message' => 'yt-dlp does not seem to be available on this server.'], 500);
    }


    // Try to download
    $tmpDir = \OC::$server->getTempManager()->getTempBaseDir();
    $hashedFilename = md5($mediaUrl);
    $outputTemplate = $tmpDir . '/' . $hashedFilename . '.%(ext)s';

    $command = 'yt-dlp ' . $formatParams . '-o ' . escapeshellarg($outputTemplate) . ' ' . escapeshellarg($mediaUrl);

    // Execute the command
    set_time_limit(0);
    $output = @shell_exec($command);
    sleep(1);

    // Check if downloaded file exists
    $downloadedFile = glob($tmpDir . '/' . $hashedFilename . '.*');
    if (empty($downloadedFile)) {
      return new DataResponse([
           'message' => 'The downloaded file could not be located in the tmp folder!'
      ], 400, ['Content-Type' => 'application/json']);
    }

    try {
      $userId = \OC::$server->getUserSession()->getUser()->getUID();
      $rootFolder = \OC::$server->getRootFolder();
      $userFolder = $rootFolder->getUserFolder($userId);
      $downloadedFile = $downloadedFile[0];
      $fileExtension = pathinfo($downloadedFile, PATHINFO_EXTENSION);

      $content = file_get_contents($downloadedFile);

      if (!$userFolder->nodeExists('downloader')) {
        $downloaderFolder = $userFolder->newFolder('downloader');
      } else {
        $downloaderFolder = $userFolder->get('downloader');
      }

      $downloaderFolder->newFile(basename($fileName . '.' .$fileExtension))->putContent($content);
    } catch (\Exception $e) {
      return new DataResponse([
           'message' => 'Error trying to move the file'
      ], 500, ['Content-Type' => 'application/json']);
    }

    return new DataResponse(['message' => 'success', 'file' => 'downloader/' + $fileName . '.' .$fileExtension], 200, ['Content-Type' => 'application/json']);
  }


  /**
   * Checks if a command exist on a typical Linux system
   *   Reference: https://beamtic.com/if-command-exists-php
   * @param mixed $command_name
   * @return bool
   */
  public function commandExists($command_name)
  {
     return (null === shell_exec("command -v $command_name")) ? false : true;
  }

}

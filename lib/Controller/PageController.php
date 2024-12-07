<?php

declare(strict_types=1);

namespace OCA\Downloader\Controller;

use OCA\Downloader\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller {

        #[NoAdminRequired]
        #[NoCSRFRequired]
        #[FrontpageRoute(verb: 'GET', url: '/')]
        public function index(): TemplateResponse {
          return new TemplateResponse('downloader', 'index');
        }

        #[NoAdminRequired]
        #[NoCSRFRequired]
        #[FrontpageRoute(verb: 'GET', url: '/api-test')]
        public function test(): DataResponse {
          return new DataResponse(['message' => 'API test working']);
        }

}

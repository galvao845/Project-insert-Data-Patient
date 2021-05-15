<?php
namespace Zeedhi\Framework\Security\AntiCSRF;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\HTTP\Kernel;

class AntiCSRFController {

    /** @var AntiCSRF */
    private $antiCSRF;
    /** @var Kernel */
    private $kernel;

    public function __construct(Kernel $kernel, AntiCSRF $antiCSRF) {
        $this->antiCSRF = $antiCSRF;
        $this->kernel = $kernel;
    }

    public function generateSessionCookieAndToken(DTO\Request $request, DTO\Response $response){
        try {
            $cookie = $this->getAntiCSRFCookie();
            $generatedObject = $this->antiCSRF->getTokenAndCookie($cookie);
        } catch (\Exception $e) {
            $generatedObject = $this->antiCSRF->generateTokenAndCookie();
        }

        setcookie(AntiCSRF::COOKIE_NAME, $generatedObject['cookie']);
        $response->addDataSet(new DataSet(AntiCSRF::DATASET_NAME, array('token' => $generatedObject['token'])));
    }

    /**
     * @throws Exception When cookie was not found in request headers
     *
     * @return string The cookie value
     */
    private function getAntiCSRFCookie() {
        if ($cookieHeader = $this->kernel->getHttpRequest()->getHeaders()->get('cookie')) {
            foreach(explode(';', $cookieHeader) as $cookieHeaderEntry) {
                list($cookieName, $cookieValue) = explode('=', $cookieHeaderEntry);
                if ($cookieName === AntiCSRF::COOKIE_NAME) {
                    return $cookieValue;
                }
            }
        }

        throw Exception::cookieNotFoundInRequest();
    }
}
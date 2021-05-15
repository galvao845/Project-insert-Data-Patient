<?php
namespace Zeedhi\Framework\Security\AntiCSRF;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\HTTP\Kernel;
use Zeedhi\Framework\HTTP\Request;
use Zeedhi\Framework\Events\PreDispatch\Listener;

class PreDispatchListener extends Listener {

    /** @var Kernel */
    private $kernel;
    /** @var AntiCSRF */
    private $antiCSRF;
    /** @var string */
    private $routeToGenerate;
    /** @var Array */
    private $publivcRoutes;

    public function __construct(Kernel $kernel, AntiCSRF $antiCSRF, $routeToGenerate, $publicRoutes = array()){
        $this->kernel = $kernel;
        $this->antiCSRF = $antiCSRF;
        $this->routeToGenerate = $routeToGenerate;
        $this->publicRoutes = $publicRoutes;
    }

    public static function factoryWithPublicRoutesFile($kernel, $antiCSRF, $routeToGenerate, $publicRoutesFile) {
        $publicRoutes = json_decode(file_get_contents($publicRoutesFile));
        return new static($kernel, $antiCSRF, $routeToGenerate, $publicRoutes);
    }

    private function getRequestCookie(Request $request){
        if ($request->getHeaders()->has('cookie')) {
            $headerCookie = $request->getHeaders()->get('cookie');
            $cookies = explode(';', $headerCookie);
            foreach ($cookies as $cookie) {
                $cookieParts = explode('=', $cookie);
                if (trim($cookieParts[0]) == AntiCSRF::COOKIE_NAME) {
                    return $cookieParts[1];
                }
            }
        }

        throw Exception::cookieNotFoundInRequest();
    }

    private function getRequestToken(Request $request){
        if(!$request->getHeaders()->has(AntiCSRF::TOKEN_NAME)){
            throw Exception::tokenNotFoundInRequest();
        }
        return $request->getHeaders()->get(AntiCSRF::TOKEN_NAME);
    }

    private function validateToken($cookie, $token){
        $isTokenValid = $this->antiCSRF->isTokenValid($cookie, $token);
        if(!$isTokenValid){
            throw Exception::invalidToken();
        }
    }

    public function validateSameOrigin(Request $request) {
        $requestHeaders = $request->getHeaders();
        if ($requestHeaders->has('Origin') && $requestHeaders->get('Origin') !== $request->getSchemeAndHttpHost()) {
            throw Exception::invalidAccess();
        }
    }

    public function validateCSRFToken(Request $request) {
        $cookie = $this->getRequestCookie($request);
        $token = $this->getRequestToken($request);
        $this->validateToken($cookie, $token);
    }

    public function preDispatch(DTO\Request $dtoRequest){
        $request = $this->kernel->getHttpRequest();
        if ($this->isGenerateTokenRoute($dtoRequest)) {
            $this->validateSameOrigin($request);
        } else if(!$this->isPublicRoute($dtoRequest)){
            $this->validateCSRFToken($request);
        }
    }

    /**
     * @param DTO\Request $dtoRequest
     * @return bool
     */
    private function isGenerateTokenRoute(DTO\Request $dtoRequest){
        return $this->routeToGenerate === $dtoRequest->getRoutePath();
    }

    /**
     * @param DTO\Request $dtoRequest
     * @return false|number
     */
    private function isPublicRoute(DTO\Request $dtoRequest){
        return array_search($dtoRequest->getRoutePath(), $this->publicRoutes) !== false;
    }
}
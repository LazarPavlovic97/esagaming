<?php
namespace App\Core;

class Route {
    private $controllerName;
    private $methodName;
    private $pattern;
    private $httpMethod;

    private function __construct($httpMethod, $pattern, $controllerName, $methodName) {
        $this->httpMethod     = $httpMethod;
        $this->pattern        = $pattern;
        $this->controllerName = $controllerName;
        $this->methodName     = $methodName;
    }

    public static function get($pattern, $controllerName, $methodName) {
        return new Route('GET', $pattern, $controllerName, $methodName);
    }

    public static function post($pattern, $controllerName, $methodName) {
        return new Route('POST', $pattern, $controllerName, $methodName);
    }

    public static function any($pattern, $controllerName, $methodName) {
        return new Route('GET|POST', $pattern, $controllerName, $methodName);
    }

    public function matches($httpMethod, $url) {
        if (!\preg_match('/^' . $this->httpMethod . '$/', $httpMethod)) {
            return false;
        }

        if (!\preg_match($this->pattern, $url)) {
            return false;
        }

        return true;
    }

    public function getControllerName() {
        return $this->controllerName;
    }

    public function getMethodName() {
        return $this->methodName;
    }

    public function &extractArguments($url) {
        # Polazna pretpostavka je da nema uparenih grupa iz URL-a
        $matches = [];

        # Metod koji po regularnom izrazu iz URL-a uzima uaprene grupe
        preg_match_all($this->pattern, $url, $matches);

        # Brisemo brvi element niza, jer on sadrzi ceo URL
        unset($matches[0]);

        # Pripremimo niz za buduce argumente izvucene iz URL-a na osnovu reg. izr.
        $arguments = [];

        # Za svaku uparenu grupu iz matches uzimamo samo vrednost argumenta
        foreach ($matches as $match) {
            # i dodajemo tu vrednos tu prethodno napravljeni niz argumenata
            $arguments[] = $match[0];
        }

        # Tako napravljeni niz vracamo nazad da bude prosledjen metodu kontrolera
        return $arguments;
    }
}

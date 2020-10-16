<?php
    namespace App\Core;

    class UserController extends Controller {
        public function __pre() {
            if (!$this->getSession()->get('userId', null)) {
                ob_clean();
                header('Location: ' . BASE . 'user/login', true, 307);
                exit;
            }
        }
    }

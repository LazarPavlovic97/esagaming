<?php
    namespace App\Core;

use App\Core\Session\Session;

class Controller {
        private $dbc;
        private $data = [];
        private $localSession;

        public function __construct(DatabaseConnection &$dbc) {
            $this->dbc = $dbc;
        }

        public function setSession(Session &$session) {
            $this->localSession = $session;
        }

        public function &getSession(): Session {
            return $this->localSession;
        }

        protected function &getDatabaseConnection() {
            return $this->dbc;
        }

        protected function set(string $name, $value) {
            if (\preg_match('/^[a-z][A-z0-9]*$/', $name)) {
                $this->data[$name] = $value;
            }
        }

        public function &getData() {
            return $this->data;
        }

        public function __pre() {
            
        }

        public function userSession() {
            return $this->getSession()->get('userId');
        }

        public function checkUserSession() {
            if($this->userSession() == '') {
                header('Location: ' . BASE);
            }
        }

        protected function notifyUser($body,$subject,$recipient) {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>';
            $html .= $body;
            $html .= '</body></html>';

            $mailer = new \PHPMailer\PHPMailer\PHPMailer();
            $mailer->isSMTP();
            $mailer->Host = \Configuration::MAIL_HOST;
            $mailer->Port = \Configuration::MAIL_PORT;
            $mailer->SMTPSecure = \Configuration::MAIL_PROTOCOL;
            $mailer->SMTPAuth = true;
            $mailer->Username = \Configuration::MAIL_USERNAME;
            $mailer->Password = \Configuration::MAIL_PASSWORD;

            $mailer->isHTML(true);
            $mailer->Body = $html;
            $mailer->Subject = $subject;
            $mailer->setFrom(\Configuration::MAIL_USERNAME);

            $mailer->addAddress($recipient);

            try {
                $mailer->send();
            } catch (Exception $e) {
                die($e);
            }
        }

        protected function possibilityToWin(array $set, $length=10000) {
            $left = 0;

            foreach($set as $num=>$right)  {
                $set[$num] = $left + $right*$length;
                $left = $set[$num];
            }

            $test = mt_rand(1, $length);
            $left = 1;

            foreach($set as $num=>$right) {
                if($test>=$left && $test<=$right) {
                    return $num;
                }

                $left = $right;
            }

            return null;
        }
    }

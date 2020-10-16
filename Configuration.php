<?php
    final class Configuration {
        const BASE = 'http://esagaming.local/';

        const DATABASE_HOST = 'localhost';
        const DATABASE_USER = 'root';
        const DATABASE_PASS = 'Adminabc123!';
        const DATABASE_NAME = 'esagaming';

        const SESSION_STORAGE_CLASS = '\\App\\Core\\Session\\FileSessionStorage';
        const SESSION_STORAGE_ARGUMENTS = [ './session/' ]; # !!!

        const FINGERPRINT_PROVIDER_CLASS = '\\App\\Core\\Fingerprint\\BasicFingerprintProvider';

        const UPLOAD_DIR = 'assets/uploads/';
    }
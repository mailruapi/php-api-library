<?php

interface MailRu_ITransport {
    const DEFAULT_API_BASE_URL = 'http://www.appsmail.ru/platform/api';

    public function get($params);
}

<?php

if (! function_exists('sihrd_get_token')) {
    function sihrd_get_token(): ?string
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.ums.ac.id/token/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => [
                'username'    => 'mycpl',
                'password'    => '10oWUWrF4Zt&',
                'consumer_id' => 'mhi595',
            ],
        ]);

        $response = curl_exec($curl);

        if (! $response) {
            return null;
        }

        $data = json_decode($response, true);

        return $data['access'] ?? null;
    }
}

if (! function_exists('sihrd_get_jabatan')) {
    /**
     * Ambil daftar jabatan dari SIHRD API.
     *
     * @return array|null Array hasil response, atau null jika gagal.
     */
    function sihrd_get_jabatan(): ?array
    {
        $token = sihrd_get_token();

        if (! $token) {
            return null;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.ums.ac.id/umar/v2/jabatan',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($curl);

        if (! $response) {
            return null;
        }

        return json_decode($response, true);
    }
}

if (! function_exists('sihrd_get_detail_dosen')) {
    function sihrd_get_detail_dosen(string $nimNip): ?array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://sihrd.ums.ac.id/umar/v3/profil/' . urlencode($nimNip) . '/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_POSTFIELDS     => ['username' => 'abubakar', 'password' => 'AbuB4kr1'],
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic YWJ1YmFrYXI6QWJ1QjRrcjE=',
            ],
        ]);

        $response = curl_exec($curl);

        if (! $response) {
            return null;
        }

        return json_decode($response, true);
    }
}

<?php

if (! function_exists('star_get_token')) {
    function star_get_token(): ?string
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://star.ums.ac.id/abubakar/',
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
                'act'      => 'GetToken',
                'username' => 'wur115',
                'password' => 'a',
            ],
        ]);

        $response = curl_exec($curl);

        if (! $response) {
            return null;
        }

        $data = json_decode($response, true);

        return $data['token'] ?? null;
    }
}

if (! function_exists('star_get_profil_mahasiswa')) {
    function star_get_profil_mahasiswa(string $nim): ?array
    {
        $token = star_get_token();

        if (! $token) {
            return null;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://star.ums.ac.id/abubakar/',
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
                'act'   => 'Mhs',
                'token' => $token,
                'nim'   => $nim,
            ],
        ]);

        $response = curl_exec($curl);

        if (! $response) {
            return null;
        }

        return json_decode($response, true);
    }
}

<?php

namespace App\Libraries;

use Jumbojett\OpenIDConnectClient;

class Lib_Oidc
{
    private OpenIDConnectClient $oidc;

    public function __construct()
    {
        helper('url');

        $this->oidc = new OpenIDConnectClient(
            self::konfig('oidc_issuer'),
            self::konfig('oidc_client_id'),
            self::konfig('oidc_client_secret')
        );
        // Bila tidak diisi, redirect URI mengikuti app.baseURL (mis. https://smhws.ums.ac.id/oidc/callback).
        // URI ini harus terdaftar di client Keycloak.
        $this->oidc->setRedirectURL(self::konfig('oidc_redirect_uri') ?: base_url('oidc/callback'));
        $this->oidc->addScope(['profile', 'email']);
    }

    /** Baca konfigurasi dari .env; menerima nama huruf kecil (oidc_issuer) maupun besar (OIDC_ISSUER). */
    private static function konfig(string $kunci): ?string
    {
        $nilai = env($kunci) ?? env(strtoupper($kunci));
        return ($nilai === null || $nilai === '') ? null : (string) $nilai;
    }

    // force OIDC authentication (redirects to Keycloak if not authenticated yet)
    public function forceAuth()
    {
        $this->oidc->authenticate();
        // stash the id token so a later request (logout) can end the session at Keycloak too
        $_SESSION['oidc_id_token'] = $this->oidc->getIdToken();
    }

    public function user()
    {
        // claims from the already-verified ID token -- avoids a second call to the
        // /userinfo endpoint, which intermittently 401s (likely Cloudflare bot-management
        // reacting to jumbojett's library User-Agent on that specific endpoint)
        $claims = (array) $this->oidc->getVerifiedClaims();

        $userlogin = $claims['preferred_username'] ?? $claims['sub'] ?? null;
        if (empty($userlogin)) {
            exit("User was not authenticated yet.");
        }

        $employeeNumber = $claims['employeeNumber'] ?? null; // NIM (mhs) / NIP (dosen-tendik)
        $mail           = $claims['email'] ?? null;
        $mailPribadi    = $claims['mailAlternateAddress'] ?? null;

        return (object) array(
            'userlogin'      => $userlogin,
            'employeeNumber' => $employeeNumber,
            'mail'           => $mail,
            'mailPribadi'    => $mailPribadi,
            'attributes'     => $claims, // tetap disertakan kalau butuh atribut lain
        );
    }

    /**
     * Akhiri sesi di Keycloak lalu kembali ke $url (default: halaman utama).
     * $idToken diberikan bila sesi aplikasi sudah dihapus sebelum fungsi ini dipanggil.
     */
    public function keluar($url = '', ?string $idToken = null)
    {
        if (empty($url)) {
            helper('url');
            $url = base_url();
        }
        $idToken ??= $_SESSION['oidc_id_token'] ?? null;
        unset($_SESSION['oidc_id_token']);
        if (empty($idToken)) {
            header('Location: ' . $url);
            exit;
        }
        $this->oidc->signOut($idToken, $url);
    }
}

<?php

namespace Genealogy\App\Service;

use PDO;
use RuntimeException;

require_once __DIR__ . '/../../../../.env.php';

/**
 * OAuth authentication and account linking for external identity providers.
 * Provider identities are deliberately kept separate from HuMo user records.
 */
class SocialAuthService
{
    private const PROVIDERS = ['google', 'facebook', 'apple'];

    public function __construct(private PDO $dbh)
    {
    }

    public function handleRequest(): void
    {
        $provider = strtolower(trim((string) ($_GET['provider'] ?? $_POST['provider'] ?? '')));
        // OAuth providers return the code and state, but do not echo our provider
        // query parameter. Recover it from the state created before redirecting.
        if ($provider === '' && isset($_SESSION['social_auth_state']['provider'])) {
            $provider = strtolower(trim((string) $_SESSION['social_auth_state']['provider']));
        }
        if (!in_array($provider, self::PROVIDERS, true)) {
            return;
        }

        try {
            if (isset($_GET['error']) || isset($_POST['error'])) {
                throw new RuntimeException('The social login was cancelled or denied.');
            }
            if (!isset($_GET['code']) && !isset($_POST['code'])) {
                $this->start($provider);
                return;
            }

            $this->complete($provider, (string) ($_GET['code'] ?? $_POST['code'] ?? ''));
        } catch (RuntimeException $exception) {
            error_log('[HuMo social authentication] ' . $exception->getMessage());
            $_SESSION['social_auth_error'] = $exception->getMessage();
            $redirectPage = $this->isLinking() ? 'user_settings' : 'login';
            unset($_SESSION['social_auth_error_action']);
            $this->redirect($redirectPage);
        }
    }

    public function consumeError(): string
    {
        $error = $_SESSION['social_auth_error'] ?? '';
        unset($_SESSION['social_auth_error']);
        return is_string($error) ? $error : '';
    }

    public function csrfToken(): string
    {
        if (!isset($_SESSION['social_auth_csrf']) || !is_string($_SESSION['social_auth_csrf'])) {
            $_SESSION['social_auth_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['social_auth_csrf'];
    }

    public function linkedProviders(int $userId): array
    {
        $stmt = $this->dbh->prepare('SELECT provider FROM humo_user_social_logins WHERE user_id = :user_id ORDER BY provider');
        $stmt->execute([':user_id' => $userId]);
        return array_map(static fn (array $row): string => $row['provider'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function start(string $provider): void
    {
        $action = ($_POST['action'] ?? $_GET['action'] ?? '') === 'link' ? 'link' : 'login';
        if ($action === 'link') {
            $this->requireAuthenticatedUser();
            $csrf = (string) ($_POST['social_auth_csrf'] ?? '');
            if (!hash_equals($this->csrfToken(), $csrf)) {
                throw new RuntimeException('The social login request could not be verified.');
            }
        }

        $_SESSION['social_auth_state'] = [
            'value' => bin2hex(random_bytes(32)),
            'provider' => $provider,
            'action' => $action,
            'created' => time(),
        ];

        $this->redirectExternal($this->authorizationUrl($provider, $_SESSION['social_auth_state']['value']));
    }

    private function complete(string $provider, string $code): void
    {
        $state = $_GET['state'] ?? $_POST['state'] ?? '';
        $savedState = $_SESSION['social_auth_state'] ?? null;
        if (is_array($savedState)) {
            $_SESSION['social_auth_error_action'] = $savedState['action'] ?? 'login';
        }
        unset($_SESSION['social_auth_state']);
        if (!is_array($savedState)
            || ($savedState['provider'] ?? '') !== $provider
            || !is_string($state)
            || !hash_equals((string) ($savedState['value'] ?? ''), $state)
            || time() - (int) ($savedState['created'] ?? 0) > 600
        ) {
            throw new RuntimeException('The social login request expired or was invalid.');
        }

        $identity = $this->exchangeCode($provider, $code);
        $userId = $this->findLinkedUser($provider, $identity['subject']);
        $isLinking = ($savedState['action'] ?? '') === 'link';

        if ($isLinking) {
            $currentUserId = $this->requireAuthenticatedUser();
            if ($userId !== null && $userId !== $currentUserId) {
                throw new RuntimeException('That social login is already linked to another account.');
            }
            $this->linkIdentity($currentUserId, $provider, $identity);
            unset($_SESSION['social_auth_error_action']);
            $this->redirect('user_settings');
            return;
        }

        if ($userId === null) {
            throw new RuntimeException('This social login is not linked to a HuMo account. Sign in with your existing login first, then link it in User settings.');
        }

        $user = $this->getActiveUser($userId);
        if (!$user) {
            throw new RuntimeException('This HuMo account is inactive.');
        }
        if (!empty($user->user_2fa_enabled)) {
            unset($_SESSION['social_auth_error_action']);
            $_SESSION['social_pending_user_id'] = (int) $user->user_id;
            $this->redirect('login');
        }
        unset($_SESSION['social_auth_error_action']);
        $_SESSION['social_authenticated_user_id'] = (int) $user->user_id;
        $this->redirect('index');
    }

    private function authorizationUrl(string $provider, string $state): string
    {
        $redirectUri = $this->redirectUri();
        if ($provider === 'google') {
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $this->requiredEnv('HUMO_GOOGLE_CLIENT_ID'),
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $state,
                'access_type' => 'online',
                'prompt' => 'select_account',
            ]);
        }
        if ($provider === 'facebook') {
            return 'https://www.facebook.com/' . $this->env('HUMO_FACEBOOK_GRAPH_VERSION', 'v19.0') . '/dialog/oauth?' . http_build_query([
                'client_id' => $this->requiredEnv('HUMO_FACEBOOK_CLIENT_ID'),
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'email,public_profile',
                'state' => $state,
            ]);
        }

        return 'https://appleid.apple.com/auth/authorize?' . http_build_query([
            'client_id' => $this->requiredEnv('HUMO_APPLE_CLIENT_ID'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'response_mode' => 'query',
            'scope' => 'name email',
            'state' => $state,
        ]);
    }

    private function exchangeCode(string $provider, string $code): array
    {
        $redirectUri = $this->redirectUri();
        if ($provider === 'google') {
            $token = $this->postForm('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $this->requiredEnv('HUMO_GOOGLE_CLIENT_ID'),
                'client_secret' => $this->requiredEnv('HUMO_GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);
            $claims = $this->getJson('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode((string) ($token['id_token'] ?? '')));
            if (($claims['iss'] ?? '') !== 'https://accounts.google.com'
                || ($claims['aud'] ?? '') !== $this->requiredEnv('HUMO_GOOGLE_CLIENT_ID')
                || empty($claims['email_verified'])
            ) {
                throw new RuntimeException('Google did not return a verified identity.');
            }
            return $this->identity((string) ($claims['sub'] ?? ''), (string) ($claims['email'] ?? ''));
        }

        if ($provider === 'facebook') {
            $token = $this->postForm('https://graph.facebook.com/' . $this->env('HUMO_FACEBOOK_GRAPH_VERSION', 'v19.0') . '/oauth/access_token', [
                'code' => $code,
                'client_id' => $this->requiredEnv('HUMO_FACEBOOK_CLIENT_ID'),
                'client_secret' => $this->requiredEnv('HUMO_FACEBOOK_CLIENT_SECRET'),
                'redirect_uri' => $redirectUri,
            ]);
            $profile = $this->getJson('https://graph.facebook.com/' . $this->env('HUMO_FACEBOOK_GRAPH_VERSION', 'v19.0') . '/me?fields=id,email&access_token=' . rawurlencode((string) ($token['access_token'] ?? '')));
            if (empty($profile['id']) || empty($profile['email'])) {
                throw new RuntimeException('Facebook did not return a usable verified identity.');
            }
            return $this->identity((string) $profile['id'], (string) $profile['email']);
        }

        $token = $this->postForm('https://appleid.apple.com/auth/token', [
            'client_id' => $this->requiredEnv('HUMO_APPLE_CLIENT_ID'),
            'client_secret' => $this->appleClientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);
        $claims = $this->decodeAppleIdentityToken((string) ($token['id_token'] ?? ''));
        if (($claims['iss'] ?? '') !== 'https://appleid.apple.com'
            || ($claims['aud'] ?? '') !== $this->requiredEnv('HUMO_APPLE_CLIENT_ID')
            || empty($claims['sub'])
            || (isset($claims['exp']) && (int) $claims['exp'] < time())
        ) {
            throw new RuntimeException('Apple did not return a valid identity.');
        }
        return $this->identity((string) $claims['sub'], (string) ($claims['email'] ?? ''));
    }

    private function linkIdentity(int $userId, string $provider, array $identity): void
    {
        $existing = $this->dbh->prepare('SELECT user_id FROM humo_user_social_logins WHERE provider = :provider AND provider_user_id = :provider_user_id');
        $existing->execute([':provider' => $provider, ':provider_user_id' => $identity['subject']]);
        $row = $existing->fetch(PDO::FETCH_OBJ);
        if ($row && (int) $row->user_id !== $userId) {
            throw new RuntimeException('That social login is already linked to another account.');
        }
        if (!$row) {
            $stmt = $this->dbh->prepare('INSERT INTO humo_user_social_logins (user_id, provider, provider_user_id, provider_email, created_at) VALUES (:user_id, :provider, :provider_user_id, :provider_email, :created_at)');
            $stmt->execute([
                ':user_id' => $userId,
                ':provider' => $provider,
                ':provider_user_id' => $identity['subject'],
                ':provider_email' => $identity['email'],
                ':created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function findLinkedUser(string $provider, string $subject): ?int
    {
        $stmt = $this->dbh->prepare('SELECT user_id FROM humo_user_social_logins WHERE provider = :provider AND provider_user_id = :provider_user_id');
        $stmt->execute([':provider' => $provider, ':provider_user_id' => $subject]);
        $userId = $stmt->fetchColumn();
        return $userId === false ? null : (int) $userId;
    }

    private function getActiveUser(int $userId): ?object
    {
        $stmt = $this->dbh->prepare("SELECT * FROM humo_users WHERE user_id = :user_id AND UPPER(COALESCE(user_status, '')) = 'A'");
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        return $user ?: null;
    }

    private function requireAuthenticatedUser(): int
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!is_numeric($userId) || (int) $userId < 1) {
            throw new RuntimeException('You must sign in before linking a social login.');
        }
        return (int) $userId;
    }

    private function isLinking(): bool
    {
        return ($_POST['action'] ?? $_GET['action'] ?? '') === 'link'
            || ($_SESSION['social_auth_error_action'] ?? '') === 'link'
            || (isset($_SESSION['social_auth_state']['action']) && $_SESSION['social_auth_state']['action'] === 'link');
    }

    private function identity(string $subject, string $email): array
    {
        if ($subject === '') {
            throw new RuntimeException('The provider returned no stable identity.');
        }
        return ['subject' => $subject, 'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : ''];
    }

    private function postForm(string $url, array $fields): array
    {
        return $this->request($url, 'POST', $fields);
    }

    private function getJson(string $url): array
    {
        return $this->request($url, 'GET');
    }

    private function request(string $url, string $method, array $fields = []): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_CUSTOMREQUEST => $method,
        ]);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded']);
        }
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        $data = is_string($body) ? json_decode($body, true) : null;
        if ($error || $status < 200 || $status >= 300 || !is_array($data)) {
            throw new RuntimeException('The social identity provider could not be reached.');
        }
        return $data;
    }

    private function decodeJwt(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('The provider returned an invalid identity token.');
        }
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        if (!is_array($payload)) {
            throw new RuntimeException('The provider returned an invalid identity token.');
        }
        return $payload;
    }

    private function decodeAppleIdentityToken(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Apple returned an invalid identity token.');
        }
        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
            throw new RuntimeException('Apple returned an invalid identity token.');
        }
        $keys = $this->getJson('https://appleid.apple.com/auth/keys')['keys'] ?? [];
        foreach ($keys as $key) {
            if (($key['kid'] ?? '') !== $header['kid'] || ($key['kty'] ?? '') !== 'RSA') {
                continue;
            }
            $modulus = $this->base64UrlDecode((string) ($key['n'] ?? ''));
            $exponent = $this->base64UrlDecode((string) ($key['e'] ?? ''));
            $publicKey = openssl_pkey_get_public($this->rsaPublicKey($modulus, $exponent));
            if ($publicKey && openssl_verify($parts[0] . '.' . $parts[1], $this->base64UrlDecode($parts[2]), $publicKey, OPENSSL_ALGO_SHA256) === 1) {
                $claims = json_decode($this->base64UrlDecode($parts[1]), true);
                if (!is_array($claims)) {
                    break;
                }
                return $claims;
            }
        }
        throw new RuntimeException('Apple returned an unverifiable identity token.');
    }

    private function rsaPublicKey(string $modulus, string $exponent): string
    {
        $modulus = "\x00" . $modulus;
        $rsa = $this->derSequence(
            $this->derInteger($modulus) . $this->derInteger($exponent)
        );
        $algorithm = hex2bin('300d06092a864886f70d0101010500');
        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($this->derSequence($algorithm . $this->derBitString($rsa))), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    private function derInteger(string $value): string
    {
        return "\x02" . $this->derLength(strlen($value)) . $value;
    }

    private function derBitString(string $value): string
    {
        return "\x03" . $this->derLength(strlen($value) + 1) . "\x00" . $value;
    }

    private function derSequence(string $value): string
    {
        return "\x30" . $this->derLength(strlen($value)) . $value;
    }

    private function derLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }
        $encoded = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($encoded)) . $encoded;
    }

    private function appleClientSecret(): string
    {
        $privateKey = getenv('HUMO_APPLE_PRIVATE_KEY') ?: '';
        $privateKeyFile = getenv('HUMO_APPLE_PRIVATE_KEY_FILE') ?: '';
        if ($privateKey === '' && $privateKeyFile !== '') {
            $privateKey = (string) @file_get_contents($privateKeyFile);
        }
        if ($privateKey === '') {
            throw new RuntimeException('Apple login is not configured.');
        }
        $header = $this->base64UrlEncode(json_encode(['alg' => 'ES256', 'kid' => $this->requiredEnv('HUMO_APPLE_KEY_ID'), 'typ' => 'JWT']));
        $claims = $this->base64UrlEncode(json_encode(['iss' => $this->requiredEnv('HUMO_APPLE_TEAM_ID'), 'iat' => time(), 'exp' => time() + 86400, 'aud' => 'https://appleid.apple.com', 'sub' => $this->requiredEnv('HUMO_APPLE_CLIENT_ID')]));
        $signingInput = $header . '.' . $claims;
        if (!openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Apple login is not configured correctly.');
        }
        return $signingInput . '.' . $this->base64UrlEncode($this->ecdsaDerToJose($signature, 32));
    }

    private function ecdsaDerToJose(string $der, int $componentLength): string
    {
        $offset = 2;
        if (ord($der[1]) & 0x80) {
            $offset = 2 + (ord($der[1]) & 0x7f);
        }
        if (($der[0] ?? '') !== "\x30" || ($der[$offset] ?? '') !== "\x02") {
            throw new RuntimeException('Apple login is not configured correctly.');
        }
        $rLength = ord($der[$offset + 1]);
        $r = substr($der, $offset + 2, $rLength);
        $sOffset = $offset + 2 + $rLength;
        if (($der[$sOffset] ?? '') !== "\x02") {
            throw new RuntimeException('Apple login is not configured correctly.');
        }
        $sLength = ord($der[$sOffset + 1]);
        $s = substr($der, $sOffset + 2, $sLength);
        return str_pad(ltrim($r, "\x00"), $componentLength, "\x00", STR_PAD_LEFT)
            . str_pad(ltrim($s, "\x00"), $componentLength, "\x00", STR_PAD_LEFT);
    }

    private function redirectUri(): string
    {
        return $this->requiredEnv('HUMO_SOCIAL_REDIRECT_URI');
    }

    private function requiredEnv(string $name): string
    {
        $value = $this->env($name, '');
        if ($value === '') {
            throw new RuntimeException($name . ' is not configured.');
        }
        return $value;
    }

    private function env(string $name, string $default): string
    {
        $value = getenv($name);
        return $value === false ? $default : trim((string) $value);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value . str_repeat('=', (4 - strlen($value) % 4) % 4), '-_', '+/'), true);
    }

    private function redirect(string $page): void
    {
        header('Location: index.php?page=' . rawurlencode($page));
        exit;
    }

    private function redirectExternal(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

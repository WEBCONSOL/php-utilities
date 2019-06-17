<?php

namespace WC\Utilities\Security;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\ValidationData;
use WC\Utilities\Logger;

final class JWT {

    private static $issuer = 'EzpizeeClient';
    private static $audience = 'EzpizeeApp';
    private static $key_data = 'data';
    private static $secretStr = '';

    private function __construct(){}

    public static function encrypt(string $data): string {
        $signer = new Sha384();
        return ((new Builder())
            ->setIssuer(self::$issuer)
            ->setHeader('alg', 'HS384')
            ->setHeader('typ', 'JWT')
            ->setId(self::$audience, true)
            ->setAudience(self::$audience)
            ->set(self::$key_data, $data)
            ->sign($signer, self::secret())
            ->getToken()).'';
    }

    public static function decrypt(string $token): string {
        try {
            $token = (new Parser())->parse($token);
            $data = new ValidationData();
            $data->setId(self::$audience);
            $data->setIssuer(self::$issuer);
            $data->setAudience(self::$audience);
            $signer = new Sha384();
            if ($token->validate($data) && $token->verify($signer, self::secret())) {
                return $token->getClaim(self::$key_data, '');
            }
        }
        catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
        return '';
    }

    private static function sshPhrase(): string {return 'EzpzApp';}

    private static function secret(): string {
        if (empty(self::$secretStr)) {
            self::$secretStr = file_get_contents(__DIR__.'/key/id_rsa.pub');
        }
        return self::$secretStr;
    }
}
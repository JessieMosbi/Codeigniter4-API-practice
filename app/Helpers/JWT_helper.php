<?php

use CodeIgniter\HTTP\RequestInterface;
use App\Models\ClientModel;
// UUID (for generating JWT payload jti)
use Ramsey\Uuid\Uuid;
// JWT Core
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
// JWS
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\Serializer\CompactSerializer as CompactSerializer_sign;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\Algorithm\PS256;
// JWE
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\Encryption\Serializer\CompactSerializer as CompactSerializer_en;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
// Checker: if column exist, then check format and if value is a proper value that in list or not.
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker;
// Nested JWT (JWE with JWS payload)
use Jose\Component\NestedToken\NestedTokenBuilder;
use Jose\Component\NestedToken\NestedTokenLoader;
use Jose\Component\Core\JWKSet;

/**
 * Generate a JWS (signed JWT) for an authenticated user
 *
 * @return array
 */
function generateSignedJWT($email): array
{
    $clientModel = new ClientModel();
    $client = $clientModel->findClientByEmail($email);

    $jwtSetting = getJWTSetting();
    $jwk = JWKFactory::createFromKeyFile($jwtSetting['SK'], $jwtSetting['KP']);
    $iat = time();
    $exp = $iat + $jwtSetting['TTL'];
    $jti = (Uuid::uuid4())->toString(); // uuid 4: random
    $aud = $client->name;

    $payload = json_encode([
        // Registered claims
        'jti' => $jti,
        'iss' => $jwtSetting['ISSUER'],
        'iat' => $iat,
        'exp' => $exp,
        'aud' => $aud,
        // Private claims
        'user' => [
            'id' => $client->id,
            'email' => $client->email
        ]
    ]);

    $algorithmManager = new AlgorithmManager([new PS256()]);
    $serializer = new CompactSerializer_sign();
    $jwsBuilder = new JWSBuilder($algorithmManager);

    $jws = $jwsBuilder
    ->create()
    ->withPayload($payload)
    ->addSignature($jwk, ['alg' => 'PS256', 'crit' => ['alg']])
    ->build();
    $token = $serializer->serialize($jws, 0);

    return [$token];
}

/**
 * Get JWS (signed JWT) from Authentication header (format: Bearer XXXXXXXXX)
 *
 * @param RequestInterface $request
 * @return string
 * @throws \Exception
 */
function getSignedJWTFromRequest(RequestInterface $request): string
{
    $authenticationHeader = $request->getServer('HTTP_AUTHORIZATION');

    if (is_null($authenticationHeader)) {
        throw new Exception('Missing or invalid JWT in request');
    }

    $authenticationHeaderArray = explode(' ', $authenticationHeader);
    if (empty($authenticationHeaderArray[1])) {
        throw new Exception('Missing or invalid JWT in request');
    }

    return $authenticationHeaderArray[1];
}

/**
 * Decodes JWS (signed JWT) and validate it
 *
 * @param string $encodedToken
 * @return bool
 * @throws \Jose\Component\Checker\InvalidClaimException
 * @throws \Jose\Component\Checker\MissingMandatoryClaimException
 */
function validateSignedJWT(string $encodedToken): bool
{
    $jwtSetting = getJWTSetting();
    $jwk = JWKFactory::createFromKeyFile($jwtSetting['PK'], $jwtSetting['KP']);

    $algorithmManager = new AlgorithmManager([new PS256()]);
    $jwsVerifier = new JWSVerifier($algorithmManager);
    $jwsSerializerManager = new JWSSerializerManager([new CompactSerializer_sign()]);
    $jwsLoader = new JWSLoader(
        $jwsSerializerManager,
        $jwsVerifier,
        null
    );

    // Validate Signature
    $jws = $jwsLoader->loadAndVerifyWithKey($encodedToken, $jwk, $signature);

    // Check header
    $headerCheckerManager_JWS = new HeaderCheckerManager([new AlgorithmChecker(['PS256'])], [new JWSTokenSupport()]);
    $headerCheckerManager_JWS->check($jws, 0, ['crit']);
    if (!$jwsVerifier->verifyWithKey($jws, $jwk, 0)) {
        return false;
    }

    // Check payload
    // $model = new ClientModel();
    // $clientInfo = $model->findClientByIPAddress($ipAddress);
    $claimCheckerManager = new ClaimCheckerManager(
        [
            new Checker\IssuerChecker([$jwtSetting['ISSUER']]),
            new Checker\IssuedAtChecker(),
            new Checker\ExpirationTimeChecker()
            // new Checker\AudienceChecker('client-1') // TODO: get value from DB correspond by request ip
        ]
    );
    $claims = json_decode($jws->getPayload(), true);
    $claimCheckerManager->check($claims, ['jti', 'iss', 'iat', 'exp', 'aud']);

    return true;
}

/**
 * Get JWT payload
 *
 * @param string $encodedToken
 * @param string|null $column
 * @return array
 */
function getPayloadFromJWT(string $encodedToken, string $column = null): array
{
    $jwsSerializerManager = new JWSSerializerManager([new CompactSerializer_sign()]);
    $jws = $jwsSerializerManager->unserialize($encodedToken);
    $claims = json_decode($jws->getPayload(), true);

    if ($column) {
        return $claims[$column];
    }
    return $claims;
}

/**
 * Get JWT setting from .env file
 *
 * @return array
 */
function getJWTSetting(): array
{
    return [
        'KP' => getenv('KEY_FILE_PASSWORD'),
        'PK' => getenv('PUBLIC_KEY_FILE'),
        'SK' => getenv('PRIVATE_KEY_FILE'),
        'ISSUER' => getenv('JWT_ISSUER'),
        'TTL' => getenv('JWT_TIME_TO_LIVE'),
        'CLIENT-1' => [
            'KP' => getenv('KEY_FILE_PASSWORD_CLIENT1'),
            'PK' => getenv('PUBLIC_KEY_FILE_CLIENT1'),
            'SK' => getenv('PRIVATE_KEY_FILE_CLIENT1'),
        ]
    ];
}

/**
 * Generate a Nested JWT (JWE with JWT as payload) for passing data.
 *
 * @param string $userName
 * @param array $data
 * @return array
 */
function getEncryptJWT(string $userName, array $data): array
{
    $jwtSetting = getJWTSetting();
    $iat = time();

    $payload = json_encode([
        'iat' => $iat,
        'iss' => $jwtSetting['ISSUER'],
        'exp' => $iat + $jwtSetting['TTL'],
        'aud' => $userName,
        'data' => $data
    ]);

    $signatureKey = JWKFactory::createFromKeyFile($jwtSetting['SK'], $jwtSetting['KP']);
    $encryptionKey = JWKFactory::createFromKeyFile($jwtSetting['CLIENT-1']['PK'], $jwtSetting['CLIENT-1']['KP']);

    // JWS
    $algorithmManager = new AlgorithmManager([new PS256()]);
    $jwsBuilder = new JWSBuilder($algorithmManager);
    $jwsSerializerManager = new JWSSerializerManager([new CompactSerializer_sign()]);

    // JWE
    $keyEncryptionAlgorithmManager = new AlgorithmManager([new RSAOAEP256()]);
    $contentEncryptionAlgorithmManager = new AlgorithmManager([new A256GCM()]);
    $compressionMethodManager = new CompressionMethodManager([new Deflate()]);
    $jweBuilder = new JWEBuilder(
        $keyEncryptionAlgorithmManager,
        $contentEncryptionAlgorithmManager,
        $compressionMethodManager
    );
    $jweSerializerManager = new JWESerializerManager([new CompactSerializer_en()]); // Compact Serialization Mode

    // Nested JWT
    $nestedTokenBuilder = new NestedTokenBuilder(
        $jweBuilder,
        $jweSerializerManager,
        $jwsBuilder,
        $jwsSerializerManager
    );
    $encodedToken = $nestedTokenBuilder->create(
        // The payload to protect
        $payload,
        // A list of signatures. (correspond to the list of recipients)
        [
            [
                'key' => $signatureKey, // The key used to sign (mandatory)
                // At least one of 'protected_header' or 'header' has to be set
                'protected_header' => [
                'alg' => 'PS256', // alg for sign
                'crit' => ['alg'] // critical header extension, let client to check.
                ]
            ]
        ],
        // The serialization mode for the JWS.
        'jws_compact',
        // The shared protected header for JWE info. (optional)
        [
            'alg' => 'RSA-OAEP-256', // Key Encryption Algorithm
            'enc' => 'A256GCM' // Content Encryption Algorithm
        ],
        // The shared unprotected header. (optional)
        [],
        // A list of recipients (a shared key or public key) for JWE.
        [
            [
                'key' => $encryptionKey // The recipient key. (mandatory)
            ]
        ],
        // The serialization mode for the JWE.
        'jwe_compact'
    );

    // test decode
    $result = decodePayloadFromNestedJWT($encodedToken);

    return [$encodedToken, $result];
}

/**
 * Test: simulate client to decode Nested JWT.
 *
 * @param string $encodedToken
 * @return array
 * @throws \Jose\Component\Checker\InvalidClaimException
 * @throws \Jose\Component\Checker\MissingMandatoryClaimException
 */
function decodePayloadFromNestedJWT(string $encodedToken): array
{
    $jwtSetting = getJWTSetting();

    $signatureKeySet = new JWKSet([JWKFactory::createFromKeyFile($jwtSetting['PK'], $jwtSetting['KP'])]);
    $encryptionKeySet = new JWKSet([JWKFactory::createFromKeyFile(
        $jwtSetting['CLIENT-1']['SK'],
        $jwtSetting['CLIENT-1']['KP']
    )]);

    // JWE
    $keyEncryptionAlgorithmManager = new AlgorithmManager([new RSAOAEP256()]);
    $contentEncryptionAlgorithmManager = new AlgorithmManager([new A256GCM()]);
    $compressionMethodManager = new CompressionMethodManager([new Deflate()]);
    $jweSerializerManager = new JWESerializerManager([new CompactSerializer_en()]);
    $jweDecrypter = new JWEDecrypter(
        $keyEncryptionAlgorithmManager,
        $contentEncryptionAlgorithmManager,
        $compressionMethodManager
    );
    $jweLoader = new JWELoader(
        $jweSerializerManager,
        $jweDecrypter,
        null
    );

    // JWS
    $algorithmManager = new AlgorithmManager([new PS256()]);
    $jwsVerifier = new JWSVerifier($algorithmManager);
    $jwsSerializerManager = new JWSSerializerManager([new CompactSerializer_sign()]);
    $jwsLoader = new JWSLoader(
        $jwsSerializerManager,
        $jwsVerifier,
        null
    );

    // === Nested JWT ===
    $nestedTokenLoader = new NestedTokenLoader($jweLoader, $jwsLoader);

    // 1. check jwe header
    $headerCheckerManager_JWE = new HeaderCheckerManager(
        [new AlgorithmChecker(['RSA-OAEP-256'])],
        [new JWETokenSupport()]
    );
    $serializer = new CompactSerializer_en();
    $headerCheckerManager_JWE->check($serializer->unserialize($encodedToken, 0), 0, ['alg', 'enc']);

    // 2. load jws (will decrypt jew -> verify jws)
    $jws = $nestedTokenLoader->load($encodedToken, $encryptionKeySet, $signatureKeySet, $signature);

    // 3. check jws header
    $headerCheckerManager_JWS = new HeaderCheckerManager(
        [new AlgorithmChecker(['PS256'])],
        [new JWSTokenSupport()]
    );
    $headerCheckerManager_JWS->check($jws, 0, ['crit']);

    // 4. check jws claim
    $claimCheckerManager = new ClaimCheckerManager([
        new Checker\IssuerChecker(['JCompany']),
        new Checker\IssuedAtChecker(),
        new Checker\ExpirationTimeChecker()
        // new Checker\AudienceChecker('client-1') // TODO: get data from database
    ]);
    $claims = json_decode($jws->getPayload(), true);
    $claimCheckerManager->check($claims, ['iat', 'iss', 'exp', 'aud', 'data']);

    return json_decode($jws->getPayload(), true);
}

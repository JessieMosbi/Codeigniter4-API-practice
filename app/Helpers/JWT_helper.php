<?php

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

// Checker: 若欄位存在，則 check 是否符合格式、指定的值
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

// Generate a JWS (signed JWT) for an authenticated user
function getSignedJWTForUser(): array
{
  // TODO: validate Client
  // $clientModel = new ClientModel();
  // $clientInfo = $clientModel->findClientByEmailAddress($emailAddress);

  $jwtSetting = getJWTSetting();
  $jwk = JWKFactory::createFromKeyFile($jwtSetting['SK'], $jwtSetting['KP']);
  $iat = time();
  $exp = $iat + $jwtSetting['TTL'];
  $jti = (Uuid::uuid4())->toString(); // uuid 4: random

  $payload = json_encode([
    'jti' => $jti,
    'iss' => $jwtSetting['ISSUER'],
    'iat' => $iat,
    'exp' => $exp,
    'aud' => 'client-1' // TODO: get name from db
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

// Get JWS (signed JWT) from Authentication header (format: Bearer XXXXXXXXX)
function getJWTFromRequest($authenticationHeader): string
{
  if (is_null($authenticationHeader)) {
    throw new Exception('Missing or invalid JWT in request');
  }
  return explode(' ', $authenticationHeader)[1];
}

// Decodes JWS (signed JWT) and validate it
function validateJWTFromRequest(string $encodedToken): bool
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
  if (!$jwsVerifier->verifyWithKey($jws, $jwk, 0)) return false;

  // Check payload
  // $model = new ClientModel();
  // $clientInfo = $model->findClientByIPAddress($ipAddress);
  $claimCheckerManager = new ClaimCheckerManager(
    [
      new Checker\IssuerChecker([$jwtSetting['ISSUER']]),
      new Checker\IssuedAtChecker(),
      new Checker\ExpirationTimeChecker(),
      new Checker\AudienceChecker('client-1') // TODO: get value from DB correspond by request ip
    ]
  );
  $claims = json_decode($jws->getPayload(), true);
  $claimCheckerManager->check($claims, ['jti', 'iss', 'iat', 'exp', 'aud']);

  return true;
}

function getJWTSetting(): array
{
  return [
    "KP" => getenv('KEY_FILE_PASSWORD'),
    "PK" => getenv('PUBLIC_KEY_FILE'),
    "SK" => getenv('PRIVATE_KEY_FILE'),
    "ISSUER" => getenv('JWT_ISSUER'),
    "TTL" => getenv('JWT_TIME_TO_LIVE')
  ];
}

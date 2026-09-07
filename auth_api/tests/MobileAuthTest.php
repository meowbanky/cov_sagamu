<?php
/**
 * Token-identity guarantees for the API.
 *
 * Run: php tests/MobileAuthTest.php
 *
 * These cover the rules that stop one caller reading another person's records:
 * a member token must not open staff endpoints, a staff token must not open
 * member endpoints, and an absent or unsigned token must open nothing.
 */

require_once __DIR__ . '/../utils/MobileAuth.php';
require_once __DIR__ . '/../utils/ProfileFields.php';

$passed = 0;
$failed = 0;

function check($name, callable $fn)
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "  PASS  $name\n";
    } catch (Throwable $e) {
        $failed++;
        echo "  FAIL  $name -- " . $e->getMessage() . "\n";
    }
}

function assertTrue($cond, $msg = 'expected true')
{
    if (!$cond) {
        throw new Exception($msg);
    }
}

function assertSame($expected, $actual)
{
    if ($expected !== $actual) {
        throw new Exception("expected " . var_export($expected, true) . ", got " . var_export($actual, true));
    }
}

/** Presents a token to MobileAuth the way a real request would. */
function withToken($token, callable $fn)
{
    $previous = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
    $_SERVER['HTTP_AUTHORIZATION'] = $token === null ? '' : 'Bearer ' . $token;
    try {
        return $fn();
    } finally {
        if ($previous === null) {
            unset($_SERVER['HTTP_AUTHORIZATION']);
        } else {
            $_SERVER['HTTP_AUTHORIZATION'] = $previous;
        }
    }
}

function rejects($token, $method)
{
    return withToken($token, function () use ($method) {
        try {
            MobileAuth::$method();
        } catch (Exception $e) {
            return $e->getCode() === 401;
        }
        return false;
    });
}

$jwt = new JWTHandler();

$memberToken = $jwt->generateToken('387', MobileAuth::TYPE_MEMBER);
$staffToken  = $jwt->generateToken('42', MobileAuth::TYPE_STAFF);

echo "MobileAuth\n";

check('member token yields its own member id', function () use ($memberToken) {
    assertSame('387', withToken($memberToken, function () {
        return MobileAuth::requireMemberId();
    }));
});

check('staff token yields its own staff id', function () use ($staffToken) {
    assertSame('42', withToken($staffToken, function () {
        return MobileAuth::requireStaffId();
    }));
});

check('member token is rejected by staff endpoints', function () use ($memberToken) {
    assertTrue(rejects($memberToken, 'requireStaffId'));
});

check('staff token is rejected by member endpoints', function () use ($staffToken) {
    assertTrue(rejects($staffToken, 'requireMemberId'));
});

check('a missing token is rejected everywhere', function () {
    assertTrue(rejects(null, 'requireMemberId'));
    assertTrue(rejects(null, 'requireStaffId'));
});

check('a tampered signature is rejected', function () use ($memberToken) {
    assertTrue(rejects($memberToken . 'x', 'requireMemberId'));
});

check('a forged unsigned token is rejected', function () {
    $forge = function ($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    };
    $header  = $forge(json_encode(['typ' => 'JWT', 'alg' => 'none']));
    $payload = $forge(json_encode(['user_id' => '1', 'typ' => 'staff', 'exp' => time() + 3600]));
    assertTrue(rejects("$header.$payload.", 'requireStaffId'));
});

check('an expired token is rejected', function () {
    $jwt = new JWTHandler();
    $token = $jwt->generateToken('387', MobileAuth::TYPE_MEMBER);
    // Rebuild the payload in the past, keeping the original signature.
    [$h, , $s] = explode('.', $token);
    $stale = rtrim(strtr(base64_encode(json_encode([
        'user_id' => '387', 'typ' => 'member', 'iat' => time() - 7200, 'exp' => time() - 3600,
    ])), '+/', '-_'), '=');
    assertTrue(rejects("$h.$stale.$s", 'requireMemberId'));
});

check('legacy tokens without a type still work for members', function () {
    // Deploying the type claim must not sign out the mobile app mid-session.
    $jwt = new JWTHandler();
    $reflection = new ReflectionClass($jwt);
    $encode = $reflection->getMethod('encodeToken');
    $legacy = $encode->invoke($jwt, ['user_id' => '387', 'iat' => time(), 'exp' => time() + 3600]);

    assertSame('387', withToken($legacy, function () {
        return MobileAuth::requireMemberId();
    }));
});

check('legacy tokens without a type are refused by staff endpoints', function () {
    $jwt = new JWTHandler();
    $reflection = new ReflectionClass($jwt);
    $encode = $reflection->getMethod('encodeToken');
    $legacy = $encode->invoke($jwt, ['user_id' => '42', 'iat' => time(), 'exp' => time() + 3600]);

    assertTrue(rejects($legacy, 'requireStaffId'));
});

echo "\nProfileFields\n";

check('personal fields are editable', function () {
    assertTrue(ProfileFields::isEditable('EMAIL'));
    assertTrue(ProfileFields::isEditable('MOBILE_NO'));
});

check('payroll and grade fields are not editable', function () {
    foreach (['LEVE_APT', 'EMPDATE', 'DEPTCD', 'PPNO', 'DOPA', 'DOC'] as $field) {
        assertTrue(!ProfileFields::isEditable($field), "$field should be rejected");
    }
});

check('SQL fragments are rejected as field names', function () {
    foreach ([
        'EMAIL, PASSWORD',
        '(SELECT password FROM master_staff LIMIT 1)',
        'NAME FROM employee WHERE 1=1 -- ',
        '*',
        1234,
        null,
    ] as $injection) {
        assertTrue(!ProfileFields::isEditable($injection), 'should reject: ' . var_export($injection, true));
    }
});

check('assertEditable throws a 400 on a bad field', function () {
    try {
        ProfileFields::assertEditable('LEVE_APT');
    } catch (Exception $e) {
        assertSame(400, $e->getCode());
        return;
    }
    throw new Exception('no exception thrown');
});

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);

<?php

declare(strict_types=1);

namespace MirazMac\DotEnv\Tests;

use Dotenv\Dotenv;
use MirazMac\DotEnv\Writer;
use PHPUnit\Framework\TestCase;

/**
 * WriterTest
 */
final class WriterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/.env.example', __DIR__ . '/.env');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unlink(__DIR__.'/.env');
    }

    /**
     * Tests setting of values
     */
    public function testEnvSetValues(): void
    {
        $env_vars = [
            'APP_NAME' => '',
            'APP_ENV' => '',
            'APP_KEY' => '',
            'APP_DEBUG' => '',
            'APP_URL' => '',
            'LOG_CHANNEL' => '',
            'DB_CONNECTION' => '',
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'BROADCAST_DRIVER' => '',
            'CACHE_DRIVER' => '',
            'QUEUE_CONNECTION' => '',
            'SESSION_DRIVER' => '',
            'SESSION_LIFETIME' => '',
            'REDIS_HOST' => '',
            'REDIS_PASSWORD' => '',
            'REDIS_PORT' => '',
            'MAIL_MAILER' => '',
            'MAIL_HOST' => '',
            'MAIL_PORT' => '',
            'MAIL_USERNAME' => 'NULL',
            'MAIL_PASSWORD' => '123456',
            'MAIL_ENCRYPTION' => 'false',
            'MAIL_FROM_ADDRESS' => '',
            'MAIL_FROM_NAME' => '',
            'AWS_ACCESS_KEY_ID' => '',
            'AWS_SECRET_ACCESS_KEY' => '',
            'AWS_DEFAULT_REGION' => '',
            'AWS_BUCKET' => 'উইনিকোড',
            'PUSHER_APP_ID' => '',
            'PUSHER_APP_KEY' => '',
            'PUSHER_APP_SECRET' => '',
            'PUSHER_APP_CLUSTER' => '',
            'MIX_PUSHER_APP_KEY' => '',
            'MIX_PUSHER_APP_CLUSTER' => '',
            'dummy_variable' => '',
            'NEW_VARIABLE' => 'more_value'
        ];

        foreach ($env_vars as $key => &$val) {
            if ($val === '') {
                $val = base64_encode(random_bytes(rand(10, 50)));
            }
        }

        $writer = new Writer(__DIR__ . '/.env');

        foreach ($env_vars as $key => $value) {
            $writer->set($key, $value);
        }

        // Write the
        $this->assertTrue($writer->write());

        // Now that we have written the file, load is via dotenv parser
        $parsed = Dotenv::parse(file_get_contents(__DIR__ . '/.env'));

        // Make sure the values are same
        foreach ($parsed as $key => $value) {
            $this->assertSame($value, $env_vars[$key]);
        }
    }

    /**
     * Test seting of a new value with invalid key name
     */
    public function testSetInvalidKey() : void
    {
        $writer = new Writer(__DIR__ . '/.env');

        $this->expectException('\InvalidArgumentException');
        $writer->set('INVALID KEY', 'value');
    }

    /**
     * Set deleting a value
     */
    public function testDeleteValue() : void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $writer->delete('APP_NAME')->write();

        // Now that we have written the file, load is via dotenv parser
        $parsed = Dotenv::parse(file_get_contents(__DIR__ . '/.env'));

        // and check if indeed the value was deleted or not
        $this->assertFalse(array_key_exists('APP_NAME', $parsed));
    }

    /**
     * Tests constructing new instance
     */
    public function testIntialize(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $this->assertSame(file_get_contents(__DIR__ . '/.env'), $writer->getContent());
    }

    /**
     * Tests constructing new instance when the file is missing
     */
    public function testMissingFile(): void
    {
        $this->expectException('\LogicException');
        $writer = new Writer(__DIR__ . '/.envx');
    }

    /**
     * Tests URLs
     */
    public function testUrls(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $url = 'https://mywebsite.com';

        $writer->set('APP_URL', $url)
        ->write();

        // Now that we have written the file, load is via dotenv parser
        $parsed = Dotenv::parse(file_get_contents(__DIR__ . '/.env'));

        $this->assertSame($url, $parsed['APP_URL']);
    }
}

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
        copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unlink(__DIR__ . '/.env');
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
    public function testSetInvalidKey(): void
    {
        $writer = new Writer(__DIR__ . '/.env');

        $this->expectException('\InvalidArgumentException');
        $writer->set('INVALID KEY', 'value');
    }

    /**
     * Set deleting a value
     */
    public function testDeleteValue(): void
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
     * Tests write env file without providing source file neither dest file
     */
    public function testMissingFile(): void
    {
        $this->expectException('\LogicException');
        (new Writer())->write();
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

    /**
     * Test not provide source file
     */
    public function testNotProvideSourceFile(): void
    {
        $writer = new Writer();
        $url = 'https://mywebsite.com';

        $writer->set('APP_URL', $url);

        $parsed = Dotenv::parse($writer->getContent());

        $this->assertSame($url, $parsed['APP_URL']);
    }

    /**
     * Test write in custom path
     */
    public function testWriteCustomPath(): void
    {
        $writer = new Writer();
        $url = 'https://mywebsite.com';

        $writer
            ->set('APP_URL', $url)
            ->write(false, __DIR__ . '/.env');

        // Now that we have written the file, load is via dotenv parser
        $parsed = Dotenv::parse(file_get_contents(__DIR__ . '/.env'));

        $this->assertSame($url, $parsed['APP_URL']);
    }

    /**
     * Test basic rename preserves value and removes old key
     */
    public function testRenameBasic(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $original = $writer->get('APP_NAME');
        $writer->rename('APP_NAME', 'APPLICATION_NAME')->write();

        $parsed = Dotenv::parse(file_get_contents(__DIR__ . '/.env'));

        $this->assertSame($original, $parsed['APPLICATION_NAME']);
        $this->assertArrayNotHasKey('APP_NAME', $parsed);
    }

    /**
     * Test rename preserves position in file
     */
    public function testRenamePreservesPosition(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $contentBefore = $writer->getContent();

        // APP_NAME is the first key - after rename it should still be first
        $writer->rename('APP_NAME', 'APPLICATION_NAME');
        $content = $writer->getContent();

        // APPLICATION_NAME should appear before APP_ENV
        $this->assertLessThan(
            strpos($content, 'APP_ENV='),
            strpos($content, 'APPLICATION_NAME=')
        );
    }

    /**
     * Test rename preserves quoted values
     */
    public function testRenamePreservesQuotedValue(): void
    {
        $envContent = "QUOTED_VAR=\"hello world\"\nOTHER=value\n";
        file_put_contents(__DIR__ . '/.env', $envContent);

        $writer = new Writer(__DIR__ . '/.env');
        $writer->rename('QUOTED_VAR', 'RENAMED_VAR');

        $this->assertStringContainsString('RENAMED_VAR="hello world"', $writer->getContent());
        $this->assertStringNotContainsString('QUOTED_VAR=', $writer->getContent());
    }

    /**
     * Test rename is chainable
     */
    public function testRenameIsChainable(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $result = $writer->rename('APP_NAME', 'APPLICATION_NAME');

        $this->assertSame($writer, $result);
    }

    /**
     * Test rename sets changed flag
     */
    public function testRenameSetsChanged(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $this->assertFalse($writer->hasChanged());

        $writer->rename('APP_NAME', 'APPLICATION_NAME');
        $this->assertTrue($writer->hasChanged());
    }

    /**
     * Test rename throws on missing old key
     */
    public function testRenameThrowsOnMissingOldKey(): void
    {
        $writer = new Writer(__DIR__ . '/.env');

        $this->expectException(\InvalidArgumentException::class);
        $writer->rename('NONEXISTENT_KEY', 'NEW_KEY');
    }

    /**
     * Test rename throws on existing new key
     */
    public function testRenameThrowsOnExistingNewKey(): void
    {
        $writer = new Writer(__DIR__ . '/.env');

        $this->expectException(\InvalidArgumentException::class);
        $writer->rename('APP_NAME', 'APP_ENV');
    }

    /**
     * Test rename throws on invalid key names
     */
    public function testRenameThrowsOnInvalidKeyNames(): void
    {
        $writer = new Writer(__DIR__ . '/.env');

        $this->expectException(\InvalidArgumentException::class);
        $writer->rename('INVALID KEY', 'NEW_KEY');
    }

    /**
     * Test rename with same key is a no-op
     */
    public function testRenameSameKeyNoOp(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $contentBefore = $writer->getContent();

        $writer->rename('APP_NAME', 'APP_NAME');

        $this->assertFalse($writer->hasChanged());
        $this->assertSame($contentBefore, $writer->getContent());
    }

    /**
     * Test rename chained with other operations
     */
    public function testRenameChainedWithOtherOps(): void
    {
        $writer = new Writer(__DIR__ . '/.env');
        $writer
            ->rename('APP_NAME', 'APPLICATION_NAME')
            ->set('NEW_VAR', 'new_value')
            ->delete('APP_DEBUG')
            ->write();

        $parsed = Dotenv::parse(file_get_contents(__DIR__ . '/.env'));

        $this->assertArrayHasKey('APPLICATION_NAME', $parsed);
        $this->assertArrayNotHasKey('APP_NAME', $parsed);
        $this->assertSame('new_value', $parsed['NEW_VAR']);
        $this->assertArrayNotHasKey('APP_DEBUG', $parsed);
    }

    private function normalizeLineEndings(string $text): string
    {
        return str_replace(["\r\n", "\r"], "\n", $text);
    }



    /**
     * Test multi-line value parsing
     */
    public function testMultiLineValues(): void
    {
        // Create a temporary .env file with multi-line values
        $envContent = <<<'EOT'
SINGLE_LINE="normal value"
MULTI_LINE_DOUBLE="first line
second line
third line"
MULTI_LINE_SINGLE='first line
second line
third line'
WITH_COMMENT="multi line
with comment" # this is a comment
AFTER_MULTILINE=simple_value

EOT;

        file_put_contents(__DIR__ . '/.env', $envContent);

        $writer = new Writer(__DIR__ . '/.env');

        // Test that values are parsed correctly
        $this->assertSame('normal value', $this->normalizeLineEndings($writer->get('SINGLE_LINE')));
        $this->assertSame("first line\nsecond line\nthird line", $this->normalizeLineEndings($writer->get('MULTI_LINE_DOUBLE')), 'MULTI_LINE_DOUBLE');
        $this->assertSame("first line\nsecond line\nthird line", $this->normalizeLineEndings($writer->get('MULTI_LINE_SINGLE')), 'MULTI_LINE_SINGLE');
        $this->assertSame("multi line\nwith comment",$this->normalizeLineEndings($writer->get('WITH_COMMENT')));
        $this->assertSame('simple_value', $this->normalizeLineEndings($writer->get('AFTER_MULTILINE')));

        // Test that we can write back the values
        $writer->write(true);

        // Read the file again to ensure values were preserved
        $newWriter = new Writer(__DIR__ . '/.env');
        $this->assertSame($writer->getAll(), $newWriter->getAll());
    }
}

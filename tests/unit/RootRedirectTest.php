<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RootRedirectTest extends CIUnitTestCase
{
    public function testRootIndexPhpExists(): void
    {
        $indexPath = ROOTPATH . 'index.php';
        $this->assertFileExists($indexPath, 'Root index.php must exist at project root');
    }

    public function testRootIndexPhpContains302Redirect(): void
    {
        $indexPath = ROOTPATH . 'index.php';
        $this->assertFileExists($indexPath);

        $content = file_get_contents($indexPath);

        $this->assertStringContainsString(
            'header',
            $content,
            'index.php must contain a header() call',
        );

        $this->assertStringContainsString(
            'Location',
            $content,
            'index.php must contain a Location header',
        );

        $this->assertStringContainsString(
            'public/app.html',
            $content,
            'index.php must redirect to public/app.html',
        );

        $this->assertStringContainsString(
            '302',
            $content,
            'index.php must use HTTP 302 Temporary Redirect',
        );
    }

    public function testRootIndexPhpDoesNotContainEchoOrOutput(): void
    {
        $indexPath = ROOTPATH . 'index.php';
        $this->assertFileExists($indexPath);

        $content = file_get_contents($indexPath);

        $this->assertStringNotContainsString(
            'echo',
            $content,
            'index.php should not output anything before redirect',
        );
    }

    public function testCodeIgniterIndexPhpUntouched(): void
    {
        $ciIndexPath = FCPATH . 'index.php';
        $this->assertFileExists($ciIndexPath, 'public/index.php must still exist');

        $content = file_get_contents($ciIndexPath);
        $this->assertStringContainsString('bootWeb', $content, 'CI entry point must remain intact');
    }
}

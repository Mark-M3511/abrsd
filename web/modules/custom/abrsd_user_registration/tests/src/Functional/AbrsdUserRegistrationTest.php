<?php

namespace Drupal\Tests\abrsd_user_registration\Functional;

use Drupal\Tests\BrowserTestBase;

class AbrsdUserRegistrationTest extends BrowserTestBase
{

    /**
     * Modules to enable.
     *
     * @var array
     */
    public static $modules = ['node', 'abrsd_user_registration'];

    /**
     * A user with the 'administer site configuration' permission.
     *
     * @var \Drupal\user\UserInterface
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Add your custom setup code here.

        // Create user.
        $this->user = $this->drupalCreateUser(['administer site configuration']);

        // Log in user.
        $this->drupalLogin($this->user);
    }

    /**
     * Tests that the 'your_module' page loads with a 200 response.
     */
    public function testYourModulePage()
    {
        $this->drupalGet('user/profile');
        $this->assertSession()->statusCodeEquals(200);
    }
}

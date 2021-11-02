<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Laravel\Dusk\Browser;
use MicroweberPackages\User\Models\PasswordReset;
use Tests\DuskTestCase;

class AdminForgotPassowrdFormTest extends DuskTestCase
{
    public $siteUrl = 'http://127.0.0.1:8000/';

    public function testSubmitEmail()
    {
        $data = [];
        $data['option_value'] = 'y';
        $data['option_key'] = 'captcha_disabled';
        $data['option_group'] = 'users';
        save_option($data);

        $siteUrl = $this->siteUrl;

        $this->browse(function (Browser $browser) use($siteUrl) {

            $browser->visit($siteUrl . 'admin/login');
            $browser->pause('2000');

            $browser->click('@forgot-password-link');
            $browser->pause('2000');

            $browser->type('username', 'bobi@microweber.com');
            $browser->click('@reset-password-button');
            $browser->pause('2000');

            $browser->waitForText('We have emailed your password reset link');
            $browser->assertSee('We have emailed your password reset link');
        });
    }

    public function testSubmitWrongEmail()
    {
        $siteUrl = $this->siteUrl;

        $this->browse(function (Browser $browser) use($siteUrl) {

            $browser->visit($siteUrl . 'admin/login');
            $browser->pause('2000');

            $browser->click('@forgot-password-link');
            $browser->pause('2000');

            $browser->type('username', 'wrong-email@microweber.com');
            $browser->click('@reset-password-button');
            $browser->pause('2000');

            $browser->waitForText('We can\'t find a user with that email address');
            $browser->assertSee('We can\'t find a user with that email address');
        });
    }


    public function testCaptchaValidation()
    {
        $data = [];
        $data['option_value'] = 'n';
        $data['option_key'] = 'captcha_disabled';
        $data['option_group'] = 'users';
        save_option($data);

        $siteUrl = $this->siteUrl;

        $this->browse(function (Browser $browser) use($siteUrl) {

            $sendTime = Carbon::now();

            $browser->visit($siteUrl . 'admin/login');
            $browser->pause('2000');

            $browser->click('@forgot-password-link');
            $browser->pause('2000');

            $browser->type('username', 'bobi@microweber.com');
            $browser->click('@reset-password-button');
            $browser->pause('2000');

            $browser->waitForText('Invalid captcha answer');
            $browser->assertSee('Invalid captcha answer');

            $findPasswordReset = PasswordReset::where('email', 'bobi@microweber.com')->first();
            $this->assertNotEmpty($findPasswordReset);
            $this->assertTrue($sendTime > $findPasswordReset->created_at);

        });
    }
}
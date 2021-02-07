<?php namespace App\Tests;
use App\Tests\AcceptanceTester;

class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/login');
        $I->see('Username');
        $I->fillField('username', 'lisamichelle@gmail.com');
        $I->click("LOG IN");
        $I->see('Annie');
        $I->click('Annie');
        $I->pause();
        $I->see('Blends');
        $I->click('.active');
        $I->see('suffix blends');
        $I->click('a[href~=suffix_blends');
        $I->see('#practice-tab');
        $I->click('#practice-tab');
        $I->see(".tic-tac-toe");
        $I->click(".tic-tac-toe");
        $I->see("New Game");
        $I->click("#cboxClose");
    }
}

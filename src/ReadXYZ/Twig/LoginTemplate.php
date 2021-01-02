<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\Page;

class LoginTemplate
{

    const USERNAME        = 'username';

    private Page $page;

    public function __construct(string $errorMessage = '')
    {
        $this->page = new Page('ReadXYZ Login');
        if ($errorMessage) {
            $this->page->addError($errorMessage);
        }
    }

    /**
     * @param string $errorMessage
     * @throws PhonicsException
     */
    public function display(string $errorMessage = ''): void
    {
        $userObject = Session::getUserObject();
        $this->page->addError($errorMessage);
        $this->page->addArgument(self::USERNAME, Session::getUserName());

        $this->page->display('login');
    }

}

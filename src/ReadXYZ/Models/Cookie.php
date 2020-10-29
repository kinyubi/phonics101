<?php

namespace App\ReadXYZ\Models;

use App\ReadXYZ\Helpers\Util;
use RuntimeException;

class Cookie
{
    private static int $COOKIE_DURATION = 60 * 60 * 24 * 60; // 2 months
    private string $username;
    private string $studentId;
    private string $sessionId;
    private string $currentLesson;
    private string $currentTab;
    private array  $listIndexes;
    private int $status;

    public function __construct()
    {
        Util::sessionContinue();
        if (not(isset($_COOKIE['readXYZ_user']))) {
            $this->clearCookie();
        } else {
            $json = $_COOKIE['readXYZ_user'];
            $cookie = json_decode($json);
            $this->username = $cookie->username ?? '';
            $this->studentId = $cookie->studentId ?? '';
            $this->currentLesson = $cookie->currentLesson ?? '';
            $this->currentTab = $cookie->currentTab ?? '';
            $this->sessionId = $cookie->sessionId ?? '';
            if (isset($cookei->listIndexes)) {
                $this->listIndexes = $cookie->listIndexes;
            } else {
                $this->clearIndexes();
            }
            $this->updateStatus();
        }
    }


    // private function clearCookie(): void
    // {
    //     $identity = Identity::getInstance();
    //     $validUser = $identity->isValidUser();
    //     $this->username = $validUser ? $identity->getUserName() : '';
    //     $this->studentId = $validUser ? $identity->getStudentId() : '';
    //     $this->sessionId = $validUser ? $identity->getSessionId() : '';
    //     $this->currentTab = '';
    //     if ($this->username and $this->studentId) {
    //         $this->currentLesson = Lessons::getInstance()->getNextLessonName(); //gets the first lesson
    //     } else {
    //         $this->currentLesson = '';
    //     }
    //     $this->clearIndexes();
    //     $this->setCookie();
    // }

    private function clearCookie(): void
    {
        Identity::getInstance()->clearIdentity();
        $this->username =  '';
        $this->studentId = '';
        $this->sessionId = '';
        $this->currentTab = '';
        $this->currentLesson = '';
        $this->clearIndexes();
        $this->setCookie();
    }

    public function getCookieString(): string
    {
        return $_COOKIE['readXYZ_user'];
    }

    private function clearIndexes(): void
    {
        $this->listIndexes = [
            'intro' => 0,
            'write' => 0,
            'practice' => 0,
            'spell' => 0,
            'mastery' => 0,
            'fluency' => 0,
            'test' => 0
        ];
    }

    private function setCookie(): void
    {
        $cookie = [
            'username' => $this->username,
            'studentId' => $this->studentId,
            'sessionId' => $this->sessionId,
            'currentLesson' => $this->currentLesson,
            'currentTab' => $this->currentTab,
            'listIndexes' => $this->listIndexes
        ];
        $this->updateStatus();
        if (Util::testingInProgress()) return;

        $json = json_encode($cookie);
        setcookie('readXYZ_user', $json, time() + self::$COOKIE_DURATION,'/');
    }

    public function getListIndex(string $tabName): int
    {
        $realTabName = Util::fixTabName($tabName);
        $index = $this->listIndexes[$realTabName] ?? -1;
        if (-1 == $index) {
            error_log("Unable to find list index for $tabName tab");
            $index = 0;
        }

        return $index;
    }

    public function updateListIndex(string $tabName): void
    {
        $realTabName = Util::fixTabName($tabName);
        $index = $this->listIndexes[$realTabName];
        $index = ($index + 1) % 3;
        $this->listIndexes[$realTabName] = $index;
        $this->setCookie();
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function updateStatus(): int
    {
        if ($this->username) {
            if ($this->studentId) {
                if ($this->currentLesson) {
                    if ($this->currentTab) {
                        $this->status = 4;
                    } else {
                        $this->status = 3;
                    }
                } else {
                    $this->status = 2;
                }
            } else {
                $this->status = 1;
            }
        } else {
            $this->status = 0;
        }

        return $this->status;
    }

    public function clearUsername(): Cookie
    {
    }

    /**
     * @param string $username
     *
     * @return Cookie
     */
    public function setUsername(string $username): Cookie
    {
        $this->username = $username;
        $this->studentId = '';
        $this->sessionId = '';
        $this->currentLesson = '';
        $this->setCookie();

        return $this;
    }

    /**
     * @param string $studentId
     * @param string $sessionId
     *
     * @return Cookie
     */
    public function setStudentId(string $studentId, string $sessionId): Cookie
    {
        if (empty($this->username)) {
            throw new RuntimeException('StudentId cannot be set when username is unset.');
        }
        if (($this->studentId != $studentId) or ($this->sessionId != $sessionId)) {
            // only change current lesson if not the same student
            if ($this->studentId != $studentId) {
                $this->currentLesson = '';
            }
            $this->studentId = $studentId;
            $this->sessionId = $sessionId;
            $this->setCookie();
        }

        return $this;
    }

    /**
     * @param string $currentLesson
     *
     * @return Cookie
     */
    public function setCurrentLesson(string $currentLesson): Cookie
    {
        if (empty($this->username) or empty($this->studentId)) {
            throw new RuntimeException('Lesson cannot be set when username or student is unset.');
        }
        $this->currentLesson = $currentLesson;
        $this->currentTab = ''; // if we change lessons we don't know the tab we are on
        $this->setCookie();

        return $this;
    }

    /**
     * @param string $currentTab
     * @return Cookie
     */
    public function setCurrentTab(string $currentTab): Cookie
    {
        if (empty($this->username) or empty($this->studentId)) {
            throw new RuntimeException('Lesson cannot be set when username or student is unset.');
        }
        $this->currentTab = $currentTab;
        $this->setCookie();

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getStudentId(): string
    {
        return $this->studentId;
    }

    /**
     * @return string
     */
    public function getCurrentLesson(): string
    {
        return $this->currentLesson;
    }

    /**
     * @return string
     */
    public function getCurrentTab(): string
    {
        return $this->currentTab;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * attempts to continue session. You cannot access $_SESSION variables unless you have done a session start.
     *
     * @see https://stackoverflow.com/questions/31367464/session-variables-not-working-php
     *
     * @return bool
     */
    public function tryContinueSession(): bool
    {
        Util::sessionContinue();
        $identity = Identity::getInstance();
        $identity->validateSignin($this->username, 'xx');
        if ($identity->isValidUser() && not(empty($this->studentId))) {
            $identity->setStudent($this->studentId);
            $identity->savePersistentState();
            return true;
        }

        return false;
    }
}

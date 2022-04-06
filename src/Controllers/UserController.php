<?php

namespace Mvc\Controllers;

use Config\Controller;
use JetBrains\PhpStorm\NoReturn;
use Mvc\Models\UserModel;

class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        parent::__construct();
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname']) && isset($_POST['email']) && isset($_POST['password']))
        {
            $doesExit = $this->userModel->doesExist($_POST['nickname'], $_POST['email']);

            if ($doesExit === false) {
                $this->userModel->createUser($_POST['nickname'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT));

                header('Location: /login');
                exit;
            }
        }

        echo $this->twig->render('User/register.html.twig');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']))
        {
            $user = $this->userModel->findOneByEmail($_POST['email']);

            if ($user && password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nickname' => $user['nickname'],
                    'email' => $user['email']
                ];

                header('Location: /');
                exit;
            }
        }
        echo $this->twig->render('User/login.html.twig');
    }


    #[NoReturn] public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
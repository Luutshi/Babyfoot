<?php

namespace Mvc\Models;

use Config\Model;
use PDO;

class UserModel extends Model
{
    public function createUser(string $nickname, string $email, string $password)
    {
        $statement = $this->pdo->prepare('INSERT INTO `user`(`nickname`, `email`, `password`) VALUES (:nickname, :email, :password)');
        $statement->execute([
            'nickname' => $nickname,
            'email' => $email,
            'password' => $password
        ]);
    }

    public function findOneByEmail(string $entry)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `user` WHERE `email` = :entry OR `nickname` = :entry');
        $statement->execute([
            'entry' => $entry,
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function findOneByID(string $id)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `user` WHERE `id` = :id');
        $statement->execute([
            'id' => $id,
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function doesExist(string $nickname, string $email)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `user` WHERE `nickname` = :nickname OR `email` = :email');
        $statement->execute([
            'nickname' => $nickname,
            'email' => $email
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
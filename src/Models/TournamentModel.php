<?php

namespace Mvc\Models;

use Config\Model;
use PDO;

class TournamentModel extends Model
{
    public function createTournament(string $tournamentCreator, string $tournamentName, string $tournamentDescription)
    {
        $statement = $this->pdo->prepare('INSERT INTO `tournament`(`creator`, `name`, `description`) VALUES (:creator, :name, :description)');
        $statement->execute([
            'creator' => $tournamentCreator,
            'name' => $tournamentName,
            'description' => $tournamentDescription
        ]);
    }

    public function eachTournaments()
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament`');
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tournamentByID($tournamentID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament` WHERE `id` = :tournamentID');
        $statement->execute([
            'tournamentID' => $tournamentID
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
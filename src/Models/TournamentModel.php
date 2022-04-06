<?php

namespace Mvc\Models;

use Config\Model;
use PDO;

class TournamentModel extends Model
{
    public function createTournament(string $creator, string $name, string $description, int $numberOfTeam)
    {
        $statement = $this->pdo->prepare('INSERT INTO `tournament`(`creator`, `name`, `description`, `numberOfTeam`) VALUES (:creator, :name, :description, :numberOfTeam)');
        $statement->execute([
            'creator' => $creator,
            'name' => $name,
            'description' => $description,
            'numberOfTeam' => $numberOfTeam
        ]);
    }

    public function eachTournaments()
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament` ORDER BY id DESC');
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eachPlayers($tournamentID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament_player` WHERE `tournament_id` = :tournamentID');
        $statement->execute([
            "tournamentID" => $tournamentID
        ]);

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

    public function addPlayerToTeam(int $tournamentID, int $teamID, string $user_function, int $user_id)
    {
        $statement = $this->pdo->prepare('INSERT INTO `tournament_player`(`tournament_id`, `team`, `user_function`, `user_id`) VALUES (:tournamentID, :teamID, :user_function, :user_id)');
        $statement->execute([
            'tournamentID' => $tournamentID,
            'teamID' => $teamID,
            'user_function' => $user_function,
            'user_id' => $user_id
        ]);
    }

    public function removePlayerFromTournament(int $tournamentID, int $user_id)
    {
        $statement = $this->pdo->prepare('DELETE FROM `tournament_player` WHERE `tournament_id` = :tournamentID AND `user_id` = :user_id');
        $statement->execute([
            'tournamentID' => $tournamentID,
            'user_id' => $user_id
        ]);
    }
}
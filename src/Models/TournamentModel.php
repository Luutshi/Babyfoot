<?php

namespace Mvc\Models;

use Config\Model;
use PDO;

class TournamentModel extends Model
{
    public function createTournament(string $creator, string $name, string $description, int $numberOfTeam)
    {
        $statement = $this->pdo->prepare('INSERT INTO `tournament`(`creator`, `name`, `description`, `numberOfTeam`, `tournamentStatus`) VALUES (:creator, :name, :description, :numberOfTeam, :tournamentStatus)');
        $statement->execute([
            'creator' => $creator,
            'name' => $name,
            'description' => $description,
            'numberOfTeam' => $numberOfTeam,
            'tournamentStatus' => 'before'
        ]);
    }

    public function insertMatch(int $tournamentID, int $homeTeamID, int $awayTeamID)
    {
        $statement = $this->pdo->prepare('INSERT INTO `tournament_matches` (`tournament_id`, `homeTeam_id`, `awayTeam_id`, `home_goals`, `away_goals`, `matchStatus`) VALUES (:tournament_id, :homeTeam_id, :awayTeam_id, :home_goals, :away_goals, :matchStatus)');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'homeTeam_id' => $homeTeamID,
            'awayTeam_id' => $awayTeamID,
            'home_goals' => 0,
            'away_goals' => 0,
            'matchStatus' => 'before'
        ]);
    }

    public function changeTournamentStatus($tournamentID, $tournamentStatus)
    {
        $statement = $this->pdo->prepare('UPDATE `tournament` SET `tournamentStatus` = :tournamentStatus WHERE `id` = :id ORDER BY id DESC');
        $statement->execute([
            'id' => $tournamentID,
            'tournamentStatus' => $tournamentStatus
        ]);
    }

    public function eachTournaments(string $tournamentStatus)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament` WHERE `tournamentStatus` = :tournamentStatus ORDER BY id DESC');
        $statement->execute([
            'tournamentStatus' => $tournamentStatus
        ]);

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
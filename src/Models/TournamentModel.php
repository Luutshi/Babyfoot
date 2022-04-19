<?php

namespace Mvc\Models;

use Config\Model;
use PDO;

class TournamentModel extends Model
{
    public function createTournament(string $creator, string $name, string $description, int $numberOfTeam)
    {
        $statement = $this->pdo->prepare('INSERT INTO `tournament`(`creator`, `name`, `description`, `numberOfTeam`, `status`) VALUES (:creator, :name, :description, :numberOfTeam, :status)');
        $statement->execute([
            'creator' => $creator,
            'name' => $name,
            'description' => $description,
            'numberOfTeam' => $numberOfTeam,
            'status' => 'before'
        ]);
    }

    public function insertMatch(int $tournamentID, int $homeTeamID, int $awayTeamID)
    {
        $statement = $this->pdo->prepare('INSERT INTO `match` (`tournament_id`, `homeTeam_id`, `awayTeam_id`, `home_goals`, `away_goals`, `status`) VALUES (:tournament_id, :homeTeam_id, :awayTeam_id, :home_goals, :away_goals, :status)');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'homeTeam_id' => $homeTeamID,
            'awayTeam_id' => $awayTeamID,
            'home_goals' => 0,
            'away_goals' => 0,
            'status' => 'before'
        ]);
    }

    public function changeTournamentStatus($tournamentID, $status)
    {
        $statement = $this->pdo->prepare('UPDATE `tournament` SET `status` = :status WHERE `id` = :id');
        $statement->execute([
            'id' => $tournamentID,
            'status' => $status
        ]);
    }

    public function eachMatches($tournamentID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `match` WHERE `tournament_id` = :tournament_id');
        $statement->execute([
            'tournament_id' => $tournamentID
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function matchByTournamentHomeAwayID($tournamentID, $homeID, $awayID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `match` WHERE `tournament_id` = :tournament_id AND `homeTeam_id` = :homeTeam_id AND `awayTeam_id` = :awayTeam_id');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'homeTeam_id' => $homeID,
            'awayTeam_id' => $awayID
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function changeMatchStatus($tournamentID, $homeID, $awayID, $status){
        $statement = $this->pdo->prepare('UPDATE `match` SET `status` = :status WHERE `tournament_id` = :tournament_id AND `homeTeam_id` = :homeTeam_id AND `awayTeam_id` = :awayTeam_id');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'homeTeam_id' => $homeID,
            'awayTeam_id' => $awayID,
            'status' => $status
        ]);
    }

    public function eachTournaments(string $status)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament` WHERE `status` = :status ORDER BY id DESC');
        $statement->execute([
            'status' => $status
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eachPlayers($tournamentID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `player` WHERE `tournament_id` = :tournamentID');
        $statement->execute([
            "tournamentID" => $tournamentID
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tournamentByID(int $tournamentID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `tournament` WHERE `id` = :tournamentID');
        $statement->execute([
            'tournamentID' => $tournamentID
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function addPlayerToTeam(int $tournamentID, int $teamID, string $user_function, int $user_id)
    {
        $statement = $this->pdo->prepare('INSERT INTO `player`(`tournament_id`, `team`, `user_function`, `user_id`) VALUES (:tournamentID, :teamID, :user_function, :user_id)');
        $statement->execute([
            'tournamentID' => $tournamentID,
            'teamID' => $teamID,
            'user_function' => $user_function,
            'user_id' => $user_id
        ]);
    }

    public function removePlayerFromTournament(int $tournamentID, int $user_id)
    {
        $statement = $this->pdo->prepare('DELETE FROM `player` WHERE `tournament_id` = :tournamentID AND `user_id` = :user_id');
        $statement->execute([
            'tournamentID' => $tournamentID,
            'user_id' => $user_id
        ]);
    }

    public function tournamentGoalsByID(int $tournamentID, int $homeID, int $awayID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `goal` WHERE `tournament_id` = :tournamentID AND `home_id` = :home_id AND `away_id` = :away_id');
        $statement->execute([
            'tournamentID' => $tournamentID,
            'home_id' => $homeID,
            'away_id' => $awayID
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertGoal(int $tournamentID, int $homeID, int $awayID, string $teamScore, int $homeGoals, int $awayGoals)
    {
        $statement = $this->pdo->prepare('INSERT INTO `goal`(`tournament_id`, `home_id`, `away_id`, `teamScore`, `home_goals`, `away_goals`) VALUES (:tournament_id, :home_id, :away_id, :teamScore, :home_goals, :away_goals)');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'home_id' => $homeID,
            'away_id' => $awayID,
            'teamScore' => $teamScore,
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals
        ]);
    }

    public function updateMatchScore(int $tournamentID, int $homeID, int $awayID, int $homeGoals, int $awayGoals){
        $statement = $this->pdo->prepare('UPDATE `match` SET `home_goals` = :home_goals, `away_goals` = :away_goals WHERE `tournament_id` = :tournament_id AND `homeTeam_id` = :homeTeam_id AND `awayTeam_id` = :awayTeam_id');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'homeTeam_id' => $homeID,
            'awayTeam_id' => $awayID,
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals
        ]);
    }

    public function insertTeamIntoTournamentTable(int $tournamentID, int $teamID)
    {
        $statement = $this->pdo->prepare('INSERT INTO `table`(`tournament_id`, `team_id`, `goalsFor`, `goalsAgainst`, `played`, `win`, `lose`, `points`) VALUES (:tournament_id, :team_id, :goalsFor, :goalsAgainst, :played, :win, :lose, :points)');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'team_id' => $teamID,
            'goalsFor' => 0,
            'goalsAgainst' => 0,
            'played' => 0,
            'win' => 0,
            'lose' => 0,
            'points' => 0
        ]);
    }

    public function tournamentTableByID($tournamentID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `table` WHERE `tournament_id` = :tournament_id ORDER BY `points` DESC, `goalsFor` DESC');
        $statement->execute([
            'tournament_id' => $tournamentID
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTeamStatsTable($tournamentID, $teamID, $goalsFor, $goalsAgainst, $played, $win, $lose, $points)
    {
        $statement = $this->pdo->prepare('UPDATE `table` SET `goalsFor` = :goalsFor, `goalsAgainst` = :goalsAgainst, `played` = :played, `win` = :win, `lose` = :lose, `points` = :points WHERE `tournament_id` = :tournament_id AND `team_id` = :team_id');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'team_id' => $teamID,
            'goalsFor' => $goalsFor,
            'goalsAgainst' => $goalsAgainst,
            'played' => $played,
            'win' => $win,
            'lose' => $lose,
            'points' => $points
        ]);
    }

    public function teamStatsByID($tournamentID, $teamID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM `table` WHERE `tournament_id` = :tournament_id AND `team_id` = :team_id');
        $statement->execute([
            'tournament_id' => $tournamentID,
            'team_id' => $teamID
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
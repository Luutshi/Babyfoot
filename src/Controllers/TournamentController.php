<?php

namespace Mvc\Controllers;

use Config\Controller;
use Mvc\Models\TournamentModel;
use Mvc\Models\UserModel;

class TournamentController extends Controller
{
    private TournamentModel $tournamentModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->tournamentModel = new TournamentModel();
        $this->userModel = new UserModel();
        parent::__construct();
    }

    public function addTournament()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['numberOfTeam']) &&
            $_POST['numberOfTeam'] <= 8 && $_POST['numberOfTeam'] >= 3)
        {
            $this->tournamentModel->createTournament($_SESSION['user']['nickname'], $_POST['name'], $_POST['description'], $_POST['numberOfTeam']);

            header('Location: /');
            exit;
        }
        echo $this->twig->render('Tournaments/createTournament.html.twig');
    }

    public function listTournament()
    {
        $availablePlaces = [];

        $beforeTournaments = $this->tournamentModel->eachTournaments('before');
        $pendingTournaments = $this->tournamentModel->eachTournaments('pending');
        $finishedTournaments = $this->tournamentModel->eachTournaments('finished');

        foreach($beforeTournaments as $tournament)
        {
            $availablePlaces[$tournament['id']] = $this->tournamentModel->eachPlayers($tournament['id']);
        }

        echo $this->twig->render('Home/home.html.twig', [
            'beforeTournaments' => $beforeTournaments,
            'availablePlaces' => $availablePlaces,
            'pendingTournaments' => $pendingTournaments,
            'finishedTournaments' => $finishedTournaments
        ]);
    }

    public function tournament()
    {
        $tournament = $this->tournamentModel->tournamentByID($_GET['id']);

        if ($tournament) {
            if ($tournament['status'] === 'before') {
                $players = $this->tournamentModel->eachPlayers($_GET['id']);

                $teams = [];
                for ($i = 1; $i <= $tournament['numberOfTeam']; $i++)
                {
                    $teams[$i]['attaquant'] = null;
                    $teams[$i]['defenseur'] = null;

                    foreach($players as $player)
                    {
                        if ($player['team'] == $i) {
                            $user = $this->userModel->findOneByID($player['user_id']);
                            $teams[$i][$player['user_function']] = $user['nickname'];
                        }
                    }
                }

                echo $this->twig->render('Tournaments/beforeTournament.html.twig', [
                    'tournament' => $tournament,
                    'teams' => $teams
                ]);
            } elseif ($tournament['status'] === 'pending' || $tournament['status'] === 'finished') {
                $matches = $this->tournamentModel->eachMatches($_GET['id']);
                $table = $this->tournamentModel->tournamentTableByID($_GET['id']);

                echo $this->twig->render('Tournaments/pendingFinishedTournament.html.twig', [
                    'tournament' => $tournament,
                    'matches' => $matches,
                    'table' => $table
                ]);
            }
        } else {
            header('Location: /');
            exit;
        }
    }

    public function joinTeam()
    {
        $players = $this->tournamentModel->eachPlayers($_GET['tournamentID']);
        $tournament = $this->tournamentModel->tournamentByID($_GET['tournamentID']);

        if ($tournament['status'] === 'before') {
            $valid = true;
            foreach ($players as $player) {
                if ($player['team'] === $_GET['teamID'] && $player['user_function'] === $_GET['user_function']) {
                    $valid = false;
                }
            }

            $teams = [];
            for ($i = 1; $i <= $tournament['numberOfTeam']; $i++)
            {
                $teams[$i]['attaquant'] = null;
                $teams[$i]['defenseur'] = null;

                foreach($players as $player)
                {
                    if ($player['team'] == $i) {
                        $user = $this->userModel->findOneByID($player['user_id']);
                        $teams[$i][$player['user_function']] = $user['nickname'];
                    }
                }
            }

            if ($valid === true) {
                foreach ($players as $player)
                {
                    if ($_SESSION['user']['id'] == $player['user_id']) {
                        $this->tournamentModel->removePlayerFromTournament($_GET['tournamentID'], $_SESSION['user']['id']);
                    }
                }
                $this->tournamentModel->addPlayerToTeam($_GET['tournamentID'], $_GET['teamID'], $_GET['user_function'], $_SESSION['user']['id']);
                $players = $this->tournamentModel->eachPlayers($_GET['tournamentID']);

                if (count($players) === $tournament['numberOfTeam']*2) {
                    $this->tournamentModel->changeTournamentStatus($_GET['tournamentID'], 'pending');

                    for($i = 1; $i <= $tournament['numberOfTeam']; $i++) {
                        $this->tournamentModel->insertTeamIntoTournamentTable($_GET['tournamentID'], $i);
                    }

                    $matches = [];
                    for ($home = 1; $home <= count($teams); $home++) {
                        for ($away = 1; $away <= count($teams); $away++) {
                            if($home !== $away) {
                                $matches[] = [
                                    'homeID' => $home,
                                    'awayID' => $away
                                ];
                            }
                        }
                    }

                    shuffle($matches);

                    foreach($matches as $match) {
                        $this->tournamentModel->insertMatch($_GET['tournamentID'], $match['homeID'], $match['awayID']);
                    }
                }
                header('Location: /tournament?id='.$_GET['tournamentID']);
            }
        } else {
            header('Location: /tournament?id='.$_GET['tournamentID']);
        }
    }

    public function activeMatch()
    {
        $match = $this->tournamentModel->matchByTournamentHomeAwayID($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID']);
        $tournament = $this->tournamentModel->tournamentByID($match['tournament_id']);

        if ($match && $_SESSION['user']['nickname'] === $tournament['creator']) {
            $this->tournamentModel->changeMatchStatus($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], 'pending');
        }
        header('Location: /tournament?id='.$_GET['tournamentID']);
    }

    public function match()
    {
        $match = $this->tournamentModel->matchByTournamentHomeAwayID($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID']);
        $tournament = $this->tournamentModel->tournamentByID($_GET['tournamentID']);
        $goals = $this->tournamentModel->tournamentGoalsByID($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID']);

        if ($match['status'] === 'before') {
            header('Location: /tournament?id='.$_GET['tournamentID']);
        } else {
            echo $this->twig->render('Tournaments/match.html.twig', [
                'match' => $match,
                'tournament' => $tournament,
                'goals' => $goals
            ]);

            if ($tournament['status'] === 'pending') {
                $matches = $this->tournamentModel->eachMatches($_GET['tournamentID']);

                $valid = true;
                foreach($matches as $match) {
                    if ($match['status'] !== 'finished') {
                        $valid = false;
                    }
                }

                if ($valid) {
                    $this->tournamentModel->changeTournamentStatus($_GET['tournamentID'], 'finished');
                }
            }
        }
    }

    public function addGoal()
    {
        $tournament = $this->tournamentModel->tournamentByID($_GET['tournamentID']);
        $match = $this->tournamentModel->matchByTournamentHomeAwayID($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID']);

        if ($match['status'] === 'pending') {
            if ($tournament && $tournament['creator'] === $_SESSION['user']['nickname']) {
                if ($_GET['teamScore'] === 'home') {
                    $this->tournamentModel->insertGoal($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], $_GET['teamScore'],$match['home_goals']+1, $match['away_goals']);
                    $this->tournamentModel->updateMatchScore($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], $match['home_goals']+1, $match['away_goals']);
                } elseif ($_GET['teamScore'] === 'away') {
                    $this->tournamentModel->insertGoal($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], $_GET['teamScore'], $match['home_goals'], $match['away_goals']+1);
                    $this->tournamentModel->updateMatchScore($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], $match['home_goals'], $match['away_goals']+1);
                }

                $match = $this->tournamentModel->matchByTournamentHomeAwayID($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID']);
                if ($match['home_goals'] >= 10 || $match['away_goals'] >= 10) {
                    $this->tournamentModel->changeMatchStatus($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], 'finished');

                    $homeData = $this->tournamentModel->teamStatsByID($_GET['tournamentID'], $match['homeTeam_id']);
                    $awayData = $this->tournamentModel->teamStatsByID($_GET['tournamentID'], $match['awayTeam_id']);

                    if ($match['home_goals'] >= 10) {
                        $this->tournamentModel->updateTeamStatsTable($_GET['tournamentID'], $match['homeTeam_id'], $homeData['goalsFor']+$match['home_goals'], $homeData['goalsAgainst']+$match['away_goals'], $homeData['played']+1, $homeData['win']+1, $homeData['lose'], $homeData['points']+3);
                        $this->tournamentModel->updateTeamStatsTable($_GET['tournamentID'], $match['awayTeam_id'], $awayData['goalsFor']+$match['away_goals'], $awayData['goalsAgainst']+$match['home_goals'], $awayData['played']+1, $awayData['win'], $awayData['lose']+1, $awayData['points']);
                    } else {
                        $this->tournamentModel->updateTeamStatsTable($_GET['tournamentID'], $match['homeTeam_id'], $homeData['goalsFor']+$match['home_goals'], $homeData['goalsAgainst']+$match['away_goals'], $homeData['played']+1, $homeData['win'], $homeData['lose']+1, $homeData['points']);
                        $this->tournamentModel->updateTeamStatsTable($_GET['tournamentID'], $match['awayTeam_id'], $awayData['goalsFor']+$match['away_goals'], $awayData['goalsAgainst']+$match['home_goals'], $awayData['played']+1, $awayData['win']+1, $awayData['lose'], $awayData['points']+3);
                    }
                }
            }
        }

        header('Location: /match?tournamentID='.$_GET['tournamentID'].'&homeID='.$_GET['homeID'].'&awayID='.$_GET['awayID']);
    }
}
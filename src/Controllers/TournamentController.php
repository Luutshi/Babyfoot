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

        foreach($beforeTournaments as $tournament)
        {
            $availablePlaces[$tournament['id']] = $this->tournamentModel->eachPlayers($tournament['id']);
        }

        foreach($beforeTournaments as $tournament)
        {
            $availablePlaces[$tournament['id']] = $this->tournamentModel->eachPlayers($tournament['id']);
        }

        echo $this->twig->render('Home/home.html.twig', [
            'beforeTournaments' => $beforeTournaments,
            'availablePlaces' => $availablePlaces,
            'pendingTournaments' => $pendingTournaments
        ]);
    }

    public function tournament()
    {
        $tournament = $this->tournamentModel->tournamentByID($_GET['id']);

        if ($tournament) {
            if ($tournament['tournamentStatus'] === 'before') {
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
            } elseif ($tournament['tournamentStatus'] === 'pending') {
                $matches = $this->tournamentModel->eachMatches($_GET['id']);

                echo $this->twig->render('Tournaments/pendingTournament.html.twig', [
                    'tournament' => $tournament,
                    'matches' => $matches
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

        if ($tournament['tournamentStatus'] === 'before') {
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

        if ($match) {
            $this->tournamentModel->changeMatchStatus($_GET['tournamentID'], $_GET['homeID'], $_GET['awayID'], 'pending');
        }
        header('Location: /tournament?id='.$_GET['tournamentID']);
    }
    public function match()
    {
        echo('match');
    }
}
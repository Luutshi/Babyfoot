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

        $tournaments = $this->tournamentModel->eachTournaments();

        foreach($tournaments as $tournament)
        {
            $availablePlaces[$tournament['id']] = $this->tournamentModel->eachPlayers($tournament['id']);
        }

        echo $this->twig->render('Home/home.html.twig', [
            'tournaments' => $tournaments,
            'availablePlaces' => $availablePlaces
        ]);
    }

    public function joinTournament()
    {
        $tournament = $this->tournamentModel->tournamentByID($_GET['id']);

        if ($tournament) {
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

            echo $this->twig->render('Tournaments/joinTournament.html.twig', [
                "tournament" => $tournament,
                "teams" => $teams
            ]);
        } else {
            header('Location: /');
            exit;
        }
    }

    public function joinTeam()
    {
        $players = $this->tournamentModel->eachPlayers($_GET['tournamentID']);

        $valid = true;
        foreach ($players as $player) {
            if ($player['team'] === $_GET['teamID'] && $player['user_function'] === $_GET['user_function']) {
                $valid = false;
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
        }

        header('Location: ../joinTournament?id='.$_GET['tournamentID']);
    }
}
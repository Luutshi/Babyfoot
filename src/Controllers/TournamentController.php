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
        $tournaments = $this->tournamentModel->eachTournaments();

        echo $this->twig->render('Home/home.html.twig', [
            'tournaments' => $tournaments,
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

        dump($tournament);
        dump($teams);
    }

}
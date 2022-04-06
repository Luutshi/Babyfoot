<?php

namespace Mvc\Controllers;

use Config\Controller;
use Mvc\Models\TournamentModel;

class TournamentController extends Controller
{
    private TournamentModel $tournamentModel;

    public function __construct()
    {
        $this->tournamentModel = new TournamentModel();
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
            'tournaments' => $tournaments
        ]);
    }

    public function joinTournament()
    {
        $tournament = $this->tournamentModel->tournamentByID($_GET['id']);

        if ($tournament) {
            echo $this->twig->render('Tournaments/joinTournament.html.twig', [
                "tournament" => $tournament
            ]);
        } else {
            header('Location: /');
            exit;
        }

        var_dump($tournament);
    }

}
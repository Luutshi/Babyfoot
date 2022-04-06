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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tournamentName']))
        {
            $this->tournamentModel->createTournament($_SESSION['user']['nickname'], $_POST['tournamentName'], $_POST['tournamentDescription']);

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
        dump($tournaments);
    }

    public function joinTournament()
    {
        $doesExist = $this->tournamentModel->tournamentByID($_GET['id']);

        if ($doesExist) {
            echo $this->twig->render('Tournaments/joinTournament.html.twig');
        } else {
            header('Location: /');
            exit;
        }

        dump($doesExist);
    }

}